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
use App\ReportRecord;
use App\Mail\Store\StoresOverLimit;

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

        $this->resetNutritionix();
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
            $storesOverLimit = [];
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
                    $data = [
                        'store_name' => $storePlan->store_name,
                        'contact_name' => $storePlan->contact_name,
                        'contact_email' => $storePlan->contact_email,
                        'contact_phone' => $storePlan->contact_phone,
                        'allowed_orders' => $storePlan->allowed_orders,
                        'last_month_total_orders' =>
                            $storePlan->last_month_total_orders,
                        'months_over_limit' => $storePlan->months_over_limit,
                        'joined_store_ids' => $storePlan->joined_store_ids,
                        'plan_name' => $storePlan->plan_name,
                        'plan_notes' => $storePlan->plan_notes,
                        'amount' => $storePlan->amount,
                        'period' => $storePlan->period
                    ];
                    $storesOverLimit[] = $data;
                }
            }
            if (count($storesOverLimit) > 0) {
                $email = new StoresOverLimit([
                    'stores' => $storesOverLimit
                ]);
                try {
                    Mail::to('danny@goprep.com')
                        ->bcc('mike@goprep.com')
                        ->send($email);
                } catch (\Exception $e) {
                }
            }
        }
    }

    protected function storePlanRenewals()
    {
        $plans = StorePlanService::getRenewingPlans();
        $this->info(count($plans) . ' store plans renewing today');

        foreach ($plans as $plan) {
            if ($plan->method === 'connect' && $plan->amount > 0) {
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
            $storeSettings = StoreSetting::where(
                'store_id',
                $store->id
            )->first();
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

    protected function resetNutritionix()
    {
        ReportRecord::query()->update(['daily_nutritionix_calls' => 0]);

        $path = base_path('.env');
        if (file_exists($path)) {
            file_put_contents(
                $path,
                str_replace(
                    'NUTRITIONIX_ID=1cb5966f',
                    'NUTRITIONIX_ID=d9ff48c3',
                    file_get_contents($path)
                )
            );
            file_put_contents(
                $path,
                str_replace(
                    'NUTRITIONIX_KEY=95534f28cd549764a32c5363f13699a9',
                    'NUTRITIONIX_KEY=0ba7e0ffd7b498eaa01035b05fa8717f',
                    file_get_contents($path)
                )
            );
        }
    }
}
