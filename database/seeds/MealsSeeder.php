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
        	for ($i=0;$i<6;$i++)
            $u->ingredient()->save(factory(App\Ingredient::class)->make());

          });

    }
}
