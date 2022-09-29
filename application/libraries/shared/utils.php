<?php
namespace Shared;

use Framework\{Registry, ArrayMethods, RequestMethods};

class Utils {
	
	

	public static function getCurrentUser() {
		$controller = Registry::get("controller");
		return $controller->user;
	}

	/**
	 * Check whether the User is Accessing the webapp from a fixed IP
	 * @param string $ip Current IP of the connecting User
	 * @param  mixed $var Variable to be debuged
	 * @return boolean      if IP is debugging IP
	 */
	public static function debugMode($ip = null, $var = null) {
		if (is_null($ip)) {
			$ip = RequestMethods::getIp();
		}
		$debugIps = static::getConfig('app')->app->debug_mode->ips;
		$debugIps = explode(",", $debugIps);
		$ipsList = array_merge(['14.141.173.170', '14.102.189.183', '14.102.189.141'], $debugIps);
		if (in_array($ip, $ipsList)) {
			if (!is_null($var)) {
				var_dump($var);	
			}
			return true;
		}
		return false;
	}

	

	/**
	 * Get Class of an object
	 * @param  object  $obj  Any object
	 * @param  boolean $full Whether full name is required (with namespace as prefix)
	 * @return string        Name of the class of the object
	 */
	public static function getClass($obj, $full = false) {
		$cl = get_class($obj);

		if (!$full) {
			$parts = explode("\\", $cl);
			$cl = array_pop($parts);
		}
		return $cl;
	}

	/**
	 * Converts the object to string by using 'sprintf'
	 * @param  mixed $field Can be any thing which is needed in string format
	 * @return string
	 */
	public static function getMongoID($field) {
		if (is_object($field)) {
			$id = sprintf('%s', $field);
		} else {
			$id = $field;
		}
		return $id;
	}

	/**
	 * Capture the output of var_dump in a string and return it
	 * !!!!!! Use CAREFULLY !!!!!!
	 * @param  mixed $var Variable to be debuged
	 * @return string
	 */
	public static function debugVar($var) {
		ob_start();
		var_dump($var);
		$result = ob_get_clean();
		return $result;
	}

	/**
	 * Set a message to Session that will only be displayed once
	 * @param  string $msg Message to display
	 * @return null
	 */
	public static function flashMsg($msg) {
		$session = Registry::get("session");
		$session->set('$flashMessage', $msg);
	}

	/**
	 * Converts the string to a valid BSON ObjectID of 24 characters or if $id -> string
	 * else if $id -> array recursively converts each id to bson objectId
	 * 
	 * @param  string|object|array $id ID to converted to bson type
	 * @return string|object|array (objects)     Returns an BSON ObjectID if $id is valid else empty string
	 */
	public static function mongoObjectId($id) {
		$result = "";
		try {
			if (is_array($id)) {
				$result = [];
				foreach ($id as $i) {
					$result[] = self::mongoObjectId($i);
				}
			} else if (!Services\Db::isType($id, 'id')) {
				if (strlen($id) === 24) {
					$result = new \MongoDB\BSON\ObjectID($id);	
				} else if (is_null($id)) {
					$result = null;
				} else {
					$result = "";
				}
	        } else {
	        	$result = $id;
	        }
		} catch (\Exception $e) {
			$result = "";
		}
        return $result;
	}

	/**
	 * Function to check whether a file exist in Bucket or not
	 * @return boolean
	 */
	public static function fileExistInBucket($name, $opts = []) {
		try {
			$url = static::media($name, 'display', array_merge($opts, ['useBucket' => true]));
			$client = static::getGuzzleClient(20);
			$resp = $client->request('HEAD', static::fixUrl($url));
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	public static function streamFileTemporary($url = null, $opts = []) {
		// TODO: Update this when upgrading to >= PHP8
		$phpVerArr = explode(".", phpversion()); $phpMinorVer = $phpVerArr[1];
		if (!$url) return false;
		try {
			$type = $opts['type'] ?? 'file';
			$appendExtension = $opts['appendExtension'] ?? '';
			$tempFilePath = self::media(uniqid(), 'path', ['type' => $type]);
			if ($appendExtension) {
				$tempFilePath = sprintf("%s.%s", $tempFilePath, $appendExtension);
			}
			
			$file = fopen($tempFilePath, "w");
			if (!$file) throw new \Exception("Error opening temporary file!!");
			$tempFile = stream_get_meta_data($file)['uri'];

			if (($opts['useBucket'] ?? false) && (isset($opts['fileName']) && $opts['fileName'])) {
				$objectName = sprintf("%ss/%s", $type, $opts['fileName']);
				$bucketObj = new \Media\Bucket\Obj($objectName);
				file_put_contents($tempFile, $bucketObj->getContents());
			} else {
				$client = static::getGuzzleClient(20);
				$resp = $client->request('GET', static::fixUrl($url), ['sink' => $tempFile]);
			}
			$contentType = mime_content_type($tempFile);
			
			if ($appendExtension && in_array($appendExtension, ['json', 'ndjson']) && $phpMinorVer == '4') {
				preg_match('/application\/(.*)/i', $contentType, $matches);
			} else {
				preg_match('/text\/(.*)/i', $contentType, $matches);
			}
			if (!isset($matches[1])) throw new \Exception("Mime Content Type Regex not Match!!");

			$extension = $matches[1];

			$allowedExtension = $opts['extension'] ?? 'csv|plain|html|json';
			if (!preg_match('/^'.$allowedExtension.'$/', $extension)) throw new \Exception("Extension is not supported!!");
			chmod($tempFile, 0664);
		} catch (\Exception $e) {
			if (isset($file) && $file) fclose($file);
			if (isset($tempFilePath) && $tempFilePath) unlink($tempFilePath);
			return false;
		}
		fclose($file);
		return $tempFile;
	}

	/**
	 * Downloads the Image From the URL by checking its Content Type and matching against
	 * the valid image content types defined by the standard. Image is stored into
	 * the uploads directory
	 * @param  string $url URL of the image
	 * @return string|boolean      FALSE on failure else uploaded image name
	 */
	public static function downloadImage($url = null, $opts = []) {
		if (!$url) { return false; }
		try {
			$file = tmpfile();
			if ($file == false) {
				return false;
			}
			$tempFile = stream_get_meta_data($file)['uri'];

			$client = static::getGuzzleClient(4);
			$resp = $client->request('GET', static::fixUrl($url), ['sink' => $tempFile]);
			$fsize = filesize($tempFile);
			$maxFileSize = $opts['maxFileSize'] ?? 0;
			if ($maxFileSize && $fsize && $fsize >= $maxFileSize) {
				return false;
			}


			$contentType = mime_content_type($tempFile);
			preg_match('/image\/(.*)/i', $contentType, $matches);
			if (!isset($matches[1])) {
				fclose($file);
				return false;
			} else {
				$extension = $matches[1];
			}

		} catch (\Exception $e) {
			return false;
		}

		$allowedExtension = $opts['extension'] ?? 'jpe?g|gif|bmp|png|ico|tif';
		if (!preg_match('/^'.$allowedExtension.'$/', $extension)) {
			fclose($file);
			return false;
		}

		$path = APP_PATH . '/public/assets/uploads/images/';
		if (isset($opts['name'])) {
			$img = $opts['name'];
		} else {
			$img = uniqid() . ".{$extension}";
		}
		$status = rename($tempFile, $path . $img);
		fclose($file);
		if ($status === false) {
			return false;
		}
		return $img;
	}

	public static function getGuzzleClient($timeout = null) {
		$opts = [];
		if ($timeout) {
			$opts['timeout'] = $timeout;
		}
		$client = new \GuzzleHttp\Client($opts);
		return $client;
	}

	public static function fixUrl($url) {
		$urlComponents = \League\Uri\Parse($url);
		$urlComponents['path'] = str_replace(' ', '%20', $urlComponents['path']);
		$url = \League\Uri\build($urlComponents);
		return $url;
	}

	public static function downloadZip($url, $opts = []) {
		$file = tmpfile();
		if ($file == false || !$url) {
			return false;
		}
		$tempFile = stream_get_meta_data($file)['uri'];

		try {
			$client = static::getGuzzleClient(10);
			$resp = $client->request('GET', static::fixUrl($url), ['sink' => $tempFile]);
		} catch (\Exception $e) {
			return false;
		}

		chmod($tempFile, 0664);
		chgrp($tempFile, 'www-data');

		$mimes = new \Mimey\MimeTypes;
		$extension = $mimes->getExtension(mime_content_type($tempFile));
		$allowedExtension = $opts['extension'] ?? 'jpe?g|gif|bmp|png|ico|tif|zip';
		if (!preg_match('/^'.$allowedExtension.'$/', $extension)) {
			fclose($file);
			return false;
		}
		$path = APP_PATH . '/public/assets/uploads/images/';
		if (isset($opts['name'])) {
			$img = $opts['name'];
		} else {
			$img = uniqid() . ".{$extension}";
		}

		$status = rename($tempFile, $path . $img);
		fclose($file);
		if ($status === false) {
			return false;
		}
		return $img;
	}

	public static function particularFields($field) {
	    switch ($field) {
	        case 'name':
	            $type = 'text';
	            break;
	        
	        case 'password':
	            $type = 'password';
	            break;

	        case 'email':
	            $type = 'email';
	            break;

	        case 'phone':
	            $type = "text";
	            break;

	        default:
	            $type = 'text';
	            break;
	    }
	    return array("type" => $type);
	}

	public static function parseValidations($validations) {
	    $html = ''; $pattern = '';
	    foreach ($validations as $key => $value) {
	        preg_match("/(\w+)(\((\d+)\))?/", $value, $matches);
	        $type = isset($matches[1]) ? $matches[1] : 'none';
	        switch ($type) {
	            case 'required':
	                $html .= ' required="" ';
	                break;
	            
	            case 'max':
	                $html .= ' maxlength="' .$matches[3] . '" ';
	                break;

	            case 'min':
	                $pattern .= ' pattern="(.){' . $matches[3] . ',}" ';
	                break;
	        }
	    }
	    return array("html" => $html, "pattern" => $pattern);
	}

	
	/**
	 * @deprecated should not be used
	 * @param  boolean $numbers Whether numbers are required in the string
	 * @return string           [a-zA-Z0-9]+
	 */
	public static function randomPass($numbers = true) {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

		if (!$numbers) {
			$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		}
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
	}

	public static function urlRegex($url) {
		$regex = "((https?|ftp)\:\/\/)"; // SCHEME
        $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
        $regex .= "([a-z0-9-.]*)\.([a-z]{2,4})"; // Host or IP
        $regex .= "(\:[0-9]{2,5})?"; // Port
        $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
        $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
        $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor

        $result = preg_match('/^'.$regex.'$/', $url);
        return (boolean) $result;
	}

	/**
	 * Converts dates to be passed for mongodb query
	 * @return array       mongodb start and end date
	 */
	public static function dateQuery($dateQ, $endDate = null) {
		if (!is_array($dateQ)) {
            $opts = ['start' => $dateQ, 'end' => $endDate];
        } else {
            $opts = $dateQ;
        }
        
        $startDt = new \DateTime(); $endDt = new \DateTime();
		$tz = new \DateTimeZone('UTC');
		$startDt->setTimezone($tz);
		$endDt->setTimezone($tz);
       

        $start = strtotime("-1 day"); $end = strtotime("+1 day");
        if (isset($opts['start']) && is_string($opts['start'])) {
            $start = (int) strtotime($opts['start'] . ' 00:00:00'); // this returns in seconds
        }

        if (isset($opts['end']) && is_string($opts['end'])) {
            $end = (int) strtotime($opts['end'] . ' 23:59:59');
        }

        $startDt->setTimestamp($start - $startDt->getOffset());
        $endDt->setTimestamp($end - $endDt->getOffset());

        $startTimeStamp = $startDt->getTimestamp() * 1000;
        $endTimeStamp = $endDt->getTimestamp() * 1000 + 999;

        return [
            'start' => new \MongoDB\BSON\UTCDateTime($startTimeStamp),
            'end' => new \MongoDB\BSON\UTCDateTime($endTimeStamp)
        ];
	}

	/**
	 * Convert an object to array recursively
	 * @param  object $object Object derived from simple class that can be used as array in foreach
	 * @return array         Final Array
	 */
	public static function toArray($object) {
		$arr = [];
		$obj = (array) $object;
		foreach ($obj as $key => $value) {
			if (Services\Db::isType($value, 'document')) {
				$arr[$key] = self::toArray($value);
			} else {
				$arr[$key] = $value;
			}
		}
		return $arr;
	}

	public static function mongoRegex($val) {
		return new \MongoDB\BSON\Regex($val, 'i');
	}

	public static function dateArray($arr) {
		$result = [];
		foreach ($arr as $key => $value) {
			$date = \Framework\StringMethods::only_date($key);
			$result[$date] = $value;
		}
		return $result;
	}

	/**
	 * Returns a string if the URL has parameters or NULL if not
	 * @return string
	 */
	public static function addURLParams($linkUrl, $string) {
		if (parse_url($linkUrl, PHP_URL_QUERY)) {
			$append = '&'.$string;
		} else {
			$append = '?'.$string;
		}
		return $append;
	}

	/**
	 * [PUBLIC] This function will return url with unique query parameters
	 * @param $url 			String
	 * @param $appendStr 	String
	 * 
	 * @return $url 		String (Url with unique query params)
	 */ 
	public static function addURLWithUniqueQueryParams($url, $appendStr) {
		parse_str($appendStr, $macros);
		$urlComponents = \League\Uri\Parse($url);
		parse_str($urlComponents['query'], $queryParams);
		$queryParams = array_merge($queryParams, $macros);
		$urlComponents['query'] = urldecode(http_build_query($queryParams));
		$url = \League\Uri\Build($urlComponents);
		return $url;
	}

	public static function getConfig($name, $property = null) {
		$config = Registry::get("configuration")->parse("configuration/{$name}");

		if ($property && property_exists($config, $property)) {
			return $config->$property;
		}
		return $config;
	}

	public function getAssetsVersion($type, $name) {
		$key = APP_PATH . "ASSETS_VERSION";
		$version = Utils::getCache($key);
		if (is_null($version)) {
			$filePath = APP_PATH . "/assetsVersion.json";
			$version = json_decode(file_get_contents($filePath));
			static::setCache($key, $version, 60);
		}
		return $version->$type->$name;
	}

	public static function getAppConfig() {
		return static::getConfig('app')->app;
	}

	/**
	 * Uploads the image sent by the user in $_FILES array when submitting
	 * the form using file-upload. Assigns a name to the file and also checks
	 * for a valid file extension based on the type (if provided)
	 * 
	 * @return string|boolean      FALSE on failure else uploaded image name
	 */
	public static function uploadImage($name, $type = "images", $opts = []) {
		if (!isset($opts['extension'])) {
			$opts['extension'] = 'jpe?g|gif|bmp|png|ico|tif';
		}
	    return static::_uploadFile($name, $type, $opts);
	}

	public static function uploadFileObj($file, $type, $opts) {
		$path = APP_PATH . "/public/assets/uploads/{$type}/";
		$extension = pathinfo($file["name"], PATHINFO_EXTENSION);

		$extensionRegex = $opts['extension'];
		if (!preg_match("/^".$extensionRegex."$/i", $extension)) {
		    return false;
		}

		if (isset($opts['name'])) {
		    $filename = $opts['name'];
		} else {
		    $filename = uniqid() . ".{$extension}";
		}
		$isRename = $opts['is_rename'] ?? false;
		if ($isRename) {
			if (rename($file["tmp_name"], $path . $filename)) {
				return $filename;
			}
			return false;
		}

		if (move_uploaded_file($file["tmp_name"], $path . $filename)) {
		    return $filename;
		}
		return false;
	}

	protected static function _uploadFile($name, $type, $opts) {
		if (isset($_FILES[$name])) {
	        $file = $_FILES[$name];
	        return static::uploadFileObj($file, $type, $opts);
	    }
	    return false;
	}

	/**
	 * Download Video from a URL
	 * @param  string $url  Youtube URL
	 * @param  array  $opts Array of Options, Keys-> ('extension', 'quality')
	 * @return false|string       False on failure, string -> name of the newly created file
	 */
	public static function downloadVideo($url, $opts = []) {
		$folder = self::media('', 'show', ['type' => 'video']);
		$extension = $opts['extension'] ?? 'mp4';
		try {
			$ytdl = new \YTDownloader\Service\Download($url, [
				'path' => $folder
			]);
			$file = $ytdl->convert($extension, [
				'type' => 'video',
				'quality' => $opts['quality'] ?? '240p'
			]);

			$name = uniqid() . ".{$extension}";
			copy($folder . $file, $folder . $name);
			unlink($folder . $file);
		} catch (\Exception $e) {
			$name = false;
		}
		return $name;
	}

	public static function getRedis() {
		return Registry::get("redis");
	}

	public static function getRedisService() {
		$redis = static::getRedis();
		return $redis->getService();
	}

	/**
	 * Set Cache in Memcache
	 * @param string  $key      Name of the Key
	 * @param mixed  $value    The value to be stored
	 * @param integer $duration No of seconds for which the value should be stored
	 */
	public static function setCache($key, $value, $duration = 300) {
        /** @var \Framework\Cache\Driver\Memcached $memCache */
		$memCache = Registry::get("cache");
		return $memCache->set($key, $value, $duration);
	}

	/**
	 * Get Cache Value from the key
	 * @param  string $key Name of the key
	 * @return mixed      Corresponding value in the key
	 */
	public static function getCache($key, $default = null) {
	    /** @var \Framework\Cache\Driver\Memcached $memCache */
		$memCache = Registry::get("cache");
		return $memCache->get($key, $default);
	}

	public static function removeCache($key) {
		/** @var \Framework\Cache\Driver\Memcached $memCache */
		$memCache = Registry::get("cache");
		return $memCache->erase($key);
	}

	public static function removeModelCache($model, $query, $fields = []) {
		$m = "\\$model";
		$cacheKey = $m::getCacheKey($query, $fields);
		$dbCacheKey = Services\Db::getCacheKey($model, $query, $fields);
		static::removeCache($cacheKey);
		static::removeCache($dbCacheKey);
	}

	public static function getSmartCache($date, $resourceUid) {
		$cacheKey = sprintf("Date:%s_ID:%s", $date, $resourceUid);
		return static::getCache($cacheKey);
	}

	public static function setSmartCache($date, $resourceUid, $resource) {
		$cacheKey = sprintf("Date:%s_ID:%s", $date, $resourceUid);
		static::setCache($cacheKey, $resource, 86400);
	}

	/**
	 * Group an array of objects with a key
	 * @param  array $objArr  Array of objects
	 * @param  string $groupBy Group By Key
	 * @return array          Modified Array
	 */
	public static function groupBy($objArr, $groupBy) {
		$result = [];
		try {
			foreach ($objArr as $key => $value) {
				if (!is_object($value)) {
					continue;
				}

				$newKey = $value->$groupBy ?? 'Empty';
				if (!isset($result[$newKey])) {
					$result[$newKey] = [];
				}
				$result[$newKey][] = $value;
			}
		} catch (\Exception $e) {
			// log the exception
		}

		return $result;
	}

	public static function uploadFile($name, $folder = "files", $opts = []) {
		if (!isset($opts['extension'])) {
			$opts['extension'] = 'tar|zip|pdf|txt|csv';
		}
		return static::_uploadFile($name, $folder, $opts);
	}

	public static function mailError(Organization $org, $e, $opts = []) {
		$mode = php_sapi_name() == 'cli' ? 'CLI' : 'WEBAPP';
		$msg = substr($e->getMessage(), 0, 100);
		$emails = $opts['emails'] ?? [];
		Mail::send([
		    'org' => $org,
		    'subject' => sprintf("%s Msg: %s", $mode, $msg),
		    'emails' => array_merge(['hemant.mann@trackier.com'], $emails),
		    'ex' => $e,
		    'template' => 'error'
		]);
	}

	/**
	 * Perform Various Media Related functions
	 * @param  string $name Name of the file - local, remote
	 * @param  string $task Name of the task to be performed on the file
	 * @param  array  $opts Keys -> ('extension', 'type', 'quality')
	 * @return mixed       Return type depending on the action performed
	 */
	public static function media($name, $task = 'show', $opts = []) {
		$type = ($opts['type']) ?? 'image';
		$folder = APP_PATH . "/public/assets/uploads/{$type}s/";
		switch ($task) {
			case 'remove':
				if ($type === 'image' && $name === Ad::NO_IMAGE) {
					break;	// dont delete it
				}
				@unlink($folder . $name);
				$removeFromBucket = $opts['removeFromBucket'] ?? true;
				if ($removeFromBucket) {
					$filename = sprintf("%ss/%s", $type, $name);
					$bucketObj = new \Media\Bucket\Obj($filename);
					$bucketObj->delete();
				}
				break;
			
			case 'show':
			case 'path':
				return $folder . $name;

			case 'getType':
				return mime_content_type($folder . $name);

			case 'upload':
				$func = "upload" . ucfirst($type);
				$media = self::$func($name, "{$type}s", $opts);
				if ($media === false) {
					$media = '';
				}
				$uploadToBucket = $opts['uploadToBucket'] ?? true;
				$removeAfterUpload = $opts['removeAfterUpload'] ?? false;
				if ($uploadToBucket && $media) {
					static::media($media, 'uploadToBucket', $opts);
					if ($removeAfterUpload) {
						static::media($media, 'remove', array_merge($opts, ['removeFromBucket' => false]));
					}
				}
				return $media;

			case 'uploadToBucket':
				$objectName = sprintf("%ss/%s", $type, $name);
				$filePath = static::media($name, 'show', $opts);
				$bucketObj = new \Media\Bucket\Obj($objectName);
				$bucketObj->upload($filePath);
				break;

			case 'download':
				$func = "download" . ucfirst($type);
				$media = self::$func($name, $opts);
				if ($media === false) {
					$media = '';
				}
				$uploadToBucket = $opts['uploadToBucket'] ?? true;
				if ($uploadToBucket && $media) {
					static::media($media, 'uploadToBucket', $opts);
				}
				return $media;

			case 'downloadZip':
				$media = self::downloadZip($name, $opts);
				if ($media === false) {
					$media = '';
				}
				$uploadToBucket = $opts['uploadToBucket'] ?? true;
				if ($uploadToBucket && $media) {
					static::media($media, 'uploadToBucket', $opts);
				}
				return $media;

			case 'display':
				$useBucket = $opts['useBucket'] ?? true;
				$customCdnDomain = $opts['cdn_domain'] ?? null;
				if ($useBucket) {
					$path = sprintf("%s%s/%s", $customCdnDomain ?? GCDN, "{$type}s", $name);
				} else {
					$path = sprintf("%suploads/{$type}s/%s", CDN, $name);
				}
				return $path;

			case 'dimensions':
				$type = static::media($name, 'getType');
				$info = @getimagesize($folder . $name);
				if (preg_match('/image/', $type) && $info !== false) {
					return [
						'width' => $info[0],
						'height' => $info[1]
					];
				} else {
					return [];
				}

			case 'sendFile':
				$useBucket = $opts['useBucket'] ?? false;
				if ($useBucket) {
					$file = sprintf("%simages/%s", GCDN, $name);
				} else {
					$file = $folder . $name;
				}
				$sendFile = $opts['send'] ?? false;
				if ($sendFile) {
					header('Content-Description: File Transfer');
					header(sprintf('Content-Disposition: attachment; filename="%s"', $name));
					if ($useBucket) {
						try {
							$client = static::getGuzzleClient(5);
							$resp = $client->request('GET', $file);
							echo $resp->getBody();
						} catch (\Exception $e) {
							// do nothing
						}
					} else {
						readfile($file);
					}
				} else {
					if ($useBucket) {
						try {
							$client = static::getGuzzleClient(5);
							$resp = $client->request('GET', $file);
							$fileContents = $resp->getBody();
						} catch (\Exception $e) {
							$fileContents = "";
						}
					} else {
						$fileContents = file_get_contents($file);
					}
					$utf8Clean = $opts['utf8_clean'] ?? true;
					if ($utf8Clean) {
						return \Framework\StringMethods::utf8Clean($fileContents);
					}
					return (string) $fileContents;
				}
				break;

			case 'getFileName':
				$fileName = $_FILES[$name]['name'];
				return basename($fileName);

			case 'exists':
				return file_exists($folder . $name);
		}
	}

	public static function convertSecondToDays($seconds) {
		// Days
		$days =(int) ($seconds / (24 * 3600));
		// Hours
		$seconds = $seconds % (24 * 3600);
		$hours = (int) ($seconds / 3600);
		// Minutes
		$seconds = $seconds % 3600;
		$minutes = (int) ($seconds / 60);
		// Seconds 
		$seconds = $seconds % 60;
		$stringFormat = '';
		if ($days > 0) {
			$stringFormat = sprintf("%d days ", $days);
		} elseif ($hours > 0) {
			$stringFormat = sprintf("%d hours ", $hours);	
		} elseif ($minutes > 0) {
			$stringFormat = sprintf("%d minutes ", $minutes);
		} elseif ($seconds > 0) {
			$stringFormat = sprintf("%d seconds ", $seconds);
		}
		return $stringFormat;
	}




	public static function isValidEmail($email) {
		return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) ? FALSE : TRUE;
	}

	
}
