<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//GET ROUTES
Route::get('/', 'Orders\OrdersController@all');
Route::get('/products/', 'Products\ProductsController@all')->name('products');
Route::get('/order/{id}', 'Orders\OrdersController@showOrder');
Route::get('/weather/{lat}/{long}', 'Weather\WeatherController@getWeatherByLatLong');
Route::get('/weather/', 'Weather\WeatherController@getWeatherByOfBryansk');

//POST ROUTES
Route::post('/order/{id}', 'Orders\OrdersController@editOrder')->name('orderEdit');
Route::post('/product/edit/{id}', 'Products\ProductsController@editProduct');