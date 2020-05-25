<?php

namespace App\Http\Controllers\Store;

use App\SmsContact;
use Illuminate\Http\Request;
use App\Customer;
use App\SmsList;
use stdClass;

class SMSContactController extends StoreController
{
    protected $baseURL = 'https://rest.textmagic.com/api/v2/contacts';
    protected $headers = [
        'X-TM-Username' => 'mikesoldano',
        'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    public function index()
    {
        $contactIds = SmsContact::where('store_id', $this->store->id)->pluck(
            'contact_id'
        );

        $contacts = [];

        foreach ($contactIds as $contactId) {
            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->request(
                    'GET',
                    $this->baseURL . '/' . $contactId,
                    [
                        'headers' => $this->headers
                    ]
                );
                $body = $res->getBody();
                $contact = new stdClass();
                $contact->id = json_decode($body)->id;
                $contact->firstName = json_decode($body)->firstName;
                $contact->lastName = json_decode($body)->lastName;
                $contact->phone = json_decode($body)->phone;
                array_push($contacts, $contact);
            } catch (\Exception $e) {
            }
        }

        return $contacts;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $contact = $request->get('contact');

        // Assign all newly added customers to the first list which is the master list of all contacts.
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
                        'firstName' => $contact['firstName'],
                        'lastName' => $contact['lastName']
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();

            $smsContact = new SmsContact();
            $smsContact->store_id = $this->store->id;
            $smsContact->contact_id = json_decode($body)->id;
            $smsContact->save();
        } catch (\Exception $e) {
        }
    }

    public function importCustomers(Request $request)
    {
        // $customers = $request->get('customers');
        $customers = Customer::where('store_id', $this->store->id)->get();

        // Assign all newly added customers to the first list which is the master list of all contacts.
        $listId = SmsList::where('store_id', $this->store->id)
            ->pluck('list_id')
            ->first();

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
                            'lists' => $listId,
                            'firstName' => $customer->firstname,
                            'lastName' => $customer->lastname,
                            'email' => $customer->email
                        ]
                    ]
                );
                $status = $res->getStatusCode();
                $body = $res->getBody();

                $smsContact = new SmsContact();
                $smsContact->store_id = $this->store->id;
                $smsContact->contact_id = json_decode($body)->id;
                $smsContact->save();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SMSContact  $sMSContact
     * @return \Illuminate\Http\Response
     */
    public function show(SMSContact $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SMSContact  $sMSContact
     * @return \Illuminate\Http\Response
     */
    public function edit(SMSContact $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SMSContact  $sMSContact
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $contact = $request->get('contact');

        $phone = (int) preg_replace('/[^0-9]/', '', $contact['phone']);
        if (strlen((string) $phone) === 10) {
            $phone = 1 . $phone;
        }
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'PUT',
            'https://rest.textmagic.com/api/v2/contacts/' . $contact['id'],
            [
                'headers' => $this->headers,
                'form_params' => [
                    'phone' => $phone,
                    'firstName' => $contact['firstName'],
                    'lastName' => $contact['lastName']
                ]
            ]
        );
        $status = $res->getStatusCode();
        $body = $res->getBody();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SMSContact  $sMSContact
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $contact = SmsContact::where('contact_id', $id)->first();
        $contact->delete();

        $client = new \GuzzleHttp\Client();
        $res = $client->request('DELETE', $this->baseURL . '/' . $id, [
            'headers' => $this->headers
        ]);
        $body = $res->getBody();

        return $body;
    }
}
