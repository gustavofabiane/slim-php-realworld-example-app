<?php

use Slim\App;

/**
 * Boot Application Services
 */
return function (App $app): void {

    $container = $app->getContainer();

    /**
     * Bootstrap database connection
     * 
     * @var \Illuminate\Database\Capsule\Manager $db
     */
    $db = $container->get('db');
    $db->setAsGlobal();
    $db->bootEloquent();
};
