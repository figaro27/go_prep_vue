<?php

namespace App\Http\Controllers\Store;

use App\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Store\StoreController;
use App\Exportable\Store\MealQuantities;
use App\Exportable\Store\IngredientQuantities;
use App\Exportable\Store\Orders;
use App\Exportable\Store\PackingSlips;
use App\Exportable\Store\Customers;
use App\Exportable\Store\Meals;
use App\Exportable\Store\MealOrders;
use App\Exportable\Store\OrdersByCustomer;
use App\Exportable\Store\PastOrders;

class PrintController extends StoreController
{
    public function print(Request $request, $report, $format = 'pdf') {
      switch($report) {
        case 'meal_quantities':
          $exportable = new MealQuantities($this->store);
        break;

        case 'ingredient_quantities':
          $exportable = new IngredientQuantities($this->store);
        break;

        case 'orders':
          $exportable = new Orders($this->store);
        break;

        case 'past_orders':
          $exportable = new PastOrders($this->store);
        break;

        case 'orders_by_customer':
          $exportable = new OrdersByCustomer($this->store);
        break;

        case 'packing_slips':
          $exportable = new PackingSlips($this->store);
        break;

        case 'customers':
          $exportable = new Customers($this->store);
        break;

        case 'meals':
          $exportable = new Meals($this->store);
        break;

        case 'meal_orders':
          $exportable = new MealOrders($this->store);
        break;
      }

      try {
        $url = $exportable->export($format);
        return [
          'url' => $url
        ];
      }
      catch(\Exception $e) {
        return response()->json([
          'error' => $e->getMessage(),
        ], 500);
      }

      
    }
}
