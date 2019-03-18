<?php

namespace App\Http\Controllers\Store;

use App\StoreDetail;
use App\Http\Controllers\Store\StoreController;
use Illuminate\Http\Request;
use \App\Utils\Images;

class StoreDetailController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\StoreDetail  $storeDetail
     * @return \Illuminate\Http\Response
     */
    public function show(StoreDetail $storeDetail)
    {
        return $this->store->details;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StoreDetail  $storeDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(StoreDetail $storeDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StoreDetail  $storeDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $store = $this->store->details;

        $rules = [
            'name' => 'required|string',
            'logo' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required|numeric',
            // 'description' => 'required|string|max:450',
        ];

        $this->validate($request, $rules);

        $newLogo = $request->has('logo') && substr($request->get('logo'), 0, 4) === 'data';

        if ($newLogo) {
            $image = Images::decodeB64($request->get('logo'));
            $size = getimagesizefromstring($image);

            if ($size && $size[0] !== $size[1]) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'logo' => ['The logo must have an equal width and height.'],
                    ],
                ], 422);
            }
        }

        $store->update($request->except('logo'));

        if ($newLogo) {
            $imageUrl = Images::uploadB64($request->get('logo'));

            if ($imageUrl) {
                $store->logo = $imageUrl;
                $store->save();
            }
        }

        return $store;

    }

    public function updateLogo(Request $request)
    {
        $store = $this->store->details;

        $newLogo = $request->has('logo') && substr($request->get('logo'), 0, 4) === 'data';

        if ($newLogo) {
            $image = Images::decodeB64($request->get('logo'));
            $size = getimagesizefromstring($image);

            if ($size && $size[0] !== $size[1]) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'logo' => ['The logo must have an equal width and height.'],
                    ],
                ], 422);
            }
        }

        if ($newLogo) {
            $imageUrl = Images::uploadB64($request->get('logo'));


                $store->logo = $imageUrl;
                $store->save();
            
        }

        return $store;

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StoreDetail  $storeDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(StoreDetail $storeDetail)
    {
        //
    }

    public function acceptedTOA(){
        $this->store->accepted_toa = 1;
        $this->store->save();
    }

    public function getAcceptedTOA(){
        return $this->store->accepted_toa;
    }
}
