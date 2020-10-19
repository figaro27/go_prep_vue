<?php

namespace App\Http\Controllers\Store;

use App\DeliveryFeeZipCode;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\StoreSetting;

class DeliveryFeeZipCodeController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->deliveryFeeZipCodes;
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
     * @param  \App\DeliveryFeeZipCode  $deliveryFeeZipCode
     * @return \Illuminate\Http\Response
     */
    public function show(DeliveryFeeZipCode $deliveryFeeZipCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DeliveryFeeZipCode  $deliveryFeeZipCode
     * @return \Illuminate\Http\Response
     */
    public function edit(DeliveryFeeZipCode $deliveryFeeZipCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DeliveryFeeZipCode  $deliveryFeeZipCode
     * @return \Illuminate\Http\Response
     */
    public function updateDeliveryFeeZipCodes(Request $request)
    {
        $rows = $request->all();

        foreach ($rows as $row) {
            $dfzc = DeliveryFeeZipCode::where([
                'zip_code' => $row['zip_code'],
                'store_id' => $this->store->id
            ])->first();
            if ($dfzc) {
                $dfzc->delivery_fee = $row['delivery_fee'];
                $dfzc->shipping = isset($row['shipping'])
                    ? $row['shipping']
                    : 0;
                $dfzc->update();
            } else {
                $dfzc = new DeliveryFeeZipCode();
                $dfzc->store_id = $this->store->id;
                $dfzc->zip_code = $row['zip_code'];
                $dfzc->delivery_fee = $row['delivery_fee'];
                $dfzc->shipping = isset($row['shipping'])
                    ? $row['shipping']
                    : 0;
                $dfzc->save();
            }
        }
    }

    public function addDeliveryFeeCity(Request $request)
    {
        $dfc = $request->get('dfc');
        $client = new \GuzzleHttp\Client();
        $key = [
            'wJzSps4L8CKO5NKtM08l2GDH0ZJTipdhVZHyBx6uzzdsaM4bsqgI9MVzhabsxdYw',
            'cThtIHAkpbKKYbvjXLrfuNwuTLfASFxvDxDOQe5Hi4vIuCjp7e8pIpKiMufUNrR0'
        ];

        try {
            $res = $client->request(
                'GET',
                'https://www.zipcodeapi.com/rest/' .
                    $key[0] .
                    '/city-zips.json/' .
                    $dfc['city'] .
                    '/' .
                    $dfc['state']
            );
        } catch (\Exception $e) {
            $res = $client->request(
                'GET',
                'https://www.zipcodeapi.com/rest/' .
                    $key[1] .
                    '/city-zips.json/' .
                    $dfc['city'] .
                    '/' .
                    $dfc['state']
            );
        }

        $zipCodes = json_decode((string) $res->getBody());

        $settings = StoreSetting::where('store_id', $this->store->id)->first();
        $existingZipCodes = $settings->delivery_distance_zipcodes;

        foreach ($zipCodes->zip_codes as $zipCode) {
            $dfzc = DeliveryFeeZipCode::where([
                'zip_code' => $zipCode,
                'store_id' => $this->store->id
            ])->first();
            if ($dfzc) {
                $dfzc->delivery_fee = $dfc['rate'];
                $dfzc->update();
            } else {
                $dfzc = new DeliveryFeeZipCode();
                $dfzc->store_id = $this->store->id;
                $dfzc->zip_code = $zipCode;
                $dfzc->delivery_fee = $dfc['rate'];
                $dfzc->shipping = isset($row['shipping'])
                    ? $row['shipping']
                    : 0;
                $dfzc->save();
            }

            if (!in_array($zipCode, $existingZipCodes)) {
                array_push($existingZipCodes, $zipCode);
            }
        }

        $settings->delivery_distance_zipcodes += $existingZipCodes;
        $settings->update();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DeliveryFeeZipCode  $deliveryFeeZipCode
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeliveryFeeZipCode $deliveryFeeZipCode)
    {
        //
    }
}
