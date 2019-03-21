<?php

use Illuminate\Database\Seeder;

class AllergyMealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('allergy_meal')->insert([
            'meal_id' => 1,
            'allergy_id' => 7
        ]);
    	DB::table('allergy_meal')->insert([
            'meal_id' => 2,
            'allergy_id' => 2
        ]);
        DB::table('allergy_meal')->insert([
            'meal_id' => 3,
            'allergy_id' => 2
        ]);
        DB::table('allergy_meal')->insert([
            'meal_id' => 4,
            'allergy_id' => 6
        ]);
        DB::table('allergy_meal')->insert([
            'meal_id' => 6,
            'allergy_id' => 4
        ]);
        DB::table('allergy_meal')->insert([
            'meal_id' => 6,
            'allergy_id' => 7
        ]);
        DB::table('allergy_meal')->insert([
            'meal_id' => 9,
            'allergy_id' => 6
        ]);
        DB::table('allergy_meal')->insert([
            'meal_id' => 10,
            'allergy_id' => 7
        ]);
        DB::table('allergy_meal')->insert([
            'meal_id' => 10,
            'allergy_id' => 3
        ]);
        DB::table('allergy_meal')->insert([
            'meal_id' => 11,
            'allergy_id' => 7
        ]);
        DB::table('allergy_meal')->insert([
            'meal_id' => 13,
            'allergy_id' => 2
        ]);
        DB::table('allergy_meal')->insert([
            'meal_id' => 14,
            'allergy_id' => 4
        ]);
        DB::table('allergy_meal')->insert([
            'meal_id' => 16,
            'allergy_id' => 4
        ]);

    	
    }
}
