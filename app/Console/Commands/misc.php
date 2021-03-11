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
    protected $description = 'Miscellaneous scripts. Intentionally left blank. Code changed / added directly on server when needed.';

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
        $customers = Customer::all();
        foreach ($customers as $customer) {
            $customer->total_payments = 0;
            $customer->total_paid = 0;
            $customer->last_order = null;
            $customer->update();
        }

        $orders = Order::all();

        foreach ($orders as $order) {
            try {
                if ($order->paid) {
                    $customer = Customer::where(
                        'id',
                        $order->customer_id
                    )->first();
                    if ($customer) {
                        $this->info($customer->id);
                        $customer->total_payments += 1;
                        $customer->total_paid += $order->amount;
                        if (
                            $customer->last_order &&
                            $customer->last_order < $order->created_at
                        ) {
                            $customer->last_order = $order->created_at;
                        }
                        $customer->update();
                    }
                }
            } catch (\Exception $e) {
            }
        }
    }
}
