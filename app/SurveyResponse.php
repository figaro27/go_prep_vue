<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function order()
    {
        return $this->belongsTo('App\Ordera');
    }

    public function question()
    {
        return $this->belongsTo('App\SurveyQuestion');
    }
}
