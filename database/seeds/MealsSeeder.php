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
        $units = ['oz', 'g'];

        factory(App\Meal::class, 50)->create()->each(function ($u) use ($tags, $ingredients, $units) {
            $u->tags()->save($tags[rand(0, count($tags) - 1)]);

            for($i=0; $i<10; $i++) {
              $u->ingredients()->attach($ingredients[rand(0, count($ingredients) - 1)], [
                'quantity' => rand(1, 15),
                'quantity_unit' => $units[rand(0,1)],
              ]);
            }
        });

    }
}
