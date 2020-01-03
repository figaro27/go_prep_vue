<?php

namespace App\Console\Commands;

use App\Store;
use App\Subscription;
use App\Order;
use App\StoreSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class Hourly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs hourly';

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
        // Store reports

        $stores = Store::with(['settings', 'details'])->get();
        $count = 0;

        foreach ($stores as $store) {
            $date = $store->getNextDeliveryDate();
            $orders = $store
                ->orders()
                ->where('delivery_date', $date)
                ->get();
            $storeDetails = $store->details;
            if (count($orders) > 0 && $store->cutoffPassed('hour')) {
                if ($store->notificationEnabled('ready_to_print')) {
                    $store->sendNotification('ready_to_print', $storeDetails);
                    $count++;
                }

                // Get subscription orders
                $subOrders = $store
                    ->orders()
                    ->with('subscription')
                    ->where([
                        ['subscription_id', 'NOT', null],
                        ['delivery_date', $date]
                    ])
                    ->whereHas('subscription', function ($query) {
                        $query->where('status', 'active');
                    })
                    ->get()
                    ->map(function ($order) {
                        return $order->subscription;
                    });
            }

            $currentDay = date('D');
            $currentHour = date('H');
            $settings = $store->settings;

            if (
                $settings->disableNextWeekDay === $currentDay &&
                $settings->disableNextWeekHour === $currentHour
            ) {
                // $store->disableNextWeekOrders();
                // Testing
                $settings = StoreSetting::where(
                    'store_id',
                    $store->id
                )->first();
                $settings->preventNextWeekOrders = 1;
                $settings->update();
            }
            if (
                $settings->enableNextWeekDay === $currentDay &&
                $settings->enableNextWeekHour === $currentHour
            ) {
                $store->enableNextWeekOrders();
            }
        }
        $this->info($count . ' `Ready to Print` notifications sent');
        $count = 0;

        // Subscriptions about to renew
        $dateRange = [
            Carbon::now('utc')
                ->addDays(1)
                ->subMinutes(30)
                ->toDateTimeString(),
            Carbon::now('utc')
                ->addDays(1)
                ->addMinutes(30)
                ->toDateTimeString()
        ];
        $subs = Subscription::whereBetween('next_renewal_at', $dateRange)
            ->where('status', 'active')
            ->get();

        foreach ($subs as $sub) {
            $sub->user->sendNotification('subscription_renewing', [
                'subscription' => $sub
            ]);
            $count++;
        }
        $this->info($count . ' `Subscription Renewing` notifications sent');
        $count = 0;

        // Delivery Today Emails

        $orders = Order::where([
            'delivery_date' => date('Y-m-d'),
            'paid' => 1
        ])->get();

        // Adjust for timezone in Store Settings
        //$currentHour = date('H') - 4;

        foreach ($orders as $order) {
            try {
                if (!$order->store->modules->hideTransferOptions) {
                    /* Timezone */
                    $settings = $order->store->settings;
                    if ($settings && $settings->timezone) {
                        $timezone = $settings->timezone;
                        date_default_timezone_set($timezone);
                    }
                    /* Timezone Set */

                    $currentHour = date('H');

                    if ($currentHour === 10) {
                        if ($order->store->modules->hideTransferOptions === 0) {
                            $order->user->sendNotification('delivery_today', [
                                'user' => $order->user,
                                'customer' => $order->customer,
                                'order' => $order,
                                'settings' => $order->store->settings
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        }
    }
}
