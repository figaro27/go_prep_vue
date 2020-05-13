<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SMSController extends StoreController
{
    public function SendTestMessage()
    {
        $baseURL = 'https://rest.textmagic.com/api/v2/message';
        $message = 'Test';

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'POST',
            $baseURL,
            [
                'headers' => [
                    'X-TM-Username' => 'mikesoldano',
                    'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg'
                ]
            ],
            [
                'text' => urlencode($message),
                'phones' => 3475269628
            ]
        );
        $status = $res->getStatusCode();
        $body = json_decode((string) $res->getBody());

        return $body;
    }
}
