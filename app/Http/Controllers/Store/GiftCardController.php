<?php

namespace App\Http\Controllers\Store;

use App\GiftCard;
use Illuminate\Http\Request;

class GiftCardController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->giftCards;
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
        $giftCard = new GiftCard();
        $giftCard->store_id = $this->store->id;
        $giftCard->title = $request->get('title');
        $giftCard->price = $request->get('price');

        if ($request->has('featured_image')) {
            $imagePath = \App\Utils\Images::uploadB64(
                $request->get('featured_image'),
                'path',
                'giftCards/'
            );
            $fullImagePath = \Storage::disk('public')->path($imagePath);
            $giftCard->clearMediaCollection('featured_image');
            $giftCard
                ->addMedia($fullImagePath)
                ->toMediaCollection('featured_image');
        }

        $giftCard->save();

        $categories = $request->get('category_ids');
        if (is_array($categories)) {
            $giftCard->categories()->sync($categories);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\GiftCard  $giftCard
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return GiftCard::where('id', $id)->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\GiftCard  $giftCard
     * @return \Illuminate\Http\Response
     */
    public function edit(GiftCard $giftCard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\GiftCard  $giftCard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->get('fromModal')) {
            $giftCard = GiftCard::where('id', $request->get('id'))->first();
            $giftCard->title = $request->get('title');
            $giftCard->price = $request->get('price');

            $categories = $request->get('category_ids');
            if (is_array($categories)) {
                $giftCard->categories()->sync($categories);
            }

            if ($request->has('featured_image')) {
                $imagePath = \App\Utils\Images::uploadB64(
                    $request->get('featured_image'),
                    'path',
                    'giftCards/'
                );
                $fullImagePath = \Storage::disk('public')->path($imagePath);
                $giftCard->clearMediaCollection('featured_image');
                $giftCard
                    ->addMedia($fullImagePath)
                    ->toMediaCollection('featured_image');
            }

            $giftCard->update();
        } else {
            $giftCard = GiftCard::where('id', $id)->first();
            $giftCard->active = $request->get('active');
            $giftCard->update();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\GiftCard  $giftCard
     * @return \Illuminate\Http\Response
     */
    public function destroy(GiftCard $giftCard)
    {
        //
    }
}
