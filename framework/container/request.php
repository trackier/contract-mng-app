<?php
namespace Framework\Container;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends Base {
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_DELETE = 'DELETE';
	const METHOD_PATCH = 'PATCH';
	const METHOD_PUT = 'PUT';

	/**
	 * The Request ID
	 * @readwrite
	 * @var string
	 */
	protected $_id;

	/**
	 * @readwrite
	 * @var object Hold the symfony class object
	 */
	protected $_obj = null;

	public function __construct($opts = array()) {
		parent::__construct($opts);

		if (!$this->obj) {
			$this->obj = SymfonyRequest::createFromGlobals();
		}
		$this->id = uniqid();
	}

	public function queryBag() {
		return $this->obj->query;
	}

	public function postBag() {
		return $this->obj->request;
	}

	public function serverBag() {
		return $this->obj->server;
	}

	public function headerBag() {
		return $this->obj->headers;
	}

	public function getHeader($key, $default = null) {
		return $this->headerBag()->get($key, $default);
	}

	public function getServer($key, $default = null) {
		return $this->serverBag()->get($key, $default);
	}

	public function keyExistsInQuery($key) {
		$queryBag = $this->queryBag();
		return array_key_exists($key, $queryBag->all());
	}

	/**
	 * Get Query
	 * @return string Escaped value
	 */
	public function get($key, $default = null, $validation = []) {
		$queryBag = $this->queryBag();
		if (array_key_exists($key, $queryBag->all())) {
			$val = $queryBag->get($key);
			try {
				return $this->escapeHtml($val, $validation);
			} catch (\Exception $e) {}
		}
		return $default;
	}

	/**
	 * Get a value from POST data
	 * @param  string $key     Name of the key
	 * @param  mixed $default Default value if key not found
	 * @return string          Escaped valued
	 */
	public function post($key, $default = null) {
		$postBag = $this->postBag();
		if (array_key_exists($key, $postBag->all())) {
			$val = $postBag->get($key);
			try {
				return $this->escapeHtml($val);
			} catch (\Exception $e) {}
		}
		return $default;
	}

	public function server($key, $default = null) {
		$serverBag = $this->serverBag();
		if (array_key_exists($key, $serverBag->all())) {
			$val = $serverBag->get($key);
			return $this->escapeHtml($val);	
		}
		return $default;
	}

	public function header($key, $default = null) {
		$headerBag = $this->headerBag();
		if (array_key_exists($key, $headerBag->all())) {
			$val = $headerBag->get($key);
			return $this->escapeHtml($val);
		}
		return $default;
	}

	public function getBody() {
		return $this->obj->getContent();
	}

	public function jsonKey($key, $default = null) {
		$content = $this->obj->getContent();
		$arr = json_decode($content, true);
		if (isset($arr[$key])) {
			return $this->escapeHtml($arr[$key]);
		}
		return $default;
	}

	public function xssSafe($param) {
		return htmlentities(preg_replace('/<script>|<\/script>/i', "", html_entity_decode($param)));
	}

	public function escapeHtml($val, $validation = []) {
		if (is_array($val)) {
			return $this->_escapeHtml($val, $validation);
		} else {
			$val = str_replace('${', '', $val);
			$val = str_replace('{$', '{', $val);
			$val = htmlspecialchars($val);

			$vtype = $validation['type'] ?? null;
			if ($vtype && $vtype == 'enum' && ! in_array($val, $validation['enum'])) {
				throw new \Exception("Invalid Value for key");
			}
			if ($vtype && $vtype == 'regex' && !preg_match($validation['regex'], $val)) {
				throw new \Exception("Invalid Value for key");
			}
			if ($vtype && $vtype == 'numeric') {
				if (! is_numeric($val)) {
					throw new \Exception("Value should be numeric");
				}
				$val = (int) $val;
				if (isset($validation['maxVal']) && $val > $validation['maxVal']) {
					throw new \Exception("Value should be less than maxVal");
				}
				if (isset($validation['minVal']) && $val < $validation['minVal']) {
					throw new \Exception("Value should be greater than minVal");
				}
			}
			return $val;
		}
	}

	protected function _escapeHtml($val, $validation) {
		$result = [];
		foreach ($val as $key => $value) {
			$key = $this->escapeHtml($key);
		    if (is_array($value)) {
		        $result[$key] = $this->_escapeHtml($value, $validation);
		    } else {
		        $result[$key] = $this->escapeHtml($value, $validation);
		    }
		}
		return $result;
	}

	public function path() {
		return $this->obj->getPathInfo();
	}

	public function getHost() {
		return $this->obj->getHttpHost();
	}

	public function getIp() {
		$remoteAddr = $this->server('REMOTE_ADDR', '');
		$forwardedIp = $this->header('X-Forwarded-For', null) ?? $this->header('x-forwarded-for', null);
		if ($forwardedIp) {
			$ips = explode(",", $forwardedIp);
			return trim($ips[0] ?? $remoteAddr);
		}
		return $remoteAddr;
	}

	public function getPath() {
		return $this->obj->getRequestUri();
	}

	public function getMethod($lowerCase = false) {
		$method = $this->obj->getMethod();
		if ($lowerCase) {
			$method = strtolower($method);
		}
		return $method;
	}

	/**
	 * Check whether the current request method matches the one supplied to func
	 * @param  string  $method Method Name to check against
	 * @return boolean
	 */
	public function isMethod($method = 'GET') {
		$currentMethod = $this->getMethod(true);
		return strtolower($method) === $currentMethod;
	}

	/**
	 * Check Whether the current request method is GET
	 * @return boolean
	 */
	public function isGet() {
		return $this->isMethod(self::METHOD_GET);
	}

	/**
	 * Check Whether the current request method is POST
	 * @return boolean
	 */
	public function isPost() {
		return $this->isMethod(self::METHOD_POST);
	}

	/**
	 * Check Whether the current request method is DELETE
	 * @return boolean
	 */
	public function isDelete() {
		return $this->isMethod(self::METHOD_DELETE);
	}

	/**
	 * Check Whether the current request method is PATCH
	 * @return boolean
	 */
	public function isPatch() {
		return $this->isMethod(self::METHOD_PATCH);
	}

	/**
	 * Check Whether the current request method is PUT
	 * @return boolean
	 */
	public function isPut() {
		return $this->isMethod(self::METHOD_PUT);
	}

	/**
	 * This function removes the HTML tags from a value
	 * @return mixed Sanitized value
	 */
	public function filterVar($data = '') {
		return trim(strip_tags($data));
	}

	/**
	 * Decode HTML from the string
	 * @param  string $data Data string
	 * @return string       Data with tags
	 */
	public function decodeHtml($data) {
		$data = html_entity_decode($data);
		$data = str_replace("&nbsp;", "", $data);
		return $data;
	}

	/**
	 * Remove the tags from the data string
	 * @param  string $data
	 * @return string
	 */
	public function stripTags($data) {
		$data = $this->decodeHtml($data);
		return strip_tags($data);
	}
}
