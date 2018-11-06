<?php

namespace App\Http\Controllers;

use App\Meal;
use Illuminate\Http\Request;

class MealController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Meal::getMeals();
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
        $meal = new Meal;
        $meal->store_id = 1;
        $meal->featured_image = $request->featured_image;
        $meal->title = $request->title;
        $meal->category = $request->category;
        $meal->description = $request->description;
        $meal->price = $request->price;

        $meal->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
        $meal = Meal::where('id', $id)->first();
       
        $meal->update([
            'featured_image' => $request->meal['featured_image'],
            'title' => $request->meal['title'],
            'category' => $request->meal['category'],
            'description' => $request->meal['description'],
            'price' => $request->meal['price'],
            'created_at' => $request->meal['created_at'],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Meal  $meal
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meal = Meal::find($id);
        $meal->delete();
    }
}
