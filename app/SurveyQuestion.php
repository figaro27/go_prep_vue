<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'options' => 'json',
        'limit' => 'boolean',
        'conditional' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function responses()
    {
        return $this->hasMany('App\SurveyResponse');
    }
}
