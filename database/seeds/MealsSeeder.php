<?php

use Illuminate\Database\Seeder;
use App\Utils\Data\Format;
use Carbon\Carbon;

class MealsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $mealTitles = [
        'Almond Crusted Chicken',
        'Asian Steak and Peppers',
        'Beef and Broccoli Bowl',
        'Blackened Cod',
        'Chicken Fajita Bowl',
        'Chicken Pesto Over Pasta',
        'Garlic Shrimp Bowl',
        'Hot and Spicy Chicken',
        'Lemon Cod',
        'Level 1 Bar - Peanut Butter',
        'Peanut Crusted Chicken',
        'Steak Tips',
        'Sweet Chili Turkey Meatballs',
        'Sweet Potato Ground Turkey Bake',
        'Tex Mex Stuffed Peppers',
        'Turkey Chili Bowl',
        'Protein Pancakes',
        'Protein Waffles',
        'Avocado Toast with Egg',
        'Yogurt Parfait',
        'Blueberry Oatmeal',
        'Keto Bomb Brownies',
        'Low Calorie Granola Bites'

    ];

    $mealImages = [
        '/images/store/meals/almond-crusted-chicken.jpeg',
        '/images/store/meals/asian-steak-and-peppers.jpeg',
        '/images/store/meals/beef-and-broccoli-bowl.jpeg',
        '/images/store/meals/blackened-cod.jpeg',
        '/images/store/meals/chicken-fajita-bowl.jpeg',
        '/images/store/meals/chicken-pesto-over-pasta.jpeg',
        '/images/store/meals/garlic-shrimp-bowl.jpeg',
        '/images/store/meals/hot-and-spicy-chicken.jpeg',
        '/images/store/meals/lemon-cod.jpeg',
        '/images/store/meals/level-1-bar-peanut-butter.jpeg',
        '/images/store/meals/peanut-crusted-chicken.jpeg',
        '/images/store/meals/steak-tips.jpeg',
        '/images/store/meals/sweet-chili-turkey-meatballs.jpeg',
        '/images/store/meals/sweet-potato-ground-turkey-bake.jpeg',
        '/images/store/meals/tex-mex-stuffed-peppers.jpeg',
        '/images/store/meals/turkey-chili-bowl.jpeg',
        '/images/store/meals/protein-pancakes.jpg',
        '/images/store/meals/protein-waffles.jpg',
        '/images/store/meals/avocado-toast.jpg',
        '/images/store/meals/parfait.jpg',
        '/images/store/meals/oatmeal.jpg',
        '/images/store/meals/brownies.jpg',
        '/images/store/meals/granola-bites.jpg',

    ];

    $mealDescriptions = [
        'Almond Crusted Chicken with Seasoned Grilled Asparagus and Brown Rice.',
        'Asian Inspired Steak with Peppers and Onions over Brown Rice.',
        'Asian Styled Beef and Broccoli with an assortment of Veggies over Brown Rice.',
        'Blackened Cod served with Herb Roasted Potaotes & Crispy Broccoli.',
        'Seasoned Grilled Chicken Served over Peppers, onions and Brown Rice.',
        'Seasoned Grilled Chicken Served over Brown Rice Pasta with Drizzled Pesto.',
        'Shrimp Tossed in Lemon and Garlic Sauce over Quinoa.',
        '5 Spice Seasoned Chicken with Cauliflower and Brown Rice.',
        'Lemon and Garlic Cod served over Seasoned Quinoa and Green Beans.',
        'Healthy snack for on the go.',
        'Peanut Crusted Chicken  With Pineapple salsa and Garlic Flavored Brown Rice.',
        'Seasoned Steak Tip with Sweet Potatoes and Mixed Veggies.',
        'Tasty and Delicious Turkey Meatballs, drizzled in a Sweet and Sour Sauce, with Asian Style Green Beans and Brown Rice.',
        'Delicious Ground Turkey Bake Mixed With Sweet Potatoes.',
        'Red Bell Peppers Stuffed with Ground Turkey seasoned with Taco and Tex mex styled spices and Garlic Brown Rice served with Mixed Veggies.',
        'Flavorful Ground Turkey Chili over Quinoa.',
        'Delicious protein pancakes, topped with non-sugar raw blue agave sweetener, strawberries, blueberries, and a taste of low fat butter.',
        'Golden and crispy protein waffles, topped with stawberries and blueberries, non-sugar blue agave sweetener, and a pinch of low fat butter.',
        'Lightly toasted whole wheat bread, topped with our signature avocado spread and an over easy egg. Sprinkled with cilantro and lime.',
        'A family classic. Non-fat greek yogurt, layered with organic granola and stawberries.',
        'Organic oatmeal with fresh plump blueberries. Serve warm and enjoy!',
        'Homemade brownies made for the Keto friendly diet. Packed with healthy fats and no carbs, this is the perfect treat for pre or post workout',
        'Our homemade granola snacks are the perfect low calorie alternative for a life on the go. Baked fresh on premise with all organic ingredients.'
    ];

        $daysAgo = [
            Carbon::now()->subDays(30),
            Carbon::now()->subDays(25)
        ];

        for($i=0;$i<23;$i++){
        DB::table('meals')->insert([
            'active' => 1,
            'store_id' => 1,
            'featured_image' => $mealImages[$i],
            'title' => $mealTitles[$i],
            'description' => $mealDescriptions[$i],
            'price' => mt_rand(80, 120) / 10,
            'created_at' => $daysAgo[rand(0,1)],
        ]);
    }

        // $tags = [];
        // foreach (['Low Carb', 'Low Calorie', 'Vegan', 'High Fiber', 'Low Fat', 'Low Sugar', 'Low Sodium', 'High Protein', 'Low Cholesterol', 'Low Saturated Fat'] as $tag) {
        //     $tags[] = App\MealTag::create(['tag' => $tag, 'slug' => str_slug($tag)]);
        // }

        // $ingredients = App\Ingredient::where('store_id', 1)->get();

        // factory(App\Meal::class, 20)->create()->each(function ($u) use ($tags, $ingredients) {
        //     $u->tags()->attach(rand(1, 8));
        //     $u->categories()->attach(rand(1, 2));
        //     $u->allergies()->attach(rand(1, 8));

        //     $unitTypes = [
        //         'mass' => ['oz', 'g'],
        //         'volume' => ['ml', 'tsp', 'fl-oz'],
        //         'unit' => ['unit'],
        //     ];
        //     $unitUnits = ['pieces', 'pinches', 'drops'];

        //     // Pick 10 ingredients at random
        //     $ingredientKeys = array_rand($ingredients->toArray(), min(10, count($ingredients)));

        //     foreach($ingredientKeys as $i) {
        //         $ingredient = $ingredients[$i];
        //         $units = $unitTypes[$ingredient->unit_type];
        //         $unit = $units[rand(0, count($units) - 1)];
        //         $unitDisplay = ($unit !== 'unit') ? $unit : $unitUnits[rand(0, 2)];

        //         $u->ingredients()->attach($ingredient, [
        //             'quantity' => rand(1, 15),
        //             'quantity_unit' => $unit,
        //             'quantity_unit_display' => $unitDisplay,
        //         ]);
                
        //         try {
        //           $u->store->units()->create([
        //             'store_id' => $u->store_id,
        //             'ingredient_id' => $ingredient->id,
        //             'unit' => Format::baseUnit($ingredient->unit_type),
        //           ]);
        //         }
        //         catch(\Exception $e) {}


        //     }
        // });

    }
}
