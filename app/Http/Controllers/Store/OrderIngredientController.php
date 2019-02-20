<?php

namespace App\Http\Controllers\Store;

use App\Unit;
use App\Utils\Data\ExportsData;
use App\Utils\Data\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class OrderIngredientController extends StoreController
{
    use ExportsData;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->has('delivery_dates')) {
            $ingredients = Cache::remember('store_order_ingredients' . $this->store->id, 10, function () {
                return collect($this->store->getOrderIngredients());
            });
        } else {
            $ddates = json_decode($request->get('delivery_dates'));

            $dates = [];
            if($ddates->from) $dates['from'] = Carbon::parse($ddates->from);
            if($ddates->to) $dates['to'] = Carbon::parse($ddates->to);

            $ingredients = collect($this->store->getOrderIngredients($dates));
        }

        $ingredients = $ingredients->map(function ($item, $id) {
            return [
                'id' => $id,
                'quantity' => $item['quantity'],
            ];
        })->toArray();

        return $ingredients;
    }

    public function exportData($type)
    {
        $orderIngredients = collect($this->store->getOrderIngredients());
        $units = collect($this->store->units)->keyBy('ingredient_id');
        $ingredients = $this->store->ingredients->keyBy('id');

        $data = $orderIngredients->map(function ($orderIngredient) use ($units, $ingredients) {
            $ingredient = $ingredients->get($orderIngredient['id']);
            if ($units->has($ingredient->id)) {
                $unit = $units->get($ingredient->id)->unit;
                $quantity = Unit::convert(
                    $orderIngredient['quantity'],
                    Format::baseUnit($ingredient->unit_type),
                    $unit
                );
            } else {
                $unit = Format::baseUnit($ingredient->unit_type);
                $quantity = ceil($orderIngredient['quantity']);
            }

            return [
                $ingredient->food_name,
                ceil($quantity),
                $unit,
            ];
        })->sortBy('0');

        $data = array_merge([
            [
                'Ingredient', 'Quantity', 'Unit',
            ],
        ], $data->toArray());
        return $data;
    }

    public function exportPdfView()
    {
        return 'reports.order_ingredients_pdf';
    }
}
