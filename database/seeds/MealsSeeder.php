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
        'Mediterranean Burger',
        'Texas Turkey Burger',
        'Mango Chicken',
        'BBQ Pulled Chicken',
        'Smoked BBQ Brisket',
        'Buffalo Chicken Pizza',
        'Pulled Chicken Burrito',
        'BBQ Grilled Steak',
        'Thai Chili Salmon',
        'Sesame Ginger Tilapia',
        'Chicken Caprese',
        'Ground Turkey Lasagna',
        'Keto Thai Chili Salmon',
        'Keto Chicken & Veggies',
        'Keto Texan Burger',
        'Cinnamon Banana French Toast',
        'Strawberry Protein Pancakes',
        'Breakfast Burrito Egg Whites',
        'Blueberry Protein Muffins',
        'Triple Chocolate Protein Brownies'
    ];

    $mealImages = [
        '/images/store/meals/mediterranean-burger.jpg',
        '/images/store/meals/texas-turkey-burger.jpg',
        '/images/store/meals/mango-chicken.jpg',
        '/images/store/meals/bbq-pulled-chicken.jpg',
        '/images/store/meals/smoked-bbq-brisket.jpg',
        '/images/store/meals/buffalo-chicken-pizza.jpg',
        '/images/store/meals/pulled-chicken-burrito.jpg',
        '/images/store/meals/bbq-grilled-steak.jpg',
        '/images/store/meals/thai-chili-salmon.jpg',
        '/images/store/meals/sesame-chicken-tilapia.jpg',
        '/images/store/meals/chicken-caprese.jpg',
        '/images/store/meals/ground-turkey-lasagna.jpg',
        '/images/store/meals/keto-thai-chili-salmon.jpg',
        '/images/store/meals/keto-chicken-veggies.jpg',
        '/images/store/meals/keto-texan-burger.jpg',
        '/images/store/meals/cinnamon-french-toast.jpg',
        '/images/store/meals/strawberry-protein-pancakes.jpg',
        '/images/store/meals/breakfast-burrito-egg-whites.jpg',
        '/images/store/meals/blueberry-protein-muffins.jpg',
        '/images/store/meals/protein-brownies.jpg',
    ];

    $mealDescriptions = [
        'Grilled lean fresh ground beef topped with sauteed onions and peppers and a vegan pea protein mozzarella cheese. Served with baked sweet potato fries.',
        'Low fat turkey burger tossed in our special turkey seasoning drizzled with our famous Chipotle sauce and garnished with chopped tomatoes and green onions. Served with baked sweet potato fries.',
        'Char broiled marinated chicken, fresh mango salsa with toasted coconut basmati rice',
        'Slow cooked chicken breast smothered in our tangy BBQ sauce served with creamy home made mac and cheese with fresh steamed broccoli. Have this healthy premade meal delivered to your door today.',
        'Low and slow smoked beef brisket topped with a tangy barbeque sauce, served with roasted sweet corn, and classic baked beans',
        'Tender pulled chicken tossed in our famous buffalo style sauce with aged sharp cheddar on a whole wheat crust. For more pizza options, check out our BBQ chicken pizza prep meal.',
        'Hand shredded seasoned chicken, steamed brown rice, fresh pico de gallo and sharp cheddar cheese, rolled up in a whole wheat tortilla served with a side of veggie sticks',
        '8oz of Barbeque marinated grilled steak served with a side of mixed veggies and sweet potatoes. Have this tasty prepackaged meal delivered to your door today.',
        'Thai glazed salmon over soba noodles with crisp snow peas and shredded carrots. Be sure to check out our lemon baked salmon prep meal, too!',
        'Char grilled sesame ginger tilapia prep meal glazed with sesame ginger sauce over whole wheat lo mein with broccoli. Add this meal to your diet food delivery plan.',
        'Marinated grilled chicken over whole wheat couscous, with tomato mozzarella and basil bruschetta and a balsamic drizzle.',
        'Our healthy and delicious homemade ground turkey lasagna meal. This prepared diet meal contains lean ground turkey, low-fat mozzarella, zesty marinara and whole wheat pasta.',
        'Our famous thai chili salmon dish served keto style. Grilled salmon filet drenched in a homemade thai chili sauce served alongside peas, carrots and sliced zucchini.',
        'Grilled chicken breast served with asparagus and sliced zucchini',
        'A perfectly seasoned 8oz turkey burger topped with our world famous texan burger sauce, peppers and onions served alongside a healthy serving of fresh chopped zucchini.',
        'This premade banana cinnamon French toast is the breakfast meal of champions. Whole Grain bread coated with fresh ground cinnamon, vanilla extract and egg whites mixed with ripe bananas served with a side of turkey bacon.',
        '1 Scoop Strawberry Whey Protein Isolate, Oat Flour, Oats, Egg Whites, Almond Milk. Served with a side of Turkey Bacon. If you are not feeling fruity, check out our other premade whey protein pancakes.',
        'Fluffy egg whites tossed with vegetables, vegan cheese, and vegetarian sausage. Rolled in a flour tortilla.',
        'Our blueberry protein muffin snacks are delicious, protein-packed bites that make a great snack in between meals! Have these healthy snacks delivered right to your door.',
        'Rich homemade chocolate brownies mixed with chocolate protein powder , oat flour, 100 percent dark chocolate chips and cocoa powder.'

    ];


        for($i=0;$i<20;$i++){
        DB::table('meals')->insert([
            'active' => 1,
            'store_id' => 1,
            'featured_image' => $mealImages[$i],
            'title' => $mealTitles[$i],
            'description' => $mealDescriptions[$i],
            'price' => mt_rand(80, 120) / 10,
            'created_at' => Carbon::now(),
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
