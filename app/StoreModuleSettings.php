<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreModuleSettings extends Model
{
    protected $casts = [
        'transferTimeRange' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function module()
    {
        return $this->belongsTo('App\StoreModule');
    }
}
