<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Store\StoreController;
use App\Meal;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CategoryController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->has('categories') ? $this->store->categories : [];
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
        if ($request->has('category')) {
            $newCat = new Category();
            $newCat->category = $request->get('category', '');
            $newCat->store_id = $this->store->id;
            $newCat->order = $this->store->categories()->count() + 1;
            $newCat->save();
            return $newCat;
        } elseif ($request->has('categories')) {
            $cats = collect($request->get('categories'));
            $cats = $cats->sortBy('order');

            foreach ($cats->values() as $i => $cat) {
                $c = $this->store->categories()->find($cat['id']);
                if ($c) {
                    $c->order = $i;
                    $c->save();
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
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
        $store = $this->store;

        $category = $store->categories()->findOrFail($id);
        $category->category = $request->get('category');

        $dateRangeFrom = $request->get('date_range_from', null);
        $dateRangeTo = $request->get('date_range_to', null);

        if ($dateRangeFrom && $dateRangeTo) {
            $dateRangeFrom = new Carbon($dateRangeFrom);
            $dateRangeTo = new Carbon($dateRangeTo);
        }

        if ($store->modules->category_restrictions) {
            $category->date_range = $request->get('date_range', false);
            $category->date_range_from = $dateRangeFrom;
            $category->date_range_to = $dateRangeTo;
            $category->date_range_exclusive = $request->get(
                'date_range_exclusive',
                false
            );
        }

        $category->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $cat = $this->store->categories()->find($id);

        if ($cat) {
            $cat->delete();
        }
    }
}
