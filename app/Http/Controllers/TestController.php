<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Mail\Store\NewOrder;
use Illuminate\Support\Facades\Mail;
use App\Order;
use App\User;
use App\Store;
use App\Exportable\Store\MealOrders;
use App\Exportable\Store\PackingSlips;
use App\Subscription;
use Illuminate\Support\Carbon;
use App\MealOrder;
use App\MealOrderComponent;
use App\MealOrderAddon;
use App\Bag;
use App\SmsContact;
use App\SmsChat;

class TestController extends Controller
{
    protected $baseURL = 'https://rest.textmagic.com/api/v2/chats';
    protected $headers = [
        'X-TM-Username' => 'mikesoldano',
        'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    public function test_mail()
    {
        echo "We are testing emails here...<br/>";
        $user = User::find(31);
        $storeId = 1;
        $store = Store::with(['settings', 'storeDetail'])->findOrFail($storeId);
        $customer = $user->getStoreCustomer($store->id, false);
        $order = Order::find(1);

        $store->sendNotification('new_order', [
            'order' => $order ?? null,
            'pickup' => 0,
            'card' => null,
            'customer' => $customer ?? null,
            'subscription' => null
        ]);

        /*$user->sendNotification('new_order', [
            'order' => $order ?? null,
            'pickup' => 0,
            'card' => null,
            'customer' => $customer ?? null,
            'subscription' => null
        ]);*/
    }

    public function test_print()
    {
        $storeId = 1;
        $store = Store::with(['settings', 'storeDetail'])->findOrFail($storeId);

        $format = 'pdf';

        $data = [
            'group_by_date' => false,
            'delivery_dates' =>
                '{"from":"2019-09-25T00:00:00.000Z","to":"2019-10-25T23:59:59.999Z"}',
            'order_id' => 2
        ];

        $params = collect($data);

        $exportable = new MealOrders($store, $params);
        //$exportable = new PackingSlips($store, $params);
        $url = $exportable->export($format);

        exit($url);
    }

    public function testDeleteMealOrders()
    {
        $sub = Subscription::where('id', 318)->first();

        $items = $sub->meal_subscriptions;

        $store = $sub->store;

        $bag = new Bag($items, $store);

        return $bag->getItems();

        $items = $sub->meal_subscriptions->map(function ($meal) {
            $price = $meal->meal_size
                ? $meal->meal_size->price
                : $meal->meal->price;
            foreach ($meal->components as $component) {
                $price += $component->option->price;
            }
            foreach ($meal->addons as $addon) {
                $price += $addon->addon->price;
            }
            return [
                'quantity' => $meal->quantity,
                'meal' => $meal->meal,
                'price' => $price
            ];
        });

        return $items;

        $store = $sub->store;

        $bag = new Bag($items, $store);

        // return $bag->getItems();

        $total = $bag->getTotal();

        return $total;

        $items = $sub->meal_subscriptions;

        $store = $sub->store;

        $bag = new Bag($items, $store);

        return $bag->getItems();

        foreach ($bag->getItems() as $item) {
            if (isset($item['components']) && $item['components']) {
                foreach ($item['components'] as $component) {
                }
            }

            if (isset($item['addons']) && $item['addons']) {
                foreach ($item['addons'] as $addon) {
                    MealOrderAddon::create([
                        'meal_order_id' => $mealOrder->id,
                        'meal_addon_id' => $addon->id
                    ]);
                }
            }
        }

        // Update future orders IF cutoff hasn't passed yet

        $futureOrders = $sub
            ->orders()
            ->where([['fulfilled', 0], ['paid', 0]])
            ->whereDate('delivery_date', '>=', Carbon::now())
            ->get();

        foreach ($futureOrders as $order) {
            // Cutoff already passed. Missed your chance bud!
            if ($order->cutoff_passed) {
                continue;
            }

            // Update order pricing
            // $order->preFeePreDiscount = $preFeePreDiscount;
            // $order->mealPlanDiscount = $mealPlanDiscount;
            // $order->afterDiscountBeforeFees = $afterDiscountBeforeFees;
            // $order->processingFee = $processingFee;
            // $order->deliveryFee = $deliveryFee;
            // $order->salesTax = $salesTax;
            // $order->amount = $total;
            // $order->save();

            // Replace order meals
            $mealOrders = MealOrder::where('order_id', $order->id)->get();

            foreach ($mealOrders as $mealOrder) {
                foreach ($mealOrder->components as $component) {
                    $component->delete();
                }
                foreach ($mealOrder->addons as $addon) {
                    $addon->delete();
                }
                $mealOrder->delete();
            }

            foreach ($bag->getItems() as $item) {
                $mealOrder = new MealOrder();
                $mealOrder->order_id = $order->id;
                $mealOrder->store_id = 4;
                $mealOrder->meal_id = $item['meal']['id'];
                $mealOrder->price = $item['price'] * $item['quantity'];
                $mealOrder->quantity = $item['quantity'];
                $mealOrder->save();

                if (isset($item['components']) && $item['components']) {
                    foreach ($item['components'] as $componentId => $choices) {
                        foreach ($choices as $optionId) {
                            MealOrderComponent::create([
                                'meal_order_id' => $mealOrder->id,
                                'meal_component_id' => $componentId,
                                'meal_component_option_id' => $optionId
                            ]);
                        }
                    }
                }

                if (isset($item['addons']) && $item['addons']) {
                    foreach ($item['addons'] as $addonId) {
                        MealOrderAddon::create([
                            'meal_order_id' => $mealOrder->id,
                            'meal_addon_id' => $addonId
                        ]);
                    }
                }
            }

            // foreach ($bag->getItems() as $item) {
            //     $mealOrder = new MealOrder();
            //     $mealOrder->order_id = $order->id;
            //     $mealOrder->store_id = $this->store->id;
            //     $mealOrder->meal_id = $item['meal']['id'];
            //     $mealOrder->quantity = $item['quantity'];
            //     $mealOrder->save();
            // }
        }
    }

    public function testRenewSubscription(Request $request)
    {
        $sub = Subscription::where(
            'stripe_id',
            $request->get('stripe_id')
        )->first();
        $sub->renewTest();
    }

    public function testChargeDescriptor()
    {
        // $charge = \Stripe\Charge::create([
        //     'amount' => round(29 * 100),
        //     'currency' => 'USD',
        //     'source' => 'acct_1Gq1RZCEihBkmCwH',
        //     'description' => 'Mouseflow fee for GoEatFresh'
        // ]);
        // return $charge;
        // \Stripe\Stripe::setApiKey('sk_live_w371HmpG4A0x4xG1E3FMYgdr00IjD6SplL');
        // $test = \Stripe\Account::update('acct_1GOv9dLVLsZe90Mo', [
        //     'settings' => [
        //         'payments' => [
        //             'statement_descriptor' => 'Under the Ground BloNo'
        //         ]
        //     ]
        // ]);
        // return $test;
    }

    public function testSMS()
    {
        return [];
    }

    public function testIncomingSMS()
    {
        // $phone = $request->get('sender');
        $phone = '13475269628';
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            $this->baseURL . '/' . $phone . '/by/phone',
            [
                'headers' => $this->headers
            ]
        );
        $status = $res->getStatusCode();
        $body = $res->getBody();
        $chatId = json_decode($body)->id;

        $chat = SmsChat::where('chat_id', $chatId)->first();

        if ($chat) {
            // Update existing chat
            $chat->unread = 1;
            // $chat->updatedAt = $request->get('messageTime');
            $chat->updatedAt = '2020-06-12T01:04:48+0000';
            $chat->update();
        } else {
            // Get store ID from looking up the contact by their phone number
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'GET',
                'https://rest.textmagic.com/api/v2/contacts/phone/' . $phone,
                [
                    'headers' => $this->headers
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();
            $contactId = json_decode($body)->id;
            $storeId = SmsContact::where('contact_id', $contactId)
                ->pluck('store_id')
                ->first();

            // Add new chat
            $chat = new SmsChat();
            $chat->store_id = $storeId;
            $chat->chat_id = $chatId;
            $chat->unread = 1;
            // $chat->updatedAt = $request->get('messageTime');
            $chat->updatedAt = '2020-06-10T01:04:48+0000';
            $chat->save();
        }
    }

    public function changeSubscriptionAnchor(Request $request)
    {
        $stripeId = $request->get('stripe_id');
        $timestamp = $request->get('unixTimestamp');
        $storeId = $request->get('store_id');

        $store = Store::where('id', $storeId)->first();

        \Stripe\Subscription::update(
            'sub_' . $stripeId,
            [
                'trial_end' => $timestamp,
                'proration_behavior' => 'none'
            ],
            [
                'stripe_account' => $store->settings->stripe_id
            ]
        );
    }
}
