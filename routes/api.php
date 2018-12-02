<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['middleware' => ['auth:api']], function($router) {

  Route::group(['prefix' => 'me', 'middleware' => ['role.store']], function ($router) {
    Route::resource('meals', 'Store\\MealController');
    Route::resource('ingredients', 'Store\\IngredientController');
    Route::get('customers', 'Store\\CustomerController@index');

  });

});