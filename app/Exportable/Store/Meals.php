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

    public function exportData($type = null)
    {
        return $this->store->meals->map(function($meal) {
          return [
            $meal->active ? 'Active' : 'Inactive',
            $meal->title,
            $meal->categories->implode('category', ', '),
            $meal->tags->implode('tag', ', '),
            $meal->allergies->implode('title', ', '),
            $meal->subscriptions->count(),
            $meal->lifetime_orders,
          ];
        })->prepend(['Status', 'Title', 'Categories', 'Tags', 'Contains', 'Meal Plans', 'Lifetime Orders', ])->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.meals_pdf';
    }
}
