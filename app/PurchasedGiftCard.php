<?php

namespace App;
use App\User;
use Illuminate\Database\Eloquent\Model;

class PurchasedGiftCard extends Model
{
    protected $appends = ['purchased_by'];

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function getPurchasedByAttribute()
    {
        $user = User::where('id', $this->user_id)->first();
        return $user->details->firstname . ' ' . $user->details->lastname;
    }
}
