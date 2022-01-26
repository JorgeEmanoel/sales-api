<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () {
    return 'Sales-API';
});

$router->group(['prefix' => 'providers'], function () use ($router) {
    $router->get('/', 'ProviderController@index');
    $router->post('/', 'ProviderController@store');
    $router->get('{id}', 'ProviderController@show');
    $router->put('{id}', 'ProviderController@update');
    $router->delete('{id}', 'ProviderController@delete');
});

$router->group(['prefix' => 'products'], function () use ($router) {
    $router->get('/', 'ProductController@index');
    $router->post('/', 'ProductController@store');
    $router->get('{id}', 'ProductController@show');
    $router->put('{id}', 'ProductController@update');
    $router->delete('{id}', 'ProductController@delete');
});

$router->group(['prefix' => 'clients'], function () use ($router) {
    $router->get('/', 'ClientController@index');
    $router->post('/', 'ClientController@store');
    $router->get('{id}', 'ClientController@show');
    $router->put('{id}', 'ClientController@update');
    $router->delete('{id}', 'ClientController@delete');
});

$router->group(['prefix' => 'sales'], function () use ($router) {
    $router->get('/', 'SaleController@index');
    $router->get('/{id}/products', 'SaleController@products');
    $router->post('/', 'SaleController@store');
    $router->get('{id}', 'SaleController@show');
    $router->put('{id}', 'SaleController@update');
    $router->delete('{id}', 'SaleController@cancel');
});
