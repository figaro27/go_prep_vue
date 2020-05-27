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
    protected $description = 'Creates GoPrep master list, master list for each store, and puts all store\'s customers in their master list. One time job.';

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
        $smsList->store_id = 0;
        $smsList->list_id = json_decode($body)->id;
        $smsList->save();

        $masterListId = json_decode($body)->id;

        // Add all customers as contacts and into the master list

        $customers = Customer::all();

        foreach ($customers as $customer) {
            // Get country prefix
            $phone = (int) 1 . preg_replace('/[^0-9]/', '', $customer->phone);
            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->request(
                    'POST',
                    'https://rest.textmagic.com/api/v2/contacts',
                    [
                        'headers' => $this->headers,
                        'form_params' => [
                            'phone' => $phone,
                            'lists' => $masterListId,
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
            }
        }

        // Create master list for each store

        $stores = Store::get()->take(3);
        foreach ($stores as $store) {
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

            // Assign contacts to store

            $contacts = SMSContact::where('store_id', $store->id)->get();
            $contactIds = [];
            foreach ($contacts as $contact) {
                array_push($contactIds, $contact->contact_id);
            }
            $contactIds = implode(',', $contactIds);
            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->request(
                    'PUT',
                    $this->baseURL . '/' . $listId . '/contacts',
                    [
                        'headers' => $this->headers,
                        'form_params' => [
                            'contacts' => $contactIds
                        ]
                    ]
                );
                $status = $res->getStatusCode();
                $body = $res->getBody();
            } catch (\Exception $e) {
            }
        }
    }
}
