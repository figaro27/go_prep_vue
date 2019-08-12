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
        for ($i = 0; $i <= 600; $i += 23) {
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 1 + $i,
                'meal_tag_id' => 4
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 1 + $i,
                'meal_tag_id' => 5
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 1 + $i,
                'meal_tag_id' => 6
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 2 + $i,
                'meal_tag_id' => 4
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 2 + $i,
                'meal_tag_id' => 5
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 2 + $i,
                'meal_tag_id' => 10
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 3 + $i,
                'meal_tag_id' => 4
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 3 + $i,
                'meal_tag_id' => 5
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 3 + $i,
                'meal_tag_id' => 6
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 4 + $i,
                'meal_tag_id' => 2
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 4 + $i,
                'meal_tag_id' => 5
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 4 + $i,
                'meal_tag_id' => 8
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 5 + $i,
                'meal_tag_id' => 4
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 5 + $i,
                'meal_tag_id' => 6
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 5 + $i,
                'meal_tag_id' => 8
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 6 + $i,
                'meal_tag_id' => 4
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 6 + $i,
                'meal_tag_id' => 9
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 6 + $i,
                'meal_tag_id' => 8
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 7 + $i,
                'meal_tag_id' => 2
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 7 + $i,
                'meal_tag_id' => 9
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 7 + $i,
                'meal_tag_id' => 6
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 8 + $i,
                'meal_tag_id' => 2
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 8 + $i,
                'meal_tag_id' => 5
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 8 + $i,
                'meal_tag_id' => 6
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 9 + $i,
                'meal_tag_id' => 2
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 9 + $i,
                'meal_tag_id' => 5
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 9 + $i,
                'meal_tag_id' => 6
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 10 + $i,
                'meal_tag_id' => 1
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 10 + $i,
                'meal_tag_id' => 3
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 10 + $i,
                'meal_tag_id' => 9
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 11 + $i,
                'meal_tag_id' => 5
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 11 + $i,
                'meal_tag_id' => 6
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 11 + $i,
                'meal_tag_id' => 7
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 12 + $i,
                'meal_tag_id' => 4
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 12 + $i,
                'meal_tag_id' => 5
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 12 + $i,
                'meal_tag_id' => 7
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 13 + $i,
                'meal_tag_id' => 4
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 13 + $i,
                'meal_tag_id' => 6
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 13 + $i,
                'meal_tag_id' => 7
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 14 + $i,
                'meal_tag_id' => 4
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 14 + $i,
                'meal_tag_id' => 5
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 14 + $i,
                'meal_tag_id' => 7
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 15 + $i,
                'meal_tag_id' => 1
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 15 + $i,
                'meal_tag_id' => 4
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 15 + $i,
                'meal_tag_id' => 9
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 16 + $i,
                'meal_tag_id' => 1
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 16 + $i,
                'meal_tag_id' => 3
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 16 + $i,
                'meal_tag_id' => 9
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 17 + $i,
                'meal_tag_id' => 8
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 17 + $i,
                'meal_tag_id' => 6
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 17 + $i,
                'meal_tag_id' => 10
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 18 + $i,
                'meal_tag_id' => 8
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 18 + $i,
                'meal_tag_id' => 6
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 18 + $i,
                'meal_tag_id' => 10
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 19 + $i,
                'meal_tag_id' => 2
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 19 + $i,
                'meal_tag_id' => 9
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 20 + $i,
                'meal_tag_id' => 1
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 20 + $i,
                'meal_tag_id' => 6
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 20 + $i,
                'meal_tag_id' => 10
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 21 + $i,
                'meal_tag_id' => 4
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 21 + $i,
                'meal_tag_id' => 9
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 22 + $i,
                'meal_tag_id' => 1
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 22 + $i,
                'meal_tag_id' => 4
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 22 + $i,
                'meal_tag_id' => 8
            ]);

            DB::table('meal_meal_tag')->insert([
                'meal_id' => 23 + $i,
                'meal_tag_id' => 2
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 23 + $i,
                'meal_tag_id' => 9
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 23 + $i,
                'meal_tag_id' => 7
            ]);
            DB::table('meal_meal_tag')->insert([
                'meal_id' => 23 + $i,
                'meal_tag_id' => 4
            ]);
        }
    }
}
