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

        factory(App\Meal::class, 50)->create()->each(function($u) {
            $u->tags()->save(factory(App\MealTag::class)->make());
        	for ($i=0;$i<6;$i++)
            $u->ingredients()->save(factory(App\Ingredient::class)->make());
          });

    }
}
