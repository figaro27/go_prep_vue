<?php

use App\Meal;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

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
            'Low Calorie Granola Bites',
            'Level 1 Bar - Peanut Butter'
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
            '/images/store/meals/level-1-bar-peanut-butter.jpeg'
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
            'Our homemade granola snacks are the perfect low calorie alternative for a life on the go. Baked fresh on premise with all organic ingredients.',
            'Healthy snack for on the go.'
        ];

        $daysAgo = [Carbon::now()->subDays(30), Carbon::now()->subDays(25)];

        for ($store = 1; $store <= 30; $store++) {
            for ($i = 0; $i <= 22; $i++) {
                $id = DB::table('meals')->insertGetId([
                    'active' => 1,
                    'store_id' => $store,
                    'featured_image' => $mealImages[$i],
                    'title' => $mealTitles[$i],
                    'description' => $mealDescriptions[$i],
                    'price' => mt_rand(80, 120) / 10,
                    'default_size_title' => 'Medium',
                    'created_at' => $daysAgo[rand(0, 1)]
                ]);

                if ($i === 0) {
                    DB::table('meal_sizes')->insert([
                        'meal_id' => $id,
                        'title' => 'Large',
                        'price' => mt_rand(80, 120) / 10,
                        'multiplier' => mt_rand(10, 20) / 10
                    ]);

                    DB::table('meal_sizes')->insert([
                        'meal_id' => $id,
                        'title' => 'Extra Large',
                        'price' => mt_rand(80, 120) / 10,
                        'multiplier' => mt_rand(10, 20) / 10
                    ]);
                }

                $meal = Meal::find($id);
                $fullImagePath = resource_path('assets' . $mealImages[$i]);
                try {
                    $meal->clearMediaCollection('featured_image');
                    $meal
                        ->addMedia($fullImagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('featured_image');
                } catch (\Exception $e) {
                    echo "Failed to migrate image $fullImagePath - file not found\r\n";
                }
            }

            // for($i=15;$i<=19;$i++){
            //     DB::table('meals')->insert([
            //         'active' => 1,
            //         'store_id' => $store,
            //         'featured_image' => $mealImages[$i],
            //         'title' => $mealTitles[$i],
            //         'description' => $mealDescriptions[$i],
            //         'price' => mt_rand(60, 80) / 10,
            //         'created_at' => $daysAgo[rand(0,1)],
            //     ]);
            // }

            // for($i=20;$i<=22;$i++){
            //     DB::table('meals')->insert([
            //         'active' => $store,
            //         'store_id' => 1,
            //         'featured_image' => $mealImages[$i],
            //         'title' => $mealTitles[$i],
            //         'description' => $mealDescriptions[$i],
            //         'price' => mt_rand(40, 60) / 10,
            //         'created_at' => $daysAgo[rand(0,1)],
            //     ]);
            // }
        }
    }
}
