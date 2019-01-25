<?php

use Illuminate\Database\Seeder;
use App\Utils\Data\Format;

class MealsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = [];
        foreach (['Low Carb', 'Low Calorie', 'Vegan', 'High Fiber', 'Low Fat', 'Low Sugar', 'Low Sodium', 'High Protein', 'Low Cholesterol', 'Low Saturated Fat'] as $tag) {
            $tags[] = App\MealTag::create(['tag' => $tag, 'slug' => str_slug($tag)]);
        }

        $ingredients = App\Ingredient::where('store_id', 1)->get();

        factory(App\Meal::class, 20)->create()->each(function ($u) use ($tags, $ingredients) {
            $u->tags()->attach(rand(1, 8));
            $u->categories()->attach(rand(1, 3));
            $u->allergies()->attach(rand(1, 8));

            $unitTypes = [
                'mass' => ['oz', 'g'],
                'volume' => ['ml', 'tsp', 'fl-oz'],
                'unit' => ['unit'],
            ];

            // Pick 10 ingredients at random
            $ingredientKeys = array_rand($ingredients->toArray(), min(10, count($ingredients)));

            foreach($ingredientKeys as $i) {
                $ingredient = $ingredients[$i];
                $units = $unitTypes[$ingredient->unit_type];
                $unit = $units[rand(0, count($units) - 1)];

                $u->ingredients()->attach($ingredient, [
                    'quantity' => rand(1, 15),
                    'quantity_unit' => $unit,
                ]);
                
                try {
                  $u->store->units()->create([
                    'store_id' => $u->store_id,
                    'ingredient_id' => $ingredient->id,
                    'unit' => Format::baseUnit($ingredient->unit_type),
                  ]);
                }
                catch(\Exception $e) {}


            }
        });

    }
}
