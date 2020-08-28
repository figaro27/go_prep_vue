<?php

namespace App\Http\Controllers\Store;

use App\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Store\StoreController;
use App\Exportable\Store\MealQuantities;
use App\Exportable\Store\IngredientQuantities;
use App\Exportable\Store\IngredientsByMeal;
use App\Exportable\Store\Orders;
use App\Exportable\Store\PackingSlips;
use App\Exportable\Store\Customers;
use App\Exportable\Store\Meals;
use App\Exportable\Store\MealOrders;
use App\Exportable\Store\OrdersByCustomer;
use App\Exportable\Store\PastOrders;
use App\Exportable\Store\Subscriptions;
use App\Exportable\Store\MealsIngredients;
use App\Exportable\Store\DeliveryRoutes;
use App\Exportable\Store\DeliveryRoutesLivotis;
use App\Exportable\Store\Payments;
use App\Exportable\Store\Labels;
use App\Exportable\Store\Leads;
use App\Exportable\Store\Referrals;
use Illuminate\Support\Facades\Storage;

class PrintController extends StoreController
{
    public function print(Request $request, $report, $format = 'pdf')
    {
        $params = collect($request->all());

        switch ($report) {
            case 'ingredient_quantities':
                $exportable = new IngredientQuantities($this->store, $params);
                break;

            case 'ingredients_by_meal':
                $exportable = new IngredientsByMeal($this->store, $params);
                break;

            case 'orders':
                $exportable = new Orders($this->store, $params);
                break;

            case 'orders_by_customer':
                $exportable = new OrdersByCustomer($this->store, $params);
                break;

            case 'subscriptions':
                $exportable = new Subscriptions($this->store, $params);
                break;

            case 'packing_slips':
                $exportable = new PackingSlips($this->store, $params);
                break;

            case 'customers':
                $exportable = new Customers($this->store, $params);
                break;

            case 'meals':
                $exportable = new Meals($this->store, $params);
                break;

            case 'labels':
                $exportable = new Labels($this->store, $params);
                break;

            case 'meal_orders':
                $exportable = new MealOrders($this->store, $params);
                break;

            case 'meal_orders_all':
                $exportable = new MealOrders($this->store, $params);

                try {
                    $url = $exportable->exportAll($format);
                    return [
                        'url' => $url
                    ];
                } catch (\Exception $e) {
                    return response()->json(
                        [
                            'error' => $e->getMessage()
                        ],
                        500
                    );
                }
                break;
            case 'meals_ingredients':
                $exportable = new MealsIngredients($this->store, $params);
                break;

            case 'delivery_routes':
                $exportable = new DeliveryRoutes($this->store, $params);
                break;

            case 'delivery_routes_livotis':
                $exportable = new DeliveryRoutesLivotis($this->store, $params);
                break;

            case 'payments':
                $exportable = new Payments($this->store, $params);
                break;

            case 'leads':
                $exportable = new Leads($this->store, $params);
                break;
            case 'referrals':
                $exportable = new Referrals($this->store, $params);
                break;
        }

        try {
            $url = $exportable->export($format);
            return [
                'url' => $url,
                'next_page' => (int) $exportable->getPage()
            ];
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => $e->getMessage()
                ],
                500
            );
        }
    }

    /**
     * Signs QZ messages with private key
     *
     * @param Request $request
     * @return void
     */
    public function sign(Request $request)
    {
        $key = config('qz.keyFile');
        $keyContents = Storage::disk('local')->get($key);

        $req = $request->get('request');
        $privateKey = openssl_get_privatekey($keyContents, null);

        $signature = null;
        openssl_sign($req, $signature, $privateKey, "sha512");

        if ($signature) {
            return response(base64_encode($signature))->header(
                'Content-type',
                'text/plain'
            );
        }

        return response()->json(
            [
                'error' => 'Failed to sign message'
            ],
            500
        );
    }

    public function certficate(Request $request)
    {
        $key = config('qz.certificateFile');
        $keyContents = Storage::disk('local')->get($key);

        return response($keyContents)->header('Content-type', 'text/plain');
    }
}
