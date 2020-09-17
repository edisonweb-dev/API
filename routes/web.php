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


Route::group(['middleware' => 'prueba'], function () {
    Route::get('/tareas/login', 'TaskController@login' );    
});

Route::get('/tareas', 'TaskController@index');

Route::post('/tareas/guardar', 'TaskController@store');

Route::put('/tareas/actualizar', 'TaskController@update');

Route::delete('/tareas/borrar/{id}', 'TaskController@destroy');

Route::get('/tareas/buscar/{email}', 'TaskController@show');




Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
