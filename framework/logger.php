<?php

namespace Framework {

    use Framework\Base;
    use Framework\Events;
    use Psr\Log\LogLevel;
    use Framework\Core\Exception;

    /**
     * Logger Factory
     */
    class Logger extends Base {
    	const LOGGER_TYPE_SENTRY = 'sentry';
    	const LOGGER_TYPE_FILE = 'simpleFile';

        /**
         * @readwrite
         */
        protected $_type;

        /**
         * @readwrite
         */
        protected $_env;

        /**
         * @readwrite
         */
        protected $_options;

        protected function _getExceptionForImplementation($method) {
            return new Exception\Implementation("{$method} method not implemented");
        }

        /**
         * @deprecated
         * This is now automatically done by sentry
         */
        private function registerErrors($loggerBackend) {
        	// $errorHandler = new \Monolog\ErrorHandler($loggerBackend);
        	// $errorHandler->registerErrorHandler([
	        //     E_WARNING           => LogLevel::WARNING,
	        //     E_NOTICE            => LogLevel::NOTICE,
	        //     E_CORE_WARNING      => LogLevel::WARNING,
	        //     E_COMPILE_WARNING   => LogLevel::WARNING,
	        //     E_USER_WARNING      => LogLevel::WARNING,
	        //     E_USER_NOTICE       => LogLevel::NOTICE,
	        //     E_STRICT            => LogLevel::NOTICE,
	        //     E_DEPRECATED        => LogLevel::NOTICE,
	        //     E_USER_DEPRECATED   => LogLevel::NOTICE,
	        // ]);
        }

        public function initialize() {
            Events::fire("framework.logger.initialize.before", array($this->type, $this->options));

            $configuration = Registry::get("configuration");
            $configuration = $configuration->initialize();
            $parsed = $configuration->parse("configuration/app");
            if (!$this->type) {
                $type = $parsed->app->logger;
                $this->type = $type;
                $this->options = (array) $parsed->app->logger_config->{$type};
                $this->env = $parsed->app->environment;
            }

            if (! $this->type) {
                throw new Exception\Argument("Invalid type");
            }

            Events::fire("framework.logger.initialize.after", array($this->type, $this->options));
            $loggerBackend = new \Monolog\Logger($this->type);

            switch ($this->type) {
                case static::LOGGER_TYPE_SENTRY:
                    // \Sentry\init(['dsn' => $this->options[$this->env], 'traces_sample_rate' => 0.2]);
                    // $hub = \Sentry\SentrySdk::getCurrentHub();
                    // $handler = new \Sentry\Monolog\Handler($hub);
                    break;

                case static::LOGGER_TYPE_FILE:
                	$handler = new \Monolog\Handler\StreamHandler($this->options[$this->env]);
                	break;

                default:
                    throw new Exception\Argument("Invalid type");
            }
            // $loggerBackend->pushHandler($handler);
            // $this->registerErrors($loggerBackend);
            return new Logger\Base([
            	'logger' => $loggerBackend,
            	'logLevel' => $parsed->app->log_level
            ]);
        }

    }

}
