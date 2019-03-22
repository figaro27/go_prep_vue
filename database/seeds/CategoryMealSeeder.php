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

        for($i=1;$i<=9;$i++){
        DB::table('category_meal')->insert([
            'meal_id' => $i,
            'category_id' => 1
        ]);
    	}

        for($i=11;$i<=16;$i++){
        DB::table('category_meal')->insert([
            'meal_id' => $i,
            'category_id' => 1
        ]);
        }



    	for($i=17;$i<=21;$i++){
        DB::table('category_meal')->insert([
            'meal_id' => $i,
            'category_id' => 2
        ]);
    	}

        for($i=22;$i<=23;$i++){
        DB::table('category_meal')->insert([
            'meal_id' => $i,
            'category_id' => 3
        ]);
        }

        DB::table('category_meal')->insert([
            'meal_id' => 10,
            'category_id' => 3
        ]);
    }
}
