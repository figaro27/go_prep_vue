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

class duplicateStore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:duplicateStore';

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
        $oldStoreId = 108;
        $newStoreId = 278;

        $syncMeals = [];
        $syncMealSizes = [];
        $syncCategories = [];
        $syncAllergies = [];
        $syncTags = [];
        $syncProductionGroups = [];
        $syncPackageComponentOptions = [];

        // Categories
        $categories = Category::where('store_id', $oldStoreId)->get();
        foreach ($categories as $category) {
            $newCategory = new Category();
            $newCategory = $category->replicate();
            $newCategory->store_id = $newStoreId;
            $newCategory->save();

            $syncCategories[$category->id] = $newCategory->id;
        }

        // Production Groups
        $productionGroups = ProductionGroup::where(
            'store_id',
            $oldStoreId
        )->get();
        foreach ($productionGroups as $productionGroup) {
            $newProductionGroup = new ProductionGroup();
            $newProductionGroup = $productionGroup->replicate();
            $newProductionGroup->store_id = $newStoreId;
            $newProductionGroup->save();

            $syncProductionGroups[$productionGroup->id] =
                $newProductionGroup->id;
        }

        // Meals
        $meals = Meal::where('store_id', $oldStoreId)
            ->with(['sizes', 'components', 'componentOptions', 'addons'])
            ->withTrashed()
            ->get();
        foreach ($meals as $meal) {
            // Main Meal
            $newMeal = new Meal();
            $newMeal = $meal->replicate();
            $newMeal->store_id = $newStoreId;
            $newMeal->production_group_id = isset(
                $syncProductionGroups[$meal->production_group_id]
            )
                ? $syncProductionGroups[$meal->production_group_id]
                : null;
            $newMeal->save();

            $syncMeals[$meal->id] = $newMeal->id;
            $syncAllergies[$meal->id] = $newMeal->id;
            $syncTags[$meal->id] = $newMeal->id;

            foreach ($meal->categories as $category) {
                $categoryMeal = new CategoryMeal();
                $categoryMeal->category_id = $syncCategories[$category->id];
                $categoryMeal->meal_id = $newMeal->id;
                $categoryMeal->save();
            }

            foreach ($meal->allergies as $allergy) {
                $allergyMeal = new MealAllergy();
                $allergyMeal->meal_id = $syncAllergies[$meal->id];
                $allergyMeal->allergy_id = $allergy->pivot['allergy_id'];
                $allergyMeal->save();
            }

            foreach ($meal->tags as $tag) {
                $tagMeal = new MealMealTag();
                $tagMeal->meal_id = $syncTags[$meal->id];
                $tagMeal->meal_tag_id = $tag->pivot['meal_tag_id'];
                $tagMeal->save();
            }

            // Components (Base Size)
            foreach ($meal->components as $component) {
                $newComponent = new MealComponent();
                $newComponent = $component->replicate();
                $newComponent->store_id = $newStoreId;
                $newComponent->meal_id = $newMeal->id;
                $newComponent->save();

                // Added to add the size components later
                $component->newComponentId = $newComponent->id;

                // Component Options (Base Size)
                foreach ($component->options as $option) {
                    if ($option->meal_size_id === null) {
                        $newComponentOption = new MealComponentOption();
                        $newComponentOption = $option->replicate();
                        $newComponentOption->store_id = $newStoreId;
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
                    $newAddon->store_id = $newStoreId;
                    $newAddon->meal_id = $newMeal->id;
                    $newAddon->save();
                }
            }

            foreach ($meal->sizes as $size) {
                $newSize = new MealSize();
                $newSize = $size->replicate();
                $newSize->meal_id = $newMeal->id;
                $newSize->store_id = $newStoreId;
                $newSize->save();

                $syncMealSizes[$size->id] = $newSize->id;

                // Components (Meal Sizes)
                foreach ($meal->components as $component) {
                    foreach ($component->options as $option) {
                        if ($option->meal_size_id === $size->id) {
                            $newComponentOption = new MealComponentOption();
                            $newComponentOption = $option->replicate();
                            $newComponentOption->store_id = $newStoreId;
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
                        $newAddon->store_id = $newStoreId;
                        $newAddon->meal_size_id = $newSize->id;
                        $newAddon->meal_id = $newMeal->id;
                        $newAddon->save();
                    }
                }
            }
        }

        // Meal Packages
        $mealPackages = MealPackage::where('store_id', $oldStoreId)
            ->with(['sizes', 'components', 'componentOptions', 'addons'])
            ->withTrashed()
            ->get();

        foreach ($mealPackages as $mealPackage) {
            // Main Meal Package
            $newMealPackage = new MealPackage();
            $newMealPackage = $mealPackage->replicate();
            $newMealPackage->store_id = $newStoreId;
            $newMealPackage->save();

            foreach ($mealPackage->categories as $category) {
                $categoryMealPackage = new CategoryMealPackage();
                $categoryMealPackage->category_id =
                    $syncCategories[$category->id];
                $categoryMealPackage->meal_package_id = $newMealPackage->id;
                $categoryMealPackage->save();
            }

            // Top level meal package meals
            foreach (
                MealMealPackage::where(
                    'meal_package_id',
                    $mealPackage->id
                )->get()
                as $mealMeal
            ) {
                $newMeal = new MealMealPackage();
                $newMeal = $mealMeal->replicate();
                $newMeal->meal_package_id = $newMealPackage->id;
                $newMeal->meal_id = $syncMeals[$mealMeal->meal_id];
                $newMeal->save();
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
                            $newMeal = new MealMealPackageComponentOption();
                            $newMeal = $mealMeal->replicate();
                            $newMeal->meal_package_component_option_id =
                                $newComponentOption->id;
                            $newMeal->meal_id = $syncMeals[$mealMeal->meal_id];
                            $newMeal->meal_size_id =
                                $syncMealSizes[$mealMeal->meal_size_id];
                            $newMeal->save();
                        }
                    }
                }
            }

            // Addons (Base Size)
            foreach ($mealPackage->addons as $addon) {
                if ($addon->meal_package_size_id === null) {
                    $newAddon = new MealPackageAddon();
                    $newAddon = $addon->replicate();
                    $newAddon->meal_package_id = $newMealPackage->id;
                    $newAddon->save();

                    // Top level meal package addon meals
                    foreach (
                        MealMealPackageAddon::where(
                            'meal_package_addon_id',
                            $addon->id
                        )->get()
                        as $mealMeal
                    ) {
                        $newMeal = new MealMealPackageAddon();
                        $newMeal = $mealMeal->replicate();
                        $newMeal->meal_package_addon_id = $newAddon->id;
                        $newMeal->meal_id = $syncMeals[$mealMeal->meal_id];
                        $newMeal->save();
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
                    $newMeal = new MealMealPackageSize();
                    $newMeal = $mealMeal->replicate();
                    $newMeal->meal_package_size_id = $newSize->id;
                    $newMeal->meal_id = $syncMeals[$mealMeal->meal_id];
                    $newMeal->save();
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
                                $newMeal = new MealMealPackageComponentOption();
                                $newMeal = $mealMeal->replicate();
                                $newMeal->meal_package_component_option_id =
                                    $newComponentOption->id;
                                $newMeal->meal_id =
                                    $syncMeals[$mealMeal->meal_id];
                                $newMeal->save();
                            }
                        }
                    }
                }

                // Addons (Meal Package Sizes)
                foreach ($size->addons as $addon) {
                    if ($addon->meal_package_size_id === $size->id) {
                        $newAddon = new MealPackageAddon();
                        $newAddon = $addon->replicate();
                        $newAddon->meal_package_size_id = $newSize->id;
                        $newAddon->meal_package_id = $newMealPackage->id;
                        $newAddon->save();

                        // Size meal package addon meals
                        foreach (
                            MealMealPackageAddon::where(
                                'meal_package_addon_id',
                                $addon->id
                            )->get()
                            as $mealMeal
                        ) {
                            $newMeal = new MealMealPackageAddon();
                            $newMeal = $mealMeal->replicate();
                            $newMeal->meal_package_addon_id = $newAddon->id;
                            $newMeal->meal_id = $syncMeals[$mealMeal->meal_id];
                            $newMeal->meal_size_id =
                                $syncMealSizes[$mealMeal->meal_size_id];
                            $newMeal->save();
                        }
                    }
                }
            }
        }

        // Setting restricted package component option IDs now that old and new are synced
        $newMealPackages = MealPackage::where('store_id', $newStoreId)
            ->with(['sizes', 'components', 'componentOptions', 'addons'])
            ->withTrashed()
            ->get();
        foreach ($newMealPackages as $newMealPackage) {
            foreach ($newMealPackage->components as $component) {
                foreach ($component->options as $option) {
                    if ($option->restrict_meals_option_id) {
                        $option->restrict_meals_option_id =
                            $syncPackageComponentOptions[
                                $option->restrict_meals_option_id
                            ];
                        $option->update();
                    }
                }
            }
        }

        // Meal attachments
        $attachments = MealAttachment::where('store_id', $oldStoreId)->get();

        foreach ($attachments as $attachment) {
            $newAttachment = new MealAttachment();
            $newAttachment = $attachment->replicate();
            $newAttachment->store_id = $newStoreId;
            $newAttachment->meal_id = $syncMeals[$attachment->meal_id];
            $newAttachment->meal_size_id = $attachment->meal_size_id
                ? $syncMealSizes[$attachment->meal_size_id]
                : null;
            $newAttachment->attached_meal_id =
                $syncMeals[$attachment->attached_meal_id];
            $newAttachment->attached_meal_size_id = $attachment->attached_meal_size_id
                ? $syncMealSizes[$attachment->attached_meal_size_id]
                : null;
            $newAttachment->save();
        }
    }
}
