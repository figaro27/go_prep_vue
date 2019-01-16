<?php

namespace App\Http\Controllers\Store;

use App\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Store\StoreController;
use App\Exportable\Store\MealQuantities;
use App\Exportable\Store\IngredientQuantities;
use App\Exportable\Store\Orders;
use App\Exportable\Store\PackingSlips;

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

        case 'packing_slips':
          $exportable = new PackingSlips($this->store);
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
