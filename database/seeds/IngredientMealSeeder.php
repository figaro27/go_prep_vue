<?php

use Illuminate\Database\Seeder;

class IngredientMealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $unitTypes = [
            'mass' => ['oz', 'g'],
            'volume' => ['ml', 'tsp', 'fl-oz'],
            'unit' => ['unit'],
        ];
        $unitUnits = ['pieces', 'pinches', 'drops'];

        for ($i = 1; $i < 20; $i++) {
            DB::table('ingredient_meal')->insert([
                'ingredient_id' => $i,
                'meal_id' => $i,
                'quantity' => rand(1, 15),
                'quantity_unit' => 'oz',
                'quantity_unit_display' => 'pinches',
            ]);
        }
    }
}
