<?php

namespace App\Console\Commands;

use App\Mail\Customer\DeliveryToday;
use App\Order;
use App\Store;
use App\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class Daily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs at midnight daily';

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
        // Orders

        $orders = Order::where([
            'delivery_date' => date('Y-m-d'),
            'paid' => 1
        ])->get();

        $this->info(count($orders) . ' orders for delivery today');

        foreach ($orders as $order) {
            try {
                // Send notification
                $email = new DeliveryToday([
                    'user' => $order->user,
                    'customer' => $order->customer,
                    'order' => $order,
                    'settings' => $order->store->settings
                ]);
                Mail::to($order->user)->send($email);
            } catch (\Exception $e) {
                // Should not be fatal
            }
        }
    }
}
