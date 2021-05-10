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
use App\LabelSetting;
use App\OrderLabelSetting;
use App\ReportSetting;

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
        $reportSettings = ReportSetting::all();

        foreach ($reportSettings as $reportSetting) {
            $labelSetting = new OrderLabelSetting();
            $labelSetting->store_id = $reportSetting->store_id;
            $labelSetting->customer = $reportSetting->o_lab_customer;
            $labelSetting->address = $reportSetting->o_lab_address;
            $labelSetting->phone = $reportSetting->o_lab_phone;
            $labelSetting->delivery = $reportSetting->o_lab_delivery;
            $labelSetting->order_number = $reportSetting->o_lab_order_number;
            $labelSetting->order_date = $reportSetting->o_lab_order_date;
            $labelSetting->delivery_date = $reportSetting->o_lab_delivery_date;
            $labelSetting->amount = $reportSetting->o_lab_amount;
            $labelSetting->balance = $reportSetting->o_lab_balance;
            $labelSetting->daily_order_number =
                $reportSetting->o_lab_daily_order_number;
            $labelSetting->pickup_location =
                $reportSetting->o_lab_pickup_location;
            $labelSetting->website = $reportSetting->o_lab_website;
            $labelSetting->social = $reportSetting->o_lab_social;
            $labelSetting->width = $reportSetting->o_lab_width;
            $labelSetting->height = $reportSetting->o_lab_height;
            $labelSetting->save();
        }
    }
}
