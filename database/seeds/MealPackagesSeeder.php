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

        for ($i = 1; $i <= 5; $i++) {
            $package = MealPackage::create([
                'title' => 'Package ' . $i,
                'description' => 'This is the description for package ' . $i,
                'price' => rand(500, 20000) / 100,
                'store_id' => 1,
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

            for ($m = 0; $m < rand(4, 10); $m++) {
                try {
                    MealPackageMeal::create([
                        'meal_id' => $meals->random()->id,
                        'meal_package_id' => $package->id,
                        'quantity' => rand(1, 5)
                    ]);
                } catch (\Exception $e) {
                }
            }
        }
    }
}
