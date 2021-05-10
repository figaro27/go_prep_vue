<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabelSetting extends Model
{
    protected $table = 'label_settings';

    protected $fillable = ['store_id'];

    protected $casts = [
        'index' => 'boolean',
        'nutrition' => 'boolean',
        'macros' => 'boolean',
        'logo' => 'boolean',
        'website' => 'boolean',
        'social' => 'boolean',
        'customer' => 'boolean',
        'description' => 'boolean',
        'instructions' => 'boolean',
        'expiration' => 'boolean',
        'ingredients' => 'boolean',
        'allergies' => 'boolean',
        'packaged_by' => 'boolean',
        'packaged_on' => 'boolean',
        'daily_order_number' => 'boolean'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
