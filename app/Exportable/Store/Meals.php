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
        $menu = $this->store->meals->map(function($meal) {
          return [
            $meal->active ? 'Active' : 'Inactive',
            $meal->title,
            $meal->categories->implode('category', ', '),
            $meal->tags->implode('tag', ', '),
            $meal->allergies->implode('title', ', '),
            $meal->lifetime_orders,
            $meal->created_at->format('m/d/Y')
          ];
        });

        if($type !== 'pdf'){
            $menu->prepend(['Status', 'Title', 'Categories', 'Tags', 'Contains', 'Lifetime Orders', 'Added' ]);
        }
        
        return $menu->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.meals_pdf';
    }
}
