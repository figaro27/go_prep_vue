<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Customer;
use App\Promotion;

class updateCustomerPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:updateCustomerPoints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrects the customer promotion points due to an error where points would be added on every meal replacement.';

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
        $promotions = Promotion::where('promotionType', 'points')->get();

        foreach ($promotions as $promotion) {
            try {
                $store = $promotion->store;
                foreach ($store->customers as $customer) {
                    $customer->points = 0;
                    foreach ($customer->orders as $order) {
                        if ($order->paid === 1) {
                            $customer->points +=
                                $order->preFeePreDiscount *
                                $promotion->promotionAmount;
                            $customer->points -= $order->pointsReduction
                                ? $order->pointsReduction * 100
                                : 0;
                            if ($customer->points < 0) {
                                $customer->points = 0;
                            }
                        }
                    }
                    $customer->update();
                }
            } catch (\Exception $e) {
            }
        }
    }
}
