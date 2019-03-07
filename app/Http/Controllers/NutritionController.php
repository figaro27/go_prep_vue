<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class NutritionController extends Controller
{

    protected $app_id, $app_key, $track_url, $nutrients_url;

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
        'nf_addedsugars' => 'sugars',
    ];

    public function __construct($app_id = null, $app_key = null, $track_url = null, $nutrients_url = null, $search_url = null)
    {
        $this->app_id = config('nutritionix.app_id');
        $this->app_key = config('nutritionix.app_key');
        $this->track_url = config('nutritionix.track_url');
        $this->nutrients_url = config('nutritionix.nutrients_url');
        $this->search_url = config('nutritionix.search_url');
    }

    public function getNutrients(Request $request)
    {
        $client = new Client();
        $response = $client->post($this->nutrients_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-app-id' => $this->app_id,
                'x-app-key' => $this->app_key,
            ],
            'body' =>
            json_encode([
                'query' => $request->get('query'),
            ]),
        ]);

        $res = json_decode($response->getBody());

        $rawFoods = $res->foods;
        $foods = [];
        $normalizeKeys = ['totalfat', 'satfat', 'totalcarb'];

        foreach ($rawFoods as $rawFood) {
            $foods[] = collect($rawFood)->mapWithKeys(function ($item, $key) use ($rawFood, $normalizeKeys) {
                if(isset(self::$keyMap[$key])) {
                  $key = self::$keyMap[$key];
                }
                if(in_array($key, $normalizeKeys)) {
                  $item = $item / $rawFood->serving_weight_grams;
                }
                return [$key => $item];
            });
        }
        return [
            'foods' => $foods,
        ];
    }

    public function searchInstant(Request $request)
    {
        $food = $request->get('search', '');
        $client = new Client();
        $response = $client->post($this->search_url, [
            'headers' => [
                'x-app-id' => $this->app_id,
                'x-app-key' => $this->app_key,
            ],
            'form_params' => [
              'query' => $food,
              'detailed' => true,
            ]
        ]);

        $res = (string) $response->getBody();
        return $res;

    }
}
