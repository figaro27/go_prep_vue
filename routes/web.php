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

// To Be Removed
//Route::get('/getDeliveryRoutes', 'DeliveryRouteController@getRoutes');

Route::get('testDeleteMealOrders', 'TestController@testDeleteMealOrders');
Route::get('testRenewSubscription', 'TestController@testRenewSubscription');

Route::get(
    '/mail/cancelledSubscription',
    'EmailTestController@storeCancelledSubscription'
);
Route::get('/mail/readyToPrint', 'EmailTestController@storeReadyToPrint');
Route::get('/mail/deliveryToday', 'EmailTestController@customerDeliveryToday');
Route::get('/mail/mealPlan', 'EmailTestController@customerMealPlan');
Route::get(
    '/mail/subscriptionRenewing',
    'EmailTestController@customerSubscriptionRenewing'
);
Route::get('/mail/newSubscription', 'EmailTestController@storeNewSubscription');
Route::get('/mail/newOrder', 'EmailTestController@customerNewOrder');
Route::get(
    '/mail/mealPlanPaused',
    'EmailTestController@customerMealPlanPaused'
);
Route::get('/mail/storeNewOrder', 'EmailTestController@storeNewOrder');

foreach ([config('app.domain')] as $domain) {
    Route::any('/stripe/event', 'Billing\\StripeController@event');
    //Auth::routes();
    Route::fallback('SpaController@index');

    Route::group(
        ['domain' => $domain, 'middleware' => ['web', 'store_slug']],
        function ($router) {
            // Override reset password route
            Route::get('forgot/reset/{token}', 'SpaController@index')->name(
                'password.reset'
            );

            // All logged in users
            Route::group(['middleware' => []], function ($router) {
                /*Route::get('/', function (Request $request) {
                $user = auth('api')->user();
                if ($user->hasRole('store')) {
                    return redirect($user->store->getUrl('/store/orders', $request->secure));
                } else {
                    return redirect('/customer/orders');
                }
            });*/

                Route::get('storeMeals', 'MealController@getStoreMeals');

                Route::post('storeMealAdmin', 'MealController@storeAdmin');
                Route::post('updateActive', 'MealController@updateActive');

                Route::get('/getCustomer', 'User\\UserDetailController@show');
            });

            // All logged in stores
            Route::group(['middleware' => ['auth:api']], function ($router) {});

            // All logged in admin
            Route::group(['middleware' => ['auth', 'admin']], function (
                $router
            ) {});
        }
    );
}
