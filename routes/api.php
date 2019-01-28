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
        $user = auth('api')->user();

        Route::get('/', ['middleware' => ['view.api'], 'uses' => 'SpaController@index']);

        Route::get('ping', function () {
            if (!auth('api')->check()) {
                return response('', 401);
            }
        });

        if ($user && $user->user_role_id === 2) {

            Route::group(['prefix' => 'me', 'middleware' => ['role.store'], 'namespace' => 'Store'], function ($router) {
                Route::patch('user', 'UserController@update');
                Route::get('user', 'UserController@show');

                Route::get('orders/ingredients/export/{type}', 'OrderIngredientController@export');
                Route::get('orders/ingredients', 'OrderIngredientController@index');

                Route::resource('meals', 'MealController');
                Route::resource('ingredients', 'IngredientController');
                Route::resource('orders', 'OrderController');
                Route::resource('subscriptions', 'SubscriptionController');
                Route::resource('customers', 'CustomerController');
                Route::resource('units', 'UnitController');
                Route::resource('categories', 'CategoryController');
                Route::resource('settings', 'StoreSettingController');

                Route::get('stripe/login', 'StripeController@getLoginLinks');
                Route::resource('stripe', 'StripeController');

                Route::get('print/{report}/{type}', 'PrintController@print');
            });
        } else if ($user && $user->user_role_id === 1) {
            Route::group(['middleware' => ['role.customer'], 'namespace' => 'User'], function ($router) {
                //Route::resource('stores', 'User\\StoreController');
                Route::resource('/user', 'UserController');
                Route::post('bag/checkout', 'CheckoutController@checkout');
                Route::resource('me/subscriptions', 'SubscriptionController');
                Route::resource('me/orders', 'OrderController');
                Route::get('stores', 'StoreController@index');
                Route::get('store/{id}/meals', 'StoreController@meals');
                Route::get('store/meals', 'StoreController@meals');

                Route::resource('me/cards', 'Billing\CardController');
            });
        }

        Route::get('store/viewed', 'SpaController@getViewedStore');

        Route::get('getStore', 'StoreDetailController@show');
        Route::patch('storeDetail', 'StoreDetailController@update');
        Route::post('updateStoreSettings', 'Store\\StoreSettingController@update');

    });

}
