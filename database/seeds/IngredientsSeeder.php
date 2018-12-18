<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class IngredientsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    // Cache ingredient dataset
    $ingredients = collect(json_decode(Storage::get('datasets/ingredients.json')));

    for($i = 0; $i < 50; $i++) {
      factory(App\Ingredient::class)->create([
        'food_name' => $ingredients->random()->name,
      ]);
    }
  }
}
