<?php

namespace App;
use App\User;
use Illuminate\Database\Eloquent\Model;
use App\GiftCard;

class PurchasedGiftCard extends Model
{
    protected $appends = ['purchased_by', 'title', 'image'];

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function getPurchasedByAttribute()
    {
        $user = User::where('id', $this->user_id)->first();
        return $user && $user->details
            ? $user->details->firstname . ' ' . $user->details->lastname
            : null;
    }

    public function getTitleAttribute()
    {
        $giftCard = GiftCard::where('id', $this->gift_card_id)->first();
        if ($giftCard) {
            return $giftCard->title;
        } else {
            return $this->amount . ' - Gift Card';
        }
    }

    public function getImageAttribute()
    {
        $giftCard = GiftCard::where('id', $this->gift_card_id)->first();
        if ($giftCard) {
            return $giftCard->image['url_thumb'];
        } else {
            '';
        }
    }
}
