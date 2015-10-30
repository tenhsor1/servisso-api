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

Route::resource('partner','PartnerController');
Route::resource('company','CompanyController');
Route::resource('branch','BranchController');

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'v1'], function()
{
    Route::resource('authenticate', 'Auth\UserAuthController', ['only' => ['index']]);
    Route::post('authenticate', 'Auth\UserAuthController@authenticate');
});
