<?php

namespace App;

use App\Mail\Store\CancelledSubscription;
use App\Mail\Store\NewOrder;
use App\Mail\Store\NewSubscription;
use App\Mail\Store\ReadyToPrint;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class Store extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'orders',
      'customers',
    ];

    protected $appends = [
      'cutoff_passed',
      'next_delivery_date',
      'next_cutoff_date',
    ];

    protected $casts = [

    ];

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
        return $this->hasMany('App\Subscription')->orderBy('created_at', 'desc');
    }

    public function meals()
    {
        return $this->hasMany('App\Meal')->orderBy('title');
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

    public function categories()
    {
        return $this->hasMany('App\Category')->orderBy('order');
    }

    public function customers()
    {
        return $this->hasMany('App\Customer');
    }

    public function clearCaches() {
      Cache::forget('store_order_ingredients'.$this->id);
    }

    public function getUrl($append = '', $secure = true) {
      $protocol = $secure ? 'https://' : 'http://';
      $url = $protocol.$this->details->domain.'.'.config('app.domain').$append;
      return $url;
    }

    public static function getStore($id)
    {
        return Store::with('storeDetail', 'order')->where('id', $id)->first();
    }

    public static function getStores()
    {
        return Store::with('storeDetail', 'order')->get()->map(function ($store) {
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
                "TotalCustomers" => Order::all()->unique('user_id')->where('store_id', '=', $store->id)->count(),
                "TotalPaid" => '$' . number_format(Order::all()->where('store_id', '=', $store->id)->pluck('amount')->sum(), 2, '.', ','),
            ];
        });
    }

    public function getOrderIngredients($dateRange = [])
    {
        $ingredients = [];

        $orders = $this->orders()->with(['meals', 'meals.ingredients']);

        if($dateRange === []) {
          $orders = $orders->where('delivery_date', $this->getNextDeliveryDate());
        }
        if(isset($dateRange['from'])) {
          $from = Carbon::parse($dateRange['from']);
          $orders = $orders->where('delivery_date', '>=', $from->format('Y-m-d'));
        }
        if(isset($dateRange['to'])) {
          $to = Carbon::parse($dateRange['to']);
          $orders = $orders->where('delivery_date', '<=', $to->format('Y-m-d'));
        }

        $orders = $orders->get();

        foreach ($orders as $order) {
            foreach ($order->meals as $meal) {
                foreach ($meal->ingredients as $ingredient) {

                    $quantity = $ingredient->pivot->quantity;
                    $quantity_unit = $ingredient->pivot->quantity_unit;
                    $quantity_base = $ingredient->pivot->quantity_base;

                    $key = $ingredient->id;

                    if (!isset($ingredients[$key])) {
                        $ingredients[$key] = [
                            'id' => $ingredient->id,
                            'ingredient' => $ingredient,
                            'quantity' => $quantity_base,
                        ];
                    } else {
                        $ingredients[$key]['quantity'] += $quantity_base;
                    }
                }
            }
        }

        return $ingredients;
    }

    public function getOrderMeals($dateRange = [])
    {
        $meals = [];

        $orders = $this->orders()->with(['meals']);
        if($dateRange === []) {
          $orders = $orders->where('delivery_date', $this->getNextDeliveryDate());
        }
        if(isset($dateRange['from'])) {
          $from = Carbon::parse($dateRange['from']);
          $orders = $orders->where('delivery_date', '>=', $from->format('Y-m-d'));
        }
        if(isset($dateRange['to'])) {
          $to = Carbon::parse($dateRange['to']);
          $orders = $orders->where('delivery_date', '<=', $to->format('Y-m-d'));
        }
        $orders = $orders->get();

        foreach ($orders as $order) {
            foreach ($order->meals as $meal) {

              $key = $meal->id;

              if (!isset($meals[$key])) {
                $meals[$key] = [
                  'id' => $key,
                  'meal' => $meal,
                  'quantity' => 1,
                ];
              }
              else {
                $meals[$key]['quantity']++;
              }
            }
        }

        return $meals;
    }

    public function getNextDeliveryDay($weekIndex) {
      $week = Carbon::createFromFormat('N', $weekIndex)->format();
      $date = new Carbon('next '.$week);
      $date->setTimezone($this->settings->timezone);
      $date->setTime(0, 0, 0);
      return $date;
    }
    
    public function getNextDeliveryDate($factorCutoff = false) {
      return $this->settings->getNextDeliveryDates($factorCutoff)[0] ?? null;
    }

    public function getNextCutoffDate() {
      $date = $this->getNextDeliveryDate(false);
      return $date ? $date->subSeconds($this->getCutoffSeconds()) : null;
    }

    public function getOrders($groupBy = null, $dateRange = []) {
      $orders = $this->orders()->with('meals');
      
      if(isset($dateRange['from'])) {
        $from = Carbon::parse($dateRange['from']);
        $orders = $orders->where('delivery_date', '>=', $from->format('Y-m-d'));
      }
      if(isset($dateRange['to'])) {
        $to = Carbon::parse($dateRange['to']);
        $orders = $orders->where('delivery_date', '<=', $to->format('Y-m-d'));
      }

      $orders = $orders->get();

      if($groupBy) {
        $orders = $orders->groupBy($groupBy);
      }

      return $orders;
    }

    public function getOrdersForNextDelivery($groupBy = null) {
      $date = $this->getNextDeliveryDate();
      $orders = $this->orders()->with('meals')->where([
        'delivery_date' => $date->format('Y-m-d'),
      ])->get();

      if($groupBy) {
        $orders = $orders->groupBy($groupBy);
      }

      return $orders;
    }

    public function getPastOrders($groupBy = null) {
      $date = $this->getNextDeliveryDate();
      $orders = $this->orders()->with('meals')->where(
        'delivery_date', '<', $date->format('Y-m-d')
      )->get();

      if($groupBy) {
        $orders = $orders->groupBy($groupBy);
      }

      return $orders;
    }

    public function getFulfilledOrders($groupBy = null) {
      $date = $this->getNextDeliveryDate();
      $orders = $this->orders()->with('meals')->where(
        'fulfilled', '1'
      )->get();

      if($groupBy) {
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
        $email = null;

        switch ($notif) {
            case 'new_order':
                $email = new NewOrder(
                    $data
                );
                break;

            case 'new_subscription':
                $email = new NewSubscription(
                    $data
                );
                break;

            case 'cancelled_subscription':
                $email = new CancelledSubscription([
                    'subscription' => $data,
                ]);
                break;

            case 'ready_to_print':
                $email = new ReadyToPrint($data);
                break;
        }

        if ($email) {
            Mail::to($this->user)->send($email);
            return true;
        }

        return false;
    }

    protected function getCutoffSeconds() {
      $cutoff = $this->settings->cutoff_days * (60 * 60 * 24) + $this->settings->cutoff_hours * (60 * 60);
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

        $cutoff = $this->settings->cutoff_days * (60 * 60 * 24) + $this->settings->cutoff_hours * (60 * 60);

        foreach ($this->settings->delivery_days as $day) {
            $date = Carbon::createFromFormat('D', $day, $this->settings->timezone)->setTime(0, 0, 0);
            $diff = $date->getTimestamp() - $now->getTimestamp() - $cutoff;
            //echo $diff."\r\n";

            // Cutoff passed less than an hour ago
            if ($period === 'hour' && $diff >= -60 * 60 && $diff < 0) {
                return true;
            }
        }

        return false;
    }

    public function getCutoffPassedAttribute() {
      $now = Carbon::now('utc');
      $date = $this->getNextDeliveryDate();
      if(!$date) {
        return false;
      }
      return $now->getTimestamp() > ($date->getTimestamp() - $this->getCutoffSeconds());
    }

    public function getNextCutoffDateAttribute() {
      $date = $this->getNextCutoffDate();
      return $date ? $date->toDateTimeString() : null;
    }

    public function getNextDeliveryDateAttribute() {
      $date = $this->getNextDeliveryDate();
      return $date ? $date->toDateTimeString() : null;
    }
}
