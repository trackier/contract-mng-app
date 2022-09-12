<?php

namespace Framework\Logger;

use Monolog\Logger as MonoLogger;

class Base extends \Framework\Base implements \Psr\Log\LoggerInterface {
	/**
	 * @readwrite
	 * @var \Monolog\Logger
	 */
	protected $_logger;

	/**
	 * PSR standard log level
	 * @readwrite
	 * @var int
	 */
	protected $_logLevel;

	public function __construct($opts = []) {
		parent::__construct($opts);
	}

	public function pushProcessor(callable $fn) {
		$this->logger->pushProcessor($fn);
	}

	protected function shouldLogMessage($messageLevel) {
        return $messageLevel >= $this->logLevel;
    }

    public function debug($message, array $context = array()) {
        if ($this->shouldLogMessage(MonoLogger::DEBUG)) {
            $this->logger->debug($message, $context);
        }
    }

    public function info($message, array $context = array()) {
        if ($this->shouldLogMessage(MonoLogger::INFO)) {
            $this->logger->info($message, $context);
        }
    }

    public function notice($message, array $context = array()) {
        if ($this->shouldLogMessage(MonoLogger::NOTICE)) {
            $this->logger->notice($message, $context);
        }
    }

    public function warning($message, array $context = array()) {
        if ($this->shouldLogMessage(MonoLogger::WARNING)) {
            $this->logger->warning($message, $context);
        }
    }

    public function error($message, array $context = array()) {
        if ($this->shouldLogMessage(MonoLogger::ERROR)) {
            $this->logger->error($message, $context);
        }
    }

    public function critical($message, array $context = array()) {
        if ($this->shouldLogMessage(MonoLogger::CRITICAL)) {
            $this->logger->critical($message, $context);
        }
    }

    public function alert($message, array $context = array()) {
        if ($this->shouldLogMessage(MonoLogger::ALERT)) {
            $this->logger->alert($message, $context);
        }
    }

    public function emergency($message, array $context = array()) {
        if ($this->shouldLogMessage(MonoLogger::EMERGENCY)) {
            $this->logger->emergency($message, $context);
        }
    }

    public function log($level, $message, array $context = array()) {
        if ($this->shouldLogMessage($level)) {
            $this->logger->log($level, $message, $context);
        }
    }

    public function handleException($e, $context = []) {
    	$level = MonoLogger::ERROR;
    	if ($e instanceof \Error) {
    		$level = MonoLogger::CRITICAL;
    		$e = new \Exception($e->getMessage(), 500, $e);
    	}
    	$this->logger->log(
            $level,
            sprintf('Uncaught Exception %s: "%s" at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
            array_merge(['exception' => $e], $context)
        );
    }
}
