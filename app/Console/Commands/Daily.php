<?php

namespace App\Console\Commands;

use App\Mail\Customer\DeliveryToday;
use App\Order;
use App\Store;
use App\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Facades\StorePlanService;
use Carbon\Carbon;
use App\SmsSetting;
use Illuminate\Support\Facades\Storage;

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
        $this->storePlanRenewals();

        $this->SMSPhoneRenewals();

        $this->deleteWeekOldStorage();

        // Moved to Hourly cron job so it can be sent at a certain time in the morning instead of midnight.

        // Orders
        // $orders = Order::where([
        //     'delivery_date' => date('Y-m-d'),
        //     'paid' => 1
        // ])->get();

        // $this->info(count($orders) . ' orders for delivery today');

        // foreach ($orders as $order) {
        //     try {
        //         if ($order->store->modules->hideTransferOptions === 0) {
        //             $order->user->sendNotification('delivery_today', [
        //                 'user' => $order->user,
        //                 'customer' => $order->customer,
        //                 'order' => $order,
        //                 'settings' => $order->store->settings
        //             ]);
        //         }
        //     } catch (\Exception $e) {
        //     }
        // }
    }

    protected function storePlanRenewals()
    {
        $plans = StorePlanService::getRenewingPlans();
        $this->info(count($plans) . ' store plans renewing today');

        foreach ($plans as $plan) {
            if ($plan->billing_method === 'connect') {
                dispatch(function () use ($plan) {
                    StorePlanService::renew($plan);
                });
            }
        }
    }

    protected function SMSPhoneRenewals()
    {
        $today = Carbon::now()->format('d');

        $stores = Store::all();

        foreach ($stores as $store) {
            $smsSettings = SmsSetting::where('store_id', $store->id)->first();

            if ($smsSettings->last_payment !== null) {
                $lastPayment = new Carbon($smsSettings->last_payment);
                $lastPaymentDay = $lastPayment->format('d');
                if ($today === $lastPaymentDay) {
                    $charge = \Stripe\Charge::create([
                        'amount' => 795,
                        'currency' => $store->settings->currency,
                        'source' => $store->settings->stripe_id,
                        'description' =>
                            'Monthly SMS phone number fee ' .
                            $store->storeDetail->name
                    ]);
                }
            }
        }
    }

    protected function deleteWeekOldStorage()
    {
        $files = Storage::files('/public');
        $lastWeek = Carbon::now()->subWeek();

        foreach ($files as $file) {
            $time = Storage::lastModified($file);
            $time = Carbon::createFromTimestamp($time);
            if ($time < $lastWeek) {
                // Storage::delete($file);
            }
        }
    }
}
