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



//Admin Routes
Route::resource('user', 'UserController');
Route::get('stores', 'StoreController@index');
Route::resource('meals', 'MealController');
Route::post('storeMealAdmin', 'MealController@storeAdmin');
Route::get('storeMeals', 'MealController@getStoreMeals');
Route::post('updateActive', 'MealController@updateActive');

//Store Routes
Route::get('storeCustomers', 'UserController@storeIndex');



Route::fallback('SpaController@index');

Route::post('nutrients', 'NutritionController@getNutrients');
Route::post('searchInstant', 'NutritionController@searchInstant');

Route::resource('ingredients', 'IngredientController');