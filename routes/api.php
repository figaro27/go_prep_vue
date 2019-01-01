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

foreach ([config('app.domain'), '{store_slug}.' . config('app.domain')] as $domain) {
    Route::group(['domain' => $domain, 'middleware' => ['view.api', 'store_slug']], function ($router) {

        Route::group(['middleware' => ['view.api']], function ($router) {
            Route::get('/', ['middleware' => ['view.api'], 'uses' => 'SpaController@index']);

            Route::group(['prefix' => 'me', 'middleware' => ['role.store']], function ($router) {
                Route::resource('meals', 'Store\\MealController');
                Route::resource('ingredients', 'Store\\IngredientController');
                Route::get('orders/ingredients/export/{type}', 'Store\\OrderIngredientController@export');
                Route::get('orders/ingredients', 'Store\\OrderIngredientController@index');
                Route::resource('orders', 'Store\\OrderController');
                Route::resource('customers', 'Store\\CustomerController');
                Route::resource('units', 'Store\\UnitController');
            });

            Route::get('store/{id}/meals', 'User\\StoreController@meals');
            Route::get('store/meals', 'User\\StoreController@meals');

        });
    });

}
