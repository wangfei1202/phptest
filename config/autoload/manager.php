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
    'administrator' => 'admin',
    //无需校验token路由
    'no_check_route' => [
        "/app/captcha",
        "/app/login",
    ],
    //无需校验权限路由
    'no_auth_route' => [
        "/app/logout",
        "/user/getAllUser",
        "/user/modifyPassword",
        "/user/getLoginUser",
        "/role/getAllRole",
        "/role/getRolePermission",
        "/permission/getAllPermission",
        "/menu/getUserMenu",
        "/menu/getAllMenu",
        "/supplier/getList",
        "/common/getAllShopName",
        "/common/getLogisticProviderAndTransport",
    ],
];