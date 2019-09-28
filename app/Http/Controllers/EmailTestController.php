<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Store\CancelledSubscription;
use App\Mail\Store\ReadyToPrint;
use App\Mail\Customer\DeliveryToday;
use App\Mail\Customer\MealPLan;
use App\Mail\Customer\SubscriptionRenewing;
use App\Mail\Customer\NewOrder;
use App\Mail\Customer\MealPlanPaused;
use App\Mail\Store\NewSubscription;
use App\Store;
use App\Customer;
use App\Card;
use App\StoreDetail;
use App\Order;
use App\Subscription;
use App\StoreSetting;

class EmailTestController extends Controller
{
    public function storeCancelledSubscription()
    {
        $subscription = Subscription::first();
        $customer = Customer::first();
        $email = new CancelledSubscription([
            'subscription' => $subscription,
            'customer' => $customer
        ]);
        Mail::to('store@goprep.com')->send($email);
    }

    public function storeReadyToPrint()
    {
        $store = Store::first();
        $storeDetails = StoreDetail::first();
        $email = new ReadyToPrint([
            'store' => $store,
            'storeDetails' => $storeDetails
        ]);
        Mail::to('store@goprep.com')->send($email);
    }

    public function customerDeliveryToday()
    {
        $orders = Order::where([
            'delivery_date' => date('Y-m-d'),
            'paid' => 1
        ])->get();

        // Adjust for timezone in Store Settings
        $currentHour = date('H') - 4;

        foreach ($orders as $order) {
            try {
                if (!$order->store->modules->hideTransferOptions) {
                    if ($currentHour === 6) {
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

    public function customerMealPlan()
    {
        $customer = Customer::first();
        $subscription = Subscription::first();
        $order = Order::orderBy('created_at', 'desc')->first();
        $email = new MealPlan([
            'subscription' => $subscription,
            'customer' => $subscription,
            'order' => $order
        ]);
        Mail::to('customer@goprep.com')->send($email);
    }

    public function customerSubscriptionRenewing()
    {
        $customer = Customer::first();
        $subscription = Subscription::orderBy('created_at', 'desc')->first();
        $email = new SubscriptionRenewing([
            'customer' => $customer,
            'subscription' => $subscription
        ]);
        Mail::to('customer@goprep.com')->send($email);
    }

    public function storeNewSubscription()
    {
        $customer = Customer::first();
        $subscription = Subscription::first();
        $email = new NewSubscription([
            'customer' => $customer,
            'subscription' => $subscription
        ]);
        Mail::to('store@goprep.com')->send($email);
    }

    public function customerNewOrder()
    {
        $customer = Customer::first();
        $order = Order::first();
        $email = new NewOrder([
            'customer' => $customer,
            'order' => $order
        ]);
        Mail::to('customer@goprep.com')->send($email);
    }

    public function customerMealPlanPaused()
    {
        $customer = Customer::first();
        $subscription = Subscription::first();
        $email = new MealPlanPaused([
            'customer' => $customer,
            'subscription' => $subscription
        ]);
        Mail::to('customer@goprep.com')->send($email);
    }

    public function storeNewOrder()
    {
        $customer = Customer::first();
        $order = Order::first();
        $email = new NewOrder([
            'customer' => $customer,
            'order' => $order
        ]);
        Mail::to('store@goprep.com')->send($email);
    }
}
