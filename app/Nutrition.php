<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

use Illuminate\Database\Eloquent\Model;

class Nutrition extends Model
{
    protected $app_id, $app_key;

    public function __construct($app_id = null, $app_key = null)
    {
        $this->app_id = config('nutritionix.app_id');
        $this->app_key = config('nutritionix.app_key');
        $this->track_url = config('nutritionix.track_url');
    }


    public static function getNutrition($query){

    $client = new Client(['base_uri' => 'https://trackapi.nutritionix.com/v2']);
        $response = $client->post('https://trackapi.nutritionix.com/v2/natural/nutrients/', [
        	'headers' => [
        		'Content-Type' => 'application/json',
        		'x-app-id' => '1cb5966f',
        		'x-app-key' => '95534f28cd549764a32c5363f13699a9'
        	], 
        	'body' => 
        	json_encode([
        		'query' => 'for breakfast i ate 2 eggs, bacon, and french toast',
        	]),
        ]);

        $res = $response->getBody();
        return $res;
        return $query;

    }



}

