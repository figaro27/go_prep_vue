<?php

namespace App;

use App\Mail\Store\CancelledSubscription;
use App\Mail\Store\NewOrder;
use App\Mail\Store\NewSubscription;
use App\Mail\Store\ReadyToPrint;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class Store extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['accepted_toa'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['orders', 'customers', 'plan'];

    protected $appends = [
        'cutoff_passed',
        'next_delivery_date',
        'next_cutoff_date',
        'url'
    ];

    protected $casts = [];

    public static function boot()
    {
        parent::boot();

        self::saved(function ($model) {
            $model->clearCaches();
        });
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $arr = parent::toArray();
        return $arr;
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function orders()
    {
        return $this->hasMany('App\Order')->orderBy('created_at', 'desc');
    }

    public function subscriptions()
    {
        return $this->hasMany('App\Subscription')->orderBy(
            'created_at',
            'desc'
        );
    }

    public function meals()
    {
        return $this->hasMany('App\Meal')->orderBy('title');
    }

    public function packages()
    {
        return $this->hasMany('App\MealPackage')->orderBy('title');
    }

    public function ingredients()
    {
        return $this->hasMany('App\Ingredient')->orderBy('food_name');
    }

    public function units()
    {
        return $this->hasMany('App\StoreUnit');
    }

    public function storeDetail()
    {
        return $this->hasOne('App\StoreDetail');
    }

    public function details()
    {
        return $this->hasOne('App\StoreDetail');
    }

    public function settings()
    {
        return $this->hasOne('App\StoreSetting');
    }

    public function modules()
    {
        return $this->hasOne('App\StoreModule');
    }

    public function moduleSettings()
    {
        return $this->hasOne('App\StoreModuleSettings');
    }

    public function categories()
    {
        return $this->hasMany('App\Category')->orderBy('order');
    }

    public function customers()
    {
        return $this->hasMany('App\Customer');
    }

    public function coupons()
    {
        return $this->hasMany('App\Coupon');
    }

    public function pickupLocations()
    {
        return $this->hasMany('App\PickupLocation');
    }

    public function productionGroups()
    {
        return $this->hasMany('App\ProductionGroup');
    }

    public function lineItems()
    {
        return $this->hasMany('App\LineItem');
    }

    public function plan()
    {
        return $this->hasOne('App\StorePlan');
    }

    public function clearCaches()
    {
        Cache::forget('store_order_ingredients' . $this->id);
        Cache::forget('store_logo_' . $this->id);
        Cache::forget('store_' . $this->id . '_subscribed_delivery_days');
    }

    public function getUrl($append = '', $secure = true)
    {
        $protocol = config('app.secure') && $secure ? 'https://' : 'http://';
        if (starts_with($append, config('app.url'))) {
            $append = str_replace(config('app.url'), '', $append);
        }
        $url =
            $protocol .
            $this->details->domain .
            '.' .
            config('app.domain') .
            $append;
        return $url;
    }

    public function getConnectUrl()
    {
        if (env('APP_ENV') === 'production') {
            $ca = 'ca_ER2OUNQq30X2xHMqkWo8ilUSz7Txyn1A';
        } else {
            $ca = 'ca_ER2OYlaTUrWz7LRQvhtKLIjZsRcM8mh9';
        }

        if (in_array($this->details->country, ['US', 'CA'])) {
            return "https://connect.stripe.com/express/oauth/authorize?client_id=$ca";
        } else {
            return "https://connect.stripe.com/oauth/authorize?client_id=$ca&response_type=code&scope=read_write";
        }
    }

    public static function getStore($id)
    {
        return Store::with('storeDetail', 'order')
            ->where('id', $id)
            ->first();
    }

    public static function getStores()
    {
        return Store::with('storeDetail', 'order')
            ->get()
            ->map(function ($store) {
                return [
                    "id" => $store->id,
                    "logo" => $store->storeDetail->logo,
                    "name" => $store->storeDetail->name,
                    "phone" => $store->storeDetail->phone,
                    "address" => $store->storeDetail->address,
                    "city" => $store->storeDetail->city,
                    "state" => $store->storeDetail->state,
                    "Joined" => $store->created_at->format('m-d-Y'),
                    "TotalOrders" => $store->order->count(),
                    "TotalCustomers" => Order::all()
                        ->unique('user_id')
                        ->where('store_id', '=', $store->id)
                        ->count(),
                    "TotalPaid" =>
                        '$' .
                        number_format(
                            Order::all()
                                ->where('store_id', '=', $store->id)
                                ->pluck('amount')
                                ->sum(),
                            2,
                            '.',
                            ','
                        )
                ];
            });
    }

    public function getOrderIngredients($dateRange = [])
    {
        // $excludeFulfilled = true
        $ingredients = [];

        $orders = $this->orders()
            ->with(['meals', 'meals.ingredients', 'meal_orders'])
            ->where(['paid' => 1, 'voided' => 0]);

        if ($dateRange === []) {
            //$orders = $orders->where('delivery_date', $this->getNextDeliveryDate());
        }
        if (isset($dateRange['from'])) {
            $from = Carbon::parse($dateRange['from']);
            $orders = $orders->where(
                'delivery_date',
                '>=',
                $from->format('Y-m-d')
            );
        } else {
            $orders = $orders->where(
                'delivery_date',
                '>=',
                Carbon::now()->format('Y-m-d')
            );
        }
        if (isset($dateRange['to'])) {
            $to = Carbon::parse($dateRange['to']);
            $orders = $orders->where(
                'delivery_date',
                '<=',
                $to->format('Y-m-d')
            );
        }

        // if ($excludeFulfilled) {
        //     $orders = $orders->where('fulfilled', false);
        // }

        $orders = $orders->get();

        foreach ($orders as $order) {
            $mealOrders = $order->meal_orders()->get();
            foreach ($mealOrders as $mealOrder) {
                $quantity = $mealOrder->quantity;
                $meal = $mealOrder->meal;
                $multiplier = 1;
                $mealIngredients = $meal->ingredients;

                // A size was chosen. Use the multiplier
                // 2019-07-24 DB - multipliers no longer used for meal sizes
                //                 store now manually assigns ingredients
                if ($mealOrder->meal_size_id && $mealOrder->meal_size) {
                    $multiplier = 1; //$mealOrder->meal_size->multiplier;
                    $mealIngredients = $mealOrder->meal_size->ingredients;
                }

                foreach ($mealIngredients as $ingredient) {
                    $adjuster = $ingredient->adjuster / 100;
                    $quantity_unit = $ingredient->pivot->quantity_unit;
                    $quantity_base =
                        $ingredient->pivot->quantity_base *
                        $quantity *
                        $multiplier *
                        $adjuster;

                    $key = $ingredient->id;

                    if (!isset($ingredients[$key])) {
                        $ingredients[$key] = [
                            'id' => $ingredient->id,
                            'ingredient' => $ingredient,
                            'quantity' => $quantity_base,
                            'adjuster' => $adjuster
                        ];
                    } else {
                        $ingredients[$key]['quantity'] += $quantity_base;
                    }
                }

                $components = collect($mealOrder->components);

                foreach ($components as $component) {
                    foreach ($component->option->ingredients as $ingredient) {
                        $quantity_unit = $ingredient->pivot->quantity_unit;
                        $quantity_base =
                            $ingredient->pivot->quantity_base * $quantity;
                        //* $multiplier;

                        $key = $ingredient->id;

                        if (!isset($ingredients[$key])) {
                            $ingredients[$key] = [
                                'id' => $ingredient->id,
                                'ingredient' => $ingredient,
                                'quantity' => $quantity_base,
                                'adjuster' => $adjuster
                            ];
                        } else {
                            $ingredients[$key]['quantity'] += $quantity_base;
                        }
                    }
                }

                $addons = collect($mealOrder->addons);

                foreach ($addons as $addon) {
                    foreach ($addon->addon->ingredients as $ingredient) {
                        $quantity_unit = $ingredient->pivot->quantity_unit;
                        $quantity_base =
                            $ingredient->pivot->quantity_base * $quantity;
                        //* $multiplier;

                        $key = $ingredient->id;

                        if (!isset($ingredients[$key])) {
                            $ingredients[$key] = [
                                'id' => $ingredient->id,
                                'ingredient' => $ingredient,
                                'quantity' => $quantity_base,
                                'adjuster' => $adjuster
                            ];
                        } else {
                            $ingredients[$key]['quantity'] += $quantity_base;
                        }
                    }
                }
            }
        }

        return $ingredients;
    }

    public function getOrderMeals($dateRange = [])
    {
        $meals = [];

        $orders = $this->orders()
            ->with(['meals'])
            ->where('paid', 1);
        if ($dateRange === []) {
            $orders = $orders->where(
                'delivery_date',
                $this->getNextDeliveryDate()
            );
        }
        if (isset($dateRange['from'])) {
            $from = Carbon::parse($dateRange['from']);
            $orders = $orders->where(
                'delivery_date',
                '>=',
                $from->format('Y-m-d')
            );
        }
        if (isset($dateRange['to'])) {
            $to = Carbon::parse($dateRange['to']);
            $orders = $orders->where(
                'delivery_date',
                '<=',
                $to->format('Y-m-d')
            );
        }
        $orders = $orders->get();

        foreach ($orders as $order) {
            foreach ($order->meals as $meal) {
                $key = $meal->id;

                if (!isset($meals[$key])) {
                    $meals[$key] = [
                        'id' => $key,
                        'meal' => $meal,
                        'quantity' => 1
                    ];
                } else {
                    $meals[$key]['quantity']++;
                }
            }
        }

        return $meals;
    }

    public function getNextDeliveryDay($weekIndex)
    {
        $week = date('D', strtotime("Sunday +{$weekIndex} days"));
        $date = new Carbon('next ' . $week, $this->settings->timezone);
        $date->setTime(0, 0, 0);
        return $date->setTimezone('utc');
    }

    public function getNextDeliveryDate($factorCutoff = false)
    {
        if (!$this->settings) {
            return null;
        }
        return $this->settings->getNextDeliveryDates($factorCutoff)[0] ?? null;
    }

    public function getNextCutoffDate($weekIndex = null)
    {
        if (is_null($weekIndex)) {
            $date = $this->getNextDeliveryDate(false);
        } else {
            $date = $this->getNextDeliveryDay($weekIndex);
        }

        return $date ? $this->getCutoffDate($date) : null;
    }

    /**
     * Get the cutoff date for a particular delivery date
     *
     * @param Carbon $deliveryDate
     * @return Carbon $cutoffDate
     */
    public function getCutoffDate(Carbon $deliveryDate)
    {
        $cutoffDate = Carbon::createFromDate(
            $deliveryDate->year,
            $deliveryDate->month,
            $deliveryDate->day,
            $this->settings->timezone
        );
        if ($this->settings->cutoff_type === 'timed') {
            return $cutoffDate
                ->setTime(0, 0, 0)
                ->subSeconds($this->getCutoffSeconds())
                ->setTimezone('utc');
        } elseif ($this->settings->cutoff_type === 'single_day') {
            $dayName = date(
                'l',
                strtotime("Sunday +{$this->settings->cutoff_days} days")
            );

            return $cutoffDate
                ->modify('last ' . $dayName)
                ->setTime($this->settings->cutoff_hours, 0, 0)
                ->setTimezone('utc');
        }
    }

    public function getOrders(
        $groupBy = null,
        $dateRange = [],
        $onlyUnfulfilled = false,
        $onlyPaid = true,
        $onlyDelivery = false,
        $orderDates = false,
        $couponCode = ''
    ) {
        $orders = $this->orders()->with(['meals', 'meal_orders']);

        $date = '';
        if ($orderDates === false) {
            $date = 'delivery_date';
        } else {
            $date = 'created_at';
        }

        if (isset($dateRange['from'])) {
            $from = Carbon::parse($dateRange['from']);
            $orders = $orders->where($date, '>=', $from->format('Y-m-d'));
        }
        if (isset($dateRange['to'])) {
            $to = Carbon::parse($dateRange['to']);
            $orders = $orders->where($date, '<=', $to->format('Y-m-d'));
        }

        // if ($onlyUnfulfilled) {
        //     $orders = $orders->where('fulfilled', 0);
        // }
        if ($onlyPaid) {
            $orders = $orders->where('paid', 1);
        }
        if ($onlyDelivery) {
            $orders = $orders->where('pickup', 0);
        }

        if ($couponCode != '') {
            $orders = $orders->where('couponCode', $couponCode);
        }

        $orders = $orders->get();

        if ($groupBy) {
            $orders = $orders->groupBy($groupBy);
        }

        return $orders;
    }

    public function getOrdersForNextDelivery($groupBy = null)
    {
        $date = $this->getNextDeliveryDate();
        $orders = $this->orders()
            ->with('meals')
            ->where([['paid', 1], ['delivery_date', $date->format('Y-m-d')]])
            ->get();

        if ($groupBy) {
            $orders = $orders->groupBy($groupBy);
        }

        return $orders;
    }

    public function getPastOrders($groupBy = null)
    {
        $date = $this->getNextDeliveryDate();
        $orders = $this->orders()
            ->with('meals')
            ->where([
                ['paid', 1],
                ['delivery_date', '<', $date->format('Y-m-d')]
            ])
            ->get();

        if ($groupBy) {
            $orders = $orders->groupBy($groupBy);
        }

        return $orders;
    }

    public function getFulfilledOrders($groupBy = null)
    {
        $date = $this->getNextDeliveryDate();
        $orders = $this->orders()
            ->with('meals')
            ->where([['paid', 1], ['fulfilled', '1']])
            ->get();

        if ($groupBy) {
            $orders = $orders->groupBy($groupBy);
        }

        return $orders;
    }

    public function deliversToZip($zip)
    {
        return in_array($zip, $this->settings->delivery_distance_zipcodes);
    }

    public function hasStripe()
    {
        return isset($this->settings->stripe_id) && $this->settings->stripe_id;
    }

    public function notificationEnabled($notif)
    {
        if (!$this->settings) {
            return false;
        }
        return $this->settings->notificationEnabled($notif);
    }

    public function sendNotification($notif, $data = [])
    {
        $store = $this;
        $storeDetails = $this->details;

        $email = null;

        /* Timezone */
        $settings = $store->settings;
        if ($settings && $settings->timezone) {
            $timezone = $settings->timezone;
            date_default_timezone_set($timezone);
        }
        /* Timezone End */

        if (isset($store->modules) && isset($store->modules->emailBranding)) {
            $emailBranding = (int) $store->modules->emailBranding;

            if ($emailBranding == 1) {
                $logo = $storeDetails->getMedia('logo')->first();

                if ($logo) {
                    $path = $logo->getPath('thumb');

                    if (file_exists($path)) {
                        $logo_b64 = \App\Utils\Images::encodeB64($path);

                        if ($logo_b64) {
                            $data['logo_b64'] = $logo_b64;
                        }
                    }
                }
            }
        }

        switch ($notif) {
            case 'new_order':
                $email = new NewOrder($data);
                $bcc = true;
                break;

            case 'new_subscription':
                $email = new NewSubscription($data);
                $bcc = true;
                break;

            case 'cancelled_subscription':
                $email = new CancelledSubscription($data);
                break;

            case 'ready_to_print':
                $email = new ReadyToPrint([
                    'store' => $store,
                    'storeDetails' => $storeDetails
                ]);
                break;
        }

        if ($email) {
            Mail::to($this->user)->send($email);
            return true;
        }

        return false;
    }

    public function getCutoffSeconds()
    {
        $cutoff =
            $this->settings->cutoff_days * (60 * 60 * 24) +
            $this->settings->cutoff_hours * (60 * 60);
        return $cutoff;
    }

    /**
     * Returns whether the store's cutoff passed
     * @param mixed $period
     * @return boolean
     */
    public function cutoffPassed($period = 'hour')
    {
        if (!$this->settings || !is_array($this->settings->delivery_days)) {
            return false;
        }

        $now = Carbon::now('utc');

        $cutoff =
            $this->settings->cutoff_days * (60 * 60 * 24) +
            $this->settings->cutoff_hours * (60 * 60);

        if ($this->settings->cutoff_type === 'timed') {
            foreach ($this->settings->delivery_days as $day) {
                $date = Carbon::createFromFormat(
                    'D',
                    $day,
                    $this->settings->timezone
                )->setTime(0, 0, 0);
                $diff = $date->getTimestamp() - $now->getTimestamp() - $cutoff;
                //echo $diff."\r\n";

                // Cutoff passed less than an hour ago
                if ($period === 'hour' && $diff >= -60 * 60 && $diff < 0) {
                    return true;
                }
            }
        } elseif ($this->settings->cutoff_type === 'single_day') {
            /*$date = $this->getNextDeliveryDate();
            if (!$date) {
                return false;
            }
            $cutoff = $this->getCutoffDate($date);*/

            $dayName = date(
                'l',
                strtotime("Sunday +{$this->settings->cutoff_days} days")
            );

            $cutoff = Carbon::parse(
                "this $dayName",
                $this->settings->timezone
            )->setTime($this->settings->cutoff_hours, 0, 0);

            $diff = $cutoff->getTimestamp() - $now->getTimestamp();

            // Cutoff passed less than an hour ago
            if ($period === 'hour' && $diff >= -60 * 60 && $diff < 0) {
                return true;
            }
        }

        return false;
    }

    public function getCutoffPassedAttribute()
    {
        $now = Carbon::now('utc');
        $date = $this->getNextDeliveryDate();
        if (!$date) {
            return false;
        }
        $cutoff = $this->getCutoffDate($date);

        if ($cutoff != false) {
            return $cutoff->isPast();
        } else {
            return false;
        }
    }

    public function getNextCutoffDateAttribute()
    {
        $date = $this->getNextCutoffDate();
        return $date ? $date->toDateTimeString() : null;
    }

    public function getNextDeliveryDateAttribute()
    {
        $date = $this->getNextDeliveryDate();
        return $date ? $date->toDateTimeString() : null;
    }

    public function getUrlAttribute()
    {
        return $this->getUrl();
    }
}
