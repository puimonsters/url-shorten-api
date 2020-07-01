<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('url_shortens/list', 'UrlShortensController@getList');
Route::get('url_shortens/{url_shorten}', 'UrlShortensController@getById');
Route::get('url_shortens/visit/{short_code}', 'UrlShortensController@visit');
Route::post('url_shortens/create', 'UrlShortensController@create');
Route::post('url_shortens/toggle-delete', 'UrlShortensController@toggleDelete');
