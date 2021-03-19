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
use App\StorePlan;
use App\StoreSetting;

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
        $this->updateStorePlans();

        $this->storePlanRenewals();

        $this->SMSPhoneRenewals();

        $this->deleteWeekOldStorage();

        $this->closeCancelledStores();
    }

    protected function updateStorePlans()
    {
        $today = Carbon::now()->format('d');
        $lastDayOfMonth = new Carbon('last day of this month');
        $lastDayOfMonth = $lastDayOfMonth->format('d');

        $now = Carbon::now();
        $firstDayOfMonth = $now->firstOfMonth()->toDateTimeString();

        if ($now === $lastDayOfMonth) {
            $storePlans = StorePlan::with('store')->get();
            foreach ($storePlans as $storePlan) {
                $ordersCount = $storePlan->store->orders
                    ->where('paid_at', '>=', $firstDayOfMonth)
                    ->count();
                $storePlan->last_month_total_orders = $ordersCount;
                $storePlan->months_over_limit +=
                    $storePlan->plan_name !== 'pay-as-you-go' &&
                    $ordersCount > $storePlan->allowed_orders
                        ? 1
                        : 0;
                $storePlan->update();

                if ($storePlan->months_over_limit >= 2) {
                    // Automatically upgrade to the next plan ?
                }
            }
        }
    }

    protected function storePlanRenewals()
    {
        $plans = StorePlanService::getRenewingPlans();
        $this->info(count($plans) . ' store plans renewing today');

        foreach ($plans as $plan) {
            if ($plan->method === 'connect') {
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
                Storage::delete($file);
            }
        }
    }

    protected function closeCancelledStores()
    {
        $stores = Store::all();
        foreach ($stores as $store) {
            $storePlan = StorePlan::where('store_id', $store->id)->first();
            $storeSettings = StoreSetting::where('id', $store->id)->first();
            if (
                $storePlan &&
                $storePlan->status === 'cancelled' &&
                $storeSettings->open
            ) {
                $dayOfMonth = Carbon::now()->format('d');
                if (
                    (int) $dayOfMonth === (int) $storePlan->day &&
                    Carbon::now()->startOfDay() > $storePlan->cancelled_at
                ) {
                    $storeSettings->open = false;
                    $storeSettings->update();
                }
            }
        }
    }
}
