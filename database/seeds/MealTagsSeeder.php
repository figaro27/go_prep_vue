<?php

use Illuminate\Database\Seeder;

class MealTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = [];
        foreach (['Low Carb', 'Low Calorie', 'Vegan', 'High Fiber', 'Low Fat', 'Low Sugar', 'Low Sodium', 'High Protein', 'Low Cholesterol', 'Low Saturated Fat'] as $tag) {
            $tags[] = App\MealTag::create(['tag' => $tag, 'slug' => str_slug($tag)]);
        }
    }
}
