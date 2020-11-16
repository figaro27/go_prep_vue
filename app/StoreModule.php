<?php

namespace App;

use App\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StoreModule extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    protected $casts = [
        'cashOrders' => 'boolean',
        'productionGroups' => 'boolean',
        'specialInstructions' => 'boolean',
        'cashOrderNoBalance' => 'boolean',
        'stockManagement' => 'boolean',
        'autoPrintPackingSlip' => 'boolean',
        'showHotCheckbox' => 'boolean',
        'mealExpiration' => 'boolean',
        'pickupOnly' => 'boolean',
        'gratuity' => 'boolean',
        'cooler' => 'boolean',
        'allowMultipleSubscriptions' => 'boolean',
        'frequencyItems' => 'boolean',
        'manualOrderEmails' => 'boolean'
    ];

    protected $guarded = [];
}
