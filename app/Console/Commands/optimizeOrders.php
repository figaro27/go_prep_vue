<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Order;
use App\Staff;
use App\PickupLocation;
use App\PurchasedGiftCard;
use App\StoreDetail;
use App\Customer;

class optimizeOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:optimizeOrders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates new fields on the orders table that were removed as attributes';

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
        $orders = Order::all();

        foreach ($orders as $order) {
            try {
                $goPrepFee =
                    $order->afterDiscountBeforeFees *
                    ($order->store->settings->application_fee / 100);
                $stripeFee =
                    !$order->cashOrder && $order->amount > 0.5
                        ? $order->amount * 0.029 + 0.3
                        : 0;

                $order->staff_member = Staff::where('id', $order->staff_id)
                    ->pluck('name')
                    ->first();
                $order->pickup_location_name = PickupLocation::where(
                    'id',
                    $order->pickup_location_id
                )
                    ->pluck('name')
                    ->first();
                $order->purchased_gift_card_code = PurchasedGiftCard::where(
                    'id',
                    $order->purchased_gift_card_id
                )
                    ->pluck('code')
                    ->first();
                $order->store_name = StoreDetail::where(
                    'store_id',
                    $order->store_id
                )
                    ->pluck('name')
                    ->first();
                $order->transfer_type = ($order->shipping
                        ? 'Shipping'
                        : $order->pickup)
                    ? 'Pickup'
                    : 'Delivery';
                $order->customer_name = Customer::where(
                    'id',
                    $order->customer_id
                )
                    ->pluck('name')
                    ->first();
                $order->customer_address = Customer::where(
                    'id',
                    $order->customer_id
                )
                    ->pluck('address')
                    ->first();
                $order->customer_zip = Customer::where(
                    'id',
                    $order->customer_id
                )
                    ->pluck('zip')
                    ->first();
                $order->goprep_fee = $goPrepFee;
                $order->stripe_fee = $stripeFee;
                $order->grandTotal = $order->amount - $goPrepFee - $stripeFee;
                $order->update();
            } catch (\Exception $e) {
                $this->info($order->id);
                $this->info($e);
            }
        }
    }
}
