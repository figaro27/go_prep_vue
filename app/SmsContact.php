<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SmsList;

class SmsContact extends Model
{
    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public static function addNumbersToContacts($phones, $storeId)
    {
        $listId = SmsList::where('store_id', $storeId)
            ->pluck('list_id')
            ->first();

        foreach ($phones as $phone) {
            $phone = (int) preg_replace('/[^0-9]/', '', $phone);

            if (strlen((string) $phone) === 10) {
                $phone = 1 . $phone;
            }

            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->request(
                    'POST',
                    'https://rest.textmagic.com/api/v2/contacts',
                    [
                        'headers' => [
                            'X-TM-Username' => 'mikesoldano',
                            'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
                            'Content-Type' =>
                                'application/x-www-form-urlencoded'
                        ],
                        'form_params' => [
                            'phone' => $phone,
                            'lists' => $listId
                        ]
                    ]
                );
                $status = $res->getStatusCode();
                $body = $res->getBody();

                $smsContact = new SmsContact();
                $smsContact->store_id = $storeId;
                $smsContact->contact_id = json_decode($body)->id;
                $smsContact->save();
            } catch (\Exception $e) {
            }
        }
    }
}
