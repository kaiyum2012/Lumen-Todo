<?php

use Laravel\Lumen\Routing\Router;

/** @var Router $router */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Api
$router->group(['prefix' => 'api'], function () use ($router) {
    // Authentication
    $router->post('/signup', ['uses' => 'AuthController@register', 'as' => 'register']);
    $router->post('/login', ['uses' => 'AuthController@login', 'as' => 'login']);

    // Authenticated Routes
    $router->group(['middleware' => 'auth',], function () use ($router) {
        $router->get('/user', ['uses' => 'AuthController@user', 'as' => 'auth.user']);
        $router->get('/arbitrary-user-notes',
            ['uses' => 'TodoController@listForArbitraryUser', 'as' => 'arbitrary_user']);

        $router->group(['prefix' => 'todo'], function () use ($router) {
            $router->get('/', ['uses' => 'TodoController@index', 'as' => 'todo.list']);
            $router->post('/', ['uses' => 'TodoController@store', 'as' => 'todo.create']);
            $router->get('/{id:[0-9]+}', ['uses' => 'TodoController@get', 'as' => 'todo.show']);
            $router->patch('/{id:[0-9]+}', ['uses' => 'TodoController@update', 'as' => 'todo.update']);
            $router->delete('{id:[0-9]+}', ['uses' => 'TodoController@destroy', 'as' => 'todo.delete']);
            $router->post('/{id:[0-9]+}/complete',
                ['uses' => 'TodoController@markComplete', 'as' => 'todo.mark-complete']);
            $router->post('/{id:[0-9]+}/incomplete',
                ['uses' => 'TodoController@markIncomplete', 'as' => 'todo.mark-incomplete']);
        });
    });
});

