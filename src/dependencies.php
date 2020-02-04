<?php

use Monolog\Logger;
use DI\ContainerBuilder;
use Conduit\Services\Auth\Auth;
use Respect\Validation\Validator;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Conduit\Middleware\OptionalAuth;
use Slim\Middleware\JwtAuthentication;
use League\Fractal\Manager as FractalManager;
use League\Fractal\Serializer\ArraySerializer;
use Illuminate\Database\Capsule\Manager as IlluminateDatabase;

/**
 * Application dependencies definitions
 */
return function (ContainerBuilder $containerBuilder): void {

    // Application Dependencies
    $containerBuilder->addDefinitions([
        
        // Monolog
        'logger' => function ($c) {
            $settings = $c->get('settings')['logger'];
            $logger = new Logger($settings['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($settings['path'], $settings['level']));
        
            return $logger;
        },

        // JWT Middleware
        'jwt' => function (array $settings) {
            return new JwtAuthentication($settings['jwt']);
        },

        // Optional Auth Middleware
        'optionalAuth' => function ($c) {
            return new OptionalAuth($c);
        },

        // Request Validator
        'validator' => function ($c) {
            Validator::with('\\Conduit\\Validation\\Rules');
            return new Validator();
        },

        // Fractal
        'fractal' => function ($c) {
            $manager = new FractalManager();
            $manager->setSerializer(new ArraySerializer());
            return $manager;
        },

        // Database Manager
        'db' => function ($c) {
            $capsule = new IlluminateDatabase();

            $config = $c->get('settings')['database'];
            $capsule->addConnection([
                'driver'    => $config['driver'],
                'host'      => $config['host'],
                'database'  => $config['database'],
                'username'  => $config['username'],
                'password'  => $config['password'],
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ]);

            return $capsule;
        },

        // Authorization service
        'auth' => function ($c) {
            return new Auth($c->get('db'), $c->get('settings'));
        }
    ]);
};
