<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Store\StoreController;
use App\Meal;
use App\Category;
use Illuminate\Http\Request;

class CategoryController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->has('categories') ?
        $this->store->categories : [];
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
        } else if ($request->has('categories')) {
            $cats = $request->get('categories');

            foreach($cats as $cat) {
              $c = $this->store->categories()->find($cat['id']);
              if($c) {
                $c->order = $cat['order'];
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

      if($cat) {
        $cat->delete();
      }
    }
}
