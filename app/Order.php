<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\MealOrder;
use App\Meal;

use Illuminate\Support\Carbon;

class Order extends Model
{

    protected $fillable = [
        'fulfilled', 'notes', 'delivery_day',
    ];

    protected $hidden = [

    ];

    protected $casts = [
        'amount' => 'double',
        'delivery_date' => 'date:Y-m-d',
        //'created_at' => 'date:F d, Y'
    ];

    protected $appends = ['has_notes', 'meal_ids', 'meal_quantities', 'store_name', 'cutoff_passed'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function meal_orders()
    {
        return $this->hasMany('App\MealOrder');
    }

    public function meals()
    {
        return $this->belongsToMany('App\Meal', 'meal_orders')->withPivot('quantity')->withTrashed();
    }

    public function subscription()
    {
        return $this->belongsTo('App\Subscription');
    }

    public function getHasNotesAttribute()
    {
        if ($this->notes){
            return true;
        }
        else
            return false;
    }

    public function getMealOrdersAttribute(){
        // $mealIDs = MealOrder::where('order_id', $this->id)->pluck('meal_id');
        // $meals = [];
        // foreach ($mealIDs as $meal){
        //     array_push($meals, Meal::where('id', $meal)->get());
        // }
        // return $meals;
    }

    public function getStoreNameAttribute(){
        return $this->store->storeDetail->name;
    }

    public function getMealIdsAttribute() {
      return $this->meals()->get()->pluck('id')->unique();
    }
    public function getMealQuantitiesAttribute() {
      return $this->meals()->get()->keyBy('id')->map(function($meal) {
        return $meal->pivot->quantity ? $meal->pivot->quantity : 0;
      });
    }
    public function getCutoffPassedAttribute() {
      $ddates = $this->store->settings->getNextDeliveryDates(true);

      $oddate = new Carbon($this->delivery_date);
      $diff = $oddate->diffInSeconds(Carbon::now());
      
      return $oddate->isPast() || $diff <= $this->store->getCutoffSeconds();
    }

    public static function updateOrder($id, $props)
    {

        $order = Order::with(['user', 'user.userDetail'])->findOrFail($id);

        $props = collect($props)->only([
            'fulfilled',
            'notes',
        ]);

        $order->update($props->toArray());

        return $order;
    }

}
