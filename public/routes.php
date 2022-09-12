<?php

// define routes

$routes = array(
	array(
		"pattern" => "login",
		"controller" => "users",
		"action" => "login"
	)
);

// add defined routes
foreach ($routes as $route) {
    $router->addRoute(new Framework\Router\Route\Simple($route));
	// var_dump($router->addRoute(new Framework\Router\Route\Simple($route)));
	// die();
}

// unset globals
unset($routes);
