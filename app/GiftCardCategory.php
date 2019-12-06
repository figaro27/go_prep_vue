<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GiftCardCategory extends Pivot
{
    public $table = 'category_gift_card';

    public $fillable = ['gift_card_id', 'category_id'];

    public function gift_card()
    {
        return $this->belongsTo('App\GiftCard');
    }

    public function category()
    {
        return $this->belongsTo('App\Category');
    }
}
