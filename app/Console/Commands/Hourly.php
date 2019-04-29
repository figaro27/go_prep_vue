<?php

namespace App\Console\Commands;

use App\Store;
use App\Subscription;
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
            $orders = $store->orders()->where('delivery_date', $date);
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
        $subs = Subscription::whereBetween(
            'next_renewal_at',
            $dateRange
        )->get();

        foreach ($subs as $sub) {
            $sub->user->sendNotification('subscription_renewing', $sub);
            $count++;
        }
        $this->info($count . ' `Meal Plan Renewing` notifications sent');
        $count = 0;
    }
}
