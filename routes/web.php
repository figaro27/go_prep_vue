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
Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();
Route::fallback('SpaController@index');

Route::resource('user', 'UserController');
Route::resource('meals', 'MealController');
Route::resource('ingredients', 'IngredientController');
Route::resource('stores', 'StoreController');

Route::get('storeCustomers', 'UserController@storeIndex');
Route::get('storeMeals', 'MealController@getStoreMeals');

Route::post('storeMealAdmin', 'MealController@storeAdmin');
Route::post('updateActive', 'MealController@updateActive');

Route::post('nutrients', 'NutritionController@getNutrients');
Route::post('searchInstant', 'NutritionController@searchInstant');

Route::post('/submitStore', 'ContactFormController@submitStore');
Route::post('/submitCustomer', 'ContactFormController@submitCustomer');