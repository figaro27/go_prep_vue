<?php

namespace App\Console\Commands;

use App\Mail\Customer\DeliveryToday;
use App\Order;
use App\Store;
use App\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

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

        $stores = Store::with(['settings'])->get();
        $count = 0;

        foreach ($stores as $store) {
            if ($store->cutoffPassed()) {

                if ($store->notificationEnabled('ready_to_print')) {
                    $store->sendNotification('ready_to_print');
                    $count++;
                }
            }

        }

        $this->info($count . ' Ready to Print notifications sent');
    }
}
