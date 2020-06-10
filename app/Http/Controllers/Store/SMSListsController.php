<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\SmsList;
use App\SmsContact;
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
        $list = $request->get('list');

        if ($list['name'] === null) {
            $count = SmsList::where('store_id', $this->store->id)->count() + 1;
            $list['name'] = 'List #' . $count;
        }

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $this->baseURL, [
            'headers' => $this->headers,
            'form_params' => [
                'name' => $list['name']
            ]
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody();

        $smsList = new SmsList();
        $smsList->store_id = $this->store->id;
        $smsList->list_id = json_decode($body)->id;
        $smsList->save();

        $listId = json_decode($body)->id;

        // Add contacts to list
        $contacts = implode(',', $list['contacts']);

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'PUT',
            $this->baseURL . '/' . $listId . '/contacts',
            [
                'headers' => $this->headers,
                'form_params' => [
                    'contacts' => $contacts
                ]
            ]
        );
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

    public function showContactsInList(Request $request)
    {
        // Get all contacts in list
        $listId = $request->get('id');

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            $this->baseURL . '/' . $listId . '/contacts',
            [
                'headers' => $this->headers
            ]
        );

        $body = $res->getBody();
        $includedContacts = json_decode($body)->resources;
        return collect($includedContacts)->map(function ($contact) {
            return [
                'id' => $contact->id
            ];
        });
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

    public function updateList(Request $request)
    {
        $list = $request->get('list');
        $listId = $list['id'];
        $includedContactIds = $request->get('includedContactIds');
        $allContactIds = $request->get('allContactIds');

        // Update list name
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $this->baseURL . '/' . $listId, [
            'headers' => $this->headers,
            'form_params' => [
                'name' => $list['name']
            ]
        ]);
        $body = new stdClass();
        $body = $res->getBody();

        // Remove all contacts from list

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'DELETE',
            $this->baseURL . '/' . $listId . '/contacts',
            [
                'headers' => $this->headers,
                'form_params' => [
                    'contacts' => $allContactIds
                ]
            ]
        );
        $body = new stdClass();
        $body = $res->getBody();

        // Add included ones back in

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'PUT',
            $this->baseURL . '/' . $listId . '/contacts',
            [
                'headers' => $this->headers,
                'form_params' => [
                    'contacts' => $includedContactIds
                ]
            ]
        );
        $body = new stdClass();
        $body = $res->getBody();
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
