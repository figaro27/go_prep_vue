<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class IngredientsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    // // Cache ingredient dataset
    // $ingredients = collect(json_decode(Storage::get('datasets/ingredients.json')));

    // for($i = 0; $i < 50; $i++) {
    //   factory(App\Ingredient::class)->create([
    //     // 'food_name' => $ingredients->random()->name,
    //   ]);
    // }


    $unitTypes = ['mass', 'volume', 'unit'];
    $food_names = ['Banana', 'Eggs', 'Sugar', 'Milk', 'Baking Powder', 'Oats', 'Bread', 'Breadcrumb', 'Chicken Breast', 'Tomatoes', 'Onion', 'Potatoes', 'Ground Turkey', 'Oregano', 'Celery', 'Carrots', 'Cream Cheese', 'Flour', 'Parsley', 'Penne'];
    $image = [
        '/images/store/ingredients/banana.jpg',
        '/images/store/ingredients/eggs.jpg',
        '/images/store/ingredients/sugar.jpg',
        '/images/store/ingredients/milk.jpg',
        '/images/store/ingredients/baking-powder.jpg',
        '/images/store/ingredients/oats.jpg',
        '/images/store/ingredients/bread.jpg',
        '/images/store/ingredients/breadcrumb.jpg',
        '/images/store/ingredients/chicken-breast.jpg',
        '/images/store/ingredients/tomatoes.jpg',
        '/images/store/ingredients/onion.jpg',
        '/images/store/ingredients/potatoes.jpg',
        '/images/store/ingredients/ground-turkey.jpg',
        '/images/store/ingredients/oregano.jpg',
        '/images/store/ingredients/celery.jpg',
        '/images/store/ingredients/carrots.jpg',
        '/images/store/ingredients/cream-cheese.jpg',
        '/images/store/ingredients/flour.jpg',
        '/images/store/ingredients/parsley.jpg',
        '/images/store/ingredients/penne.jpg',
    ];


    for($i=0;$i<20;$i++){
        DB::table('ingredients')->insert([
            'store_id' => 1,
            'food_name' => $food_names[$i],
            'image' => $image[$i],
            'image_thumb' => $image[$i],
            'unit_type' => $unitTypes[rand(0,2)],
            'calories' => rand(1, 10),
            'fatcalories' => rand(1, 2),
            'totalfat' => rand(1, 4),
            'satfat' => rand(1, 2),
            'transfat' => rand(0, 1),
            'cholesterol' => rand(1, 10),
            'sodium' => rand(5, 20),
            'totalcarb' => rand(1, 3),
            'fibers' => rand(5, 20),
            'sugars' => rand(1, 3),
            'proteins' => rand(2, 5),
            'vitamind' => rand(1, 10),
            'potassium' => rand(1, 10),
            'calcium' => rand(1, 10),
            'iron' => rand(1, 20),
            'addedsugars' => rand(1, 3),
            'created_at' => Carbon::now(),
        ]);
      }

  }
}
