<?php

use Monolog\Logger;
use function DI\get;
use DI\ContainerBuilder;
use Conduit\Middleware\Cors;
use Psr\Log\LoggerInterface;
use Conduit\Services\Auth\Auth;
use Conduit\Validation\Validator;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Conduit\Middleware\OptionalAuth;
use Tuupola\Middleware\JwtAuthentication;
use Slim\Factory\Psr17\NyholmPsr17Factory;
use Conduit\Middleware\RemoveTrailingSlash;
use Respect\Validation\Validator as Respect;
use League\Fractal\Manager as FractalManager;

use League\Fractal\Serializer\ArraySerializer;
use Psr\Http\Message\ResponseFactoryInterface;
use Illuminate\Database\Capsule\Manager as IlluminateDatabase;

/**
 * Application dependencies definitions
 */
return function (ContainerBuilder $containerBuilder): void {

    // Slim dependencies
    $containerBuilder->addDefinitions([
        ResponseFactoryInterface::class => function () {
            return NyholmPsr17Factory::getResponseFactory();
        },
    ]);

    // Application Dependencies
    $containerBuilder->addDefinitions([
        
        // Monolog
        LoggerInterface::class => get('logger'),
        'logger' => function ($c) {
            $settings = $c->get('settings')['logger'];
            $logger = new Logger($settings['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($settings['path'], $settings['level']));
        
            return $logger;
        },

        // Request Validator
        Validator::class => get('validator'),
        'validator' => function () {
            Respect::with('\\Conduit\\Validation\\Rules');
            return new Validator();
        },

        // Fractal
        FractalManager::class => get('fractal'),
        'fractal' => function () {
            $manager = new FractalManager();
            $manager->setSerializer(new ArraySerializer());
            return $manager;
        },

        // Database Manager
        IlluminateDatabase::class => get('db'),
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
        Auth::class => get('auth'),
        'auth' => function ($c) {
            return new Auth($c->get('db'), $c->get('settings'));
        },
    ]);

    // Middleware definitions
    $containerBuilder->addDefinitions([

        // JWT Middleware
        'jwt' => function ($c) {
            return new JwtAuthentication($c->get('settings')['jwt']);
        },

        // Optional Auth Middleware
        'optionalAuth' => function ($c) {
            return new OptionalAuth($c->get('jwt'));
        },

        // Remove trailing slash from URI path
        RemoveTrailingSlash::class => function () {
            $responseFactory = NyholmPsr17Factory::getResponseFactory();
            return new RemoveTrailingSlash($responseFactory);
        },

        // CORS middleware
        Cors::class => function ($c) {
            return new Cors($c->get('settings')['cors']);
        },
    ]);
};
