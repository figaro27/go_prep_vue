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

class duplicatePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:duplicatePackage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Duplicates package without creating new meals';

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
        $oldStoreId = 108;
        $newStoreId = 110;

        $syncMeals = [];
        $syncMealSizes = [];
        $syncPackageComponentOptions = [];

        $mealPackage = MealPackage::where('id', 1000)
            ->with(['sizes', 'components', 'componentOptions', 'addons'])
            ->first();

        $newMealPackage = $mealPackage->replicate();
        $newMealPackage->store_id = $newStoreId;
        $newMealPackage->save();

        $meals = Meal::where('store_id', $oldStoreId)->get();

        // SYNCING MEALS
        foreach ($meals as $meal) {
            $newMeal = Meal::where([
                'store_id' => $newStoreId,
                'title' => $meal->title,
                'deleted_at' => $meal->deleted_at
            ])->first();
            if ($newMeal) {
                $syncMeals[$meal->id] = $newMeal->id;
            }
        }

        // SYNCING MEAL SIZES
        foreach ($meals as $meal) {
            foreach ($meal->sizes as $mealSize) {
                if (isset($syncMeals[$meal->id])) {
                    $newMealSize = MealSize::where([
                        'store_id' => $newStoreId,
                        'meal_id' => $syncMeals[$meal->id],
                        'title' => $mealSize->title,
                        'full_title' => $mealSize->full_title
                    ])->first();
                    if ($newMealSize) {
                        $syncMealSizes[$mealSize->id] = $newMealSize->id;
                    }
                }
            }
        }

        // Top level meal package meals
        foreach (
            MealMealPackage::where('meal_package_id', $mealPackage->id)->get()
            as $mealMeal
        ) {
            if (isset($syncMeals[$mealMeal->meal_id])) {
                $newMeal = new MealMealPackage();
                $newMeal = $mealMeal->replicate();
                $newMeal->meal_package_id = $newMealPackage->id;
                $newMeal->meal_id = $syncMeals[$mealMeal->meal_id];
                $newMeal->save();
            }
        }

        // Components (Base Size)
        foreach ($mealPackage->components as $component) {
            $newComponent = new MealPackageComponent();
            $newComponent = $component->replicate();
            $newComponent->store_id = $newStoreId;
            $newComponent->meal_package_id = $newMealPackage->id;
            $newComponent->save();

            // Added to add the size components later
            $component->newComponentId = $newComponent->id;

            // Component Options (Base Size)
            foreach ($component->options as $option) {
                if ($option->meal_package_size_id === null) {
                    $newComponentOption = new MealPackageComponentOption();
                    $newComponentOption = $option->replicate();
                    $newComponentOption->meal_package_component_id =
                        $newComponent->id;
                    $newComponentOption->save();

                    $syncPackageComponentOptions[$option->id] =
                        $newComponentOption->id;

                    // Top level meal package component meals
                    foreach (
                        MealMealPackageComponentOption::where(
                            'meal_package_component_option_id',
                            $option->id
                        )->get()
                        as $mealMeal
                    ) {
                        if (isset($syncMealSizes[$mealMeal->meal_size_id])) {
                            if (isset($syncMeals[$mealMeal->meal_id])) {
                                $newMeal = new MealMealPackageComponentOption();
                                $newMeal = $mealMeal->replicate();
                                $newMeal->meal_package_component_option_id =
                                    $newComponentOption->id;
                                $newMeal->meal_id =
                                    $syncMeals[$mealMeal->meal_id];
                                $newMeal->meal_size_id =
                                    $syncMealSizes[$mealMeal->meal_size_id];
                                $newMeal->save();
                            }
                        }
                    }
                }
            }
        }

        foreach ($mealPackage->sizes as $size) {
            $newSize = new MealPackageSize();
            $newSize = $size->replicate();
            $newSize->meal_package_id = $newMealPackage->id;
            $newSize->store_id = $newStoreId;
            $newSize->save();

            // Size meal package meals
            foreach (
                MealMealPackageSize::where(
                    'meal_package_size_id',
                    $size->id
                )->get()
                as $mealMeal
            ) {
                if (isset($syncMeals[$mealMeal->meal_id])) {
                    if (isset($syncMealSizes[$mealMeal->meal_size_id])) {
                        $newMeal = new MealMealPackageSize();
                        $newMeal = $mealMeal->replicate();
                        $newMeal->meal_package_size_id = $newSize->id;
                        $newMeal->meal_size_id =
                            $syncMealSizes[$mealMeal->meal_size_id];
                        $newMeal->meal_id = $syncMeals[$mealMeal->meal_id];
                        $newMeal->save();
                    }
                }
            }

            // Components (Meal Package Sizes)
            foreach ($mealPackage->components as $component) {
                foreach ($component->options as $option) {
                    if ($option->meal_package_size_id === $size->id) {
                        $newComponentOption = new MealPackageComponentOption();
                        $newComponentOption = $option->replicate();
                        $newComponentOption->meal_package_size_id =
                            $newSize->id;
                        $newComponentOption->meal_package_component_id =
                            $component->newComponentId;
                        $newComponentOption->save();

                        $syncPackageComponentOptions[$option->id] =
                            $newComponentOption->id;

                        // Size meal package component meals
                        foreach (
                            MealMealPackageComponentOption::where(
                                'meal_package_component_option_id',
                                $option->id
                            )->get()
                            as $mealMeal
                        ) {
                            if (isset($syncMeals[$mealMeal->meal_id])) {
                                if (
                                    isset(
                                        $syncMealSizes[$mealMeal->meal_size_id]
                                    )
                                ) {
                                    $newMeal = new MealMealPackageComponentOption();
                                    $newMeal = $mealMeal->replicate();
                                    $newMeal->meal_package_component_option_id =
                                        $newComponentOption->id;
                                    $newMeal->meal_id =
                                        $syncMeals[$mealMeal->meal_id];
                                    $newMeal->meal_size_id =
                                        $syncMealSizes[$mealMeal->meal_size_id];
                                    $newMeal->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
