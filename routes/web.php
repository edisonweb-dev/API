<?php

use Illuminate\Support\Facades\Route;

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



Route::get('/tareas/login', 'TaskController@login' );    

Route::get('/tareas', 'TaskController@index');

Route::post('/tareas/guardar', 'TaskController@store');

Route::put('/tareas/actualizar', 'TaskController@update');

Route::delete('/tareas/borrar/{id}', 'TaskController@destroy');

Route::get('/tareas/buscar/{email}', 'TaskController@show');

// Estas rutas requiren de un token válido para poder accederse.
Route::post('/tokens/register', 'UserController@register');
Route::post('/tokens/login', 'UserController@authenticate');
Route::get('/tokens/open', 'DataController@open');

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('/tokens/user', 'UserController@getAuthenticatedUser');
    Route::get('/tokens/closed', 'DataController@closed');
});
      






Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
