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
        ->where('role', '(?i)(user|admin)'); //just match auth/{user|admin}

    Route::post('password/email', 'Auth\PasswordController@postEmail');
	Route::post('users/predict', 'UserController@predict');
    Route::post('users/confirm','UserController@confirm');
    Route::get('users/{user_id}/companies', 'UserController@companies');
    Route::resource('users', 'UserController', ['only' => ['update', 'destroy', 'show', 'store']]);


    Route::resource('guests', 'GuestController', ['only' => ['update', 'destroy', 'show', 'store']]);
    Route::resource('services', 'ServiceController', ['only' => ['update', 'destroy', 'show', 'store', 'index']]);
    Route::resource('calls', 'CallController', ['only' => ['update', 'destroy', 'show', 'store']]);
    Route::resource('sms', 'SmsController', ['only' => ['update', 'destroy', 'show', 'store']]);

	Route::resource('companies','CompanyController',['only' => ['index','update','destroy','show','store']]);

    Route::get('branches/{branch}/services','BranchController@services');//to get all services that belongs to one branch
    Route::resource('branches','BranchController',['only' => ['index','update','destroy','show','store']]);

    Route::resource('categories','CategoryController',['only' => ['index','update','destroy','show','store']]);
    Route::resource('partnerrates','PartnerRateController',['only' => ['update','destroy','show','store']]);
    Route::resource('userrates','UserRateController',['only' => ['update','destroy','show','store']]);
	Route::get('categories/{id}/tags','CategoryController@categoryTags');
	Route::resource('categories','CategoryController',['only' => ['index']]);
	Route::resource('tags','TagController',['only' => ['index','store','show','update','destroy']]);


    Route::resource('admins', 'AdminController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
    Route::resource('news', 'NewController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
    Route::resource('newcomments', 'NewCommentController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
	Route::get('countries/{id}/states', 'StateController@states');
	Route::resource('countries', 'CountryController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
	Route::resource('states', 'StateController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
    Route::resource('emails', 'EmailController', ['only' => ['store']]);
	Route::post('images/{id}', 'CompanyController@image');

});




