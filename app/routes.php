<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/',function(){
    return View::make('hello');
});
Route::get('users/edit/{id}',array('uses'=>'UserController@getUserByID','as'=>'users.getedit'));
Route::get('users/list',array('uses'=>'UserController@getList','as'=>'users.list'));

Route::post('users',array('uses'=>'UserController@create','as'=>'users'));
Route::post('users/activate/{id}/{activationcode}',array('uses'=>'UserController@activate','as'=>'users.activate'));
Route::post('users/login',array('uses'=>'UserController@postLogin','as'=>'users.login'));
Route::post('users/password',array('uses'=>'UserController@retreiveResetPasswordCode','as'=>'users.password'));
Route::post('users/password/{resetpasswordcode}/{id}',array('uses'=>'UserController@postResetPassword','as'=>'users.postresetpassword'));
Route::post('users/delete/{id}',array('uses'=>'UserController@delete','as'=>'users.delete'));
Route::post('users/update/{id}',array('uses'=>'UserController@postUpdate','as'=>'users.update'));




