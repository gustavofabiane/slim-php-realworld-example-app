<?php

require_once './vendor/autoload.php';

/** @var Slim\App $app */
$app = require './src/app.php';

$container = $app->getContainer();
$config = $container->get('settings')['database'];

return [
    'paths'                => [
        'migrations' => 'database/migrations',
        'seeds'      => 'database/seeds',
    ],
    'migration_base_class' => 'BaseMigration',
    'templates'            => [
        'class' => 'TemplateGenerator',
    ],
    'aliases'              => [
        'create' => 'CreateTableTemplateGenerator',
    ],

    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database'        => 'development',
        'development'             => [
            'name'       => $config['database'],
            'connection' => $container->get('db')->getConnection()->getPdo(),
        ],
        'production'              => [
            'adapter'   => $config['driver'],
            'host'      => $config['host'],
            'name'      => $config['database'],
            'user'      => $config['username'],
            'pass'      => $config['password'],
            'port'      => $config['port'],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
    ],
];