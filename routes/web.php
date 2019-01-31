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

foreach (['{store_slug}.' . config('app.domain'), config('app.domain')] as $domain) {

    Auth::routes();
    Route::fallback('SpaController@index');

    Route::group(['domain' => $domain, 'middleware' => ['web', 'store_slug']], function ($router) {

        // All logged in users
        Route::group(['middleware' => ['auth:api']], function ($router) {

            Route::get('/', function (Request $request) {
                $user = auth('api')->user();
                if ($user->hasRole('store')) {
                    return redirect($user->store->getUrl('/store/orders', $request->secure));
                } else {
                    return redirect('/customer/orders');
                }
            });

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
        Route::group(['middleware' => ['auth:api']], function ($router) {
            Route::get('/store/stripe/redirect', 'Store\\StripeController@connect');
        });

        // All logged in admin
        Route::group(['middleware' => ['auth', 'admin']], function ($router) {

        });

    });

}
