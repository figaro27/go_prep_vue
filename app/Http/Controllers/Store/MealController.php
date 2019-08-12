<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Store\StoreController;
use App\Http\Requests\StoreMealRequest;
use App\Http\Requests\UpdateMealRequest;
use App\Meal;
use Illuminate\Http\Request;

class MealController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->has('meals')
            ? $this->store
                ->meals()
                ->with(['orders', 'tags', 'ingredients', 'sizes'])
                ->without(['allergies', 'categories', 'store'])
                ->get()
            : [];
    }

    public function getStoreMeals()
    {
        $id = auth()->id;
        $storeId = Store::where('user_id', $id)
            ->pluck('id')
            ->first();

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
    public function store(StoreMealRequest $request)
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
    public function update(UpdateMealRequest $request, $id)
    {
        return Meal::updateMeal(
            $id,
            $request->only([
                'active',
                'title',
                'description',
                'instructions',
                'price',
                'category_ids',
                'tag_ids',
                'allergy_ids',
                'featured_image',
                'gallery',
                'ingredients',
                'sizes',
                'default_size_title',
                'components',
                'addons',
                'macros'
            ])
        );
    }

    public function updateActive(Request $request, $id)
    {
        if ($request->has('active')) {
            return Meal::updateActive($id, $request->get('active'));
        }
    }

    public function deactivateAndReplace(Request $request)
    {
        $mealId = $request->get('mealId');
        $subId = $request->get('substituteId');

        $meal = $this->store->meals()->find($mealId);

        if ($subId) {
            $sub = $this->store->meals()->find($subId);
        }

        if (!$meal) {
            return response()->json(
                [
                    'error' => 'Invalid meal ID'
                ],
                400
            );
        }

        if ($meal->substitute && !$sub) {
            return response()->json(
                [
                    'error' => 'Invalid substitute meal ID'
                ],
                400
            );
        }

        return Meal::deleteMeal($mealId, $subId, true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $meal = $this->store->meals()->find($id);

        $subId = $request->get('substitute_id', null);
        if ($subId) {
            $sub = $this->store->meals()->find($subId);
        }

        if (!$meal) {
            return response()->json(
                [
                    'error' => 'Invalid meal ID'
                ],
                400
            );
        }

        if ($meal->substitute && !$sub) {
            return response()->json(
                [
                    'error' => 'Invalid substitute meal ID'
                ],
                400
            );
        }

        return Meal::deleteMeal($id, $subId);
    }

    public function destroyMealNonSubtitute(Request $request)
    {
        $meal = $this->store->meals()->find($request->id);
        $meal->delete();
    }
}
