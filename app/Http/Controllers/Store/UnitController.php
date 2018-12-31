<?php

namespace App\Http\Controllers\Store;

use App\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitController extends StoreController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->has('units') ?
        $this->store->units()->with([])->get() : [];
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
        $units = $request->get('units');

        if (!is_array($units)) {
            return [];
        }

        foreach ($units as $id => $unit) {
            if (!is_numeric($id)) {
                continue;
            }

            $ingredient = Ingredient::where([
                'id' => $id,
                'store_id' => $this->store->id,
            ])->first();

            if (!$ingredient) {
                continue;
            }

            $unit = DB::connection()->getPdo()->quote($unit);

            DB::statement("
        INSERT INTO store_units
        (store_id, ingredient_id, unit, created_at, updated_at)
        VALUES
        ({$this->store->id}, {$ingredient->id}, {$unit}, NOW(), NOW())
        ON DUPLICATE KEY UPDATE unit=VALUES(unit), updated_at=NOW()");
        }
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

    public function exportData()
    {

    }
}
