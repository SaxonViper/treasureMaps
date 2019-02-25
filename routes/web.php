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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/swag', 'SwagController@index');
Route::get('/swag/query', 'SwagController@query');
Route::get('/vue',  function () {
    return view('index');
});

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');

Route::get('/vue/api', 'ApiController@f1');
Route::post('/my_login', 'MyAuthController@login');
Route::post('/my_register', 'MyAuthController@register');
Route::get('/my_check_auth', 'MyAuthController@checkCurrentUser');
Route::get('/my_logout', 'MyAuthController@logout');

// тестовый прогон карты
Route::get('/treasure_maps', 'TreasureMapController@listAll');
Route::get('/treasure_maps/{id}/cells', 'TreasureMapController@showCells');
Route::get('/treasure_maps/add_test', 'TreasureMapController@createOneTest');
Route::get('/treasure_maps/add_cell', 'TreasureMapController@createCell');

// Попытаемся собрать всю инфу для отрисовки карты
Route::get('/treasure_maps/{id}', 'TreasureMapController@getMapData')->where('id', '[0-9]+');
Route::post('/treasure_maps/{id}', 'TreasureMapController@saveMapData')->where('id', '[0-9]+');
Route::delete('/treasure_maps/{id}', 'TreasureMapController@deleteMap')->where('id', '[0-9]+');
Route::get('/treasure_maps/make_fake', 'TreasureMapController@generateFakeMap');
