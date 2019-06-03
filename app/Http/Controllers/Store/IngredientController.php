<?php

namespace App\Http\Controllers\Store;

use App\Ingredient;
use App\Utils\Data\ExportsData;
use Illuminate\Http\Request;

class IngredientController extends StoreController
{
    use ExportsData;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->has('ingredients')
            ? $this->store
                ->ingredients()
                ->with([])
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Ingredient  $ingredient
     * @return \Illuminate\Http\Response
     */
    public function show(Ingredient $ingredient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Ingredient  $ingredient
     * @return \Illuminate\Http\Response
     */
    public function edit(Ingredient $ingredient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ingredient  $ingredient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ingredient $ingredient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ingredient  $ingredient
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ingredient $ingredient)
    {
        //
    }

    public function exportData($type = null)
    {
    }

    public function exportPdfView()
    {
        return 'reports.ingredients_pdf';
    }

    public function adjust(Request $request)
    {
        $ingredientId = $request->get('id');
        $adjustment = $request->get('adjustment');

        $ingredient = Ingredient::where('id', $ingredientId)->first();

        $ingredient->adjuster = $adjustment;
        $ingredient->save();
    }
}
