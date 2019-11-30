<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    public $appends = ['category_ids', 'gift_card', 'salesTax'];

    public function categories()
    {
        return $this->belongsToMany('App\Category')->using(
            'App\GiftCardCategory'
        );
    }

    public function getCategoryIdsAttribute()
    {
        return $this->categories->pluck('id');
    }

    public function getGiftCardAttribute()
    {
        return true;
    }

    public function getSalesTaxAttribute()
    {
        return 0;
    }
}
