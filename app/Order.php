<?php

namespace App;

use App\Coupon;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['fulfilled', 'notes', 'delivery_day'];

    protected $hidden = [];

    protected $casts = [
        'delivery_date' => 'date:Y-m-d',
        'cutoff_date' => 'date:Y-m-d H:i:s',
        'preFeePreDiscount' => 'float',
        'afterDiscountBeforeFees' => 'float',
        'processingFee' => 'float',
        'deliveryFee' => 'float',
        'amount' => 'float',
        'salesTax' => 'float',
        'mealPlanDiscount' => 'float'
        //'created_at' => 'date:F d, Y'
    ];

    protected $appends = [
        'has_notes',
        'meal_ids',
        'items',
        'store_name',
        'cutoff_date',
        'cutoff_passed',
        'pre_coupon'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer');
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
        return $this->belongsToMany('App\Meal', 'meal_orders')
            ->with('components')
            ->withPivot('quantity', 'meal_size_id')
            ->withTrashed()
            ->using('App\MealOrder');
    }

    public function subscription()
    {
        return $this->belongsTo('App\Subscription');
    }

    public function events()
    {
        return $this->hasMany('App\OrderEvent');
    }

    public function coupon()
    {
        return $this->hasOne('App\Coupon');
    }

    public function pickup_location()
    {
        return $this->belongsTo('App\PickupLocation');
    }

    public function getPreCouponAttribute()
    {
        return $this->amount + $this->couponReduction;
    }

    public function getHasNotesAttribute()
    {
        if ($this->notes) {
            return true;
        } else {
            return false;
        }
    }

    public function getMealOrdersAttribute()
    {
        // $mealIDs = MealOrder::where('order_id', $this->id)->pluck('meal_id');
        // $meals = [];
        // foreach ($mealIDs as $meal){
        //     array_push($meals, Meal::where('id', $meal)->get());
        // }
        // return $meals;
    }

    public function getStoreNameAttribute()
    {
        return $this->store->storeDetail->name;
    }

    public function getMealIdsAttribute()
    {
        return $this->meals()
            ->get()
            ->pluck('id')
            ->unique();
    }
    public function getItemsAttribute()
    {
        return $this->meal_orders()
            ->with([
                'components',
                'components.component',
                'components.option',
                'addons'
            ])
            ->get()
            ->map(function ($mealOrder) {
                return (object) [
                    'meal_id' => $mealOrder->meal_id,
                    'meal_size_id' => $mealOrder->meal_size_id,
                    'meal_title' => $mealOrder->title,
                    'title' => $mealOrder->title,
                    'quantity' => $mealOrder->quantity,
                    'unit_price' => $mealOrder->unit_price,
                    'price' => $mealOrder->price,
                    'components' => $mealOrder->components->map(function (
                        $component
                    ) {
                        return (object) [
                            'meal_component_id' => $component->component->id,
                            'meal_component_option_id' =>
                                $component->option->id,
                            'component' => $component->component->title,
                            'option' => $component->option->title
                        ];
                    }),
                    'addons' => $mealOrder->addons->map(function ($addon) {
                        return (object) [
                            'meal_addon_id' => $addon->addon->id,
                            'addon' => $addon->addon->title
                        ];
                    })
                ];
            });
        //
        return $this->meals()
            ->get()
            ->keyBy(function ($meal) {
                $id = $meal->id;

                if ($meal->meal_size) {
                    $id .= '-' . $meal->meal_size->id;
                }

                return $id;
            })
            ->map(function ($meal) {
                return $meal->pivot->quantity ? $meal->pivot->quantity : 0;
            });
    }
    public function getCutoffDateAttribute()
    {
        return $this->getCutoffDate()
            ->setTimezone('utc')
            ->toDateTimeString();
    }
    public function getCutoffPassedAttribute()
    {
        return $this->getCutoffDate()->isPast();
    }

    public function getCutoffDate()
    {
        return $this->store->getCutoffDate($this->delivery_date);

        /*$ddate = new Carbon(
    $this->delivery_date,
    $this->store->settings->timezone
    );
    $ddate->setTime(0, 0);
    $cutoff = $ddate->subSeconds($this->store->getCutoffSeconds());
    return $cutoff;*/
    }

    public static function updateOrder($id, $props)
    {
        $order = Order::with(['user', 'user.userDetail'])->findOrFail($id);

        $props = collect($props)->only(['fulfilled', 'notes']);

        $order->update($props->toArray());

        return $order;
    }
}
