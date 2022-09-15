<?php
ini_set('memory_limit','2048M');
ini_set('memcached.sess_locking', '0');
ini_set('auto_detect_line_endings', true);

if (!isset($_SERVER['HTTP_HOST'])) {	// these requests are probably sent from some malicious clients
   http_response_code(404);
	return;
}
ob_start();
define("DEBUG", TRUE);
define("APP_PATH", str_replace(DIRECTORY_SEPARATOR, "/", dirname(dirname(__FILE__))));
define("URL", "http://". $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI']);
define("CDN", "/assets/");
define("GCDN", "https://static.vnative.co/");

try {
	// 1. load the Core class that includes an autoloader
	require_once(APP_PATH. "/framework/core.php");
	Framework\Core::initialize();

	// 2. Additional Path's which
	Framework\Core::autoLoadPaths([
		"/application/libraries",
		"/application/Command",
		"/application"
	]);

	// plugins
	// $path = APP_PATH . "/application/plugins";
	// $iterator = new DirectoryIterator($path);

	// foreach ($iterator as $item) {
	// 	if (!$item->isDot() && $item->isDir()) {
	// 		include($path . "/" . $item->getFilename() . "/initialize.php");
	// 	}
	// }

	// 3. load and initialize the Configuration class 
	$configuration = new Framework\Configuration(array(
		"type" => "ini"
	));
	Framework\Registry::setConfiguration($configuration->initialize());


	// 4. load and initialize the Database class – does not connect
	$database = new Framework\Database();
	Framework\Registry::set("database", $database->initialize());

	// 5. load and initialize the Cache class – does not connect
	$cache = new Framework\Cache();
	Framework\Registry::setCache($cache->initialize());
	$redisCache = new Framework\Cache(['type' => 'redis']);
	Framework\Registry::setRedis($redisCache->initialize());

	// 6. load and initialize the Session class 
	$session = new Framework\Session();
	Framework\Registry::setSession($session->initialize());
	
	// 7. load the Router class and provide the url + extension
	$router = new Framework\Router(array(
		"url" => isset($_GET["url"]) ? $_GET["url"] : "users/login",
		"extension" => !empty($_GET["extension"]) ? $_GET["extension"] : "html"
	));
	Framework\Registry::set("router", $router);

	// include custom routes 
	include("public/routes.php");

	// 8. dispatch the current request 
    // var_dump($router);
    // die();
	
	$router->dispatch();

	// 9. unset global variables
	unset($configuration);
	unset($database);
	unset($cache);
	unset($session);
	unset($router);
} catch (Exception $e) {
    // list exceptions
	$exceptions = array(
		"401" => array(
			"Framework\Router\Exception\Inactive"
		),
		"404" => array(
			"Framework\Router\Exception\Action",
			"Framework\Router\Exception\Controller",
			"Framework\Controller\Exception\Implementation"
		),
		"500" => array(
			"Framework\Cache\Exception",
			"Framework\Configuration\Exception",
			"Framework\Controller\Exception",
			"Framework\Core\Exception",

			"Framework\Database\Exception",
			"Framework\Model\Exception",
			"Framework\Request\Exception",
			"Framework\Router\Exception",
			"Framework\Session\Exception",

			"Framework\Template\Exception",
			"Framework\View\Exception",

			"MongoDB\Driver\Exception\Exception"
		),
		"503" => array(
			"Framework\Router\Exception\Maintenance"
		)
	);

	$exception = get_class($e);

	// attempt to find the approapriate template, and render
	foreach ($exceptions as $template => $classes) {
		foreach ($classes as $class) {
			if ($class == $exception || is_subclass_of($exception, $class)) {
				header("Content-type: text/html");
				include(APP_PATH . "/application/views/layouts/errors/{$template}.php");
				exit;
			}
		}
	}

	// log or email any error
	
	// render fallback template
	header("Content-type: text/html");
	include(APP_PATH . "/application/views/layouts/errors/500.php");
	exit;
} catch (Error $e) {
	header("Content-type: text/html");
	include(APP_PATH . "/application/views/layouts/errors/500.php");
	exit;
}
?>
