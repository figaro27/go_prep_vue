<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreModuleSettings extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function module()
    {
        return $this->belongsTo('App\StoreModule');
    }
}
