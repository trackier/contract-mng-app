<?php

namespace Framework {

    /**
     * The Registry is a Singleton, used to store instance of other “normal” classes.
     */
    class Registry {

        private static $_instances = array();

        private function __construct() {
            // do nothing 
        }
        
        private function __clone() {
            // do nothing
        }
        
        /**
         * Searches the private storage for an instance with a matching key. 
         * If it finds an instance, it will return it, or default to the value supplied with the $default parameter.
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public static function get($key, $default = null) {
            if (isset(self::$_instances[$key])) {
                return self::$_instances[$key];
            }
            return $default;
        }
        
        /**
         * Used to “store” an instance with a specified key in the registry’s private storage
         * @param string $key
         * @param mixed $instance
         */
        public static function set($key, $instance = null) {
            self::$_instances[$key] = $instance;
        }

        /**
         * Useful for removing an instance at a certain key.
         * @param string $key
         */
        public static function erase($key) {
            unset(self::$_instances[$key]);
        }

        public static function getLogger() {
        	return static::get("logger");
        }

        public static function setLogger(Logger\Base $logger) {
        	static::set("logger", $logger);
        }

        public static function getCache() {
        	return static::get("cache");
        }

        public static function setCache(Cache\Driver $cache) {
        	static::set("cache", $cache);
        }

        public static function getRedis() {
        	return static::get("redis");
        }

        public static function setRedis(Cache\Driver $redis) {
        	static::set("redis", $redis);
        }

        public static function getSession() {
        	return static::get("session");
        }

        public static function setSession(Session\Driver $driver) {
        	static::set("session", $driver);
        }

        public static function getConfiguration() {
        	return static::get("configuration");
        }

        public static function setConfiguration($configuration) {
        	static::set("configuration", $configuration);
        }

        public static function getCurrentUser() {
			$controller = static::get("controller");
			$session = static::getSession();
			return $controller->user;
		}

		public static function getCurrentUserId() {
			$controller = static::get("controller");
			if ($controller && $controller->isSuperUser()) {
				return $controller->isSuperUser();
			}
			if ($controller && $controller->user) {
				return $controller->user->_id;
			}
			return null;
		}

		/**
		 * Return the Current User IP and User agent information
		 * @return array {user_ip: string, ua: string}
		 */
		public static function getUserInfo() {
			$controller = static::get("controller");
			if ($controller) {
				$ip = $controller->request->getIp();
				$ua = $controller->request->header('user-agent');
			} else {
				$ip = RequestMethods::getIp();
				$ua = RequestMethods::server('HTTP_USER_AGENT');
			}
			return ['user_ip' => $ip, 'ua' => $ua];
		}
    }
}
