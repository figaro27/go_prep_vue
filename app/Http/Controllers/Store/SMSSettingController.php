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
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            'https://rest.textmagic.com/api/v2/numbers/available/US/',
            [
                'headers' => $this->headers
            ]
        );
        $status = $res->getStatusCode();
        $body = $res->getBody();

        return $body;
    }
}
