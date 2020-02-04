<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\Psr17\NyholmPsr17Factory;

// Build container
$containerBuilder = new ContainerBuilder();

// Define settings in container
$containerBuilder->addDefinitions(require 'settings.php');

// Add dependencies definitions
$dependencies = require 'dependencies.php';
$dependencies($containerBuilder);

// Instantiate the app
$app = AppFactory::create(
    NyholmPsr17Factory::getResponseFactory(),
    $containerBuilder->build()
);

// Boot application services
$boot = require 'boot.php';
$boot($app);

// Register middleware
$middleware = require 'middleware.php';
$middleware($app);

// Register routes
$routes = require 'routes.php';
$routes($app);

return $app;
