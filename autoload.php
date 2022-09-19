<?php
ob_start();
define("DEBUG", True);
define("APP_PATH", dirname(__FILE__));
define("CDN", "/assets/");
define("CDN", "/node_modules/");

define("GCDN", "https://static.vnative.co/");

    
// 1. load the Core class that includes an autoloader
require_once(APP_PATH. "/framework/core.php");
Framework\Core::initialize();

// 2. Additional Path's which
Framework\Core::autoLoadPaths([
	"/application/libraries",
	"/application/Command",
	"/application"
]);

// 3. load and initialize the Configuration class 
$configuration = new Framework\Configuration(array(
    "type" => "ini"
));
Framework\Registry::set("configuration", $configuration->initialize());

// Load the logger
$logger = new Framework\Logger();
Framework\Registry::setLogger($logger->initialize());
unset($logger);

// 4. load and initialize the Database class – does not connect
$database = new Framework\Database();
Framework\Registry::set("database", $database->initialize());

// 5. load and initialize the Cache class – does not connect
$cache = new Framework\Cache();
Framework\Registry::set("cache", $cache->initialize());
$redisCache = new Framework\Cache(['type' => 'redis']);
Framework\Registry::set("redis", $redisCache->initialize());

// 6. load and initialize the Session class 
$session = new Framework\Session();
Framework\Registry::set("session", $session->initialize());

Shared\Services\Db::connect();

Framework\Registry::get("cache")->connect();
Framework\Registry::get("redis")->connect();

// 9. unset global variables
unset($configuration);
unset($database);
unset($cache);
unset($session);
