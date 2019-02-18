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
  
    //Auth::routes();
    Route::group([
      'middleware' => 'api',
      'prefix' => 'auth'
    ], function ($router) {
    
        Route::post('login', 'AuthController@login');
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::post('me', 'AuthController@me');
        Route::post('register', 'Auth\RegisterController@register');
        Route::post('register/validate/{step}', 'Auth\RegisterController@validateStep');
    
    });
    
    Route::group(['domain' => $domain, 'middleware' => ['api', 'view.api', 'store_slug']], function ($router) {
      
      
      Route::get('ping', function () {
        if (!auth('api')->check()) {
          return response('', 401);
        }
      });
      
      Route::get('/', ['middleware' => ['view.api'], 'uses' => 'SpaController@index']);
      Route::group(['middleware' => ['auth:api']], function ($router) {
        $user = auth('api')->user();

            if ($user && $user->user_role_id === 2) {

                Route::post('nutrients', 'NutritionController@getNutrients');
                Route::post('searchInstant', 'NutritionController@searchInstant');

                Route::group(['prefix' => 'me', 'middleware' => ['role.store'], 'namespace' => 'Store'], function ($router) {
                    Route::patch('user', 'UserController@update');
                    Route::get('user', 'UserController@show');

                    Route::get('orders/ingredients/export/{type}', 'OrderIngredientController@export');
                    Route::get('orders/ingredients', 'OrderIngredientController@index');

                    Route::resource('meals', 'MealController');
                    Route::resource('ingredients', 'IngredientController');
                    Route::resource('orders', 'OrderController');
                    Route::get('ordersUpdateViewed', 'OrderController@updateViewed');
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
                    Route::patch('/me/detail', 'UserDetailController@update');

                    Route::post('bag/checkout', 'CheckoutController@checkout');
                    Route::resource('me/subscriptions', 'SubscriptionController');
                    Route::post('me/subscriptions/{id}/pause', 'SubscriptionController@pause');
                    Route::post('me/subscriptions/{id}/resume', 'SubscriptionController@resume');
                    Route::patch('me/subscriptions/{id}/meals', 'SubscriptionController@updateMeals');
                    Route::resource('me/orders', 'OrderController');
                    Route::get('stores', 'StoreController@index');
                    Route::get('store/{id}/meals', 'StoreController@meals');
                    Route::get('store/meals', 'StoreController@meals');

                    Route::resource('me/cards', 'Billing\CardController');

                    Route::resource('/me', 'UserController');
                });
            }
        });

        Route::get('store/viewed', 'SpaController@getViewedStore');

        Route::get('getStore', 'StoreDetailController@show');
        Route::patch('storeDetail', 'StoreDetailController@update');
        Route::post('updateStoreSettings', 'Store\\StoreSettingController@update');

    });

}
