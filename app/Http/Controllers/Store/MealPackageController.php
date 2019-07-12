<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Store\StoreController;
use App\Http\Requests\StoreMealPackageRequest;
use App\Http\Requests\UpdateMealPackageRequest;
use App\Meal;
use App\MealPackage;
use Illuminate\Http\Request;

class MealPackageController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->has('packages')
            ? $this->store
                ->packages()
                ->with(['meals'])
                ->without([])
                ->get()
            : [];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMealPackageRequest $request)
    {
        $props = collect(
            $request->only([
                'active',
                'title',
                'description',
                'price',
                'featured_image',
                'meals',
                'sizes',
                'default_size_title',
                'components',
                'addons'
            ])
        );
        $props->put('store_id', $this->store->id);

        return MealPackage::_store($props);
    }

    public function storeAdmin(Request $request)
    {
        //return Meal::storeMealAdmin($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $package = collect(
            $this->store
                ->packages()
                ->with([
                    'meals',
                    'sizes',
                    'sizes.meals',
                    'components',
                    'addons',
                    'addons.meals'
                ])
                ->find($id)
        );

        return $package->map(function ($val, $key) {
            if ($key === 'meals') {
                return collect($val)->map(function ($meal) {
                    $meal = collect($meal);
                    $size = $meal->get('meal_size');

                    return [
                        'id' => $meal->get('id'),
                        'quantity' => $meal->get('quantity'),
                        'meal_size_id' => $size ? $size['id'] : null
                    ];
                });
            }

            if ($key === 'sizes') {
                return collect($val)->map(function ($size) {
                    return collect($size)
                        ->only(['id', 'title', 'price', 'meals'])
                        ->map(function ($val, $key) {
                            if ($key === 'meals') {
                                return collect($val)->map(function ($meal) {
                                    return [
                                        'id' => $meal['id'],
                                        'quantity' => $meal['quantity'],
                                        'meal_size_id' =>
                                            $meal['pivot']['meal_size_id']
                                    ];
                                });
                            }
                            return $val;
                        });
                });
            }

            // components
            if ($key === 'components') {
                return collect($val)->map(function ($component) {
                    return collect($component)
                        ->only(['id', 'title', 'minimum', 'maximum', 'options'])
                        ->map(function ($val, $key) {
                            if ($key === 'options') {
                                return collect($val)->map(function ($option) {
                                    return collect($option)->map(function (
                                        $val,
                                        $key
                                    ) {
                                        if ($key === 'meals') {
                                            return collect($val)->map(function (
                                                $meal
                                            ) {
                                                return [
                                                    'id' => $meal['meal_id'],
                                                    'quantity' =>
                                                        $meal['quantity'],
                                                    'meal_size_id' =>
                                                        $meal['meal_size_id']
                                                    //'price' => $meal['price']
                                                ];
                                            });
                                        }
                                        return $val;
                                    });
                                });
                            }

                            return $val;
                        });
                });
            }
            // - components

            // addons
            if ($key === 'addons') {
                return collect($val)->map(function ($addon) {
                    return collect($addon)
                        ->only([
                            'id',
                            'title',
                            'meals',
                            'meal_package_size_id',
                            'selectable',
                            'price'
                        ])
                        ->map(function ($val, $key) {
                            if ($key === 'meals') {
                                return collect($val)->map(function ($meal) {
                                    return [
                                        'id' => $meal['id'],
                                        'quantity' => $meal['quantity'],
                                        'meal_size_id' =>
                                            $meal['pivot']['meal_size_id']
                                    ];
                                });
                            }
                            return $val;
                        });
                });
            }
            // - addons

            return $val;
        });
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function edit(Meal $meal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMealPackageRequest $request, $id)
    {
        $package = $this->store->packages()->find($id);

        $props = collect(
            $request->only([
                'active',
                'title',
                'description',
                'price',
                'featured_image',
                'meals',
                'sizes',
                'default_size_title',
                'components',
                'addons',
                'meal_carousel'
            ])
        );

        return $package->_update($props);
    }

    public function updateActive(Request $request, $id)
    {
        $package = $this->store->packages()->find($id);

        if ($request->has('active')) {
            return $package->updateActive($id, $request->get('active'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $package = $this->store->packages()->find($id);
        $package->delete();
    }

    public function destroyPackageNonSubtitute(Request $request)
    {
        $package = $this->store->packages()->find($request->id);
        $package->delete();
    }
}
