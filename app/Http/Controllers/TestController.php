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

        /*$logo = $store->details->getMedia('logo')->first();
        $logo_b64 = '';
        
        var_dump($store->modules->emailBranding);
        exit();

        if ($logo) {
            $path = $logo->getPath();

            if (file_exists($path)) {
                $logo_b64 = \App\Utils\Images::encodeB64($path);
            }
        }

        exit($logo_b64);*/

        $store->sendNotification('new_order', [
            'order' => $order ?? null,
            'pickup' => 0,
            'card' => null,
            'customer' => $customer ?? null,
            'subscription' => null
        ]);

        /*$data = [
            'order' => $order ?? null,
            'pickup' => 0,
            'card' => null,
            'customer' => $customer ?? null,
            'subscription' => null,
            //'logo_b64' => $logo_b64
        ];

        $email = new NewOrder($data);
        Mail::to('jasoncoellox@gmail.com')->send($email);*/
    }
}
