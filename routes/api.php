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

foreach (
    [config('app.domain'), '{store_slug}.' . config('app.domain')]
    as $domain
) {
    //Auth::routes();
    Route::group(
        [
            'middleware' => ['api', 'store_slug'],
            'prefix' => 'auth'
        ],
        function ($router) {
            Route::post('login', 'AuthController@login');
            Route::post('logout', 'AuthController@logout');
            Route::post('refresh', 'AuthController@refresh');
            Route::post('me', 'AuthController@me');
            Route::post('register', 'Auth\RegisterController@register');
            Route::post(
                'register/validate/{step}',
                'Auth\RegisterController@validateStep'
            );

            // Password resetting
            Route::post(
                'forgot',
                'Auth\ForgotPasswordController@sendResetLinkEmail'
            );
            Route::post('reset', 'Auth\ResetPasswordController@reset');
        }
    );

    Route::group(
        [
            'domain' => $domain,
            'middleware' => ['api', 'view.api', 'store_slug']
        ],
        function ($router) {
            Route::get('ping', function () {
                if (!auth('api')->check()) {
                    return response('', 401);
                }
            });

            Route::get('/', [
                'middleware' => ['view.api'],
                'uses' => 'SpaController@index'
            ]);

            Route::group(['middleware' => ['auth:api']], function ($router) {
                $user = auth('api')->user();

                if ($user && $user->user_role_id === 2) {
                    Route::post(
                        'nutrients/{nixId?}',
                        'NutritionController@getNutrients'
                    );
                    Route::post(
                        'searchInstant',
                        'NutritionController@searchInstant'
                    );
                    Route::post('contact', 'ContactFormController@submitStore');

                    Route::group(
                        [
                            'prefix' => 'me',
                            'middleware' => ['role.store'],
                            'namespace' => 'Store'
                        ],
                        function ($router) {
                            Route::patch('user', 'UserController@update');
                            Route::get('user', 'UserController@show');

                            Route::get(
                                'orders/ingredients/export/{type}',
                                'OrderIngredientController@export'
                            );
                            Route::get(
                                'orders/ingredients',
                                'OrderIngredientController@index'
                            );

                            Route::resource('meals', 'MealController');
                            Route::resource(
                                'packages',
                                'MealPackageController'
                            );
                            Route::post(
                                'destroyMealNonSubstitute',
                                'MealController@destroyMealNonSubtitute'
                            );
                            Route::resource(
                                'ingredients',
                                'IngredientController'
                            );
                            Route::resource('orders', 'OrderController');
                            Route::post('getOrders', 'OrderController@index');
                            Route::post(
                                'getFulfilledOrders',
                                'OrderController@getFulfilledOrders'
                            );
                            Route::get(
                                'ordersUpdateViewed',
                                'OrderController@updateViewed'
                            );
                            Route::resource(
                                'subscriptions',
                                'SubscriptionController'
                            );
                            Route::resource('customers', 'CustomerController');
                            Route::resource('units', 'UnitController');
                            Route::resource('categories', 'CategoryController');
                            Route::resource(
                                'settings',
                                'StoreSettingController'
                            );
                            Route::post(
                                'pauseMealPlans',
                                'StoreSettingController@pauseMealPlans'
                            );

                            Route::get(
                                'getStore',
                                'StoreDetailController@show'
                            );
                            Route::patch(
                                'details',
                                'StoreDetailController@update'
                            );
                            Route::patch(
                                'updateLogo',
                                'StoreDetailController@updateLogo'
                            );
                            Route::patch(
                                'settings',
                                'StoreSettingController@update'
                            );

                            Route::post(
                                'stripe/connect',
                                'StripeController@connect'
                            );
                            Route::get(
                                'stripe/login',
                                'StripeController@getLoginLinks'
                            );

                            Route::get(
                                'print/{report}/{type}',
                                'PrintController@print'
                            );

                            Route::get(
                                'acceptedTOA',
                                'StoreDetailController@acceptedTOA'
                            );
                            Route::get(
                                'getAcceptedTOA',
                                'StoreDetailController@getAcceptedTOA'
                            );
                        }
                    );
                } elseif ($user && $user->user_role_id === 1) {
                    Route::post(
                        'contact',
                        'ContactFormController@submitCustomer'
                    );

                    Route::group(
                        [
                            'middleware' => ['role.customer'],
                            'namespace' => 'User'
                        ],
                        function ($router) {
                            //Route::resource('stores', 'User\\StoreController');
                            Route::patch(
                                '/me/detail',
                                'UserDetailController@update'
                            );

                            Route::patch(
                                '/me/password',
                                'PasswordController@update'
                            );

                            Route::post(
                                'bag/checkout',
                                'CheckoutController@checkout'
                            );
                            Route::resource(
                                'me/subscriptions',
                                'SubscriptionController'
                            );
                            Route::post(
                                'me/subscriptions/{id}/pause',
                                'SubscriptionController@pause'
                            );
                            Route::post(
                                'me/subscriptions/{id}/resume',
                                'SubscriptionController@resume'
                            );
                            Route::post(
                                'me/subscriptions/{id}/meals',
                                'SubscriptionController@updateMeals'
                            );
                            Route::resource('me/orders', 'OrderController');
                            Route::get('stores', 'StoreController@index');
                            Route::get(
                                'store/{id}/meals',
                                'StoreController@meals'
                            );
                            Route::get('store/meals', 'StoreController@meals');

                            Route::resource(
                                'me/cards',
                                'Billing\CardController'
                            );

                            Route::resource('/me', 'UserController');
                        }
                    );
                }
            });

            Route::get('store/viewed', 'SpaController@getViewedStore');
        }
    );
}
