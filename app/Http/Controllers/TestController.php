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
}
