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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('role/routes', 'HomeController@role')->middleware('role');

Route::get('role/admin', 'HomeController@admin')->middleware('role');


//Admin Routes
Route::resource('user', 'UserController');
Route::resource('store', 'StoreController');




//Store Routes
Route::get('storeCustomers', 'UserController@storeIndex');

