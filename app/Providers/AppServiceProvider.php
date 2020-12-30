<?php

namespace App\Providers;

use App\Meal;
use App\MealTag;
use App\Observers\MealObserver;
use App\Observers\MealTagObserver;
use Braintree_Configuration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
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

        $this->setupCustomDomain();

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

        Builder::macro('whereLike', function ($attributes, $searchTerm) {
            $this->where(function (Builder $query) use (
                $attributes,
                $searchTerm
            ) {
                foreach (array_wrap($attributes) as $attribute) {
                    $query->when(
                        str_contains($attribute, '.'),
                        function (Builder $query) use (
                            $attribute,
                            $searchTerm
                        ) {
                            $cmps = explode('.', $attribute);

                            if (count($cmps) > 2) {
                                $query->orWhereHas($cmps[0], function (
                                    Builder $query
                                ) use ($cmps, $searchTerm) {
                                    $query->whereHas($cmps[1], function (
                                        Builder $query
                                    ) use ($cmps, $searchTerm) {
                                        $query->where(
                                            $cmps[2],
                                            'LIKE',
                                            "%{$searchTerm}%"
                                        );
                                    });
                                });
                            } else {
                                [$relationName, $relationAttribute] = $cmps;

                                $query->orWhereHas($relationName, function (
                                    Builder $query
                                ) use ($relationAttribute, $searchTerm) {
                                    $query->where(
                                        $relationAttribute,
                                        'LIKE',
                                        "%{$searchTerm}%"
                                    );
                                });
                            }
                        },
                        function (Builder $query) use (
                            $attribute,
                            $searchTerm
                        ) {
                            $query->orWhere(
                                $attribute,
                                'LIKE',
                                "%{$searchTerm}%"
                            );
                        }
                    );
                }
            });

            return $this;
        });
    }

    /**
     * Handle custom store domain
     */
    protected function setupCustomDomain(): void
    {
        $fullHost = request()->getHttpHost();
        $extract = new \LayerShifter\TLDExtract\Extract();
        $hostParts = $extract->parse($fullHost);
        $domain = $hostParts->getRegistrableDomain();
        $domains = config('app.domains');

        if (array_key_exists($domain, $domains)) {
            $hostConfig = $domains[$domain];
            $protocol = config('app.secure') ? 'https://' : 'http://';

            // Replace core config
            config([
                'app.domain' => $domain,
                'app.url' => $protocol . $domain,
                'app.front_url' => $hostConfig['front_url'],
                'app.urls.logout_redirect' =>
                    $hostConfig['logout_redirect_url'] ??
                    config('app.urls.logout_redirect')
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Custom storage path
         */
        $storagePath = config('app.storage_path');
        if ($storagePath && is_dir($storagePath)) {
            $this->app->useStoragePath($storagePath);
        }
    }
}
