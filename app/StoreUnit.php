<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreUnit extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function ingredient()
    {
        return $this->belongsTo('App\Ingredient');
    }
}
