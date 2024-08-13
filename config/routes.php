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
use Hyperf\HttpServer\Router\Router;


Router::addGroup('/app', function () {
    Router::post('/login', 'App\Controller\AppController@login');
    Router::get('/captcha', 'App\Controller\AppController@captcha');
    Router::post('/logout', 'App\Controller\AppController@logout');
});
Router::addGroup('/user', function () {
    Router::get('/list', 'App\Controller\UserController@index');
    Router::get('/getDetail', 'App\Controller\UserController@getDetail');
    Router::get('/getAllUser', 'App\Controller\UserController@getAllUser');
    Router::get('/getLoginUser', 'App\Controller\UserController@getLoginUser');

});
Router::addGroup('/role', function () {
    Router::get('/list', 'App\Controller\RoleController@index');
    Router::get('/getLogList', 'App\Controller\RoleController@getLogList');
    Router::post('/setRolePermission', 'App\Controller\RoleController@setRolePermission');
    Router::post('/batchSetRolePermission', 'App\Controller\RoleController@batchSetRolePermission');
    Router::get('/getRoleUser', 'App\Controller\RoleController@getRoleUser');
    Router::get('/getDetail', 'App\Controller\RoleController@getDetail');
    Router::get('/getAllRole', 'App\Controller\RoleController@getAllRole');
    Router::get('/getRolePermission', 'App\Controller\RoleController@getRolePermission');
});
Router::addGroup('/permission', function () {
    Router::get('/list', 'App\Controller\PermissionController@index');
    Router::post('/add', 'App\Controller\PermissionController@add');
    Router::post('/edit', 'App\Controller\PermissionController@edit');
    Router::post('/delete', 'App\Controller\PermissionController@delete');
    Router::get('/getLogList', 'App\Controller\PermissionController@getLogList');
    Router::get('/getAllPermission', 'App\Controller\PermissionController@getAllPermission');
    Router::get('/getDetail', 'App\Controller\PermissionController@getDetail');

});
Router::addGroup('/menu', function () {
    Router::get('/list', 'App\Controller\MenuController@index');
    Router::post('/add', 'App\Controller\MenuController@add');
    Router::post('/edit', 'App\Controller\MenuController@edit');
    Router::post('/delete', 'App\Controller\MenuController@delete');
    Router::get('/getLogList', 'App\Controller\MenuController@getLogList');
    Router::get('/getDetail', 'App\Controller\MenuController@getDetail');
    Router::get('/getUserMenu', 'App\Controller\MenuController@getUserMenu');
    Router::get('/getVersion', 'App\Controller\MenuController@getVersion');
    Router::get('/getAllMenu', 'App\Controller\MenuController@getAllMenu');
});



Router::get('/favicon.ico', function () {
    return '';
});
