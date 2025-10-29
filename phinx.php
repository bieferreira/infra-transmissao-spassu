<?php
require_once __DIR__ . '/phinx-db-constants.php';

return [
    'paths' => [
        'migrations' => __DIR__ . '/db/migrations',
        'seeds'      => __DIR__ . '/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => HOST,
            'name' => BANCODADOS,
            'user' => USUARIOBANCODADOS,
            'pass' => SENHABANCODADOS,
            'port' => PORTABANCODADOS,
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => HOST,
            'name' => BANCODADOS,
            'user' => USUARIOBANCODADOS,
            'pass' => SENHABANCODADOS,
            'port' => PORTABANCODADOS,
            'charset' => 'utf8',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => HOST,
            'name' => BANCODADOS,
            'user' => USUARIOBANCODADOS,
            'pass' => SENHABANCODADOS,
            'port' => PORTABANCODADOS,
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
