<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Subscription;

class Card extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'stripe_id',
        'brand',
        'exp_month',
        'exp_year',
        'last4',
        'country',
        'payment_gateway',
        'store_id',
        'saveCard'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
