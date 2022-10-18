<?php

namespace Framework\Router {

    use Framework\Base as Base;
    use Framework\Router\Exception as Exception;

    /**
     * Contain information about the URL requested.
     */
    class Route extends Base {
    	const METHOD_ANY = 'any';

        /**
         * @readwrite
         */
        protected $_pattern;

        /**
         * @readwrite
         */
        protected $_method = 'ANY';

        /**
         * @readwrite
         */
        protected $_controller;

        /**
         * @readwrite
         */
        protected $_action;

        /**
         * @readwrite
         */
        protected $_parameters = array();

        public function _getExceptionForImplementation($method) {
            return new Exception\Implementation("{$method} method not implemented");
        }

        public function isValidMethod($method = null) {
        	if (! $method) {
        		$method = php_sapi_name() == 'cli' ? 'ANY' : $_SERVER['REQUEST_METHOD'];
        	}
        	
        	$routerMethod = strtolower($this->method);
        	return strtolower($method) === $routerMethod || $routerMethod === static::METHOD_ANY;
        }
    }

}
