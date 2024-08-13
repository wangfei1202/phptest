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
        'host' => env('AMQP_HOST', '172.16.11.98'),
        'port' => (int) env('AMQP_PORT', 5672),
        'user' => env('AMQP_USER', 'runbu-admin'),
        'password' => env('AMQP_PASSWORD', 'runbu123456'),
        'vhost' => env('AMQP_VHOST', 'allegroAdvt'),
        'concurrent' => [
            'limit' => 10,
        ],
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 100,
            'connect_timeout' => 9.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
        ],
        'params' => [
            'insist' => false,
            'login_method' => 'AMQPLAIN',
            'login_response' => null,
            'locale' => 'en_US',
            'connection_timeout' => 3.0,
            'read_write_timeout' => 6.0,
            'context' => null,
            'keepalive' => false,
            'heartbeat' => 3,
            'close_on_destruct' => false,
        ],
    ],
];
