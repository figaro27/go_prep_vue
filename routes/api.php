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
    Route::get('test/mail', 'TestController@test_mail');
    Route::get('test/print', 'TestController@test_print');
    // Auth routes
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

            Route::get('plans', function () {
                return [
                    'plans' => config('plans')
                ];
            });

            Route::get('/', [
                'middleware' => ['view.api'],
                'uses' => 'SpaController@index'
            ]);

            Route::get('/context', [
                'middleware' => ['view.api'],
                'uses' => 'SpaController@context'
            ]);

            Route::get('/optimized', [
                'middleware' => ['view.api'],
                'uses' => 'SpaController@optimized'
            ]);

            Route::get('/refresh', [
                'middleware' => ['view.api'],
                'uses' => 'SpaController@refresh'
            ]);

            Route::get('/refresh_lazy', [
                'middleware' => ['view.api'],
                'uses' => 'SpaController@refresh_lazy'
            ]);

            Route::get('/refresh_lazy_store', [
                'middleware' => ['view.api'],
                'uses' => 'SpaController@refresh_lazy_store'
            ]);

            Route::get('/refresh/meal/{meal_id}', [
                'middleware' => ['view.api'],
                'uses' => 'SpaController@refreshMeal'
            ]);

            Route::get('/refresh_bag/meal/{meal_id}', [
                'middleware' => ['view.api'],
                'uses' => 'SpaController@refreshMealBag'
            ]);

            Route::get('/refresh/meal_package/{meal_package_id}', [
                'middleware' => ['view.api'],
                'uses' => 'SpaController@refreshMealPackage'
            ]);

            Route::get('/refresh_bag/meal_package/{meal_package_id}', [
                'middleware' => ['view.api'],
                'uses' => 'SpaController@refreshMealPackageBag'
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
                            Route::get(
                                'viewedUpdates',
                                'StoreController@viewedUpdates'
                            );
                            Route::patch('user', 'UserController@update');
                            Route::post(
                                'updateCustomerEmail',
                                'CustomerController@updateEmail'
                            );
                            Route::get('user', 'UserController@show');

                            Route::resource('register', 'RegisterController');
                            Route::post(
                                'checkExistingCustomer',
                                'RegisterController@checkExistingCustomer'
                            );
                            Route::post(
                                'addExistingCustomer',
                                'RegisterController@addExistingCustomer'
                            );

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
                                'deactivateAndReplace',
                                'MealController@deactivateAndReplace'
                            );
                            Route::post(
                                'destroyMealNonSubstitute',
                                'MealController@destroyMealNonSubtitute'
                            );
                            Route::post(
                                'saveMealServings',
                                'MealController@saveMealServings'
                            );
                            Route::resource(
                                'ingredients',
                                'IngredientController'
                            );
                            Route::post(
                                'ingredients/adjust',
                                'IngredientController@adjust'
                            );
                            Route::resource('orders', 'OrderController');
                            Route::post('getOrders', 'OrderController@index');
                            Route::post(
                                'getUpcomingOrders',
                                'OrderController@getUpcomingOrders'
                            );
                            Route::post(
                                'getUpcomingOrdersWithoutItems',
                                'OrderController@getUpcomingOrdersWithoutItems'
                            );
                            Route::post(
                                'getOrdersToday',
                                'OrderController@getOrdersToday'
                            );
                            Route::post(
                                'getFulfilledOrders',
                                'OrderController@getFulfilledOrders'
                            );
                            Route::post(
                                'getOrdersWithDates',
                                'OrderController@getOrdersWithDates'
                            );
                            Route::get(
                                'ordersUpdateViewed',
                                'OrderController@updateViewed'
                            );
                            Route::get(
                                'getLatestOrder',
                                'OrderController@getLatestOrder'
                            );
                            Route::post(
                                'orders/adjustOrder',
                                'OrderController@adjustOrder'
                            );
                            Route::post(
                                'refundOrder',
                                'OrderController@refundOrder'
                            );
                            Route::post(
                                'voidOrder',
                                'OrderController@voidOrder'
                            );
                            Route::post(
                                'updateBalance',
                                'OrderController@updateBalance'
                            );
                            Route::resource(
                                'subscriptions',
                                'SubscriptionController'
                            );
                            // Route::post(
                            //     'subscriptions/{id}/meals',
                            //     'SubscriptionController@updateMeals'
                            // );
                            Route::post(
                                'subscriptions/{id}/meals',
                                'SubscriptionController@updateMeals'
                            );
                            Route::post(
                                'subscriptions/pause',
                                'SubscriptionController@pause'
                            );
                            Route::post(
                                'subscriptions/resume',
                                'SubscriptionController@resume'
                            );
                            Route::resource('customers', 'CustomerController');
                            Route::get(
                                'customersNoOrders',
                                'CustomerController@customersNoOrders'
                            );
                            Route::post(
                                'getCards',
                                'CustomerController@getCards'
                            );
                            Route::resource('units', 'UnitController');
                            Route::resource('categories', 'CategoryController');
                            Route::resource('coupons', 'CouponController');
                            Route::resource(
                                'pickupLocations',
                                'PickupLocationController'
                            );
                            Route::resource('lineItems', 'LineItemController');
                            Route::resource(
                                'productionGroups',
                                'ProductionGroupController'
                            );
                            Route::resource(
                                'settings',
                                'StoreSettingController'
                            );
                            Route::get(
                                'getApplicationFee',
                                'StoreSettingController@getApplicationFee'
                            );
                            Route::resource('cards', 'CardController');
                            Route::post(
                                'pauseMealPlans',
                                'StoreSettingController@pauseMealPlans'
                            );
                            Route::post(
                                'cancelMealPlans',
                                'StoreSettingController@cancelMealPlans'
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

                            Route::get(
                                'stripe/connect/url',
                                'StripeController@connectUrl'
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
                            Route::get(
                                'order_bag/{order_id}',
                                'CheckoutController@orderBag'
                            );
                            Route::post(
                                'checkout',
                                'CheckoutController@checkout'
                            );
                            Route::post('charge', 'OrderController@charge');

                            Route::post(
                                'settleBalance',
                                'OrderController@settleBalance'
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

                            Route::get(
                                '/me/getCustomer',
                                'UserController@getCustomer'
                            );

                            Route::patch(
                                '/me/detail',
                                'UserDetailController@update'
                            );

                            Route::post(
                                '/me/addBillingAddress',
                                'UserDetailController@addBillingAddress'
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
                            Route::get(
                                'me/getSubscriptionPickup/{id}',
                                'SubscriptionController@getSubscriptionPickup'
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
