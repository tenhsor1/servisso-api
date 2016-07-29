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
    Route::post('auth/social', 'Auth\AuthController@socialAuth');
    Route::resource('auth', 'Auth\AuthController', ['only' => ['index']]);
    Route::get('auth/refresh', 'Auth\AuthController@refresh');
    Route::post('auth/{role}', 'Auth\AuthController@authenticate')
        ->where('role', '(?i)(user|admin)'); //just match auth/{user|admin}

    Route::post('password/email', 'Auth\PassController@postEmail');
    Route::post('password/reset', 'Auth\PassController@postReset');
    Route::get('password/token/{token}', 'Auth\PassController@checkToken');


	Route::post('users/predict', 'UserController@predict');
	Route::post('users/storeSearched', 'UserController@storeSearched');
	Route::put('users/updateSearched/{id}', 'UserController@updateSearched');
    Route::get('users/confirm/{code}','UserController@confirm');
    Route::get('users/{user_id}/companies', 'UserController@companies');
    Route::put('users/{user_id}/password', 'UserController@updatePassword');
    Route::put('users/{user_id}/preferences', 'UserController@update');
    Route::resource('users', 'UserController', ['only' => ['destroy', 'show', 'store']]);

    Route::post('services/{serviceId}/images', 'ServiceController@setImages');
    Route::resource('guests', 'GuestController', ['only' => ['update', 'destroy', 'show', 'store']]);
    Route::resource('services', 'ServiceController', ['only' => ['update', 'destroy', 'show', 'store', 'index']]);
    Route::resource('calls', 'CallController', ['only' => ['update', 'destroy', 'show', 'store']]);
    Route::resource('sms', 'SmsController', ['only' => ['update', 'destroy', 'show', 'store']]);

	Route::get('companies/{companyId}/services', 'ServiceController@indexPerCompany');
    Route::resource('companies','CompanyController',['only' => ['index','update','destroy','show','store']]);

    Route::get('branches/services/{serviceId}','ServiceController@showFromBranch');//to get a service from company perspective
	Route::get('services/{usereableId}/tasks','ServiceController@taskUser');//to get all request services
	Route::get('task/{usereableId}','ServiceController@task');//to get a request services
    Route::get('branches/{branch}/services','BranchController@services');//to get all services that belongs to one branch
    Route::resource('branches','BranchController',['only' => ['index','update','destroy','show','store']]);
    Route::post('branches/{id}/verifications','BranchVerificationController@store');
    Route::put('branches/{id}/verifications/{verification_id}','BranchVerificationController@update');
    Route::delete('branches/{id}/verifications/{verification_id}','BranchVerificationController@destroy');

    Route::resource('categories','CategoryController',['only' => ['index','update','destroy','show','store']]);
    Route::resource('branchrates','BranchRateController',['only' => ['update','destroy','show','store']]);
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

    Route::get('notifications', 'NotificationController@index');
    Route::put('notifications', 'NotificationController@updateMultiple');

    Route::post('tasks/{taskId}/images', 'TaskController@setImages');
    Route::post('tasks/{taskId}/quotes', 'TaskController@storeQuote');
    Route::get('tasks/{taskId}/tbranches/{taskBranchId}', 'TaskController@showTaskBranch');
    Route::put('tasks/{taskId}/tbranches/{taskBranchId}', 'TaskController@updateTaskBranch');
    Route::get('tasks/{taskId}/tbranches/{taskBranchId}/messages', 'MessageController@indexTaskBranch');
    Route::get('branches/{branchId}/tasks', 'TaskController@indexBranch');
    Route::get('proyect/{companyId}/company', 'TaskController@indexCompany');
    Route::resource('tasks', 'TaskController', ['only' => ['index', 'show', 'store', 'update']]);
	Route::resource('contact', 'ContactUSController', ['only' => ['index','update', 'destroy', 'show', 'store']]);
    Route::resource('messages', 'MessageController', ['only' => ['index', 'show', 'store', 'update', 'destroy']]);

	Route::get('requirements/requested', 'AdminController@requirements');
});




