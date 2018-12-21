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

    public function storeDetail()
    {
        return $this->hasOne('App\StoreDetail');
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

      foreach($this->orders as $order) {
        foreach($order->meals as $meal) {
          foreach($meal->ingredients as $ingredient) {
            if(!isset($ingredients[$ingredient->id])) {
              $ingredients[$ingredient->id] = $ingredient->quantity;
            }
            else {
              $ingredients[$ingredient->id] += $ingredient->quantity;
            }
          }
        }
      }

      return $ingredients;
    }
}
