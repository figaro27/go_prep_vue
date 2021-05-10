<?php

namespace App;

use App\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportSetting extends Model
{
    protected $fillable = ['store_id'];

    protected $casts = [];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }
}
