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

class TestController extends Controller
{
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

    public function testRenewSubscription()
    {
        $sub = Subscription::where('id', 304)->first();
        $sub->renew();
    }
}
