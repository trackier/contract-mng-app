<?php
namespace Framework\Cache\Driver;

use Redis as GlobalRedis;
use Framework\Cache as Cache;
use Framework\Cache\Exception as Exception;

class Redis extends Cache\Driver {
	/**
	 * Instance of PHP’s Redis class
	 * @readwrite
	 */
	protected $_service;

	/**
	 * @readwrite
	 */
	protected $_host = "redis-1-vm";

	/**
	 * @readwrite
	 */
	protected $_port = "6379";

	/**
	 * @todo move this to conf
	 * @readwrite
	 */
	protected $_password = 'rCB3bB7Vq1ZX';

	/**
	 * @todo move this to conf
	 * @readwrite
	 */
	protected $_database = 1;

	/**
	 * @readwrite
	 */
	protected $_isConnected = false;

	protected function _isValidService() {
		$isEmpty = empty($this->_service);
		$isInstance = $this->_service instanceof GlobalRedis;
		if ($this->isConnected && $isInstance && !$isEmpty) {
			return true;
		}
		return false;
	}

	/**
	 * Attempts to connect to the Redis server at the specified host/port. If it connects, 
	 * @return \Framework\Cache\Driver\Redis
	 * @throws Exception\Service
	 */
	public function connect() {
		for ($i = 0; $i < 10; $i++) { 
			try {				
				$this->_service = new GlobalRedis();
				$this->_service->pconnect($this->host, $this->port);
				$this->_service->auth($this->password);
				$this->_service->select((int)$this->database);
				$this->isConnected = true;
				return $this;
			} catch (\Exception $e) {
				usleep($i * 100000);
				continue;
			}
		}
		throw new Exception\Service("Unable to connect to service");
	}

	/**
	 * Attempts to disconnect the $_service instance from the Redis service. It will only do so if the _isValidService() method returns true.
	 * @return \Framework\Cache\Driver\Redis
	 */
	public function disconnect() {
		if ($this->_isValidService()) {
			$this->_service->close();
			$this->isConnected = false;
		}

		return $this;
	}

	/**
	 * Get cached values
	 * @param type $key
	 * @param type $default allows for a default value to be supplied
	 * @return type returned in the event no cached value is found at the corresponding key
	 * @throws Exception\Service
	 */
	public function get($key, $default = null) {
		if (!$this->_isValidService()) {
			return $default;
		}

		$value = $this->_service->get($key);
		if ($value === false) {
			return $default;
		}
		return $value;
	}

	/**
	 * Set values to keys
	 * @param type $key
	 * @param type $value
	 * @param type $duration duration for which the data should be cached
	 * @return boolean Return status of save
	 * @throws Exception\Service
	 */
	public function set($key, $value, $duration = 300) {
		if (!$this->_isValidService()) {
			return false;
		}

		return $this->_service->set($key, $value, $duration);
	}

	public function hgetall($key) {
		if (!$this->_isValidService()) {
			return [];
		}
		return $this->_service->hGetAll($key);
	}

	public function hget($key, $field) {
		if (!$this->_isValidService()) {
			return [];
		}
		return $this->_service->hGet($key, $field);
	}

	public function hincrby($key, $field, $val) {
		if (!$this->_isValidService()) {
			return 0;
		}
		return $this->_service->hIncrBy($key, $field, $val);
	}

	public function hincrbyfloat($key, $field, $val) {
		if (!$this->_isValidService()) {
			return 0;
		}
		return $this->_service->hIncrByFloat($key, $field, $val);
	}

	public function hmset($key, $arr) {
		if (!$this->_isValidService()) {
			return false;
		}
		return $this->_service->hMSet($key, $arr);
	}

	public function hset($key, $field, $value) {
		if (!$this->_isValidService()) {
			return false;
		}
		return $this->_service->hSet($key, $field, $value);
	}

	public function getPipeline() {
		$pipe = $this->service->multi(GlobalRedis::PIPELINE);
		return $pipe;
	}

	public function multiHGetAll($keys) {
		if (count($keys) == 0) {
			return [];
		}

		if (!$this->_isValidService()) {
			return [];
		}

		$pipe = $this->getPipeline();
		foreach ($keys as $key) {
			$pipe->hGetAll($key);
		}
		$result = $pipe->exec();
		return $result;
	}

	public function keys($pattern = null) {
		if (! $pattern) {
			throw new Exception\Argument('Invalid Pattern supplied for delete!!');
		}
		if (! $this->_isValidService()) {
			return [];
		}
		return $this->service->keys($pattern);
	}

	public function multiErase($pattern = null) {
		$allKeys = $this->keys($pattern);
		$allKeys = array_chunk($allKeys, 100);

		foreach ($allKeys as $arr) {
			$pipe = $this->getPipeline();
			foreach ($arr as $key) {
				$pipe->delete($key);
			}
			$pipe->exec();
			usleep(10000);
		}
		
		return true;
	}

	/**
	 * Erase value of key
	 * @param type $key
	 * @return \Framework\Cache\Driver\Redis
	 * @throws Exception\Service
	 */
	public function erase($key) {
		if (!$this->_isValidService()) {
			return $this;
		}

		$this->_service->delete($key);
		return $this;
	}

	/**
	 * Erase data from the cache
	 */
	public function flush() {
		if (!$this->_isValidService()) {
			return false;
		}
		return $this->_service->flushDb();
	}

	/**
	 * Get Lock uses redis to check if the lock key is still set
	 * @param  string $lockKey Name of the lock key
	 * @return boolean
	 */
	public function getLock($lockKey) {
		if (! $this->_isValidService()) {
			return false;
		}
		return $this->_service->setNx($lockKey, date('Y-m-d H:i:s'));
	}

	/**
	 * This function takes a key and hash map (assoc array in PHP) and it increments
	 * the hash keys by the corresponding values based on whether the value is float
	 * hIncrByFloat is used else simple hIncrBy is used
	 *
	 * Logs Error in case no connection is established to Redis
	 *
	 * @param  string $key  Name of the Redis Key
	 * @param  array  $data Redis Hash Map Data
	 * @return boolean       Whether the operation succeeded
	 */
	public function incrHashMapKeys(string $key, array $data) {
		if (! $this->_isValidService()) {
			$logger = Registry::getLogger();
			$logger->error('Trying to write data to not connected Redis Service', ['key' => $key, 'data' => $data]);
			return false;
		}

		foreach ($data as $hashKey => $hashValue) {
			if (is_float($hashValue)) {
				$this->_service->hIncrByFloat($key, $hashKey, $hashValue);
			} else {
				$this->_service->hIncrBy($key, $hashKey, $hashValue);
			}
		}
		return true;
	}
}
