<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Order;

class StoreModuleSettings extends Model
{
    protected $casts = [
        'transferTimeRange' => 'boolean'
    ];

    protected $appends = ['omittedTransferTimes'];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function module()
    {
        return $this->belongsTo('App\StoreModule');
    }

    public function getOmittedTransferTimesAttribute()
    {
        $orders = DB::table('orders')
            ->where('store_id', $this->store->id)
            ->where('delivery_date', '>=', Carbon::today())
            ->where('delivery_date', '<=', Carbon::today()->addDay(30))
            ->select(['delivery_date', 'transferTime'])
            ->selectRaw(DB::raw("count(delivery_date) as count"))
            ->groupBy('delivery_date', 'transferTime')
            ->get();

        $omit = [];

        foreach ($orders as $order) {
            if ($order->count >= $this->maxOrdersPerHour) {
                array_push($omit, [
                    $order->delivery_date => $order->transferTime
                ]);
            }
        }

        return $omit;
    }
}
