<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Store;
use App\Customer;
use App\User;
use App\UserDetail;
use Illuminate\Support\Carbon;
use App\PurchasedGiftCard;
use App\StoreSetting;
use App\MealMealTag;
use App\Meal;
use App\MealSize;
use App\Order;
use App\MealAttachment;
use App\MealMealPackageComponentOption;
use App\MealPackageComponentOption;
use App\MealPackageComponent;
use App\MealMealPackageAddon;
use App\MealPackageAddon;
use App\MealPackage;

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
    protected $description = 'Miscellaneous scripts. Intentionally left blank. Code changed / added directly on server when needed.';

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
        $newStoreId = 278;

        $syncMeals = [];

        $meals = Meal::where('store_id', $oldStoreId)
            ->withTrashed()
            ->get();
        foreach ($meals as $meal) {
            $syncMeals[$meal->id] = Meal::where('store_id', $newStoreId)
                ->where('title', $meal->title)
                ->where('description', $meal->description)
                ->where('price', $meal->price)
                ->where('hidden', $meal->hidden)
                ->where('hideFromMenu', $meal->hideFromMenu)
                ->withTrashed()
                ->pluck('id')
                ->first();
        }

        $syncMealSizes = [];

        $mealSizes = MealSize::where('store_id', $oldStoreId)
            ->where('deleted_at', null)
            ->withTrashed()
            ->get();
        foreach ($mealSizes as $mealSize) {
            $meal = Meal::where('id', $mealSize->meal_id)
                ->withTrashed()
                ->first();
            $syncMealSizes[$mealSize->id] = MealSize::where(
                'store_id',
                $newStoreId
            )
                ->where('meal_id', $syncMeals[$mealSize->meal_id])
                ->withTrashed()
                ->pluck('id')
                ->first();
        }

        $mealMealPackageAddons = MealMealPackageAddon::all();

        foreach ($mealMealPackageAddons as $mealMealPackageAddon) {
            if (
                array_key_exists(
                    $mealMealPackageAddon->meal_size_id,
                    $syncMealSizes
                )
            ) {
                $mealPackageId = MealPackageAddon::where(
                    'id',
                    $mealMealPackageAddon->meal_package_addon_id
                )
                    ->pluck('meal_package_id')
                    ->first();

                $storeId = MealPackage::where('id', $mealPackageId)
                    ->pluck('store_id')
                    ->first();

                if ($storeId === $newStoreId) {
                    $this->info($mealMealPackageAddon->id);
                    $mealMealPackageAddon->meal_size_id =
                        $syncMealSizes[$mealMealPackageAddon->meal_size_id];
                    $mealMealPackageAddon->update();
                }

                // $mealPackageComponentOptionId = MealPackageComponentOption::where(
                //     'id',
                //     $mealMealPackageComponentOption->meal_package_component_option_id
                // )
                //     ->pluck('meal_package_component_id')
                //     ->first();
                // $mealPackageComponent = MealPackageComponent::where(
                //     'id',
                //     $mealPackageComponentOptionId
                // )->first();
                // if (
                //     $mealPackageComponent &&
                //     $mealPackageComponent->store_id === $newStoreId
                // ) {
                //     $this->info($mealMealPackageComponentOption);
                //     $mealMealPackageComponentOption->meal_size_id =
                //         $syncMealSizes[
                //             $mealMealPackageComponentOption->meal_size_id
                //         ];
                //     $mealMealPackageComponentOption->update();
                // }
            }
        }
    }
}
