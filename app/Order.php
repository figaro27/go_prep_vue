<?php

namespace App;

use App\Coupon;
use App\LineItemOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['fulfilled', 'notes', 'delivery_day'];

    protected $hidden = [
        'store',
        'store_id',
        'store_name',
        'cutoff_date',
        'cutoff_passed',
        'adjustedDifference',
        'afterDiscountBeforeFees',
        'card_id',
        'couponCode',
        'couponReduction',
        'coupon_id',
        'fulfilled'
    ];

    protected $casts = [
        'delivery_date' => 'date:Y-m-d',
        'cutoff_date' => 'date:Y-m-d H:i:s',
        'deposit' => 'float',
        'preFeePreDiscount' => 'float',
        'afterDiscountBeforeFees' => 'float',
        'processingFee' => 'float',
        'deliveryFee' => 'float',
        'amount' => 'float',
        'chargedAmount' => 'float',
        'refundedAmount' => 'float',
        'balance' => 'float',
        'originalAmount' => 'float',
        'salesTax' => 'float',
        'mealPlanDiscount' => 'float',
        'couponReduction' => 'float',
        'adjustedDifference' => 'float'
        //'created_at' => 'date:F d, Y'
    ];

    protected $appends = [
        'has_notes',
        'meal_ids',
        'items',
        'meal_orders',
        'meal_package_items',
        'store_name',
        'cutoff_date',
        'cutoff_passed',
        'pre_coupon',
        'order_day',
        'goprep_fee',
        'stripe_fee',
        'grandTotal',
        'line_items_order',
        'added_by_store_id',
        'multiple_dates',
        'delivery_dates_array'
        // 'balance'
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

    public function meal_package_orders()
    {
        return $this->hasMany('App\MealPackageOrder');
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

    public function lineItems()
    {
        return $this->hasMany('App\LineItem');
    }

    public function lineItemsOrder()
    {
        return $this->hasMany('App\LineItemOrder');
    }

    public function lineItemsOrders()
    {
        return $this->hasMany('App\LineItemOrder');
    }

    public function coupon()
    {
        return $this->hasOne('App\Coupon');
    }

    public function pickup_location()
    {
        return $this->belongsTo('App\PickupLocation');
    }

    public function card()
    {
        return $this->hasOne('App\Card');
    }

    // public function getBalanceAttribute()
    // {
    //     $amount = $this->amount * (100 - $this->deposit) / 100;
    //     return ($amount + ($amount - $this->adjustedDifference)) * -1;
    // }

    public function getDeliveryDatesArrayAttribute()
    {
        $dates = [];
        if ($this->isMultipleDelivery) {
            $items = $this->items;
            foreach ($items as $item) {
                if ($item->delivery_date) {
                    $date = (new Carbon($item->delivery_date))->format('Y-m-d');

                    if (!in_array($date, $dates)) {
                        $dates[] = $date;
                    }
                }
            }
        } else {
            $dates[] = $this->delivery_date->toDateString();
        }

        sort($dates);
        return $dates;
    }

    public function getMultipleDatesAttribute()
    {
        $multipleDates = '';
        $items = $this->items;
        if ($this->isMultipleDelivery) {
            foreach ($items as $item) {
                if ($item->delivery_date) {
                    $date = new Carbon($item->delivery_date);
                    if (
                        strpos(
                            $multipleDates,
                            strval($date->format('D m/d/Y') . ', ')
                        ) === false
                    ) {
                        $multipleDates .= $date->format('D m/d/Y') . ', ';
                    }
                }
            }
        }

        $multipleDates = substr($multipleDates, 0, -2);
        return $multipleDates;
    }

    public function getOrderDayAttribute()
    {
        return $this->created_at->format('m d');
    }

    public function getGoprepFeeAttribute()
    {
        if (!$this->cashOrder) {
            return $this->afterDiscountBeforeFees *
                ($this->store->settings->application_fee / 100);
        } else {
            return 0;
        }
    }

    public function getStripeFeeAttribute()
    {
        if (!$this->cashOrder) {
            return $this->amount * 0.029 + 0.3;
        } else {
            return 0;
        }
    }

    public function getGrandTotalAttribute()
    {
        return $this->amount - $this->goprep_fee - $this->stripe_fee;
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

    public function getAddedByStoreIdAttribute()
    {
        return $this->user->added_by_store_id;
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

    public function getLineItemsOrderAttribute()
    {
        return LineItemOrder::where('order_id', $this->id)->get();
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

    public function getMealPackageItemsAttribute()
    {
        return $this->meal_package_orders()
            ->with(['meal_package', 'meal_package_size'])
            ->get();
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
                    'delivery_date' => $mealOrder->delivery_date,
                    'short_title' => $mealOrder->short_title,
                    'base_title' => $mealOrder->base_title,
                    'base_size' => $mealOrder->base_size,
                    'meal_size_id' => $mealOrder->meal_size_id,
                    'meal_title' => $mealOrder->title,
                    'instructions' => $mealOrder->instructions,
                    'title' => $mealOrder->title,
                    'html_title' => $mealOrder->html_title,
                    'quantity' => $mealOrder->quantity,
                    'unit_price' => $mealOrder->unit_price,
                    'price' => $mealOrder->price
                        ? $mealOrder->price
                        : $mealOrder->unit_price * $mealOrder->quantity,
                    'free' => $mealOrder->free,
                    'special_instructions' => $mealOrder->special_instructions,
                    'attached' => $mealOrder->attached,
                    'meal_package_order_id' =>
                        $mealOrder->meal_package_order_id,
                    'meal_package_title' => $mealOrder->meal_package_title,
                    'components' => $mealOrder->components->map(function (
                        $component
                    ) {
                        return (object) [
                            'meal_component_id' => $component->component->id,
                            'meal_component_option_id' => $component->option
                                ? $component->option->id
                                : null,
                            'component' => $component->component->title,
                            'option' => $component->option
                                ? $component->option->title
                                : null
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
