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
    Route::get('auth/refresh', 'Auth\AuthController@refresh');
    Route::post('auth/{role}', 'Auth\AuthController@authenticate')
        ->where('role', '(?i)(user|admin|partner)'); //just match auth/{user|admin|partner}

	Route::post('user/predict', 'UserController@predict');
    Route::resource('user', 'UserController', ['only' => ['update', 'destroy', 'show', 'store']]);
    Route::resource('guest', 'GuestController', ['only' => ['update', 'destroy', 'show', 'store']]);
    Route::resource('service', 'ServiceController', ['only' => ['update', 'destroy', 'show', 'store', 'index']]);
    Route::resource('call', 'CallController', ['only' => ['update', 'destroy', 'show', 'store']]);
    Route::resource('sms', 'SmsController', ['only' => ['update', 'destroy', 'show', 'store']]);

	Route::resource('partner','PartnerController',['only' => ['update','destroy','show','store','index']]);
	Route::resource('company','CompanyController',['only' => ['index','update','destroy','show','store']]);
    Route::get('partner/{partner_id}/companies', 'PartnerController@companies');

    Route::post('partner/confirm','PartnerController@confirm');
    Route::post('user/confirm','UserController@confirm');

    Route::get('branches/{branch}/services','BranchController@services');//to get all services that belongs to one branch
    Route::resource('branches','BranchController',['only' => ['index','update','destroy','show','store']]);

    Route::resource('categories','CategoryController',['only' => ['index','update','destroy','show','store']]);
    Route::resource('partnerrate','PartnerRateController',['only' => ['update','destroy','show','store']]);
    Route::resource('userrate','UserRateController',['only' => ['update','destroy','show','store']]);
	Route::get('category/{id}/tags','CategoryController@categoryTags');
	Route::resource('category','CategoryController',['only' => ['index']]);
	Route::resource('tag','TagController',['only' => ['index','store','show','update','destroy']]);


    Route::resource('admin', 'AdminController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
    Route::resource('news', 'NewController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
    Route::resource('newcomment', 'NewCommentController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
	Route::get('country/{id}/state', 'StateController@states');
	Route::resource('country', 'CountryController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
	Route::resource('state', 'StateController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
    Route::resource('email', 'EmailController', ['only' => ['store']]);
	Route::post('image/{id}', 'CompanyController@image');
	
});




