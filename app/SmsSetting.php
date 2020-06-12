<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SmsList;
use App\SMSContact;
use stdClass;
use Carbon\Carbon;
use App\Order;

class SmsSetting extends Model
{
    protected $headers = [
        'X-TM-Username' => 'mikesoldano',
        'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    protected $casts = [
        'autoAddCustomers' => 'boolean',
        'autoSendDelivery' => 'boolean',
        'autoSendOrderConfirmation' => 'boolean'
    ];

    protected $guarded = ['id'];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function addNewCustomerToContacts($customer)
    {
        $contact = $customer;

        $listId = SmsList::where('store_id', $this->store->id)
            ->pluck('list_id')
            ->first();

        $phone = (int) preg_replace('/[^0-9]/', '', $contact['phone']);
        if (strlen((string) $phone) === 10) {
            $phone = 1 . $phone;
        }

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://rest.textmagic.com/api/v2/contacts',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'phone' => $phone,
                        'lists' => $listId,
                        'firstName' => $contact['firstname'],
                        'lastName' => $contact['lastname']
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();
            $smsContactId = json_decode($body)->id;

            $smsContact = new SmsContact();
            $smsContact->store_id = $this->store->id;
            $smsContact->contact_id = $smsContactId;
            $smsContact->save();
        } catch (\Exception $e) {
        }
    }

    public function sendOrderReminderSMS($store)
    {
        $list = SmsList::where('store_id', $store->id)
            ->pluck('list_id')
            ->first();

        $nextDeliveryDate = $store->getNextDeliveryDate();
        $cutoffDay = $store->settings
            ->getCutoffDate($nextDeliveryDate)
            ->format('l');
        $now = Carbon::now();
        $today = $now->format('l');
        if ($cutoffDay === $today) {
            $cutoffDay === 'Today';
        }

        $cutoffTime = $store->settings
            ->getCutoffDate($nextDeliveryDate)
            ->format('g a');
        $storeUrl = 'http://' . $store->details->domain . '.goprep.com';

        $message =
            'Last chance to order for ' .
            $nextDeliveryDate->format('l, F jS') .
            '. Our cutoff time is ' .
            $cutoffDay .
            ' at ' .
            $cutoffTime .
            '. Please order at ' .
            $storeUrl;

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://rest.textmagic.com/api/v2/messages',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'lists' => $list,
                        'text' => $message
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();

            $contacts = SMSContact::where('store_id', $store->id)
                ->get()
                ->count();
            $this->balance += $contacts * 0.05;
            $this->update();

            if ($this->balance >= 0.5) {
                $charge = \Stripe\Charge::create([
                    'amount' => round($this->balance * 100),
                    'currency' => $store->settings->currency,
                    'source' => $store->settings->stripe_id,
                    'description' =>
                        'SMS fee balance for ' . $store->storeDetail->name
                ]);
                $this->balance = 0;
                $this->update();
            }

            return $body;
        } catch (\Exception $e) {
        }
    }

    public function sendOrderConfirmationSMS($customer, $order)
    {
        $deliveryText = $order['pickup']
            ? 'are available for pickup on '
            : 'will be delivered on ';
        $deliveryDate = new Carbon($order['delivery_date']);
        $deliveryDate = $deliveryDate->format('l, m/d');

        $message =
            'Thank you for your order ' .
            $customer['firstname'] .
            '. Your items ' .
            $deliveryText .
            $deliveryDate;

        $phone = (int) preg_replace('/[^0-9]/', '', $customer['phone']);
        if (strlen((string) $phone) === 10) {
            $phone = 1 . $phone;
        }

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://rest.textmagic.com/api/v2/messages',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'phones' => $phone,
                        'text' => $message
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();

            $store = $this->store;
            $this->balance += 0.05;
            $this->update();
            if ($this->balance >= 0.5) {
                $charge = \Stripe\Charge::create([
                    'amount' => round($this->balance * 100),
                    'currency' => $store->settings->currency,
                    'source' => $store->settings->stripe_id,
                    'description' =>
                        'SMS fee balance for ' . $store->storeDetail->name
                ]);
                $this->balance = 0;
                $this->update();
            }

            return $body;
        } catch (\Exception $e) {
        }
    }

    public function sendDeliverySMS($order)
    {
        $deliveryText = $order['pickup']
            ? ' is available for pickup today.'
            : ' will be delivered to you today.';
        $storeName = $order->store->details->name;
        $customer = $order->customer;

        $message = 'Your order from ' . $storeName . $deliveryText;

        $phone = (int) preg_replace('/[^0-9]/', '', $customer['phone']);
        if (strlen((string) $phone) === 10) {
            $phone = 1 . $phone;
        }

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://rest.textmagic.com/api/v2/messages',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'phones' => $phone,
                        'text' => $message
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();

            $store = $this->store;
            $this->balance += 0.05;
            $this->update();
            if ($this->balance >= 0.5) {
                $charge = \Stripe\Charge::create([
                    'amount' => round($this->balance * 100),
                    'currency' => $store->settings->currency,
                    'source' => $store->settings->stripe_id,
                    'description' =>
                        'SMS fee balance for ' . $store->storeDetail->name
                ]);
                $this->balance = 0;
                $this->update();
            }

            return $body;
        } catch (\Exception $e) {
        }
    }
}
