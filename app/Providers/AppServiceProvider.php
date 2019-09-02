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
use App\Services\StorePlanService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('storeplan.service', StorePlanService::class);

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

        /**
         * Handle custom store domains
         */
        $fullHost = request()->getHttpHost();
        $extract = new \LayerShifter\TLDExtract\Extract();
        $hostParts = $extract->parse($fullHost);
        $domain = $hostParts->getRegistrableDomain();
        $domains = config('app.domains');

        if (array_key_exists($domain, $domains)) {
            $hostConfig = $domains[$domain];
            config([
                'app.domain' => $domain,
                'app.url' => 'https://' . $domain,
                'app.front_url' => $hostConfig['front_url']
            ]);
        }

        Schema::defaultStringLength(191);

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
