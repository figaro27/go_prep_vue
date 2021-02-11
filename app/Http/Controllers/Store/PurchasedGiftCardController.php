<?php

namespace App\Http\Controllers\Store;

use App\PurchasedGiftCard;
use Illuminate\Http\Request;

class PurchasedGiftCardController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->purchasedGiftCards;
    }

    public function findPurchasedGiftCard(Request $request)
    {
        $storeId = $request->get('store_id');
        $purchasedGiftCardCode = $request->get('purchasedGiftCardCode');
        $purchasedGiftCard = PurchasedGiftCard::where([
            'store_id' => $storeId,
            'code' => $purchasedGiftCardCode
        ])->first();
        if (isset($purchasedGiftCard)) {
            return $purchasedGiftCard;
        }
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
        $newGiftCard = $request->get('newGiftCard');

        $purchasedGiftCard = new PurchasedGiftCard();
        $purchasedGiftCard->store_id = $this->store->id;
        $purchasedGiftCard->gift_card_id = 1;
        $purchasedGiftCard->user_id = $this->store->user->id;
        $purchasedGiftCard->order_id = 1;
        $purchasedGiftCard->code = $newGiftCard['code']
            ? $newGiftCard['code']
            : strtoupper(
                substr(uniqid(rand(10, 99), false), 0, 6) .
                    chr(rand(65, 90)) .
                    rand(10, 99)
            );
        $purchasedGiftCard->amount = $newGiftCard['amount'];
        $purchasedGiftCard->balance = $newGiftCard['amount'];
        $purchasedGiftCard->emailRecipient = isset(
            $newGiftCard['emailRecipient']
        )
            ? $newGiftCard['emailRecipient']
            : null;
        $purchasedGiftCard->save();

        if (isset($newGiftCard['emailRecipient'])) {
            $this->store->sendNotification('new_gift_card', [
                'order' => null,
                'store' => $this->store,
                'purchasedGiftCard' => $purchasedGiftCard,
                'emailRecipient' => $newGiftCard['emailRecipient']
            ]);
        }
        return $purchasedGiftCard;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\GiftCard  $giftCard
     * @return \Illuminate\Http\Response
     */
    public function show(PurchasedGiftCard $purchasedGiftCards)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\GiftCard  $giftCard
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchasedGiftCard $purchasedGiftCards)
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
        $purchasedGiftCard = PurchasedGiftCard::where('id', $id)->first();
        $purchasedGiftCard->balance = $request->get('balance')
            ? $request->get('balance')
            : $purchasedGiftCard->balance;
        $purchasedGiftCard->amount = $request->get('amount')
            ? $request->get('amount')
            : $purchasedGiftCard->amount;
        $purchasedGiftCard->update();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\GiftCard  $giftCard
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        PurchasedGiftCard::where('id', $id)->delete();
    }
}
