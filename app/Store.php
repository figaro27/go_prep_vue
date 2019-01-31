<?php

namespace App;

use App\Mail\Store\CancelledSubscription;
use App\Mail\Store\NewOrder;
use App\Mail\Store\NewSubscription;
use App\Mail\Store\ReadyToPrint;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

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

    public function getOrderIngredients($dates = null)
    {
        $ingredients = [];

        $orders = $this->orders()->with(['meals', 'meals.ingredients']);

        if($dates && count($dates) > 0) {
          $dates = collect($dates);

          $orders = $orders->whereIn('delivery_date', $dates->map(function($date) {
            return Carbon::parse($date);
          }));
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

    public function getOrderMeals($dates = null)
    {
        $meals = [];

        $orders = $this->orders()->with(['meals']);
        if($dates && count($dates) > 0) {
          $dates = collect($dates);

          $orders = $orders->whereIn('delivery_date', $dates->map(function($date) {
            return Carbon::parse($date);
          }));
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

    public function getNextDeliveryDate() {
      return $this->settings->getNextDeliveryDates()[0] ?? null;
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
                $email = new NewOrder([
                    'order' => $data,
                ]);
                break;

            case 'new_subscription':
                $email = new NewSubscription([
                    'subscription' => $data,
                ]);
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

    public function cutoffPassed()
    {
        if (!$this->settings || !is_array($this->settings->delivery_days)) {
            return false;
        }

        $today = Carbon::today();

        $cutoff = $this->settings->cutoff_days * (60 * 60 * 24) + $this->settings->cutoff_hours * (60 * 60);

        foreach ($this->settings->delivery_days as $day) {
            $date = Carbon::createFromFormat('D', $day);
            $diff = $date->getTimestamp() - $today->getTimestamp();

            // Cutoff passed less than an hour ago
            if ($diff <= 60 * 60) {
                return true;
            }
        }

        return false;
    }
}
