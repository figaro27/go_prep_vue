<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Store;
use Auth;

class Customer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'user_id',
        'currency',
        'firstname',
        'lastname',
        'name',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'delivery'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['stripe_id', 'user'];

    protected $casts = [];

    protected $appends = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function cards()
    {
        return $this->hasManyThrough('App\Card', 'App\User');
    }

    public function orders()
    {
        return $this->hasMany('App\Order')
            ->where('paid', 1)
            ->orderBy('created_at', 'desc');
    }
}
