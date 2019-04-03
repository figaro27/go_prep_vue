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
                ->with(['orders'])
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
                'meals'
            ])
        );
        $props->put('store_id', $this->store->id);

        return MealPackage::store($props);
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
        //return Meal::getMeal($id);
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

        return $package->_update(
            $id,
            $request->only([
                'active',
                'title',
                'description',
                'price',
                'featured_image',
                'meals'
            ])
        );
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
    public function destroy(Request $request, $id)
    {
        $package = $this->store->packages()->find($id);

        $subId = $request->get('substitute_id', null);
        if ($subId) {
            $sub = $this->store->package()->find($subId);
        }

        if (!$package) {
            return response()->json(
                [
                    'error' => 'Invalid meal ID'
                ],
                400
            );
        }

        if ($package->substitute && !$sub) {
            return response()->json(
                [
                    'error' => 'Invalid substitute package ID'
                ],
                400
            );
        }

        return $package->_delete($id, $subId);
    }

    public function destroyPackageNonSubtitute(Request $request)
    {
        $package = $this->store->packages()->find($request->id);
        $package->delete();
    }
}
