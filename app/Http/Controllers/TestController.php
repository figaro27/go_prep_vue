<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Mail\Store\NewOrder;
use Illuminate\Support\Facades\Mail;
use App\Order;
use App\User;
use App\Store;
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

        /*$store->sendNotification('new_order', [
            'order' => $order ?? null,
            'pickup' => 0,
            'card' => null,
            'customer' => $customer ?? null,
            'subscription' => null
        ]);*/

        $user->sendNotification('new_order', [
            'order' => $order ?? null,
            'pickup' => 0,
            'card' => null,
            'customer' => $customer ?? null,
            'subscription' => null
        ]);
    }
}
