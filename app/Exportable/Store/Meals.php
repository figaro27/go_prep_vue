<?php

namespace App\Exportable\Store;

use App\Exportable\Exportable;
use App\Store;
use App\User;
use App\ReportRecord;
use Illuminate\Support\Carbon;

class Meals
{
    use Exportable;

    protected $store;

    public function __construct(Store $store, $params = [])
    {
        $this->params = $params;
        $this->store = $store;
    }

    public function exportData($type = null)
    {
        $this->params->put('store', $this->store->details->name);
        $this->params->put('report', 'Meals');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));

        $menu = $this->store->meals->map(function ($meal) {
            return [
                $meal->active ? 'Active' : 'Inactive',
                $meal->title,
                $meal->categories->implode('category', ', '),
                $meal->tags->implode('tag', ', '),
                $meal->allergies->implode('title', ', '),
                // $meal->lifetime_orders,
                $meal->created_at->format('m/d/Y')
            ];
        });

        if ($type !== 'pdf') {
            $menu->prepend([
                'Status',
                'Title',
                'Categories',
                'Tags',
                'Contains',
                // 'Lifetime Orders',
                'Added'
            ]);
        }

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->meals += 1;
        $reportRecord->update();

        return $menu->toArray();
    }

    public function exportPdfView()
    {
        return 'reports.meals_pdf';
    }
}
