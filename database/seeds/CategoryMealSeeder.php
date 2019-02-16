<?php

use Illuminate\Database\Seeder;

class CategoryMealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for($i=1;$i<=15;$i++){
        DB::table('category_meal')->insert([
            'meal_id' => $i,
            'category_id' => 1
        ]);
    	}

    	for($i=16;$i<=20;$i++){
        DB::table('category_meal')->insert([
            'meal_id' => $i,
            'category_id' => 2
        ]);
    	}

        for($i=19;$i<=20;$i++){
        DB::table('category_meal')->insert([
            'meal_id' => $i,
            'category_id' => 3
        ]);
        }
    }
}
