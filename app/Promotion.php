<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $casts = [
        'promotionAmount' => 'double',
        'conditionAmount' => 'double'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
