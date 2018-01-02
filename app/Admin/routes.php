<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->get('/aboutClass/list', 'AboutClassController@classList');
    $router->resource('/aboutClass', 'AboutClassController')->except('edit','update');
    $router->get('/shadowsocks/unusedPort/{port?}', 'ShadowsocksController@unusedPort')->name('shadowsocks.unusedPort');
    $router->resource('/shadowsocks', 'ShadowsocksController');
    $router->resource('/projects', 'ProjectController');
    $router->resource('/project/users', 'ProjectUserController')->name('index','project.users');

    $router->resource('/aliexpress', 'Ext\AliexpressController');




});
