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

            Route::get('ping', function() {
              if(!auth('api')->check()) {
                return response('', 401);
              }
            });

            
            Route::group(['prefix' => 'me', 'middleware' => ['role.store']], function ($router) {
              Route::patch('user', 'Store\\UserController@update');
              Route::get('user', 'Store\\UserController@show');
              Route::resource('meals', 'Store\\MealController');
              Route::resource('ingredients', 'Store\\IngredientController');
              Route::get('orders/ingredients/export/{type}', 'Store\\OrderIngredientController@export');
              Route::get('orders/ingredients', 'Store\\OrderIngredientController@index');
              Route::resource('orders', 'Store\\OrderController');
              Route::resource('customers', 'Store\\CustomerController');
              Route::resource('units', 'Store\\UnitController');
              Route::resource('categories', 'Store\\CategoryController');
              
              Route::get('stripe/login', 'Store\\StripeController@getLoginLinks');
              Route::resource('stripe', 'Store\\StripeController');

              Route::get('print/{report}/{type}', 'Store\\PrintController@print');
            });
            
            Route::group(['middleware' => ['role.customer']], function ($router) {
              //Route::resource('stores', 'User\\StoreController');
              Route::resource('/user', 'User\\UserController');
              Route::post('bag/checkout', 'User\\CheckoutController@checkout');
              Route::resource('me/subscriptions', 'User\\SubscriptionController');
              Route::resource('me/orders', 'User\\OrderController');
              Route::get('store/{id}/meals', 'User\\StoreController@meals');
              Route::get('store/meals', 'User\\StoreController@meals');
            });
            
            Route::get('getStore', 'StoreDetailController@show');
            Route::get('getStoreSettings', 'Store\\StoreSettingController@show');
            Route::post('updateStoreDetails', 'StoreDetailController@update');
            Route::post('updateStoreSettings', 'Store\\StoreSettingController@update');
            
            
            
          });
    });

}
