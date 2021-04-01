<?php

namespace App;

use App\Coupon;
use App\PurchasedGiftCard;
use App\LineItemOrder;
use App\PickupLocation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Staff;

class Order extends Model
{
    protected $fillable = [
        'fulfilled',
        'notes',
        'publicNotes',
        'delivery_day',
        'referral_id'
    ];

    protected $hidden = ['store', 'store_id', 'card_id', 'fulfilled'];

    protected $casts = [
        'delivery_date' => 'date:Y-m-d',
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
        'adjustedDifference' => 'float',
        'purchasedGiftCardReduction' => 'float',
        'referralReduction' => 'float',
        'promotionReduction' => 'float',
        'pointsReduction' => 'float',
        'gratuity' => 'float',
        'coolerDeposit' => 'float',
        'grandTotal' => 'float',
        'shipping' => 'boolean',
        'prepaid' => 'boolean'
        //'created_at' => 'date:F d, Y'
    ];

    protected $appends = [
        'has_notes',
        'meal_ids',
        'items',
        'meal_orders',
        'meal_package_items',
        'pre_coupon',
        'order_day',
        'delivery_day',
        'line_items_order',
        'multiple_dates',
        'delivery_dates_array',
        'visible_items'
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

    public function purchased_gift_cards()
    {
        return $this->hasMany('App\PurchasedGiftCard');
    }

    public function delivery_day()
    {
        return $this->hasOne('App\DeliveryDay');
    }

    public function getVisibleItemsAttribute()
    {
        if ($this->isMultipleDelivery) {
            return $this->items
                ->filter(function ($item) {
                    return !$item->hidden;
                })
                ->values()
                ->sortBy('delivery_date');
        } else {
            return $this->items
                ->filter(function ($item) {
                    return !$item->hidden;
                })
                ->values();
        }
    }

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
        $paid_at = new Carbon($this->paid_at);
        return $paid_at->format('m d');
    }

    public function getDeliveryDayAttribute()
    {
        return $this->delivery_date->format('m d');
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

    public function getLineItemsOrderAttribute()
    {
        return LineItemOrder::where('order_id', $this->id)->get();
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
                    'item_id' => $mealOrder->id,
                    'delivery_date' => $mealOrder->delivery_date,
                    'short_title' => $mealOrder->short_title,
                    'full_title' => $mealOrder->full_title,
                    'base_title' => $mealOrder->base_title,
                    'base_size' => $mealOrder->base_size,
                    'meal_size_id' => $mealOrder->meal_size_id,
                    'meal_title' => $mealOrder->title,
                    'customTitle' => $mealOrder->customTitle,
                    'customSize' => $mealOrder->customSize,
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
                    'hidden' => $mealOrder->hidden,
                    'meal_package_order_id' =>
                        $mealOrder->meal_package_order_id,
                    'meal_package_title' => $mealOrder->meal_package_title,
                    'added_price' => $mealOrder->added_price,
                    'category_id' => $mealOrder->category_id,
                    'components' => $mealOrder->components->map(function (
                        $component
                    ) {
                        return (object) [
                            'meal_component_id' => $component->component
                                ? $component->component->id
                                : null,
                            'meal_component_option_id' => $component->option
                                ? $component->option->id
                                : null,
                            'component' => $component->component
                                ? $component->component->title
                                : null,
                            'option' => $component->option
                                ? $component->option->title
                                : null
                        ];
                    }),
                    'addons' => $mealOrder->addons->map(function ($addon) {
                        return (object) [
                            'meal_addon_id' => isset($addon->addon->title)
                                ? $addon->addon->id
                                : null,
                            'addon' => isset($addon->addon->title)
                                ? $addon->addon->title
                                : null
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

    public function getCutoffDate($customDeliveryDay = null)
    {
        return $this->store->getCutoffDate(
            $this->delivery_date,
            $customDeliveryDay
        );
    }

    public static function updateOrder($id, $props)
    {
        $order = Order::with(['user', 'user.userDetail'])->findOrFail($id);

        $props = collect($props)->only(['fulfilled', 'notes', 'publicNotes']);

        $order->update($props->toArray());

        return $order;
    }
}
