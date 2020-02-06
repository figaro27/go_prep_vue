<?php

use App\Meal;
use App\MealPackage;
use App\MealPackageMeal;
use Illuminate\Database\Seeder;

class MealPackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $meals = Meal::where('store_id', 1)->get();
        $cat = 1;
        for ($store = 1; $store <= 30; $store++) {
            $package = MealPackage::create([
                'title' => 'Standard Preset Package',
                'description' => 'This is the description for the package.',
                'price' => 100,
                'store_id' => $store,
                'active' => 1
            ]);

            try {
                $package->clearMediaCollection('featured_image');
                $package
                    ->addMediaFromUrl('http://lorempixel.com/1024/1024/food/')
                    ->preservingOriginal()
                    ->toMediaCollection('featured_image');
            } catch (\Exception $e) {
            }

            for ($m = 0; $m <= 6; $m++) {
                try {
                    MealPackageMeal::create([
                        'meal_id' => $meals->random()->id,
                        'meal_package_id' => $package->id,
                        'quantity' => 2
                    ]);
                } catch (\Exception $e) {
                }
            }

            $insertCategoryMealPackage = "
            INSERT INTO `category_meal_package` 
        (category_id, meal_package_id, created_at, updated_at)
        VALUES 
        ($cat,$store,NULL,NULL)
        ";
            DB::statement($insertCategoryMealPackage);
            $cat += 3;
        }
    }
}
