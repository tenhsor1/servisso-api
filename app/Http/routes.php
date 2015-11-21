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
    Route::resource('guest', 'GuestController', ['only' => ['update', 'destroy', 'show', 'store']]);
    Route::resource('service', 'ServiceController', ['only' => ['update', 'destroy', 'show', 'store', 'index']]);
    Route::resource('call', 'CallController', ['only' => ['update', 'destroy', 'show', 'store']]);
    Route::resource('sms', 'SmsController', ['only' => ['update', 'destroy', 'show', 'store']]);

    Route::resource('partner','PartnerController',['only' => ['update','destroy','show','store','index']]);
    Route::resource('company','CompanyController',['only' => ['update','destroy','show','store']]);

    Route::get('branch/{branch}/tags','BranchController@tags');//to get all tags that belongs to one branch
    Route::get('branch/{branch}/services','BranchController@services');//to get all services that belongs to one branch
    Route::post('branch/tag','BranchController@tagStore');//to store a tag that belongs to one branch
    Route::put('branch/tag/{tag}','BranchController@tagUpdate');//to update a tag that belongs to one branch
    Route::delete('branch/tag/{tag}','BranchController@tagDestroy');//to delete a tag that belongs to one branch
    Route::resource('branch','BranchController',['only' => ['update','destroy','show','store']]);

    Route::resource('categories','CategoryController',['only' => ['index','update','destroy','show','store']]);
    Route::resource('partnerrate','PartnerRateController',['only' => ['update','destroy','show','store']]);
    Route::resource('userrate','UserRateController',['only' => ['update','destroy','show','store']]);

    Route::resource('admin', 'AdminController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
    Route::resource('news', 'NewController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
    Route::resource('newcomment', 'NewCommentController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
});
