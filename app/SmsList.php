<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsList extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
