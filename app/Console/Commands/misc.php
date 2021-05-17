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
use App\OrderTransaction;
use App\Order;
use App\Payout;

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
        $stores = Store::where('id', '>', 20)->get();

        foreach ($stores as $store) {
            $this->info('Store ID: ' . $store->id);
            $acct = $store->settings->stripe_account;
            \Stripe\Stripe::setApiKey($acct['access_token']);

            $payouts = Payout::where('store_id', $store->id)->get();

            foreach ($payouts as $payout) {
                try {
                    $balanceTransactions = \Stripe\BalanceTransaction::all([
                        'payout' => $payout->stripe_id,
                        'limit' => 100
                    ])->data;

                    foreach ($balanceTransactions as $balanceTransaction) {
                        if ($balanceTransaction->type === 'charge') {
                            $charge = $balanceTransaction->source;

                            $order = Order::where(
                                'stripe_id',
                                $charge
                            )->first();
                            if (!$order) {
                                $this->info(
                                    'No order found for charge: ' . $charge
                                );
                            } else {
                                if (!$order->payout_date) {
                                    $order->payout_date = $payout->arrival_date;
                                    $order->payout_amount = $payout->amount;
                                    $order->update();
                                    $this->info(
                                        'Order ID: ' . $order->id . ' updated.'
                                    );
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $this->info(
                        'Payout ID: ' . $payout->id . ' not found in Stripe'
                    );
                }
            }
        }

        // $orderTransactions = OrderTransaction::all();

        // foreach ($orderTransactions as $orderTransaction){
        //     if (!$orderTransaction->stripe_id){
        //         $this->info('No Stripe ID: ' . $orderTransaction->id);
        //         $order = $orderTransaction->order;

        //         if ($order->stripe_id){
        //             $this->info('Stripe ID exists. Adding it to order transaction.');
        //             try {
        //                 $orderTransaction->stripe_id = $order->stripe_id;
        //                 $orderTransaction->update();
        //                 $this->info('Successfully added stripe ID');
        //             } catch (\Exception $e){
        //                 $this->info('Failed to add stripe ID');
        //             }

        //         }
        //     }
        // }

        // $orders = Order::where('id', '>', 1)
        //     ->where('paid', 1)
        //     ->where('cashOrder', 0)
        //     ->where('payment_gateway', 'stripe')
        //     ->where('stripe_id', '!=', null)
        //     ->get();

        // foreach ($orders as $order) {
        //     if ($order->id % 1000 === 0){
        //         $this->info('Up to order ID: ' . $order->id);
        //     }
        //     $orderTransaction = $order->orderTransaction;
        //     if (!$orderTransaction) {
        //         $this->info(
        //             'Missing - Order ID: ' .
        //                 $order->id .
        //                 ' Store ID: ' .
        //                 $order->store_id
        //         );
        //         try {
        //             $newOrderTransaction = new OrderTransaction();
        //             $newOrderTransaction->order_id = $order->id;
        //             $newOrderTransaction->store_id = $order->store_id;
        //             $newOrderTransaction->user_id = $order->user_id;
        //             $newOrderTransaction->customer_id = $order->customer_id;
        //             $newOrderTransaction->card_id = $order->card_id;
        //             $newOrderTransaction->type = 'order';
        //             $newOrderTransaction->stripe_id = $order->stripe_id;
        //             $newOrderTransaction->amount = $order->amount;
        //             $newOrderTransaction->applyToBalance = 0;
        //             $newOrderTransaction->save();

        //             $this->info('New order transaction created');
        //         } catch (\Exception $e){
        //             $this->info('Failed');
        //         }

        //     }
        // }
    }
}
