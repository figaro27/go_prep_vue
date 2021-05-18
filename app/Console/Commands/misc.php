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
use App\UserDetail;
use App\Customer;
use App\Card;
use App\ReferralSetting;
use App\Referral;

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
        $storeIds = ReferralSetting::where('enabled', 1)
            ->get()
            ->map(function ($setting) {
                return $setting->store_id;
            });

        $stores = Store::whereIn('id', $storeIds)->get();

        foreach ($stores as $store) {
            $referralSettings = $store->referralSettings;
            $orders = $store->orders;
            foreach ($orders as $order) {
                if ($order->referral_id) {
                    if ($referralSettings->type === 'flat') {
                        $order->referral_kickback_amount =
                            $referralSettings->amount;
                    } else {
                        $order->referral_kickback_amount =
                            ($referralSettings->amount / 100) * $order->amount;
                    }
                    $order->update();
                }
            }
        }

        // $syncCustomersStripe = [];

        // $customers = Customer::all();

        // foreach ($customers as $customer){
        //     $syncCustomersStripe[$customer->user_id] = $customer->stripe_id;
        //     $customer->delete();
        // }

        // $orders = Order::all();

        // foreach ($orders as $order)
        // {
        //     $existingCustomer = Customer::where('store_id', $order->store_id)->where('user_id', $order->user_id)->first();
        //     if ($existingCustomer){
        //         $existingCustomer->last_order = $order->paid_at;
        //         $existingCustomer->total_payments += 1;
        //         $existingCustomer->total_paid += $order->amount;
        //         $existingCustomer->update();
        //     } else {
        //         $customer = new Customer();
        //         $customer->store_id = $order->store_id;
        //         $customer->user_id = $order->user_id;
        //         $customer->stripe_id = $syncCustomersStripe[$order->user_id];
        //         $customer->currency = $order->currency;
        //         $customer->payment_gateway = $order->payment_gateway;
        //         $customer->email = $order->user->email;
        //         $customer->firstname = $order->user->details->firstname;
        //         $customer->lastname = $order->user->details->lastname;
        //         $customer->name = $order->user->details->firstname . ' ' . $order->user->details->lastname;
        //         $customer->company = $order->user->details->company;
        //         $customer->phone = $order->user->details->phone;
        //         $customer->address = $order->user->details->address;
        //         $customer->city = $order->user->details->city;
        //         $customer->state = $order->user->details->state;
        //         $customer->zip = $order->user->details->zip;
        //         $customer->delivery = $order->user->details->delivery;
        //         $customer->last_order = $order->paid_at;
        //         $customer->total_payments = 1;
        //         $customer->total_paid = $order->amount;
        //         $customer->save();
        //     }

        // }

        // $moreThanTwo = [];
        // $totalRemoved = 0;
        // $orders = Order::where('id', '>', 25000)->where('id', '<', 45000)->get();

        // foreach ($orders as $order){
        // $this->info($order->id);
        //     $orderTransactions = OrderTransaction::where('order_id', $order->id)->where('type', 'order')->get();
        //     // if ($orderTransactions->count() > 1){
        //         if ($orderTransactions->count() > 2){
        //             dd($orderTransactions);
        //             $this->info($orderTransactions[0]['id']);
        //             array_push($orderTransactions[0]['id'], $moreThanTwo);
        //         }
        //         // else {
        //         //     $firstOrderTransaction = $orderTransactions[0];
        //         // $secondOrderTransaction = $orderTransactions[1];
        //         // $stripe_id = $firstOrderTransaction['stripe_id'];
        //         // $amount = $firstOrderTransaction['amount'];
        //         // $this->info($stripe_id);
        //         // $this->info($amount);
        //         // if ($secondOrderTransaction['stripe_id'] === $stripe_id && $secondOrderTransaction['amount'] === $amount){
        //         //     $remove = OrderTransaction::where('id', $secondOrderTransaction['id'])->first();
        //         //     $remove->delete();
        //         //     $totalRemoved += 1;
        //         //     $this->info('Exact duplicate found ' . $firstOrderTransaction['id']);
        //         // }
        //         // }
        //     // }
        // }
        // // $this->info('Total Removed: ' . $totalRemoved);
        // $this->info('More than two:' );
        // dd($moreThanTwo);

        // $stores = Store::where('id', '>', 20)->get();

        // foreach ($stores as $store) {
        //     $this->info('Store ID: ' . $store->id);
        //     $acct = $store->settings->stripe_account;
        //     \Stripe\Stripe::setApiKey($acct['access_token']);

        //     $payouts = Payout::where('store_id', $store->id)->get();
        //     foreach ($payouts as $payout) {
        //         $this->info('Payout ID: ' . $payout->id);
        //         try {
        //             $balanceTransactions = \Stripe\BalanceTransaction::all([
        //                 'payout' => $payout->stripe_id,
        //                 'limit' => 100
        //             ])->data;

        //             foreach ($balanceTransactions as $balanceTransaction) {
        //                 if ($balanceTransaction->type === 'charge') {
        //                     $charge = $balanceTransaction->source;

        //                     $order = Order::where(
        //                         'stripe_id',
        //                         $charge
        //                     )->first();
        //                     if ($order) {
        //                         if (!$order->payout_date) {
        //                             $order->payout_date = $payout->arrival_date;
        //                             $order->payout_total = $payout->amount;
        //                             $order->update();
        //                             $this->info(
        //                                 'Order ID: ' . $order->id . ' updated.'
        //                             );
        //                         }
        //                     }
        //                 }
        //             }
        //         } catch (\Exception $e) {
        //             $this->info(
        //                 'Payout ID: ' . $payout->id . ' not found in Stripe'
        //             );
        //         }
        //     }
        // }

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
