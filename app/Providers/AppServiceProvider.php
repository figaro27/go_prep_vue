<?php

namespace App\Providers;

use App\Meal;
use App\MealTag;
use App\Observers\MealObserver;
use App\Observers\MealTagObserver;
use Braintree_Configuration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (
            config('app.env') === 'production' ||
            config('app.env') === 'staging'
        ) {
            // Exclude goprep.localhost from SSL protection
            if (
                false === strpos(app('request')->fullUrl(), 'goprep.localhost')
            ) {
                \URL::forceScheme('https');
            }
        }

        Schema::defaultStringLength(191);

        Braintree_Configuration::environment(env('BRAINTREE_ENV'));
        Braintree_Configuration::merchantId(env('BRAINTREE_MERCHANT_ID'));
        Braintree_Configuration::publicKey(env('BRAINTREE_PUBLIC_KEY'));
        Braintree_Configuration::privateKey(env('BRAINTREE_PRIVATE_KEY'));

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        Meal::observe(MealObserver::class);
        MealTag::observe(MealTagObserver::class);

        Meal::saved(function ($meal) {
            $meal->store->clearCaches();
        });

        // UnitsOfMeasure aliases
        $unit = Volume::getUnit('tbsp');
        $unit->addAlias('Tbs');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
