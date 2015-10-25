<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'v1'], function()
{
    Route::resource('auth', 'Auth\AuthController', ['only' => ['index']]);
    Route::post('auth/{role}', 'Auth\AuthController@authenticate')
        ->where('role', '(?i)(user|admin|partner)'); //just match auth/{user|admin|partner}

    Route::resource('user', 'UserController', ['only' => ['update', 'destroy', 'show', 'store']]);
});
