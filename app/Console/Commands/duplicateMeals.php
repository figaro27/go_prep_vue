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

class duplicateMeals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:duplicateMeals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        // Add macros
        // Add ingredients
        $meals = Meal::where('store_id', 313)
            ->with(['sizes', 'components', 'componentOptions', 'addons'])
            ->withTrashed()
            ->get();
        foreach ($meals as $meal) {
            // Main Meal
            $newMeal = new Meal();
            $newMeal = $meal->replicate();
            $newMeal->title = 'GILICIOUS MEALS - ' . $meal->title;
            $newMeal->save();

            foreach ($meal->categories as $category) {
                $categoryMeal = new CategoryMeal();
                $categoryMeal->category_id = $category->id;
                $categoryMeal->meal_id = $newMeal->id;
                $categoryMeal->save();
            }

            foreach ($meal->allergies as $allergy) {
                $allergyMeal = new MealAllergy();
                $allergyMeal->meal_id = $newMeal->id;
                $allergyMeal->allergy_id = $allergy->pivot['allergy_id'];
                $allergyMeal->save();
            }

            foreach ($meal->tags as $tag) {
                $tagMeal = new MealMealTag();
                $tagMeal->meal_id = $newMeal->id;
                $tagMeal->meal_tag_id = $tag->pivot['meal_tag_id'];
                $tagMeal->save();
            }

            // Components (Base Size)
            foreach ($meal->components as $component) {
                $newComponent = new MealComponent();
                $newComponent = $component->replicate();
                $newComponent->meal_id = $newMeal->id;
                $newComponent->save();

                // Added to add the size components later
                $component->newComponentId = $newComponent->id;

                // Component Options (Base Size)
                foreach ($component->options as $option) {
                    if ($option->meal_size_id === null) {
                        $newComponentOption = new MealComponentOption();
                        $newComponentOption = $option->replicate();
                        $newComponentOption->meal_component_id =
                            $newComponent->id;
                        $newComponentOption->save();
                    }
                }
            }

            // Addons (Base Size)
            foreach ($meal->addons as $addon) {
                if ($addon->meal_size_id === null) {
                    $newAddon = new MealAddon();
                    $newAddon = $addon->replicate();
                    $newAddon->meal_id = $newMeal->id;
                    $newAddon->save();
                }
            }

            foreach ($meal->sizes as $size) {
                $newSize = new MealSize();
                $newSize = $size->replicate();
                $newSize->meal_id = $newMeal->id;
                $newSize->save();

                // Components (Meal Sizes)
                foreach ($meal->components as $component) {
                    foreach ($component->options as $option) {
                        if ($option->meal_size_id === $size->id) {
                            $newComponentOption = new MealComponentOption();
                            $newComponentOption = $option->replicate();
                            $newComponentOption->meal_size_id = $newSize->id;
                            $newComponentOption->meal_component_id =
                                $component->newComponentId;
                            $newComponentOption->save();
                        }
                    }
                }

                // Addons (Meal Sizes)
                foreach ($size->addons as $addon) {
                    if ($addon->meal_size_id === $size->id) {
                        $newAddon = new MealAddon();
                        $newAddon = $addon->replicate();
                        $newAddon->meal_size_id = $newSize->id;
                        $newAddon->meal_id = $newMeal->id;
                        $newAddon->save();
                    }
                }
            }

            $childMeal = new ChildMeal();
            $childMeal->meal_id = $newMeal->id;
            $childMeal->store_id = 314;
            $childMeal->save();
        }

        // foreach ($meals as $meal){
        //     $newMeal = $meal->replicate();
        //     $newMeal->title = str_replace('GILICIOUS - ', '', $meal->title);
        //     $newMeal->save();
        //     $this->info($newMeal->title);
        //     dd();
        // }

        // $users = User::where('id', '>=', 25380)->get();

        // foreach ($users as $user){
        //     $userDetail = UserDetail::where('user_id', $user->id)->first();
        //     if ($userDetail){
        //         $this->info('FOUND ' . $userDetail->name);
        //     }
        //     else {
        //         // $user->delete();
        //         $this->info('NOT FOUND ' . $user->id);
        //         $this->info('STORE ID ' . $user->added_by_store_id);
        //     }
        // }

        // $customers = Customer::all();

        // foreach ($customers as $customer) {
        //     try {
        //         $customer->email = $customer->user->email;
        //         $customer->update();
        //         $this->info($customer->id . ' customer updated successfully.');
        //     } catch (\Exception $e) {
        //         $this->info('Error with ' . $customer->id);
        //         $this->info($e);
        //     }
        // }

        // $orders = Order::all();

        // foreach ($orders as $order) {
        //     try {
        //         $order->customer_firstname = $order->user->details->firstname;
        //         $order->customer_lastname = $order->user->details->lastname;
        //         $order->customer_email = $order->user->email;
        //         $order->customer_company = $order->user->details->companyname;
        //         $order->update();
        //         $this->info($order->id . ' updated successfully.');
        //     } catch (\Exception $e) {
        //         $this->info('Error with ' . $order->id);
        //         $this->info($e);
        //     }
        // }
    }
}
