<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/user/reset_password/{rpk}', 'UserController@resetPassword');

// Route for prefix version in url.
$router->group(['prefix' => 'api/v1'], function ($router) {
    // Users
    $router->post('user/register', 'UserController@register');
    $router->post('user/login', ['uses' => 'UserController@login']);
    $router->post('user/authen/google', 'UserController@googleAuthen');
    $router->post('user/authen/facebook', 'UserController@facebookAuthen');
    $router->post('user/forgot_password', 'UserController@forgotPassword');
});

$router->group(['prefix' => 'api/v1', 'middleware' => 'jwt.auth'], function ($router) {

    //
    // User
    //
    $router->get('user/{user_id}', 'UserController@getData');
    $router->put('user/{user_id}', 'UserController@updateData');
    $router->put('user/profile_image/{user_id}', 'UserController@uploadImageProfile');
    $router->put('user/change_password/{user_id}', 'UserController@changePassword');
});
