<?php

namespace App;

use App\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StoreModule extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    protected $casts = [
        'allowCashOrders' => 'boolean',
        'transferHours' => 'boolean'
    ];

    protected $fillable = ['allowCashOrders'];
}
