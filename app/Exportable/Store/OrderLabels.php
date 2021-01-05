<?php

namespace App\Exportable\Store;

use App\Meal;
use App\Store;
use App\MealSize;
use App\ProductionGroup;
use App\LineItem;
use App\Exportable\Exportable;
use Illuminate\Support\Carbon;
use mikehaertl\wkhtmlto\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use App\MealOrder;
use App\MealAddon;
use App\MealComponentOption;
use App\Utils\Data\Format;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;
use App\Ingredient;
use App\ReportRecord;

class OrderLabels
{
    use Exportable;

    protected $store;
    protected $allDates;
    protected $orderId;

    public function __construct(Store $store, $params = [])
    {
        $this->store = $store;
        $this->params = collect($params);
        $this->orientation = 'portrait';
        $this->page = $params->get('page', 1);
        $this->perPage = 10000;
        $this->orderId = $params->get('order_id');
    }

    public function filterVars($vars)
    {
        $vars['dates'] = $this->allDates;
        $vars['body_classes'] = implode(' ', [$this->orientation, 'label']);
        return $vars;
    }

    public function exportData($type = null)
    {
        $params = $this->params;

        if ($this->orderId) {
            $orders = $this->store
                ->orders()
                ->where([
                    'paid' => 1,
                    // 'voided' => 0,
                    'id' => $this->orderId
                ])
                ->get();
        } else {
            $orders = $this->store
                ->getOrders(null, $this->getDeliveryDates())
                ->filter(function ($order) use ($params) {
                    if (
                        $params->has('fulfilled') &&
                        $order->fulfilled != $params->get('fulfilled')
                    ) {
                        return false;
                    }

                    return true;
                });
        }

        $orders = $orders->map(function ($order) {
            return [
                'dailyOrderNumber' => $order->dailyOrderNumber,
                'orderNumber' => $order->order_number,
                'firstName' => $order->user->details->firstname,
                'lastName' => $order->user->details->lastname,
                'address' => $order->user->details->address,
                'city' => $order->user->details->city,
                'state' => $order->user->details->state,
                'zip' => $order->user->details->zip,
                'phone' => $order->user->details->phone,
                'email' => $order->user->email,
                'amount' => '$' . number_format($order->amount, 2),
                'balance' => '$' . number_format($order->balance, 2),
                'created_at' => $order->created_at->format('D, m/d/Y'),
                'deliveryDate' => !$order->isMultipleDelivery
                    ? $order->delivery_date->format('D, m/d/Y')
                    : 'Multiple',
                'deliveryInstructions' => $order->user->details->delivery,
                'pickup' => $order->pickup,
                'transferType' => $order->transfer_type,
                'pickupLocation' => isset($order->pickup_location)
                    ? $order->pickup_location->name
                    : null
                // 'numberOfItems' => $order->numberOfItems
            ];
        });

        $orders = $orders
            ->whereHas('meal_orders')
            ->orWhereHas('meal_package_orders')
            ->orWhereHas('lineItemsOrders');

        $orders = $orders->toArray();

        return $orders;
    }

    public function export($type)
    {
        if (!in_array($type, ['pdf', 'b64'])) {
            return null;
        }

        Log::info('Starting label print');

        $orders = $this->exportData();

        Log::info('Found ' . count($orders) . ' orders');

        if (!count($orders)) {
            throw new \Exception('No orders');
        }

        $filename = 'public/' . md5(time()) . '.pdf';

        $width = $this->store->reportSettings->o_lab_width;
        $height = $this->store->reportSettings->o_lab_height;

        try {
            $logo = \App\Utils\Images::encodeB64(
                $this->store->details->logo['url']
            );
        } catch (\Exception $e) {
            $logo = $this->store->details->logo['url'];
        }

        // Temporary solution
        $testStore = Store::where('id', 13)->first();
        $whiteSpace = $testStore->details->logo['url'];

        $vars = [
            'orders' => $orders,
            'reportSettings' => $this->store->reportSettings,
            'website' => $this->store->settings->website,
            'social' => $this->store->details->social,
            'params' => $this->params,
            'delivery_dates' => $this->getDeliveryDates(),
            'body_classes' => implode(' ', [$this->orientation]),
            'logo' => $logo,
            'whiteSpace' => $whiteSpace
        ];

        $html = view($this->exportPdfView(), $vars)->render();

        Log::info('Page HTML: ' . $html);

        $page = Browsershot::html($html)
            ->paperSize($width, $height, 'in')
            ->waitUntilNetworkIdle()
            ->waitForFunction('window.status === "ready"', 100, 3000);

        $output = $page->pdf();

        Log::info('Saved to ' . $filename);

        $reportRecord = ReportRecord::where(
            'store_id',
            $this->store->id
        )->first();
        $reportRecord->order_labels += 1;
        $reportRecord->update();

        if ($type === 'pdf') {
            Storage::disk('local')->put($filename, $output);
            return Storage::url($filename);
        } elseif ($type === 'b64') {
            return base64_encode($output);
        }
    }

    public function exportPdfView()
    {
        return 'reports.order_labels_pdf';
    }
}
