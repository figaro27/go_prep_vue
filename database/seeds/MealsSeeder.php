<?php

use Illuminate\Database\Seeder;

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
        foreach (['Low Carb', 'Low Calorie', 'Vegan'] as $tag) {
            $tags[] = App\MealTag::create(['tag' => $tag, 'slug' => str_slug($tag)]);
        }

        $ingredients = App\Ingredient::where('store_id', 1)->get();

        factory(App\Meal::class, 20)->create()->each(function ($u) use ($tags, $ingredients) {
            $u->tags()->attach(rand(1, 3));
            $u->categories()->attach(rand(1, 3));
            $u->allergies()->attach(rand(1, 4));

            $unitTypes = [
                'mass' => ['oz', 'g'],
                'volume' => ['ml', 'teaspoon'],
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
            }
        });

    }
}
