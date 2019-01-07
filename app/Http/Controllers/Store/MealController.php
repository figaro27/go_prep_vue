<?php

namespace App\Http\Controllers\Store;

use App\Meal;
use Illuminate\Http\Request;
use App\Http\Controllers\Store\StoreController;

class MealController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->has('meals') ?
            $this->store->meals()->with(['orders', 'tags', 'ingredients'])->get() : [];
    }

    public function getStoreMeals()
    {
        $id = auth()->id; 
        $storeID = Store::where('user_id', $id)->pluck('id')->first();

        return Meal::getStoreMeals($storeId);
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
    public function store(Request $request)
    {
        return Meal::storeMeal($request);
    }

    public function storeAdmin(Request $request)
    {
        return Meal::storeMealAdmin($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        return Meal::getMeal($id);
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
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return Meal::updateMeal($id, $request->all());
    }

    public function updateActive(Request $request, $id)
    {
        if($request->has('active')) {
          return Meal::updateActive($id, $request->get('active'));
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
        return Meal::deleteMeal($id);
    }
}
