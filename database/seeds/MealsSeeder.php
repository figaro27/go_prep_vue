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

        factory(App\Meal::class, 50)->create()->each(function ($u) use ($tags) {
            $u->tags()->save($tags[rand(0, count($tags) - 1)]);
        });

    }
}
