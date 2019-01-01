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
        foreach (['Low Carb', 'Low Calorie', 'Vegan', 'Breakfast'] as $tag) {
            $tags[] = App\MealTag::create(['store_id' => 1, 'tag' => $tag, 'slug' => str_slug($tag)]);
        }

        $ingredients = App\Ingredient::where('store_id', 1)->get();

        factory(App\Meal::class, 50)->create()->each(function ($u) use ($tags, $ingredients) {
            $u->tags()->save($tags[rand(0, count($tags) - 1)]);

            $cat = factory(App\MealCategory::class)->create([
              'meal_id' => $u->id,
            ]);
            $u->categories()->save($cat);

            $unitTypes = [
                'mass' => ['oz', 'g'],
                'volume' => ['ml', 'teaspoon'],
                'unit' => ['unit'],
            ];

            for ($i = 0; $i < 10; $i++) {
                $ingredient = $ingredients[rand(0, count($ingredients) - 1)];
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
