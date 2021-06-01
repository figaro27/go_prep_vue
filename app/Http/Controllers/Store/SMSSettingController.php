<?php

namespace App\Http\Controllers\Store;

use App\SmsSetting;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\StorePlan;

class SMSSettingController extends StoreController
{
    protected $baseURL = 'https://rest.textmagic.com/api/v2/numbers';
    protected $headers = [
        'X-TM-Username' => 'mikesoldano',
        'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    public function index()
    {
        return $this->store->smsSettings;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SmsSetting  $sMSSetting
     * @return \Illuminate\Http\Response
     */
    public function show(SMSSetting $sMSSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SmsSetting  $sMSSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(SMSSetting $sMSSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SmsSetting  $sMSSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $settings = $request->get('settings');
        $smsSettings = SMSSetting::where('store_id', $this->store->id)->first();
        $settings['autoSendOrderReminderHours'] =
            $settings['autoSendOrderReminderHours'] == null
                ? $smsSettings->autoSendOrderReminderHours
                : $settings['autoSendOrderReminderHours'];
        $settings['autoSendDeliveryTime'] =
            $settings['autoSendDeliveryTime'] == null
                ? $smsSettings->autoSendDeliveryTime
                : $settings['autoSendDeliveryTime'];
        $smsSettings->update($settings);
        return $smsSettings;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SmsSetting  $sMSSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(SMSSetting $sMSSetting)
    {
        //
    }

    public function findAvailableNumbers()
    {
        $phone = (int) preg_replace(
            '/[^0-9]/',
            '',
            $this->store->details->phone
        );
        if (strlen((string) $phone) === 10) {
            $phone = 1 . $phone;
        }
        $prefix = substr($phone, 0, 4);
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            'https://rest.textmagic.com/api/v2/numbers/available',
            [
                'headers' => $this->headers,
                'query' => [
                    'country' => 'US',
                    'prefix' => $prefix
                ]
            ]
        );
        $status = $res->getStatusCode();
        $body = $res->getBody();

        return $body;
    }

    public function smsAccountInfo()
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            'https://rest.textmagic.com/api/v2/user',
            [
                'headers' => $this->headers
            ]
        );
        $status = $res->getStatusCode();
        $body = $res->getBody();

        return $body;
    }

    public function buyNumber(Request $request)
    {
        $smsSettings = SMSSetting::where('store_id', $this->store->id)->first();
        if ($smsSettings->phone) {
            return response()->json(
                ['You have already purchased a phone number.'],
                400
            );
        }
        $phone = $request->get('phone');

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $this->baseURL, [
            'headers' => $this->headers,
            'form_params' => [
                'phone' => $phone,
                'country' => 'US',
                'userId' => 309951
            ]
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody();

        $smsSettings->phone = $phone;
        $smsSettings->textmagic_phone_id = json_decode($body)->id;

        // Standard Stripe accounts don't allow direct charges to the account. Have to make it a subscription instead.
        if ($this->store->settings->account_type === 'standard') {
            $stripeCustomerId = StorePlan::where('store_id', $this->store->id)
                ->pluck('stripe_customer_id')
                ->first();

            $smsPlan = collect(config('plans')['smsNumber']);

            $subscription = \Stripe\Subscription::create([
                'customer' => $stripeCustomerId,
                'items' => [['plan' => $smsPlan->get('stripe_id')]]
            ]);
            $smsSettings->stripe_subscription_id = $subscription['id'];
            $smsSettings->update();
            return $subscription;
        } else {
            // Charge the first $7.95 and record payment date.
            $charge = \Stripe\Charge::create([
                'amount' => 795,
                'currency' => $this->store->settings->currency,
                'source' => $this->store->settings->stripe_id,
                'description' =>
                    'Monthly SMS phone number fee ' .
                    $this->store->storeDetail->name
            ]);
        }
        $smsSettings->last_payment = Carbon::now();
        $smsSettings->update();
    }
}
