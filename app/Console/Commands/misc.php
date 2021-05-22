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
use Stripe;
use App\Billing\Authorize;
use App\User;
use App\Subscription;

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

    public function createStripeOrAuthorizeId($store, $user, $name)
    {
        $gateway = $store->settings->payment_gateway;
        if ($gateway === 'stripe') {
            $acct = $store->settings->stripe_account;
            \Stripe\Stripe::setApiKey($acct['access_token']);
            $stripeCustomer = \Stripe\Customer::create([
                'email' => $user->email,
                'description' => $name
            ]);
            $gatewayCustomerId = $stripeCustomer->id;
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            return $stripeCustomer->id;
        } elseif ($gateway === 'authorize') {
            $authorize = new Authorize($store);
            $gatewayCustomerId = $authorize->createCustomer($user);
            return $gatewayCustomerId;
        }
    }

    public function handle()
    {
        $subscriptions = Subscription::all();
        $customersCreatedCount = 0;
        $customersUpdatedCount = 0;
        foreach ($subscriptions as $sub) {
            $customerId = Customer::where([
                'user_id' => $sub->user_id,
                'store_id' => $sub->store_id
            ])
                ->pluck('id')
                ->first();
            if ($customerId) {
                // Update subscription with existing customer
                $sub->customer_id = $customerId;
                $sub->update();

                $this->info(
                    'Subscription updated with new customer: ' . $customer->id
                );
                $customersUpdatedCount++;
            } else {
                // Create new customer
                $store = Store::where('id', $sub->store_id)->first();
                $user = User::where('id', $sub->user_id)->first();
                $userDetail = UserDetail::where(
                    'user_id',
                    $sub->user_id
                )->first();
                if ($userDetail) {
                    $name =
                        $userDetail->firstname . ' ' . $userDetail->lastname;
                    try {
                        $customer = new Customer();
                        $customer->store_id = $sub->store_id;
                        $customer->user_id = $sub->user_id;

                        $customer->stripe_id = $this->createStripeOrAuthorizeId(
                            $store,
                            $user,
                            $name
                        );

                        $customer->firstname = $userDetail->firstname;
                        $customer->lastname = $userDetail->lastname;
                        $customer->name = $name;
                        $customer->phone = $userDetail->phone;
                        $customer->address = $userDetail->address;
                        $customer->city = $userDetail->city;
                        $customer->state = $userDetail->state;
                        $customer->zip = $userDetail->zip;
                        $customer->delivery = $userDetail->delivery;
                        $customer->save();

                        $sub->customer_id = $customer->id;
                        $sub->update();
                        $this->info(
                            'New customer created and subscription updated with customer ID: ' .
                                $customer->id
                        );
                        $customersCreatedCount++;
                    } catch (\Exception $e) {
                        $this->info('Error: ' . $e->getMessage());
                    }
                } else {
                    $this->info(
                        'User Detail not found for sub id: ' . $sub->id
                    );
                }
            }

            // Update all subscription orders
        }

        $this->info('Customers Created Count: ' . $customersCreatedCount);
        $this->info('Customers Updated Count: ' . $customersUpdatedCount);

        // $this->info('Total subs needing updating: ' . $count);

        // $userDetails = UserDetail::all();

        // foreach ($userDetails as $userDetail){
        //     $customers = Customer::where('user_id', $userDetail->user_id)->get();

        //     $total_payments = 0;
        //     foreach ($customers as $customer){
        //         $total_payments += $customer->total_payments;
        //     }
        //     if ($userDetail->total_payments !== $total_payments){
        //         $this->info('User Detail ID: ' . $userDetail->id);
        //         $this->info('User Detail Starting Total Payments: ' . $userDetail->total_payments);
        //         $this->info('User Detail Ending Total Payments: ' . $total_payments);
        //         $userDetail->total_payments = $total_payments;
        //         $userDetail->update();
        //         $this->info('User Detail updated: ' . $userDetail->id);
        //         $this->info('');
        //         $this->info('');
        //     }

        // }

        // $orders = Order::where('id', '>', 2000)->get();
        // $count = 0;
        // foreach ($orders as $order){
        //     $customerUserId = Customer::where('id', $order->customer_id)->pluck('user_id')->first();
        //     if (!$customerUserId){
        //         $detailsPhone = $order->phone;
        //         if (!$detailsPhone){
        //             $detailsPhone = UserDetail::where('user_id', $order->user_id)->pluck('phone')->first();
        //         }

        //         if ($detailsPhone){
        //             $customerUserId = Customer::where('phone', 'like', $detailsPhone)->pluck('id')->first();
        //         }

        //         if (!$customerUserId){
        //             $store = Store::where('id', $order->store_id)->first();
        //             $user = User::where('id', $order->user_id)->first();
        //             $name = $order->customer_name;
        //             if ($store && $user && $name){
        //                 try {
        //                     $customer = new Customer();
        //                     $customer->store_id = $order->store_id;
        //                     $customer->user_id = $order->user_id;

        //                     $customer->stripe_id = $this->createStripeOrAuthorizeId(
        //                         $store,
        //                         $user,
        //                         $name
        //                     );

        //                     $customer->firstname = $order->customer_firstname;
        //                     $customer->lastname = $order->customer_lastname;
        //                     $customer->name = $name;
        //                     $customer->phone = $order->customer_phone;
        //                     $customer->address = $order->customer_address;
        //                     $customer->city = $order->customer_city;
        //                     $customer->state = $order->customer_state;
        //                     $customer->zip = $order->customer_zip;
        //                     $customer->delivery = $order->customer_delivery;
        //                     $customer->save();

        //                     $this->info('New customer created: ' . $customer->id);
        //                     $count++;
        //                 } catch (\Exception $e){
        //                     $this->info('Error: ' . $e->getMessage());
        //                 }
        //             } else {
        //                 $this->info('Null data');
        //             }

        //         }
        //     }
        // }

        // $this->info('New customers created: ' . $count);

        // $guestCheckoutStores = [118, 196, 265, 272, 309];

        // $stores = Store::all();
        // $totalDuplicatesDeleted = 0;

        // foreach ($stores as $store) {
        //     $totalStoreDuplicatesDeleted = 0;
        //     if (!in_array($store->id, $guestCheckoutStores)) {
        //         $this->info('Store ID: ' . $store->id);

        //         $duplicateCustomers = Customer::where('store_id', $store->id)
        //             ->whereIn('phone', function ($query) {
        //                 $query
        //                     ->select('phone')
        //                     ->from('customers')
        //                     ->groupBy('phone')
        //                     ->havingRaw('count(*) > 1');
        //             })
        //             ->get();

        //         if (count($duplicateCustomers) > 0) {
        //             foreach ($duplicateCustomers as $i => $duplicateCustomer) {
        //                 if ($duplicateCustomer->store_id === $store->id) {
        //                     $phone = $duplicateCustomer->phone;
        //                     $duplicateCustomers = Customer::where(
        //                         'phone',
        //                         'like',
        //                         $phone
        //                     )
        //                         ->where('store_id', $store->id)
        //                         ->get();

        //                     // Setting the main customer if the email exists
        //                     $firstCustomer = null;
        //                     foreach (
        //                         $duplicateCustomers
        //                         as $duplicateCustomer
        //                     ) {
        //                         if (
        //                             strpos(
        //                                 $duplicateCustomer->email,
        //                                 'noemail'
        //                             ) === false
        //                         ) {
        //                             $firstCustomer = $duplicateCustomer;
        //                         }
        //                     }

        //                     // If not then just choose the first one
        //                     if (!$firstCustomer) {
        //                         $firstCustomer = Customer::where(
        //                             'id',
        //                             $duplicateCustomers[0]->id
        //                         )->first();
        //                     }

        //                     foreach (
        //                         $duplicateCustomers
        //                         as $duplicateCustomer
        //                     ) {
        //                         if (
        //                             $duplicateCustomer->id !==
        //                             $firstCustomer->id
        //                         ) {
        //                             $firstCustomer->total_payments +=
        //                                 $duplicateCustomer->total_payments;
        //                             $firstCustomer->total_paid +=
        //                                 $duplicateCustomer->total_paid;
        //                             $firstCustomer->points !=
        //                                 $duplicateCustomer->points;
        //                             if (
        //                                 $firstCustomer->stripe_id === 'cash' &&
        //                                 $duplicateCustomer->stripe_id !== 'cash'
        //                             ) {
        //                                 $firstCustomer->stripe_id =
        //                                     $duplicateCustomer->stripe_id;
        //                                 $firstCustomer->payment_gateway =
        //                                     $duplicateCustomer->payment_gateway;
        //                             }
        //                             if (
        //                                 $firstCustomer->address == 'N/A' &&
        //                                 $duplicateCustomer->address !== 'N/A'
        //                             ) {
        //                                 $firstCustomer->address =
        //                                     $duplicateCustomer->address;
        //                             }
        //                             if (
        //                                 $firstCustomer->city == 'N/A' &&
        //                                 $duplicateCustomer->city !== 'N/A'
        //                             ) {
        //                                 $firstCustomer->city =
        //                                     $duplicateCustomer->city;
        //                             }
        //                             if (
        //                                 $firstCustomer->state == 'N/A' &&
        //                                 $duplicateCustomer->state !== 'N/A'
        //                             ) {
        //                                 $firstCustomer->state =
        //                                     $duplicateCustomer->state;
        //                             }
        //                             if (
        //                                 $firstCustomer->zip == 'N/A' &&
        //                                 $duplicateCustomer->zip !== 'N/A'
        //                             ) {
        //                                 $firstCustomer->zip =
        //                                     $duplicateCustomer->zip;
        //                             }
        //                             if (
        //                                 $firstCustomer->company == 'N/A' &&
        //                                 $duplicateCustomer->company !== 'N/A'
        //                             ) {
        //                                 $firstCustomer->company =
        //                                     $duplicateCustomer->company;
        //                             }
        //                             if (
        //                                 strpos(
        //                                     $firstCustomer->email,
        //                                     'no-email'
        //                                 ) !== false &&
        //                                 strpos(
        //                                     $duplicateCustomer->email,
        //                                     'no-email'
        //                                 ) === false
        //                             ) {
        //                                 $firstCustomer->email =
        //                                     $duplicateCustomer->email;
        //                             }
        //                             $this->info(
        //                                 'First customer updated: ' .
        //                                     $firstCustomer->id
        //                             );
        //                             $firstCustomer->update();

        //                             $orders = Order::where(
        //                                 'customer_id',
        //                                 $duplicateCustomer->id
        //                             )->get();
        //                             foreach ($orders as $order) {
        //                                 $order->customer_id =
        //                                     $firstCustomer->id;
        //                                 $order->user_id =
        //                                     $firstCustomer->user_id;
        //                                 $order->update();
        //                                 $this->info(
        //                                     'Order updated: ' . $order->id
        //                                 );
        //                             }

        //                             $this->info(
        //                                 'Customer deleted: ' .
        //                                     $duplicateCustomer->id
        //                             );
        //                             $duplicateCustomer->delete();
        //                             $totalDuplicatesDeleted += 1;
        //                             $totalStoreDuplicatesDeleted += 1;
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //     }

        //     $this->info(
        //         'Total store duplicate customers deleted: ' .
        //             $totalStoreDuplicatesDeleted
        //     );
        // }

        // $this->info(
        //     'Total duplicate customers deleted: ' . $totalDuplicatesDeleted
        // );

        // ------------------------------

        // $syncCustomersStripe = [];

        // $customers = Customer::all();

        // foreach ($customers as $customer) {
        //     try {
        //         $syncCustomersStripe[$customer->user_id] = $customer->stripe_id;
        //         $this->info('Deleting customer: ' . $customer->id);
        //         $customer->delete();
        //     } catch (\Exception $e) {
        //         $this->info('Failed to delete customer: ' . $customer->id);
        //         $this->info('Error: ' . $e->getMessage());
        //     }
        // }

        // $orders = Order::all();

        // foreach ($orders as $order) {
        //     try {
        //         $this->info($order->id);
        //         $existingCustomer = Customer::where(
        //             'store_id',
        //             $order->store_id
        //         )
        //             ->where('user_id', $order->user_id)
        //             ->first();
        //         if ($existingCustomer) {
        //             $existingCustomer->last_order = $order->paid_at;
        //             $existingCustomer->total_payments += 1;
        //             $existingCustomer->total_paid += $order->amount;
        //             $existingCustomer->update();

        //             $order->customer_id = $existingCustomer->id;
        //             $order->update();
        //         } else {
        //             if ($syncCustomersStripe[$order->user_id]) {
        //                 $customer = new Customer();
        //                 $customer->store_id = $order->store_id;
        //                 $customer->user_id = $order->user_id;
        //                 $customer->stripe_id =
        //                     $syncCustomersStripe[$order->user_id];
        //                 $customer->currency = $order->currency;
        //                 $customer->payment_gateway = $order->payment_gateway;
        //                 $customer->email = $order->user->email;
        //                 $customer->firstname = $order->user->details->firstname;
        //                 $customer->lastname = $order->user->details->lastname;
        //                 $customer->name =
        //                     $order->user->details->firstname .
        //                     ' ' .
        //                     $order->user->details->lastname;
        //                 $customer->company = $order->user->details->company;
        //                 $customer->phone = $order->user->details->phone;
        //                 $customer->address = $order->user->details->address;
        //                 $customer->city = $order->user->details->city;
        //                 $customer->state = $order->user->details->state;
        //                 $customer->zip = $order->user->details->zip;
        //                 $customer->delivery = $order->user->details->delivery;
        //                 $customer->last_order = $order->paid_at;
        //                 $customer->total_payments = 1;
        //                 $customer->total_paid = $order->amount;
        //                 $customer->save();

        //                 $order->customer_id = $customer->id;
        //                 $order->update();
        //             } else {
        //                 $this->info(
        //                     'No synced customer for order ID: ' . $order->id
        //                 );
        //             }
        //         }
        //     } catch (\Exception $e) {
        //         $this->info(
        //             'Failed to update or create customer for order ID: ' .
        //                 $order->id
        //         );
        //         $this->info('Error: ' . $e->getMessage());
        //     }
        // }
    }
}
