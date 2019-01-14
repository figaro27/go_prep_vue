<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;

class PackingSlips
{
    use Exportable;

    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function exportData()
    {
        $ingredients = collect($this->store->getOrderIngredients());

        $ingredients = $ingredients->map(function ($item, $id) {
            return [
                'id' => $id,
                'quantity' => $item['quantity'],
            ];
        })->toArray();

        return $ingredients;
    }

    public function exportPdfView()
    {
        return 'reports.order_ingredients_pdf';
    }
}
