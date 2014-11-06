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

Route::get('/', function()
{
	return View::make('hello');
});

Route::post('users',array('uses'=>'UserController@create','as'=>'users'));
Route::post('users/login',array('uses'=>'UserController@postLogin','as'=>'users.login'));
Route::post('users/password/{email}',array('uses'=>'UserController@postResetPassword','as'=>'users.password'));
