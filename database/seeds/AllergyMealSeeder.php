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
        for($i=1;$i<=20;$i++){
        DB::table('allergy_meal')->insert([
            'meal_id' => $i,
            'allergy_id' => rand(1,8)
        ]);
    	}

    	
    }
}
