<?php

namespace App;

use App\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportSetting extends Model
{
    protected $fillable = ['store_id'];

    protected $casts = [
        'lab_index' => 'boolean',
        'lab_nutrition' => 'boolean',
        'lab_macros' => 'boolean',
        'lab_logo' => 'boolean',
        'lab_website' => 'boolean',
        'lab_social' => 'boolean',
        'lab_customer' => 'boolean',
        'lab_description' => 'boolean',
        'lab_instructions' => 'boolean',
        'lab_expiration' => 'boolean',
        'lab_ingredients' => 'boolean',
        'lab_allergies' => 'boolean',
        'lab_packaged_by' => 'boolean',
        'lab_packaged_on' => 'boolean',
        'lab_dailyOrderNumbers' => 'boolean',
        'o_lab_daily_order_number' => 'boolean',
        'o_lab_customer' => 'boolean',
        'o_lab_address' => 'boolean',
        'o_lab_phone' => 'boolean',
        'o_lab_delivery' => 'boolean',
        'o_lab_order_number' => 'boolean',
        'o_lab_order_date' => 'boolean',
        'o_lab_delivery_date' => 'boolean',
        'o_lab_amount' => 'boolean',
        'o_lab_balance' => 'boolean',
        'o_lab_daily_order_number' => 'boolean',
        'o_lab_pickup_location' => 'boolean',
        'o_lab_website' => 'boolean',
        'o_lab_social' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
