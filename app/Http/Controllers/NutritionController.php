<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Nutrition;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class NutritionController extends Controller
{

	protected $app_id, $app_key, $track_url, $nutrients_url;

    public function __construct($app_id = null, $app_key = null, $track_url = null, $nutrients_url = null, $search_url = null)
    {
        $this->app_id = config('nutritionix.app_id');
        $this->app_key = config('nutritionix.app_key');
        $this->track_url = config('nutritionix.track_url');
        $this->nutrients_url = config('nutritionix.nutrients_url');
        $this->search_url = config('nutritionix.search_url');
    }


    public function getNutrients(Request $query){
    	$client = new Client();
        $response = $client->post($this->nutrients_url, [
        	'headers' => [
        		'Content-Type' => 'application/json',
        		'x-app-id' => $this->app_id,
        		'x-app-key' => $this->app_key
        	], 
        	'body' => 
        	json_encode([
        		'query' => (string) $query,
        	]),
        ]);

        $res = $response->getBody();
        return $res;
    }

    public function searchInstant(Request $search){
    	$food = $search->search;
    	$client = new Client();
    	$response = $client->get($this->search_url . $food, [
    		'headers' => [
        		'Content-Type' => 'application/json',
        		'x-app-id' => $this->app_id,
        		'x-app-key' => $this->app_key
        	] 
    	]);

    	$res = $response->getBody();
    	return $res;

    }
}
