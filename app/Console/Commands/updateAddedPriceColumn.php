<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MealOrder;
use App\MealSubscription;

class updateAddedPriceColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:updateAddedPriceColumn';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'New added_price column added to meal_orders and meal_subscriptions for new added_price report and better way to present the additional price in emails, modals, packing slips. This command updates all the old meal_orders and meal_subscriptions.';

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
        $mealOrders = MealOrder::where('meal_package_variation', 1)
            ->where('price', '>', 0)
            ->get();

        foreach ($mealOrders as $mealOrder) {
            try {
                $mealOrder->added_price = $mealOrder->price;
                $mealOrder->update();
            } catch (\Exception $e) {
            }
        }

        $mealSubs = MealSubscription::where(
            'meal_package_subscription_id',
            '!=',
            null
        )
            ->where('price', '>', 0)
            ->get();

        foreach ($mealSubs as $mealSub) {
            try {
                $mealSub->added_price = $mealSub->price;
                $mealSub->update();
            } catch (\Exception $e) {
            }
        }
    }
}
