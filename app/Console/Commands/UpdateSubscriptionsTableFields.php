<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Subscription;
use Illuminate\Support\Carbon;

class UpdateSubscriptionsTableFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:updateSubscriptionsTableFields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moving certain subscription attributes to database fields to reduce load time. Fields include: paid_order_count, next_delivery_date, and latest_unpaid_order_date.';

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
        $subscriptions = Subscription::where(
            'status',
            '!=',
            'cancelled'
        )->get();

        foreach ($subscriptions as $subscription) {
            try {
                // Paid order count
                $subscription->paid_order_count = $subscription->orders
                    ->where('paid', 1)
                    ->count();

                // Next delivery date
                $nextOrder = $subscription->orders->firstWhere(
                    'delivery_date',
                    '>=',
                    Carbon::now()
                );
                $subscription->next_delivery_date = $nextOrder->delivery_date;

                // Latest unpaid order date
                $subscription->latest_unpaid_order_date = $subscription
                    ->orders()
                    ->where('paid', 0)
                    ->latest()
                    ->pluck('delivery_date')
                    ->first();

                $subscription->update();
            } catch (\Exception $e) {
            }
        }
    }
}
