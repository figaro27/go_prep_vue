<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MealAllergy;
use App\Category;
use App\CategoryMeal;
use App\CategoryMealPackage;
use App\MealSize;
use App\MealAddon;
use App\MealAttachment;
use App\MealComponentOption;
use App\MealComponent;
use App\MealMealPackage;
use App\MealMealPackageAddon;
use App\MealMealPackageComponentOption;
use App\MealMealPackageSize;
use App\MealMealTag;
use App\MealPackage;
use App\MealPackageAddon;
use App\MealPackageComponent;
use App\MealPackageComponentOption;
use App\MealPackageSize;
use App\Meal;
use App\ProductionGroup;
use App\StoreSetting;
use App\Store;
use App\Ingredient;
use App\IngredientMeal;
use App\IngredientMealAddon;
use App\IngredientMealComponentOption;
use App\IngredientMealSize;
use App\ChildMeal;

class misc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:misc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Created to add the 4th Livotis store opening up';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $meals = Meal::where('store_id', 313)
            ->where('title', 'LIKE', '% - G')
            ->get();
        foreach ($meals as $meal) {
            $childMeal = new ChildMeal();
            $childMeal->store_id = 314;
            $childMeal->meal_id = $meal->id;
            $childMeal->save();
        }
    }
}
