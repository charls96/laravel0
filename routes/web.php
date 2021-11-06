<?php

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

Route::get('/', function () {
    return view('welcome');
});

//Rutas usuarios
Route::get('usuarios', 'UserController@index')->name('users.index');        //le damos un nombre interno a la ruta
Route::get('usuarios/crear', 'UserController@create')->name('users.create');
Route::post('usuarios', 'UserController@store')->name('users.store');
Route::get('usuarios/{user}/editar', 'UserController@edit')->name('users.edit');
Route::get('usuarios/{user}', 'UserController@show')
    -> where('id', '[0-9]+')->name('users.show');
Route::put('usuarios/{user}', 'UserController@update')->name('users.update');
Route::delete('usuarios/{user}', 'UserController@destroy')->name('users.destroy');

//Rutas saludos
Route::get('saludo/{name}/{nickname?}', 'WelcomeUserController');

