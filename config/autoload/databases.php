<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', '172.16.11.27'),
        'database' => env('DB_DATABASE', 'elenxsERP'),
        'port' => env('DB_PORT', 3306),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', 'runbuEbay123!@#'),
        'charset' => env('DB_CHARSET', 'utf8'),
        'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model',
                'force_casts' => true,
                'inheritance' => 'Model',
            ],
        ],
    ],
    'sys' => [
        'driver' => env('DB_DRIVER_SYS', 'mysql'),
        'host' => env('DB_HOST_SYS', '172.16.11.27'),
        'database' => env('DB_DATABASE_SYS', 'ElenxsStandartProduct'),
        'port' => env('DB_PORT_SYS', 3306),
        'username' => env('DB_USERNAME_SYS', 'root'),
        'password' => env('DB_PASSWORD_SYS', 'runbuEbay123!@#'),
        'charset' => env('DB_CHARSET_SYS', 'utf8'),
        'collation' => env('DB_COLLATION_SYS', 'utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX_SYS', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model',
                'force_casts' => true,
                'inheritance' => 'Model',
            ],
        ],
    ],
];
