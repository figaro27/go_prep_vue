<?php

namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\UserDetail;

class Coupon extends Model
{
    use SoftDeletes;

    protected $appends = ['referredUserName'];
    protected $casts = [
        'freeDelivery' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function getReferredUserNameAttribute()
    {
        $userDetail = UserDetail::where(
            'user_id',
            $this->referral_user_id
        )->first();

        return $userDetail ? $userDetail->full_name : '';
    }
}
