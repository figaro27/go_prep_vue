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
use App\Mail\RenewalFailed;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\MenuSession;
use App\StoreModule;

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

    protected $currentDay;

    protected $currentHour;

    protected $stores;

    protected $deliveryDays;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $currentDay = date('D');
        $this->currentDay = $currentDay;

        $currentHour = date('H');
        $this->currentHour = $currentHour;

        $stores = Store::with(['settings', 'details'])->get();
        $this->stores = $stores;

        $deliveryDays = DeliveryDay::all();
        $this->deliveryDays = $deliveryDays;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Send automated abandoned cart emails if module is turned on
        $this->sendAbandonedCartEmails();

        // Send email check-in on new store signups 24 hours after registration
        $this->sendSignupCheckins();

        // Renew subscriptions
        $this->renewSubscriptions();

        // Auto cancel failed subscription renewals after 48 hours
        $this->cancelFailedRenewedSubscriptions();

        // Updates the next_delivery_date column
        $this->updateSubscriptionNextDeliveryDates();

        // Disable / Enable delivery days
        $this->toggleDeliveryDays();

        // Disable / enable next week delivery days
        $this->togglePreventNextWeekOrders();

        // Close / open the store entirely
        $this->toggleStoreOpenClosed();

        // Send delivery today email notifications
        $this->sendDeliveryTodayEmails();

        // Send subscriptions renewing email notifications 24 hours before
        $this->sendSubscriptionRenewingEmails();

        // Send ready to print email notification
        $this->sendReadyToPrintEmails();

        // Send Delivery Today SMS if enabled
        $this->sendDeliveryTodaySMS();

        // Send Order Reminder SMS if enabled
        $this->sendSMSReminders();
    }

    public function sendAbandonedCartEmails()
    {
        $recentMenuSessions = MenuSession::where(
            'created_at',
            '>',
            Carbon::now()
                ->subHours(1)
                ->toDateTimeString()
        )->get();
        foreach ($recentMenuSessions as $recentMenuSession) {
            $module = StoreModule::where(
                'store_id',
                $recentMenuSession->store_id
            )->first();
            if (!$module->cartReminders) {
                return;
            }
            $recentOrder = Order::where('user_id', $recentMenuSession->user_id)
                ->where('paid', 1)
                ->orderBy('created_at', 'desc')
                ->first();

            if (
                $recentOrder &&
                $recentOrder->created_at > $recentMenuSession->created_at
            ) {
                $this->info('Order was created');
                return;
            }
            $data = [
                'email' => $recentMenuSession->user->email,
                'store_email' => $recentMenuSession->store->user->email,
                'user_name' => $recentMenuSession->user->details->firstname,
                'details' => $recentMenuSession->user->details,
                'store_name' => $recentMenuSession->store_name,
                'store_url' => $recentMenuSession->store->url,
                'store_id' => $recentMenuSession->store_id
            ];
            // Testing
            $this->info($recentMenuSession->id);
            if ($recentMenuSession->user->id === 36) {
                $recentMenuSession->user->sendNotification(
                    'abandoned_cart',
                    $data
                );
            }
        }
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
    }

    public function renewSubscriptions()
    {
        // Adjusting to EST (Server Time)
        $dateRange = [
            Carbon::now('utc')->subMinutes(30),
            Carbon::now('utc')->addMinutes(30)
        ];

        $subs = Subscription::whereBetween('next_renewal_at', $dateRange);
        $subs = $subs->where('status', '!=', 'cancelled')->get();

        foreach ($subs as $sub) {
            try {
                $sub->renew();
            } catch (\Exception $e) {
            }
        }
    }

    // public function renewFailedSubscriptions()
    // {
    //     // Attempts to renew failed subscriptions once every 6 hours for 2 days.
    //     // After 6 days of attempts it auto cancels the subscription.
    //     $subs = Subscription::where('status', '!=', 'cancelled')->where('failed_renewal', '!=', null)->get();
    //     foreach ($subs as $sub){
    //         $failedRenewalTimestamp = new Carbon($sub->failed_renewal);

    //         $this->info($sub->next_delivery_date);

    //         // Loop 4 times
    //         for ($i = 6; $i <= 24; $i += 6){
    //             $timestamp = $failedRenewalTimestamp->copy()->addHours($i)->minute(0)->second(0)->toDateTimeString();
    //             $now = Carbon::now('utc')->minute(0)->second(0)->toDateTimeString();
    //             if ($timestamp === $now){
    //                 $this->info('test');
    //             }
    //         }
    //     }
    // }

    public function cancelFailedRenewedSubscriptions()
    {
        // Auto cancels failed renewed subscriptions after 48 hours
        $subs = Subscription::where('status', '!=', 'cancelled')
            ->where('failed_renewal', '!=', null)
            ->get();
        foreach ($subs as $sub) {
            $failedRenewalTimestamp = new Carbon($sub->failed_renewal);
            $timestamp = $failedRenewalTimestamp
                ->copy()
                ->addHours(48)
                ->minute(0)
                ->second(0)
                ->toDateTimeString();
            $now = Carbon::now('utc')
                ->minute(0)
                ->second(0)
                ->toDateTimeString();
            if ($timestamp === $now) {
                $sub->cancel(true);
            }
        }
    }

    public function updateSubscriptionNextDeliveryDates()
    {
        $subs = Subscription::where('status', '!=', 'cancelled')->get();

        foreach ($subs as $sub) {
            try {
                $nextDeliveryDate = new Carbon($sub->next_delivery_date);
                if ($nextDeliveryDate->isPast()) {
                    $nextDeliveryDate->addWeeks($sub->intervalCount);
                    $sub->next_delivery_date = $nextDeliveryDate;
                    $sub->update();
                }
            } catch (\Exception $e) {
            }
        }
    }

    public function sendSignupCheckins()
    {
        // Send the email 24 hours after sign up
        $hourRange = [
            Carbon::now('utc')->subHours(24),
            Carbon::now('utc')->subHours(23)
        ];

        $stores = Store::whereBetween('created_at', $hourRange)->get();

        foreach ($stores as $store) {
            $store->sendNotification('signup_checkin', [
                'store' => $store
            ]);
        }
    }

    public function toggleDeliveryDays()
    {
        foreach ($this->deliveryDays as $deliveryDay) {
            if (
                $deliveryDay->disableDay == $this->currentDay &&
                $deliveryDay->disableHour == $this->currentHour
            ) {
                $deliveryDay->active = 0;
                $deliveryDay->update();
            }
            if (
                $deliveryDay->enableDay == $this->currentDay &&
                $deliveryDay->enableHour == $this->currentHour
            ) {
                $deliveryDay->active = 1;
                $deliveryDay->update();
            }
        }
    }

    public function togglePreventNextWeekOrders()
    {
        foreach ($this->stores as $store) {
            $settings = StoreSetting::where('store_id', $store->id)->first();

            if (
                $settings->enableNextWeekDay === $this->currentDay &&
                (int) $settings->enableNextWeekHour ===
                    (int) $this->currentHour &&
                $settings->preventNextWeekOrders === 1
            ) {
                $settings->preventNextWeekOrders = 0;
                $settings->update();
            }

            if (
                $settings->disableNextWeekDay === $this->currentDay &&
                (int) $settings->disableNextWeekHour ===
                    (int) $this->currentHour &&
                $settings->preventNextWeekOrders === 0
            ) {
                $settings->preventNextWeekOrders = 1;
                $settings->update();
            }
        }
    }

    public function toggleStoreOpenClosed()
    {
        foreach ($this->stores as $store) {
            try {
                $settings = StoreSetting::where(
                    'store_id',
                    $store->id
                )->first();

                if (
                    $settings->openStoreDay === $this->currentDay &&
                    (int) $settings->openStoreHour === (int) $this->currentHour
                ) {
                    $settings->open = 1;
                    $settings->update();
                }

                if (
                    $settings->closeStoreDay === $this->currentDay &&
                    (int) $settings->closeStoreHour === (int) $this->currentHour
                ) {
                    $settings->open = 0;
                    $settings->update();
                }
            } catch (\Exception $e) {
            }
        }
    }

    public function sendDeliveryTodayEmails()
    {
        // Non MDD orders
        $orders = Order::where([
            'delivery_date' => date('Y-m-d'),
            'paid' => 1,
            'voided' => 0,
            'isMultipleDelivery' => 0
        ])->get();

        // Adjust for timezone in Store Settings
        //$currentHour = date('H') - 4;
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

                    if (
                        $settings->notificationEnabled('delivery_today') &&
                        $currentHour === "08"
                    ) {
                        $order->user->sendNotification('delivery_today', [
                            'user' => $order->user,
                            'customer' => $order->customer,
                            'order' => $order,
                            'settings' => $order->store->settings
                        ]);
                    }
                }
            } catch (\Exception $e) {
            }
        }

        // MDD orders
        $orders = Order::where([
            'paid' => 1,
            'voided' => 0,
            'isMultipleDelivery' => 1
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

                    if (
                        $settings->notificationEnabled('delivery_today') &&
                        $currentHour === "08"
                    ) {
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
    }

    public function sendSubscriptionRenewingEmails()
    {
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
            ->where('renewalCount', '>', 0)
            ->get();

        foreach ($subs as $sub) {
            $settings = $sub->store->settings;
            if ($settings->notificationEnabled('subscription_renewing')) {
                $sub->user->sendNotification('subscription_renewing', [
                    'subscription' => $sub
                ]);

                $sub->save();
            }

            // Send SMS Subscription Renewal texts
            if ($sub->store->smsSettings->autoSendSubscriptionRenewal) {
                $sub->store->smsSettings->sendSubscriptionRenewalSMS($sub);
            }
        }
    }

    public function sendReadyToPrintEmails()
    {
        foreach ($this->stores as $store) {
            $date = $store->getNextDeliveryDate();
            $orders = $store
                ->orders()
                ->where('delivery_date', $date)
                ->get();
            $storeDetails = $store->details;
            if (count($orders) > 0 && $store->cutoffPassed('hour')) {
                if ($store->notificationEnabled('ready_to_print')) {
                    $store->sendNotification('ready_to_print', $storeDetails);
                }
            }
        }
    }

    public function sendDeliveryTodaySMS()
    {
        // Non MDD orders
        $orders = Order::where([
            'delivery_date' => date('Y-m-d'),
            'paid' => 1,
            'voided' => 0,
            'isMultipleDelivery' => 0
        ])->get();

        foreach ($orders as $order) {
            $smsSettings = $order->store->smsSettings;
            if ($smsSettings && $smsSettings->autoSendDelivery) {
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
                    }
                } catch (\Exception $e) {
                }
            }
        }

        // MDD orders
        $orders = Order::where([
            'paid' => 1,
            'voided' => 0,
            'isMultipleDelivery' => 1
        ])
            ->whereHas('meal_orders', function ($mealOrder) {
                $mealOrder->where('delivery_date', date('Y-m-d'));
            })
            ->get();

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
                    }
                } catch (\Exception $e) {
                }
            }
        }
    }
}
