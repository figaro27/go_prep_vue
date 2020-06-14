<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\SmsContact;
use App\SmsList;
use App\Store;
use App\Customer;

class AddContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:addContacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates GoPrep master list, master list for each store, and puts all store\'s customers in their store lists. One time job.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    protected $baseURL = 'https://rest.textmagic.com/api/v2/lists';
    protected $headers = [
        'X-TM-Username' => 'mikesoldano',
        'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    public function handle()
    {
        // Just add MQS store list for testing

        // $client = new \GuzzleHttp\Client();
        // $res = $client->request('POST', $this->baseURL, [
        //     'headers' => $this->headers,
        //     'form_params' => [
        //         'name' => 'All Contacts - MQS'
        //     ]
        // ]);
        // $status = $res->getStatusCode();
        // $body = $res->getBody();

        // $smsList = new SmsList();
        // $smsList->store_id = 13;
        // $smsList->list_id = json_decode($body)->id;
        // $smsList->save();

        // $listId = json_decode($body)->id;

        // return;

        // Create GoPrep Master list

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $this->baseURL, [
            'headers' => $this->headers,
            'form_params' => [
                'name' => 'GoPrep Contacts'
            ]
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody();

        $smsList = new SmsList();
        $smsList->store_id = 13;
        $smsList->list_id = json_decode($body)->id;
        $smsList->save();

        $masterListId = json_decode($body)->id;

        // Create master list for each active store
        // Original active list
        // 40,
        // 74,
        // 88,
        // 111,
        // 112,
        // 116,
        // 118,
        // 119,
        // 120,
        // 121,
        // 123,
        // 125,
        // 127,
        // 131,
        // 132,
        // 136,
        // 137,
        // 139,
        // 140,
        // 141,
        // 144,
        // 146,
        // 148,
        // 149,
        // 150,
        // 151,
        // Newly added
        // 70,
        // 99,
        // 117,
        // 122,
        // 128,
        // 152,
        // 153,
        // 154,
        // 155,
        // 156,
        // 157
        // Ignore list
        // 14,
        // 108,
        // 109,
        // 110,
        // 98,
        // 100,
        // 105
        $stores = Store::whereIn('id', array(16))->get();
        foreach ($stores as $store) {
            // Create store master list
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->baseURL, [
                'headers' => $this->headers,
                'form_params' => [
                    'name' => 'All Contacts - ' . $store->details->name
                ]
            ]);
            $status = $res->getStatusCode();
            $body = $res->getBody();

            $smsList = new SmsList();
            $smsList->store_id = $store->id;
            $smsList->list_id = json_decode($body)->id;
            $smsList->save();

            $listId = json_decode($body)->id;

            // Add all customers as contacts and into the master list and store list they belong to
            $customers = Customer::where('store_id', $store->id)->get();

            foreach ($customers as $customer) {
                $storeListId = SmsList::where('store_id', $customer->store_id)
                    ->pluck('list_id')
                    ->first();
                $storeId = SmsList::where('store_id', $customer->store_id)
                    ->pluck('store_id')
                    ->first();
                $lists = $masterListId . ',' . $storeListId;
                // Get country prefix
                $phone =
                    (int) 1 . preg_replace('/[^0-9]/', '', $customer->phone);
                try {
                    $client = new \GuzzleHttp\Client();
                    $res = $client->request(
                        'POST',
                        'https://rest.textmagic.com/api/v2/contacts',
                        [
                            'headers' => $this->headers,
                            'form_params' => [
                                'phone' => $phone,
                                'lists' => $lists,
                                'firstName' => $customer->firstname,
                                'lastName' => $customer->lastname,
                                'email' => $customer->email
                            ]
                        ]
                    );
                    $status = $res->getStatusCode();
                    $body = $res->getBody();

                    $smsContact = new SmsContact();
                    $smsContact->store_id = $customer->store_id;
                    $smsContact->contact_id = json_decode($body)->id;
                    $smsContact->save();
                } catch (\Exception $e) {
                    if (
                        strpos(
                            $e,
                            'Phone number already exists in your contacts.'
                        ) !== false
                    ) {
                        $this->updateExistingContact($customer, $storeId);
                    }
                }
            }
        }
    }

    public function updateExistingContact($contact, $storeId)
    {
        $phone = (int) preg_replace('/[^0-9]/', '', $contact['phone']);
        if (strlen((string) $phone) === 10) {
            $phone = 1 . $phone;
        }

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            'https://rest.textmagic.com/api/v2/contacts/phone/' . $phone,
            ['headers' => $this->headers]
        );
        $body = $res->getBody();
        $contactId = json_decode($body)->id;

        $firstName = isset($contact['firstName'])
            ? $contact['firstName']
            : json_decode($body)->firstName;
        $lastName = isset($contact['lastName'])
            ? $contact['lastName']
            : json_decode($body)->lastName;

        // Get all lists in which the existing contact is included
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            'https://rest.textmagic.com/api/v2/contacts/' .
                $contactId .
                '/lists',
            ['headers' => $this->headers]
        );
        $body = $res->getBody();
        $existingLists = json_decode($body)->resources;
        $lists = [];
        foreach ($existingLists as $list) {
            array_push($lists, $list->id);
        }
        array_push(
            $lists,
            SMSList::where('store_id', $storeId)
                ->pluck('list_id')
                ->first()
        );

        $lists = implode(',', $lists);

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'PUT',
            'https://rest.textmagic.com/api/v2/contacts/' . $contactId,
            [
                'headers' => $this->headers,
                'form_params' => [
                    'phone' => $phone,
                    'lists' => $lists,
                    'firstName' => $firstName,
                    'lastName' => $lastName
                ]
            ]
        );
        $status = $res->getStatusCode();
        $body = $res->getBody();

        // Add existing contact to the store
        $contactExists = SMSContact::where([
            'store_id' => $storeId,
            'contact_id' => $contactId
        ])->first();
        if (!$contactExists) {
            $newContact = new SMSContact();
            $newContact->store_id = $storeId;
            $newContact->contact_id = $contactId;
            $newContact->save();
        }
    }
}
