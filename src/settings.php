<?php

// Define root path
defined('DS') ?: define('DS', DIRECTORY_SEPARATOR);
defined('ROOT') ?: define('ROOT', dirname(__DIR__) . DS);

// Load .env file
if (file_exists(ROOT . '.env')) {
    $dotenv = new Dotenv\Dotenv(ROOT);
    $dotenv->load();
}

return [
    'settings' => [

        // App Settings
        'app'                    => [
            'name' => getenv('APP_NAME'),
            'url'  => getenv('APP_URL'),
            'env'  => getenv('APP_ENV'),
        ],

        // Error Handling Settings
        'error' => [
            'display_error_details' => getenv('APP_DEBUG') === 'true', // set to false in production
            'log_errors' => true,
            'log_error_details' => getenv('APP_DEBUG') !== 'true'
        ],

        // Monolog settings
        'logger'                 => [
            'name'  => getenv('APP_NAME'),
            'path'  => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Database settings
        'database'               => [
            'driver'    => getenv('DB_CONNECTION'),
            'host'      => getenv('DB_HOST'),
            'database'  => getenv('DB_DATABASE'),
            'username'  => getenv('DB_USERNAME'),
            'password'  => getenv('DB_PASSWORD'),
            'port'      => getenv('DB_PORT'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],

        'cors' => null !== getenv('CORS_ALLOWED_ORIGINS') ? getenv('CORS_ALLOWED_ORIGINS') : '*',

        // jwt settings
        'jwt'  => [
            'secret' => getenv('JWT_SECRET'),
            'secure' => false,
            "header" => "Authorization",
            "regexp" => "/Token\s+(.*)$/i",
            'passthrough' => ['OPTIONS']
        ],
    ],
];
