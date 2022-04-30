<?php

use Laravel\Lumen\Routing\Router;

/** @var Router $router */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Api
$router->group(['prefix' => 'api'], function () use ($router) {
    // Authentication
    $router->post('/signup', 'AuthController@register');
    $router->post('/login', 'AuthController@login');

    // Authenticated Routes
    $router->group(['middleware' => 'auth',], function () use ($router) {
        $router->get('/user', 'AuthController@user');
        $router->get('/arbitrary-user-notes', 'TodoController@listForArbitraryUser');

        $router->group(['prefix' => 'todo'], function () use ($router) {
            $router->get('/', 'TodoController@index');
            $router->post('/', 'TodoController@store');
            $router->get('/{id:[0-9]+}', 'TodoController@get');
            $router->patch('/{id:[0-9]+}', 'TodoController@update');
            $router->delete('{id:[0-9]+}', 'TodoController@destroy');
            $router->post('/{id:[0-9]+}/complete', 'TodoController@markComplete');
            $router->post('/{id:[0-9]+}/incomplete', 'TodoController@markIncomplete');
        });
    });
});

