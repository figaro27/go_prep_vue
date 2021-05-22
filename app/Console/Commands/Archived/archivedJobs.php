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
use App\StoreDetail;

class archivedJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:archivedJobs';

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
        $ignoreUserIds = [
            13,
            16,
            333,
            35,
            36,
            37,
            603,
            605,
            606,
            880,
            884,
            885,
            14425,
            43,
            44,
            45,
            589,
            845,
            846,
            848,
            849,
            853,
            854,
            871,
            873,
            874,
            876,
            879,
            882,
            883,
            920,
            921,
            935,
            1102,
            1107,
            1692,
            1838,
            2189,
            2613,
            3134,
            3905,
            7818,
            7992,
            12777,
            15206,
            15207,
            20121,
            21627,
            25104,
            705,
            710,
            712,
            722,
            723,
            724,
            726,
            730,
            739,
            750,
            865,
            867,
            878,
            885,
            890,
            922,
            933,
            934,
            935,
            936,
            960,
            964,
            994,
            1106,
            1151,
            1152,
            1587,
            1838,
            2189,
            2195,
            2476,
            2478,
            2479,
            2482,
            2484,
            2492,
            2493,
            2610,
            2612,
            2613,
            3134,
            4242,
            5564,
            5565,
            5922,
            6325,
            7401,
            8280,
            8593,
            9344,
            9568,
            9569,
            9571,
            9572,
            9573,
            9574,
            9576,
            9577,
            9578,
            9585,
            9643,
            9644,
            9645,
            9646,
            9647,
            9653,
            9971,
            10159,
            10163,
            10248,
            10274,
            12780,
            13422,
            13482,
            13936,
            14060,
            14258,
            14259,
            15957,
            16239,
            17399,
            17589,
            18082,
            18121,
            18124,
            18377,
            18380,
            19637,
            21465,
            21590,
            22279,
            23736
        ];
        $orders = Order::all();

        $tompkins = [131, 137, 127];
        $letscook = [178, 180, 182, 184];
        $souza = [119, 120];
        $livotis = [108, 109, 110, 278, 15, 20, 21];
        $livingFit = [112, 261];
        $deliveredDelicious = [195, 167];
        $jbb = [40, 100, 313, 314];
        $chefsLineup = [265, 309];

        $count = 0;

        foreach ($orders as $order) {
            $storeId = $order->store_id;
            $user = $order->user;
            if ($user) {
                if (!in_array($user->id, $ignoreUserIds)) {
                    if (
                        ($user->last_viewed_store_id &&
                            $user->last_viewed_store_id !== $storeId) ||
                        ($user->added_by_store_id &&
                            $user->added_by_store_id !== $storeId)
                    ) {
                        $userAssociatedStoreId = $user->last_viewed_store_id
                            ? $user->last_viewed_store_id
                            : $user->added_by_store_id;
                        if (
                            (in_array($storeId, $tompkins) &&
                                in_array($userAssociatedStoreId, $tompkins)) ||
                            (in_array($storeId, $letscook) &&
                                in_array($userAssociatedStoreId, $letscook)) ||
                            (in_array($storeId, $souza) &&
                                in_array($userAssociatedStoreId, $souza)) ||
                            (in_array($storeId, $livotis) &&
                                in_array($userAssociatedStoreId, $livotis)) ||
                            (in_array($storeId, $livingFit) &&
                                in_array($userAssociatedStoreId, $livingFit)) ||
                            (in_array($storeId, $deliveredDelicious) &&
                                in_array(
                                    $userAssociatedStoreId,
                                    $deliveredDelicious
                                )) ||
                            (in_array($storeId, $jbb) &&
                                in_array($userAssociatedStoreId, $jbb)) ||
                            (in_array($storeId, $chefsLineup) &&
                                in_array($userAssociatedStoreId, $chefsLineup))
                        ) {
                            // Ignore
                        } else {
                            $orderStore = StoreDetail::where(
                                'store_id',
                                $storeId
                            )
                                ->pluck('name')
                                ->first();
                            $associatedStore = StoreDetail::where(
                                'store_id',
                                $userAssociatedStoreId
                            )
                                ->pluck('name')
                                ->first();
                            $this->info('Order ID: ' . $order->id);
                            $this->info('Store ID: ' . $storeId);
                            $this->info('User ID: ' . $order->user->id);
                            $this->info('Order Store: ' . $orderStore);
                            $this->info(
                                'AssociatedStore Store: ' . $associatedStore
                            );
                            $this->info('');
                            $this->info('');
                            $count++;
                        }
                    }
                }
            }
        }

        $this->info('FINAL COUNT: ' . $count);

        // $settings = StoreSetting::all();

        // foreach ($settings as $setting){

        //     if ($setting->stripe_account){
        //         $account = $setting->stripe_account;
        //         $type = $account['scope'] == 'express' ? 'express' : 'standard';
        //         $setting->account_type = $type;
        //         $setting->update();
        //     }

        // }

        // $referrals = Referral::all();

        // foreach ($referrals as $referral) {
        //     $orders = Order::where('user_id', $referral->user_id)
        //         ->where('referralReduction', '>', 0)
        //         ->get();
        //     foreach ($orders as $order) {
        //         $referral->total_paid_or_used += $order->referralReduction;
        //         $referral->update();
        //     }
        // }

        // $storeIds = ReferralSetting::where('enabled', 1)
        //     ->get()
        //     ->map(function ($setting) {
        //         return $setting->store_id;
        //     });

        // $stores = Store::whereIn('id', $storeIds)->get();

        // foreach ($stores as $store) {
        //     $this->info($store->id);
        //     $referralSettings = $store->referralSettings;
        //     $orders = $store->orders;
        //     foreach ($orders as $order) {
        //         if ($order->referral_id) {
        //             if ($referralSettings->type === 'flat') {
        //                 $order->referral_kickback_amount =
        //                     $referralSettings->amount;
        //             } else {
        //                 $order->referral_kickback_amount =
        //                     ($referralSettings->amount / 100) * $order->amount;
        //             }
        //             $order->update();
        //         }
        //     }
        // }

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
