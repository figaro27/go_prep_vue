<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;

class Meals
{
    use Exportable;

    protected $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function exportData()
    {
        return $this->store->meals->map(function($meal) {
          return [
            $meal->active ? 'Active' : 'Inactive',
            $meal->title,
            $meal->categories->implode('category', ', '),
            $meal->tags->implode('tag', ', '),
            $meal->active_orders,
            $meal->lifetime_orders,
            $meal->created_at
          ];
        })->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.meals_pdf';
    }
}
