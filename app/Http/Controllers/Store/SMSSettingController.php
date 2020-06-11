<?php

namespace App\Http\Controllers\Store;

use App\SmsSetting;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

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
        return $this->store->smsSettings();
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

    public function buyNumber(Request $request)
    {
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

        $smsSettings = SMSSetting::where('store_id', $this->store->id)->first();
        $smsSettings->phone = $phone;
        $smsSettings->update();
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
}
