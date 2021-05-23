<?php

namespace App\Http\Controllers\Store;

use App\GiftCard;
use Illuminate\Http\Request;
use App\ChildGiftCard;

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
        $giftCard->value = $request->get('value');

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

        $childStoreIds = $request->get('child_store_ids') ?? [];

        foreach ($childStoreIds as $childStoreId) {
            $newChildGiftCard = new ChildGiftCard();
            $newChildGiftCard->gift_card_id = $giftCard->id;
            $newChildGiftCard->store_id = $childStoreId;
            $newChildGiftCard->save();
        }

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
            $giftCard->value = $request->get('value');

            $childStoreIds = $request->get('child_store_ids') ?? [];

            $childGiftCards = ChildGiftCard::where(
                'gift_card_id',
                $giftCard->id
            )->get();
            foreach ($childGiftCards as $childGiftCard) {
                if (!in_array($childGiftCard->store_id, $childStoreIds)) {
                    $childGiftCard->delete();
                }
            }

            foreach ($childStoreIds as $childStoreId) {
                $existingChildGiftCard = ChildGiftcard::where([
                    'gift_card_id' => $giftCard->id,
                    'store_id' => $childStoreId
                ])->first();
                if (!$existingChildGiftCard) {
                    $newChildGiftCard = new ChildGiftCard();
                    $newChildGiftCard->gift_card_id = $giftCard->id;
                    $newChildGiftCard->store_id = $childStoreId;
                    $newChildGiftCard->save();
                }
            }

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
        return $giftCard;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\GiftCard  $giftCard
     * @return \Illuminate\Http\Response
     */
    public function destroy(GiftCard $giftCard)
    {
        $giftCard = $this->store
            ->giftCards()
            ->find($giftCard)
            ->first();
        $giftCard->delete();

        if ($this->store) {
            $this->store->setTimezone();
            $this->store->menu_update_time = date('Y-m-d H:i:s');
            $this->store->save();
        }
    }
}
