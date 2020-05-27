<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SmsList;
use App\SMSContact;
use stdClass;

class SmsSetting extends Model
{
    protected $headers = [
        'X-TM-Username' => 'mikesoldano',
        'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    protected $casts = [
        'autoAddCustomers' => 'boolean',
        'autoSendDelivery' => 'boolean',
        'autoSendOrderConfirmation' => 'boolean'
    ];

    protected $guarded = [];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function addNewCustomerToContacts($customer)
    {
        $contact = $customer;

        $listId = SmsList::where('store_id', $this->store->id)
            ->pluck('list_id')
            ->first();

        $phone = (int) preg_replace('/[^0-9]/', '', $contact['phone']);
        if (strlen((string) $phone) === 10) {
            $phone = 1 . $phone;
        }

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://rest.textmagic.com/api/v2/contacts',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'phone' => $phone,
                        'lists' => $listId,
                        'firstName' => $contact['firstname'],
                        'lastName' => $contact['lastname']
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();
            $smsContactId = json_decode($body)->id;

            $smsContact = new SmsContact();
            $smsContact->store_id = $this->store->id;
            $smsContact->contact_id = $smsContactId;
            $smsContact->save();
        } catch (\Exception $e) {
        }
    }
}
