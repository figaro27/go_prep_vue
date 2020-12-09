<?php

namespace App\Console\Commands;

use App\Store;
use App\Subscription;
use App\Order;
use App\StoreSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\SmsSetting;
use App\DeliveryDay;

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
        // Renew subscriptions
        $this->renewSubscriptions();

        // Store reports
        $currentDay = date('D');
        $currentHour = date('H');

        $stores = Store::with(['settings', 'details'])->get();
        $deliveryDays = DeliveryDay::all();

        foreach ($deliveryDays as $deliveryDay) {
            if (
                $deliveryDay->disableDay == $currentDay &&
                $deliveryDay->disableHour == $currentHour
            ) {
                $deliveryDay->active = 0;
                $deliveryDay->update();
            }
            if (
                $deliveryDay->enableDay == $currentDay &&
                $deliveryDay->enableHour == $currentHour
            ) {
                $deliveryDay->active = 1;
                $deliveryDay->update();
            }
        }

        $count = 0;

        foreach ($stores as $store) {
            $settings = StoreSetting::where('store_id', $store->id)->first();

            if (
                $settings->enableNextWeekDay === $currentDay &&
                $settings->enableNextWeekHour === $currentHour &&
                $settings->preventNextWeekOrders === 1
            ) {
                $settings->preventNextWeekOrders = 0;
                $settings->update();
            }

            if (
                $settings->disableNextWeekDay === $currentDay &&
                $settings->disableNextWeekHour === $currentHour &&
                $settings->preventNextWeekOrders === 0
            ) {
                $settings->preventNextWeekOrders = 1;
                $settings->update();
            }

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
            // $sub->updated = 0;
            $sub->save();

            // Send SMS Subscription Renewal texts
            if ($sub->store->smsSettings->autoSendSubscriptionRenewal) {
                $sub->store->smsSettings->sendSubscriptionRenewalSMS($sub);
            }
        }
        $this->info($count . ' `Subscription Renewing` notifications sent');
        $count = 0;

        // Delivery Today Emails

        $orders = Order::where([
            'delivery_date' => date('Y-m-d'),
            'paid' => 1,
            'voided' => 0
        ])->get();

        // Adjust for timezone in Store Settings
        //$currentHour = date('H') - 4;
        $count = 0;
        foreach ($orders as $order) {
            try {
                if (
                    !$order->store->modules->hideTransferOptions &&
                    !$order->store->modules->multipleDeliveryDays
                ) {
                    /* Timezone */
                    $settings = $order->store->settings;
                    if ($settings && $settings->timezone) {
                        $timezone = $settings->timezone;
                        date_default_timezone_set($timezone);
                    }
                    /* Timezone Set */

                    $currentHour = date('H');

                    if ($currentHour === "08") {
                        $order->user->sendNotification('delivery_today', [
                            'user' => $order->user,
                            'customer' => $order->customer,
                            'order' => $order,
                            'settings' => $order->store->settings
                        ]);
                        $count++;
                    }
                }
            } catch (\Exception $e) {
            }
        }

        $this->info($count . ' `Delivery Today` emails sent');

        // Multiple delivery day Delivery Today emails

        $orders = Order::where([
            'paid' => 1,
            'voided' => 0
        ])
            ->whereHas('meal_orders', function ($mealOrder) {
                $mealOrder->where('delivery_date', date('Y-m-d'));
            })
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            try {
                if ($order->store->modules->multipleDeliveryDays) {
                    /* Timezone */
                    $settings = $order->store->settings;
                    if ($settings && $settings->timezone) {
                        $timezone = $settings->timezone;
                        date_default_timezone_set($timezone);
                    }
                    /* Timezone Set */

                    $currentHour = date('H');

                    if ($currentHour === "08") {
                        $order->user->sendNotification('delivery_today', [
                            'user' => $order->user,
                            'customer' => $order->customer,
                            'order' => $order,
                            'settings' => $order->store->settings
                        ]);
                        $count++;
                    }
                }
            } catch (\Exception $e) {
            }
        }

        $this->info($count . ' Multiple delivery `Delivery Today` emails sent');

        // Send Delivery Today SMS if enabled
        $count = 0;
        foreach ($orders as $order) {
            $smsSettings = $order->store->smsSettings;
            if ($smsSettings->autoSendDelivery) {
                try {
                    /* Timezone */
                    $settings = $order->store->settings;
                    if ($settings && $settings->timezone) {
                        $timezone = $settings->timezone;
                        date_default_timezone_set($timezone);
                    }
                    /* Timezone Set */

                    $currentHour = (int) date('H');
                    if ($currentHour === $smsSettings->autoSendDeliveryTime) {
                        $smsSettings->sendDeliverySMS($order);
                        $count++;
                    }
                } catch (\Exception $e) {
                }
            }
        }
        $this->info($count . ' `Delivery Today` SMS texts sent');

        // Send Order Reminder SMS if enabled
        $this->sendSMSReminders();
    }

    public function sendSMSReminders()
    {
        $stores = Store::with(['settings', 'details'])->get();
        $count = 0;
        foreach ($stores as $store) {
            $smsSettings = SmsSetting::where('store_id', $store->id)->first();
            if ($smsSettings && $smsSettings->autoSendOrderReminder) {
                $reminderHours = $smsSettings->autoSendOrderReminderHours;

                $diff = Carbon::now()->diffInhours($smsSettings->nextCutoff);

                if ($reminderHours === $diff) {
                    $smsSettings->sendOrderReminderSMS($store);
                    $count++;
                }
            }
        }
        $this->info($count . ' `Order Reminder` SMS texts sent');
    }

    public function renewSubscriptions()
    {
        $dateRange = [
            Carbon::now('utc')
                ->subMinutes(30)
                ->toDateTimeString(),
            Carbon::now('utc')
                ->addMinutes(30)
                ->toDateTimeString()
        ];

        $subs = Subscription::whereBetween('next_renewal_at', $dateRange)
            ->where('status', 'active')
            ->orWhere('status', 'paused')
            ->get();

        $count = 0;
        foreach ($subs as $sub) {
            $sub->renew();
            $count++;
        }
        $this->info($count . ' Subscriptions renewed');
    }
}
