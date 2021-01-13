<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MealOrder;
use App\MealSubscription;

class updatePackageOrderItemQuantities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:updatePackageOrderItemQuantities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'New field in meal_package_orders and meal_package_subscriptions called items_quantity for the package order summary report. This job goes through every existing package order and gets the total count of the amount of items in each package order.';

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
        $mealSubs = MealSubscription::where(
            'created_at',
            '>=',
            '2021-01-01 00:00:00'
        )
            ->where('meal_package_subscription_id', '!=', null)
            ->with('meal_package_subscription')
            ->get();

        foreach ($mealSubs as $mealSub) {
            try {
                $mealPackageSub = $mealSub->meal_package_subscription;
                $mealPackageSub->items_quantity += $mealSub->quantity;
                $mealPackageSub->update();
            } catch (\Exception $e) {
            }
        }

        $mealOrders = MealOrder::where(
            'created_at',
            '>=',
            '2021-01-01 00:00:00'
        )
            ->where('meal_package_order_id', '!=', null)
            ->with('meal_package_order')
            ->get();

        foreach ($mealOrders as $mealOrder) {
            try {
                $mealPackageOrder = $mealOrder->meal_package_order;
                $mealPackageOrder->items_quantity += $mealOrder->quantity;
                $mealPackageOrder->update();
            } catch (\Exception $e) {
            }
        }
    }
}
