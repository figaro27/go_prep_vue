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

        for ($i = 1; $i <= 5; $i++) {
            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 1,
                'meal_id' => $i,
                'quantity' => 1,
                'quantity_unit' => 'unit',
                'quantity_unit_display' => 'unit',
            ]);

            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 2,
                'meal_id' => $i,
                'quantity' => 2,
                'quantity_unit' => 'eggs',
                'quantity_unit_display' => 'eggs',
            ]);

            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 3,
                'meal_id' => $i,
                'quantity' => 2,
                'quantity_unit' => 'tsp',
                'quantity_unit_display' => 'tsp',
            ]);

            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 4,
                'meal_id' => $i,
                'quantity' => 1,
                'quantity_unit' => 'cup',
                'quantity_unit_display' => 'cup',
            ]);

             DB::table('ingredient_meal')->insert([
                'ingredient_id' => 5,
                'meal_id' => $i,
                'quantity' => 1,
                'quantity_unit' => 'tsp',
                'quantity_unit_display' => 'tsp',
            ]);
        }

        for ($i = 6; $i <= 10; $i++) {
            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 6,
                'meal_id' => $i,
                'quantity' => 2,
                'quantity_unit' => 'cup',
                'quantity_unit_display' => 'cup',
            ]);

            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 7,
                'meal_id' => $i,
                'quantity' => 2,
                'quantity_unit' => 'slices',
                'quantity_unit_display' => 'slices',
            ]);

            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 8,
                'meal_id' => $i,
                'quantity' => 1,
                'quantity_unit' => 'cup',
                'quantity_unit_display' => 'cup',
            ]);

            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 9,
                'meal_id' => $i,
                'quantity' => 8,
                'quantity_unit' => 'oz',
                'quantity_unit_display' => 'oz',
            ]);

             DB::table('ingredient_meal')->insert([
                'ingredient_id' => 10,
                'meal_id' => $i,
                'quantity' => 1,
                'quantity_unit' => 'unit',
                'quantity_unit_display' => 'unit',
            ]);
        }

        for ($i = 11; $i <= 15; $i++) {
            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 11,
                'meal_id' => $i,
                'quantity' => .5,
                'quantity_unit' => 'unit',
                'quantity_unit_display' => 'unit',
            ]);

            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 12,
                'meal_id' => $i,
                'quantity' => 3,
                'quantity_unit' => 'oz',
                'quantity_unit_display' => 'oz',
            ]);

            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 13,
                'meal_id' => $i,
                'quantity' => 5,
                'quantity_unit' => 'oz',
                'quantity_unit_display' => 'oz',
            ]);

            DB::table('ingredient_meal')->insert([
                'ingredient_id' => 14,
                'meal_id' => $i,
                'quantity' => 1,
                'quantity_unit' => 'tsp',
                'quantity_unit_display' => 'tsp',
            ]);

             DB::table('ingredient_meal')->insert([
                'ingredient_id' => 15,
                'meal_id' => $i,
                'quantity' => 3,
                'quantity_unit' => 'oz',
                'quantity_unit_display' => 'oz',
            ]);
        }
    }
}
