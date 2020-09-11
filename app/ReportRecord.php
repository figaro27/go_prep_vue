<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportRecord extends Model
{
    protected $fillable = ['store_id'];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
