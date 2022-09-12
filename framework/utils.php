<?php
namespace Framework;

use \Firebase\JWT\JWT;

class Utils {

	const GOOGLE_PLAY_HOST = "play.google.com";
	const APP_STORE_HOST = "itunes.apple.com";
	const MARKET_URL_HOST = "details";

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
	
	public static function getConfig($name, $property = null) {
		$config = Registry::get("configuration")->parse("configuration/{$name}");

		if ($property && property_exists($config, $property)) {
			return $config->$property;
		}
		return $config;
	}

	public static function getAppConfig() {
		return static::getConfig('app')->app;
	}

	public static function getRedis() {
		return Registry::get("redis");
	}
	
	public static function getRedisService() {
		$redis = Registry::getRedis();
		return $redis->getService();
	}

	public static function getJWTToken($orgId, $userId, $secret) {
		$payload = (object) [
			"org_id" => $orgId,
			'user_id' => $userId
		];
		return JWT::encode($payload, $secret);
	}

	/**
	 * Set Cache in Memcache
	 * @param string  $key      Name of the Key
	 * @param mixed  $value    The value to be stored
	 * @param integer $duration No of seconds for which the value should be stored
	 */
	public static function setCache($key, $value, $duration = 300) {
        /** @var \Framework\Cache\Driver\Memcached $memCache */
		$memCache = Registry::getCache();
		return $memCache->set($key, $value, $duration);
	}

	/**
	 * Get Cache Value from the key
	 * @param  string $key Name of the key
	 * @return mixed      Corresponding value in the key
	 */
	public static function getCache($key, $default = null) {
	    /** @var \Framework\Cache\Driver\Memcached $memCache */
		$memCache = Registry::getCache();
		return $memCache->get($key, $default);
	}

	public static function removeCache($key) {
		/** @var \Framework\Cache\Driver\Memcached $memCache */
		$memCache = Registry::getCache();
		return $memCache->erase($key);
	}

	public static function getSmartCache($date, $resourceUid) {
		$cacheKey = sprintf("Date:%s_ID:%s", $date, $resourceUid);
		return static::getCache($cacheKey);
	}

	public static function setSmartCache($date, $resourceUid, $resource) {
		$cacheKey = sprintf("Date:%s_ID:%s", $date, $resourceUid);
		static::setCache($cacheKey, $resource, 86400);
	}

	public static function decodeBase64Image($data, $validTypes = []) {
		if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
		    $data = substr($data, strpos($data, ',') + 1);
		    $type = strtolower($type[1]); // jpg, png, gif

		    if (! in_array($type, $validTypes)) {
		        throw new \Exception('Invalid Image Type');
		    }

		    $data = base64_decode($data);
		    if ($data === false) {
		        throw new \Exception('Base64 Decode failed');
		    }
		    return ['data' => $data, 'extension' => $type];
		} else {
		    throw new \Exception('Invalid Image Data');
		}
	}

	/**
	 * Calculate the difference between two numbers
	 * @param float $old Old value
	 * @param float $new New value
	 * @return float The Change in Number
	 */
	public static function Numdiff($old, $new) {
		if ($new == 0 && $old == 0) {
			return 0;
		}
		if (!is_numeric($old) || !is_numeric($new)) {
			return 0;
		}
		$diff = $new - $old;
		if ($old == 0) {
			return 'INF';
		}
		// decrease of 100 % percent
		if ($new == 0) {
			return -1;
		}
		if ($old == 0 || $new == 0) {
			return round($diff / 100, 4);
		}
		if ($diff != 0) {
			return round($diff / $old, 4);
		} else {
			return 0;
		}
	}

	public static function mapObject(array $mapping, $obj) {
		$newObj = [];
		foreach ($mapping as $key => $prop) {
			if (is_array($prop)) {
				$newObj[$key] = static::mapObject($prop, $obj);
			} else {
				$newObj[$key] = static::getObjectProperty($obj, $prop);
			}
		}
		return (object) $newObj;
	}

	public static function getObjectProperty($obj, string $prop) {
		$parts = explode(".", $prop);
		$val = clone $obj;
		foreach ($parts as $p) {
			$val = $val->$p ?? null;
			if (is_null($val)) {
				return $val;
			}
		}
		return $val;
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

	public static function appendToFile($fromPath, $toPath) {
		$fh = fopen($fromPath, 'r');
		while (!feof($fh)) {
			file_put_contents($toPath, fgets($fh), FILE_APPEND);
		}
		fclose($fh);
	}

	public static function getPackageId($storeUrl) {
		$packageId = null;
		$components = parse_url($storeUrl);
		if (!isset($components['host'])) {
			return $packageId;
		}
		switch ($components['host']) {
			case self::GOOGLE_PLAY_HOST:
			case self::MARKET_URL_HOST:
				parse_str($components['query'] ?? '', $map);
				$packageId = $map['id'] ?? null;
				break;

			case self::APP_STORE_HOST:
				preg_match('#(id[a-z0-9]+\??)#', $components['path'], $matches);
				if (isset($matches[1])) {
					$packageId = $matches[1];
				}
				break;
		}
		return $packageId;
	}

	public static function getExchangeRates() {
		$appConf = static::getAppConfig();
		if (!isset($appConf->openExchangeRatesAppUrl)) {
			return [];
		}
		try {
			$cacheKey = '__CURRENCY_RATES__' . date('Y-m-d');
			$rates = static::getCache($cacheKey);
			if (is_null($rates)) {
				$client = new \GuzzleHttp\Client(['timeout' => 30]);
				$resp = $client->request('GET', $appConf->openExchangeRatesAppUrl);
				$json = json_decode((string) $resp->getBody());
				$result = (array) ($json->rates ?? []);
				$rates = [];
				foreach ($result as $k => $v) {
					$rates[strtolower($k)] = $v;
				}
				static::setCache($cacheKey, $rates, 86400);
			}
			return $rates;
		} catch (\Exception $e) {
			return [];
		}
	}
}
