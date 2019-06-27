<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Store\StoreController;
use App\Http\Requests\StoreMealPackageRequest;
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
        return $this->store
            ->packages()
            ->with(['meals', 'sizes', 'components', 'addons'])
            ->find($id);
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
    public function update(Request $request, $id)
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
                'addons'
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
