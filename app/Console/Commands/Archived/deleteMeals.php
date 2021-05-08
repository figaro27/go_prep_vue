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
use App\Subscription;
use App\StorePlan;
use App\StorePlanTransaction;
use App\PackingSlipSetting;
use App\Facades\StorePlanService;
use App\ChildMeal;
use App\ChildMealPackage;
use App\ChildGiftCard;
use App\GiftCard;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use App\Media\Utils as MediaUtils;
use App\CategoryMeal;
use App\MealAllergy;
use App\MealComponent;
use App\MealComponentOption;
use App\MealAddon;
use App\MealMacro;
use App\Ingredient;
use App\IngredientMeal;
use App\IngredientMealSize;
use App\IngredientMealComponentOption;
use App\IngredientMealAddon;
use App\IngredientPivot;

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
        $store = Store::where('id', 313)->first();

        $meals = $store->meals()->get();

        foreach ($meals as $meal) {
            foreach ($meal->tags()->get() as $tag) {
                $tag->delete();
            }
            foreach ($meal->allergies()->get() as $allergy) {
                $allergy->delete();
            }
            foreach ($meal->categories()->get() as $categoryMeal) {
                $categoryMeal->delete();
            }
            foreach ($meal->sizes()->get() as $mealSize) {
                $mealSize->delete();
            }
            foreach ($meal->components()->get() as $mealComponent) {
                foreach ($mealComponent->options()->get() as $option) {
                    $option->delete();
                }
                $mealComponent->delete();
            }
            foreach ($meal->addons()->get() as $mealAddon) {
                $mealAddon->delete();
            }
            $meal->delete();
        }

        $productionGroups = $store->productionGroups()->get();

        foreach ($productionGroups as $productionGroup) {
            $productionGroup->delete();
        }

        $categories = $store->categories()->get();
        foreach ($categories as $category) {
            $category->delete();
        }

        // Add macros

        // Add ingredients

        // Add attachments
    }
}
