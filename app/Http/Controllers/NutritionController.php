<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Unit;

class NutritionController extends Controller
{
    protected $app_id;
    protected $app_key;
    protected $track_url;
    protected $nutrients_url;
    protected $item_search_url;

    public static $keyMap = [
        'nf_calories' => 'calories',
        'nf_total_fat' => 'totalfat',
        'nf_saturated_fat' => 'satfat',
        'nf_trans_fat' => 'transfat',
        'nf_cholesterol' => 'cholesterol',
        'nf_sodium' => 'sodium',
        'nf_total_carbohydrate' => 'totalcarb',
        'nf_dietary_fiber' => 'fibers',
        'nf_sugars' => 'sugars',
        'nf_protein' => 'proteins',
        'nf_vitamind' => 'vitamind',
        'nf_potassium' => 'potassium',
        'nf_calcium' => 'calcium',
        'nf_iron' => 'iron',
        'nf_addedsugars' => 'sugars'
    ];

    public function __construct(
        $app_id = null,
        $app_key = null,
        $track_url = null,
        $nutrients_url = null,
        $search_url = null
    ) {
        $this->app_id = config('nutritionix.app_id');
        $this->app_key = config('nutritionix.app_key');
        $this->track_url = config('nutritionix.track_url');
        $this->nutrients_url = config('nutritionix.nutrients_url');
        $this->search_url = config('nutritionix.search_url');
        $this->item_search_url = config('nutritionix.item_search_url');
    }

    public function getNutrients(Request $request, $nixId = null)
    {
        $client = new Client();

        if ($nixId) {
            $response = $client->get($this->item_search_url, [
                'headers' => [
                    'x-app-id' => $this->app_id,
                    'x-app-key' => $this->app_key
                ],
                'query' => [
                    'nix_item_id' => $nixId
                ]
            ]);
        } else {
            $body = json_encode([
                'query' => $request->get('query')
            ]);
            $response = $client->post($this->nutrients_url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-app-id' => $this->app_id,
                    'x-app-key' => $this->app_key
                ],
                'body' => $body
            ]);
        }

        $res = json_decode($response->getBody());

        $rawFoods = $res->foods;
        $foods = [];
        $normalizeKeys = ['totalfat', 'satfat', 'totalcarb'];

        foreach ($rawFoods as $rawFood) {
            if ($nixId) {
                $rawFoodserving_weight_grams = 1;
            }
            $foods[] = collect($rawFood)->mapWithKeys(function (
                $item,
                $key
            ) use ($rawFood, $normalizeKeys) {
                if (isset(self::$keyMap[$key])) {
                    $key = self::$keyMap[$key];
                }

                $type = Unit::getType($rawFood->serving_unit);
                $base = Unit::base($type);

                if ($type !== 'unit') {
                    $multiplier = Unit::convert(
                        $rawFood->serving_qty,
                        $rawFood->serving_unit,
                        $base
                    );
                } else {
                    $multiplier = $rawFood->serving_qty;
                }

                return [$key => $item];
            });
        }
        return [
            'foods' => $foods
        ];
    }

    public function searchInstant(Request $request)
    {
        $food = $request->get('search', '');
        $client = new Client();
        $response = $client->post($this->search_url, [
            'headers' => [
                'x-app-id' => $this->app_id,
                'x-app-key' => $this->app_key
            ],
            'form_params' => [
                'query' => $food,
                'detailed' => true,
                'branded' => true,
                'branded_type' => null,
                'branded_region' => 1 // USA
            ]
        ]);

        $res = (string) $response->getBody();
        return $res;
    }
}
