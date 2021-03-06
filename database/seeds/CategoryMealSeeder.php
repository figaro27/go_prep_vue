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
        $c = 0;
        for ($u = 0; $u <= 600; $u += 23) {
            for ($i = 1; $i <= 15; $i++) {
                DB::table('category_meal')->insert([
                    'meal_id' => $i + $u,
                    'category_id' => 1 + $c
                ]);
            }

            for ($i = 16; $i <= 20; $i++) {
                DB::table('category_meal')->insert([
                    'meal_id' => $i + $u,
                    'category_id' => 2 + $c
                ]);
            }

            for ($i = 21; $i <= 23; $i++) {
                DB::table('category_meal')->insert([
                    'meal_id' => $i + $u,
                    'category_id' => 3 + $c
                ]);
            }

            $c += 3;
        }
    }
}
