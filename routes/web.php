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

Route::domain('{store_slug}.'.config('app.domain'))
->group(function ($router) {

});

Auth::routes();
Route::fallback('SpaController@index');


// All logged in users
Route::group(['middleware' => ['auth', 'store_slug']], function ($router) {

  Route::resource('user', 'UserController');
  Route::resource('stores', 'StoreController');

  Route::get('storeMeals', 'MealController@getStoreMeals');

  Route::post('storeMealAdmin', 'MealController@storeAdmin');
  Route::post('updateActive', 'MealController@updateActive');

  Route::post('nutrients', 'NutritionController@getNutrients');
  Route::post('searchInstant', 'NutritionController@searchInstant');

  Route::post('/submitStore', 'ContactFormController@submitStore');
  Route::post('/submitCustomer', 'ContactFormController@submitCustomer');

  Route::get('/getCustomer', 'UserDetailController@show');
  Route::post('/updateCustomer', 'UserDetailController@update');

});

// All logged in stores
Route::group(['middleware' => ['auth'/*, 'store'*/]], function ($router) {

});

// All logged in admin
Route::group(['middleware' => ['auth', 'admin']], function ($router) {

});