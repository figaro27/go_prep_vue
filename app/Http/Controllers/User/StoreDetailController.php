<?php

namespace App\Http\Controllers;

use App\StoreDetail;
use Auth;
use Illuminate\Http\Request;

class StoreDetailController extends Controller
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
        $id = Auth::user()->id;
        $store = StoreDetail::findOrFail($id);
        return $store;
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
        $this->validate($request, [
            'name' => 'required|string',
            'logo' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required|numeric',
            'description' => 'required|string|max:450',
        ]);

        $id = auth('api')->user()->id;
        $store = StoreDetail::findOrFail($id);

        $store->update($request->except('logo'));

        if ($request->has('logo')) {
            $imageRaw = $request->get('logo');

            if (!\Storage::exists($imageRaw)) {
                $imageRaw = str_replace(' ', '+', $imageRaw);
                
                $ext = [];
                preg_match('/^data:image\/(.{3,9});base64,/i', $imageRaw, $ext);
                
                if (count($ext) > 1) {
                  $image = substr($imageRaw, strlen($ext[0]));
                  $image = base64_decode($image);

                  $imagePath = 'public/images/stores/' . $id . '_'. sha1($image) . '.' . $ext[1];
                    \Storage::disk('local')->put($imagePath, $image);
                    $imageUrl = \Storage::url($imagePath);

                    $store->logo = $imageUrl;
                    $store->save();
                }
            }
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
}
