<?php

use Illuminate\Database\Seeder;

class MealMealTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1;$i<=20;$i++){
        DB::table('meal_meal_tag')->insert([
            'meal_id' => $i,
            'meal_tag_id' => rand(1,8)
        ]);
    	}
    }
}
