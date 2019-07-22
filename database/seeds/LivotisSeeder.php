<?php

use App\MealPackage;
use App\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LivotisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $store = Store::find(1);

        DB::transaction(function () use ($store) {
            $store->categories()->forceDelete();
            $store->meals()->forceDelete();
            $store->packages()->forceDelete();

            $categories = [
                'pasta' => [
                    'id' => null,
                    'category' => 'Pasta'
                ],
                'vegetable' => [
                    'id' => null,
                    'category' => 'Vegetables'
                ],
                'entree' => [
                    'id' => null,
                    'category' => 'Entrees'
                ],
                'side' => [
                    'id' => null,
                    'category' => 'Side Dishes'
                ]
            ];

            foreach ($categories as $slug => $category) {
                $id = DB::table('categories')->insertGetId([
                    'store_id' => $store->id,
                    'category' => $category['category']
                ]);
                $categories[$slug]['id'] = $id;
            }

            $sizes = collect([
                'full-tray' => [
                    'title' => 'Full Tray',
                    'multiplier' => 2
                ]
            ]);

            $meals = collect([
                [
                    'title' => 'Linguine & Clams',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Stuffed Shells',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Baked Ziti',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Lasagna',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Rigatoni Di Pomodoro',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Pasta Primavera',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Farfalle Siciliano',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Rigatoni Bolognese',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Cavatelli with Broccoli',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Creamy Pesto Rigatoni',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Orecchiette Pasta',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Penne Alla Vokda',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Macaroni and Cheese',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Tri-Color Tortellini',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Fusilli Alfredo',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Lobster Ravioli + $0.99',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Manicotti',
                    'price' => 0,
                    'category' => ['pasta']
                ],
                [
                    'title' => 'Eggplant Parmigiana',
                    'price' => 0,
                    'category' => ['vegetable']
                ],
                [
                    'title' => 'Eggplant Rollatini',
                    'price' => 0,
                    'category' => ['vegetable']
                ],
                [
                    'title' => 'Eggplant Corrozza',
                    'price' => 0,
                    'category' => ['vegetable']
                ],
                [
                    'title' => 'Eggplant Rolled with Spinach',
                    'price' => 0,
                    'category' => ['vegetable']
                ],
                [
                    'title' => 'Zucchini Lasagna',
                    'price' => 0,
                    'category' => ['vegetable']
                ],
                [
                    'title' => 'Eggplant Bianco',
                    'price' => 0,
                    'category' => ['vegetable']
                ],
                [
                    'title' => 'Stuffed Portobello Mushrooms',
                    'price' => 0,
                    'category' => ['vegetable']
                ],
                [
                    'title' => 'Asparagus Parmigiana',
                    'price' => 0,
                    'category' => ['vegetable']
                ],
                [
                    'title' => 'Pepper Steak',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Grilled Tri-Tip',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Meatballs in Sauce',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Roast Beef with Mushroom Gravy',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Filet Mignon Medallions + $3.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Beef Teriyaki',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Grilled Skirt Steak',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Flat Iron Capriati',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Steak Siciliano',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Beef & Broccoli',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Chicken Cutlet Parmesan',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Chicken Francaise',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Chicken Marsala',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Chicken Fontinella',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Chicken Oreganata',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Chicken Rollatini',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Livoti’s Chicken Roulade',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Chicken Sorrentino',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Grilled Chicken',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Chicken and Artichokes',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Chicken Fico',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Nona’s Baked Chicken',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Captain Crunch Chicken Fingers',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Livoti’s Chicken Portobello',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Stuffed Chicken Ala Livoti’s',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Chicken Murphy',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Chicken Rapini',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Sausage & Peppers',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Stuffed Loin of Pork',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'BBQ Ribs',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Southern Style Pulled Pork',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Pork Tenderloin',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Stuffed Pork Filet Mignon',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Veal Cutlet Parmesan + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Veal Marsala + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Veal Saltimbocca + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Veal Picatta + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Veal Francaise + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Veal Sorrentino + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Veal Milanese + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Sautéed Calamari + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Zuppa Di Mussels + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Panko Parmesan Crusted Salmon + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Shrimp Scampi + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Shrimp Oreganata + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Stuffed Filet of Sole + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Shrimp Parmesan + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Seafood Fra Diavolo or Marinara + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Tilapia Francaise + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Tilapia Oreganata + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Lemon Thyme Filet of Sole + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Bang Bang Shrimp + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],
                [
                    'title' => 'Penne Vodka w/Shrimp + $2.99',
                    'price' => 0,
                    'category' => ['entree']
                ],

                [
                    'title' => 'Broccoli Oreganata + $0.99',
                    'price' => 0,
                    'category' => ['side']
                ],
                [
                    'title' => 'String Beans Almondine + $0.99',
                    'price' => 0,
                    'category' => ['side']
                ],
                [
                    'title' => 'Broccoli Rabe + $0.99',
                    'price' => 0,
                    'category' => ['side']
                ],
                [
                    'title' => 'Crispy Roasted Potatoes + $0.99',
                    'price' => 0,
                    'category' => ['side']
                ],
                [
                    'title' => 'Risotto Milanese + $0.99',
                    'price' => 0,
                    'category' => ['side']
                ],
                [
                    'title' => 'Roasted Vegetables + $0.99',
                    'price' => 0,
                    'category' => ['side']
                ],
                [
                    'title' => 'Asparagus Almondine + $0.99',
                    'price' => 0,
                    'category' => ['side']
                ],
                [
                    'title' => 'Mini Rice Balls + $0.99',
                    'price' => 0,
                    'category' => ['side']
                ],
                [
                    'title' => 'Mini Potato Croquettes + $0.99',
                    'price' => 0,
                    'category' => ['side']
                ],
                [
                    'title' =>
                        'Mini Sicilian Rice Balls and Broccoli Rabe Rice Balls + $0.99',
                    'price' => 0,
                    'category' => ['side']
                ],
                [
                    'title' =>
                        'Mini Sausage and Broccoli Rabe Rice Balls + $0.99',
                    'price' => 0,
                    'category' => ['side']
                ]
            ]);

            // Create meals
            foreach ($meals as $i => $meal) {
                $mealId = DB::table('meals')->insertGetId([
                    'active' => 0,
                    'store_id' => $store->id,
                    'featured_image' => null,
                    'title' => $meal['title'],
                    'description' => '',
                    'price' => $meal['price'],
                    'default_size_title' => 'Half Tray',
                    'created_at' => Carbon::now()
                ]);

                foreach ($sizes as $size) {
                    DB::table('meal_sizes')->insert([
                        'meal_id' => $mealId,
                        'title' => $size['title'],
                        'price' => $meal['price'] * $size['multiplier'],
                        'multiplier' => $size['multiplier'],
                        'created_at' => Carbon::now()
                    ]);
                }

                foreach ($meal['category'] as $category) {
                    DB::table('category_meal')->insert([
                        'meal_id' => $mealId,
                        'category_id' => $categories[$category]['id'],
                        'created_at' => Carbon::now()
                    ]);
                }
            }

            $packageSizes = collect([
                [
                    'title' => '20 People',
                    'components' => [
                        [
                            'title' => 'Choose One Pasta',
                            'category' => 'pasta',
                            'minimum' => 1,
                            'maximum' => 1,
                            'sizes' => [
                                'full-tray' => 1
                            ]
                        ],
                        [
                            'title' => 'Choose One Vegetable',
                            'category' => 'vegetable',
                            'minimum' => 1,
                            'maximum' => 1,
                            'sizes' => [
                                'half-tray' => 1
                            ]
                        ],
                        [
                            'title' => 'Choose Three Entrees',
                            'category' => 'entree',
                            'minimum' => 3,
                            'maximum' => 3,
                            'sizes' => [
                                'half-tray' => 3
                            ]
                        ]
                    ],
                    'addons' => []
                ],
                [
                    'title' => '25 People',
                    'price' => 374.75,
                    'components' => [
                        [
                            'title' => 'Choose One Pasta',
                            'category' => 'pasta',
                            'minimum' => 1,
                            'maximum' => 1,
                            'sizes' => [
                                'full-tray' => 1
                            ]
                        ],
                        [
                            'title' => 'Choose One Vegetable',
                            'category' => 'vegetable',
                            'minimum' => 1,
                            'maximum' => 1,
                            'sizes' => [
                                'full-tray' => 1
                            ]
                        ],
                        [
                            'title' => 'Choose Three Entrees',
                            'category' => 'entree',
                            'minimum' => 3,
                            'maximum' => 3,
                            'sizes' => [
                                'half-tray' => 3
                            ]
                        ]
                    ],
                    'addons' => []
                ],
                [
                    'title' => '30 People',
                    'price' => 389.7,
                    'components' => [
                        [
                            'title' => 'Choose One Pasta',
                            'category' => 'pasta',
                            'minimum' => 1,
                            'maximum' => 1,
                            'sizes' => [
                                'full-tray' => 1,
                                'half-tray' => 1
                            ]
                        ],
                        [
                            'title' => 'Choose One Vegetable',
                            'category' => 'vegetable',
                            'minimum' => 1,
                            'maximum' => 1,
                            'sizes' => [
                                'full-tray' => 1
                            ]
                        ],
                        [
                            'title' => 'Choose Three Entrees',
                            'category' => 'entree',
                            'minimum' => 3,
                            'maximum' => 3,
                            'sizes' => [
                                'full-tray' => 3
                            ]
                        ]
                    ],
                    'addons' => []
                ],
                [
                    'title' => '100 People',
                    'price' => 1299,
                    'components' => [
                        [
                            'title' => 'Choose One Pasta',
                            'category' => 'pasta',
                            'minimum' => 1,
                            'maximum' => 1,
                            'sizes' => [
                                'full-tray' => 4
                            ]
                        ],
                        [
                            'title' => 'Choose One Vegetable',
                            'category' => 'vegetable',
                            'minimum' => 1,
                            'maximum' => 1,
                            'sizes' => [
                                'full-tray' => 2,
                                'half-tray' => 1
                            ]
                        ],
                        [
                            'title' => 'Choose Three Entrees',
                            'category' => 'entree',
                            'minimum' => 3,
                            'maximum' => 3,
                            'sizes' => [
                                'full-tray' => 2,
                                'half-tray' => 1
                            ]
                        ]
                    ],
                    'addons' => []
                ]
            ]);

            // Hot buffet package
            $package = MealPackage::create([
                'title' => 'Hot Buffet',
                'description' => nl2br(
                    "Hot Buffet Includes: Livoti’s Classic Tossed Salad, Grated Cheese, Dinner Rolls, Extra Sauce, Plates, Forks, Knives, Napkins, Serving Spoons, Sterno Racks and Water Pans with Deposit Refundable Upon Return of Racks and Water Pans With Copy of Receipt Choice of ONE Pasta, ONE Vegetable and THREE Entrees Substitute a Fish or Veal Entrée for $2.99 Extra Per Person"
                ),
                'price' => 259.8,
                'store_id' => $store->id,
                'active' => 1,
                'created_at' => Carbon::now()
            ]);

            foreach ($packageSizes as $packageSizeIndex => $packageSize) {
                // Default size
                if ($packageSizeIndex === 0) {
                    $packageSizeId = null;
                } else {
                    $size = $package->sizes()->create([
                        'store_id' => $store->id,
                        'title' => $packageSize['title'],
                        'price' => $packageSize['price']
                    ]);
                    $packageSizeId = $size->id;
                }

                foreach (
                    $packageSize['components']
                    as $componentIndex => $component
                ) {
                    $categorySlug = $component['category'];
                    $category = $store
                        ->categories()
                        ->find($categories[$categorySlug]['id']);

                    $_component = $package->components()->create([
                        'store_id' => $store->id,
                        'title' => $component['title'],
                        'minimum' => $component['minimum'],
                        'maximum' => $component['maximum']
                    ]);

                    foreach ($component['sizes'] as $sizeSlug => $quantity) {
                        $mealOptions = $category
                            ->meals()
                            ->whereHas('categories', function ($query) use (
                                $category
                            ) {
                                $query->where('categories.id', $category->id);
                            })
                            ->get();

                        $option = $_component->options()->create([
                            'meal_package_size_id' => $packageSizeId,
                            'title' =>
                                $component['title'] .
                                ' - ' .
                                $packageSize['title'],
                            'price' => 0,
                            'selectable' => 1
                        ]);

                        foreach ($mealOptions as $meal) {
                            $mealSizeId = null;

                            if ($sizeSlug) {
                                $sizeTitle = $sizes[$sizeSlug]['title'] ?? null;
                                $mealSize = $meal
                                    ->sizes()
                                    ->where('title', $sizeTitle)
                                    ->first();

                                if ($mealSize) {
                                    $mealSizeId = $mealSize->id;
                                }
                            }

                            $option->meals()->attach([
                                $meal->id => [
                                    'meal_size_id' => $mealSizeId,
                                    'quantity' => $quantity
                                ]
                            ]);
                        }
                    }
                }
            }

            /*$package->meals()->sync(
            $store->meals->mapWithKeys(function (Meal $meal) {
            return [$meal->id => [
            'quantity' => 1,
            ]];
            })
            );*/

            try {
                $package->clearMediaCollection('featured_image');
                $package
                    ->addMediaFromUrl('http://lorempixel.com/1024/1024/food/')
                    ->preservingOriginal()
                    ->toMediaCollection('featured_image');
            } catch (\Exception $e) {
            }
        });
    }
}
