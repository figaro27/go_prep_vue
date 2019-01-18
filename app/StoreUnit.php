<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreUnit extends Model
{
    protected $fillable = ['store_id', 'ingredient_id', 'unit'];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function ingredient()
    {
        return $this->belongsTo('App\Ingredient');
    }
}
