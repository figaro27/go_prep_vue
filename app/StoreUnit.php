<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreUnit extends Model
{
    protected $fillable = ['store_id', 'ingredient_id', 'unit'];

    protected $hidden = ['store_id', 'created_at', 'updated_at'];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function ingredient()
    {
        return $this->belongsTo('App\Ingredient');
    }
}
