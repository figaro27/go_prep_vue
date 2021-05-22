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
        $syncCustomersStripe = [];

        $customers = Customer::all();

        foreach ($customers as $customer) {
            try {
                $syncCustomersStripe[$customer->user_id] = $customer->stripe_id;
                $this->info('Deleting customer: ' . $customer->id);
                $customer->delete();
            } catch (\Exception $e) {
                $this->info('Failed to delete customer: ' . $customer->id);
                $this->info('Error: ' . $e->getMessage());
            }
        }

        $orders = Order::all();

        foreach ($orders as $order) {
            try {
                $this->info($order->id);
                $existingCustomer = Customer::where(
                    'store_id',
                    $order->store_id
                )
                    ->where('user_id', $order->user_id)
                    ->first();
                if ($existingCustomer) {
                    $existingCustomer->last_order = $order->paid_at;
                    $existingCustomer->total_payments += 1;
                    $existingCustomer->total_paid += $order->amount;
                    $existingCustomer->update();

                    $order->customer_id = $existingCustomer->id;
                    $order->update();
                } else {
                    if ($syncCustomersStripe[$order->user_id]) {
                        $customer = new Customer();
                        $customer->store_id = $order->store_id;
                        $customer->user_id = $order->user_id;
                        $customer->stripe_id =
                            $syncCustomersStripe[$order->user_id];
                        $customer->currency = $order->currency;
                        $customer->payment_gateway = $order->payment_gateway;
                        $customer->email = $order->user->email;
                        $customer->firstname = $order->user->details->firstname;
                        $customer->lastname = $order->user->details->lastname;
                        $customer->name =
                            $order->user->details->firstname .
                            ' ' .
                            $order->user->details->lastname;
                        $customer->company = $order->user->details->company;
                        $customer->phone = $order->user->details->phone;
                        $customer->address = $order->user->details->address;
                        $customer->city = $order->user->details->city;
                        $customer->state = $order->user->details->state;
                        $customer->zip = $order->user->details->zip;
                        $customer->delivery = $order->user->details->delivery;
                        $customer->last_order = $order->paid_at;
                        $customer->total_payments = 1;
                        $customer->total_paid = $order->amount;
                        $customer->save();

                        $order->customer_id = $customer->id;
                        $order->update();
                    } else {
                        $this->info(
                            'No synced customer for order ID: ' . $order->id
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->info(
                    'Failed to update or create customer for order ID: ' .
                        $order->id
                );
                $this->info('Error: ' . $e->getMessage());
            }
        }
    }
}
