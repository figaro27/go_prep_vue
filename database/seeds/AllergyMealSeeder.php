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
        for ($i = 0; $i <= 600; $i += 23) {
            DB::table('allergy_meal')->insert([
                'meal_id' => 1 + $i,
                'allergy_id' => 7
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 2 + $i,
                'allergy_id' => 2
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 3 + $i,
                'allergy_id' => 2
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 4 + $i,
                'allergy_id' => 6
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 6 + $i,
                'allergy_id' => 4
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 6 + $i,
                'allergy_id' => 7
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 9 + $i,
                'allergy_id' => 6
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 10 + $i,
                'allergy_id' => 7
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 10 + $i,
                'allergy_id' => 3
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 11 + $i,
                'allergy_id' => 7
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 13 + $i,
                'allergy_id' => 2
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 14 + $i,
                'allergy_id' => 4
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 16 + $i,
                'allergy_id' => 4
            ]);

            DB::table('allergy_meal')->insert([
                'meal_id' => 17 + $i,
                'allergy_id' => 4
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 17 + $i,
                'allergy_id' => 5
            ]);

            DB::table('allergy_meal')->insert([
                'meal_id' => 18 + $i,
                'allergy_id' => 4
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 18 + $i,
                'allergy_id' => 5
            ]);

            DB::table('allergy_meal')->insert([
                'meal_id' => 19 + $i,
                'allergy_id' => 6
            ]);

            DB::table('allergy_meal')->insert([
                'meal_id' => 20 + $i,
                'allergy_id' => 3
            ]);

            DB::table('allergy_meal')->insert([
                'meal_id' => 21 + $i,
                'allergy_id' => 4
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 21 + $i,
                'allergy_id' => 8
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 21 + $i,
                'allergy_id' => 7
            ]);

            DB::table('allergy_meal')->insert([
                'meal_id' => 22 + $i,
                'allergy_id' => 3
            ]);
            DB::table('allergy_meal')->insert([
                'meal_id' => 22 + $i,
                'allergy_id' => 5
            ]);

            DB::table('allergy_meal')->insert([
                'meal_id' => 23 + $i,
                'allergy_id' => 7
            ]);
        }
    }
}
