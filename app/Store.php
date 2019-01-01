<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    public function user()
    {
        return $this->hasOne('App\User');
    }

    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    public function meals()
    {
        return $this->hasMany('App\Meal');
    }

    public function ingredients()
    {
        return $this->hasMany('App\Ingredient');
    }

    public function units()
    {
        return $this->hasMany('App\StoreUnit');
    }

    public function storeDetail()
    {
        return $this->hasOne('App\StoreDetail');
    }

    public function settings() {
        return $this->hasOne('App\StoreSetting');
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

    public function getOrderIngredients() {
      $ingredients = [];

      $orders = $this->orders()->with(['meals'])->get();

      foreach($orders as $order) {
        foreach($order->meals as $meal) {
          foreach($meal->ingredients()->get() as $ingredient) {
            
            $quantity = $ingredient->pivot->quantity;
            $quantity_unit = $ingredient->pivot->quantity_unit;
            $quantity_base = $ingredient->pivot->quantity_base;

            $key = $ingredient->id;

            if(!isset($ingredients[$key])) {
              $ingredients[$key] = [
                'id' => $ingredient->id,
                'ingredient' => $ingredient,
                'quantity' => $quantity_base
              ];
            }
            else {
              $ingredients[$key]['quantity'] += $quantity_base;
            }
          }
        }
      }

      return $ingredients;
    }
}
