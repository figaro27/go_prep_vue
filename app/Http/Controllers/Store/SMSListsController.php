<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\SmsList;
use App\Customer;
use stdClass;

class SMSListsController extends StoreController
{
    protected $baseURL = 'https://rest.textmagic.com/api/v2/lists';
    protected $headers = [
        'X-TM-Username' => 'mikesoldano',
        'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    public function index()
    {
        $listIds = SmsList::where('store_id', $this->store->id)->pluck(
            'list_id'
        );

        $lists = [];

        foreach ($listIds as $listId) {
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $this->baseURL . '/' . $listId, [
                'headers' => $this->headers
            ]);
            $body = $res->getBody();
            $list = new stdClass();
            $list->id = json_decode($body)->id;
            $list->name = json_decode($body)->name;
            $list->membersCount = json_decode($body)->membersCount;
            array_push($lists, $list);
        }

        return $lists;
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
        $name = $request->get('name');
        if ($name === null) {
            $count = SmsList::where('store_id', $this->store->id)->count() + 1;
            $name = 'List #' . $count;
        }

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $this->baseURL, [
            'headers' => $this->headers,
            'form_params' => [
                'name' => $name
            ]
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody();

        $smsList = new SmsList();
        $smsList->store_id = $this->store->id;
        $smsList->list_id = json_decode($body)->id;
        $smsList->save();

        $listId = json_decode($body)->id;

        // If contact doesn't exist, add contact to Text Magic and update sms_contact_id in Customers table.

        $customers = $request->get('customers');

        foreach ($customers as $customer) {
            $customer = Customer::where('id', $customer)->first();
            if ($customer->sms_contact_id === null) {
                // Get country prefix
                $phone =
                    (int) 1 . preg_replace('/[^0-9]/', '', $customer->phone);

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

                $customer->sms_contact_id = json_decode($body)->id;
                $customer->update();
            }
        }
    }

    public function addContactToList(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $this->baseURL . '/' . $id, [
            'headers' => $this->headers
        ]);
        $body = new stdClass();
        $body = $res->getBody();

        return $body;
    }

    public function showContacts(Request $request)
    {
        $id = $request->get('id');

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            $this->baseURL . '/' . $id . '/contacts',
            [
                'headers' => $this->headers
            ]
        );
        $body = new stdClass();
        $body = $res->getBody();

        return $body;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $list = SmsList::where('list_id', $id)->first();
        $list->delete();

        $client = new \GuzzleHttp\Client();
        $res = $client->request('DELETE', $this->baseURL . '/' . $id, [
            'headers' => $this->headers
        ]);
        $body = $res->getBody();

        return $body;
    }
}
