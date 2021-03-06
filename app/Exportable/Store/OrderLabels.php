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
        $this->params->put('store', $this->store->details->name);
        $this->params->put('report', 'Order Labels');
        $this->params->put('date', Carbon::now()->format('m-d-Y'));

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

                    if (
                        count($order->meals) > 0 ||
                        count($order->lineItemsOrders) > 0
                    ) {
                        return true;
                    }
                });
        }

        $storeIds = [];
        foreach ($orders as $order) {
            if (!in_array($order->store_id, $storeIds)) {
                $storeIds[] = $order->store_id;
            }
        }
        $storeLogos = [];

        foreach ($storeIds as $storeId) {
            $store = Store::where('id', $storeId)->first();
            try {
                $logo = \App\Utils\Images::encodeB64(
                    $store->details->logo['url']
                );
            } catch (\Exception $e) {
                $logo = $store->details->logo['url'];
            }
            $storeLogos[$storeId] = $logo;
        }

        $orders = $orders->map(function ($order) {
            try {
                $logo = $storeLogos[$order->store_id];
            } catch (\Exception $e) {
                $logo = $order->store->details->logo['url'];
            }

            return [
                'dailyOrderNumber' => $order->dailyOrderNumber,
                'orderNumber' => $order->order_number,
                'customer' => $order->customer_name,
                'address' => $order->customer_address,
                'city' => $order->customer_city,
                'state' => $order->customer_state,
                'zip' => $order->customer_zip,
                'phone' => $order->customer_phone,
                'email' => $order->user->email,
                'amount' => '$' . number_format($order->amount, 2),
                'balance' => '$' . number_format($order->balance, 2),
                'created_at' => $order->created_at->format('D, m/d/Y'),
                'deliveryDate' => !$order->isMultipleDelivery
                    ? $order->delivery_date->format('D, m/d/Y')
                    : 'Multiple',
                'deliveryInstructions' => $order->customer_delivery,
                'pickup' => $order->pickup,
                'transferType' => $order->transfer_type,
                'pickupLocation' => isset($order->pickup_location)
                    ? $order->pickup_location->name
                    : null,
                'logo' => $logo
                // 'numberOfItems' => $order->numberOfItems
            ];
        });

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

        if (isset($this->params['order_id'])) {
            $filename =
                'public/' .
                $this->params['store'] .
                '_order_labels_' .
                $orders[0]['customer'] .
                '_' .
                $orders[0]['orderNumber'] .
                '_' .
                $this->params['date'] .
                '.pdf';
        } else {
            $filename =
                'public/' .
                $this->params['store'] .
                '_order_labels_' .
                $this->params['date'] .
                '.pdf';
        }

        $width = $this->store->orderLabelSettings->width;
        $height = $this->store->orderLabelSettings->height;

        // Temporary solution
        $testStore = Store::where('id', 13)->first();
        $whiteSpace = $testStore->details->logo['url'];

        $vars = [
            'orders' => $orders,
            'orderLabelSettings' => $this->store->orderLabelSettings,
            'website' => $this->store->settings->website,
            'social' => $this->store->details->social,
            'params' => $this->params,
            'delivery_dates' => $this->getDeliveryDates(),
            'body_classes' => implode(' ', [$this->orientation]),
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
