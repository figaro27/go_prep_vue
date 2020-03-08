<?php

namespace App;

use App\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportSetting extends Model
{
    protected $casts = [
        'lab_nutrition' => 'boolean',
        'lab_macros' => 'boolean',
        'lab_website' => 'boolean',
        'lab_social' => 'boolean',
        'lab_customer' => 'boolean',
        'lab_description' => 'boolean',
        'lab_instructions' => 'boolean',
        'lab_expiration' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
