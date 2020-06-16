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
        // Get master list ID
        $listId = SmsList::where('store_id', $this->store->id)
            ->pluck('list_id')
            ->first();

        // Get number of recipients in the list so you can loop through the page count of 100 each
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            'https://rest.textmagic.com/api/v2/lists' . '/' . $listId,
            [
                'headers' => $this->headers
            ]
        );
        $body = $res->getBody();
        $membersCount = json_decode($body)->membersCount;
        $pages = ceil($membersCount / 100);

        // Loop through the number of pages and get the contacts (100 per page. 100 max in one request allowed.)
        $contactsPages = [];

        for ($i = 1; $i <= $pages; $i++) {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'GET',
                'https://rest.textmagic.com/api/v2/lists' .
                    '/' .
                    $listId .
                    '/contacts',
                [
                    'headers' => $this->headers,
                    'query' => [
                        'page' => $i,
                        'limit' => 100
                    ]
                ]
            );

            $body = $res->getBody();
            array_push($contactsPages, json_decode($body)->resources);
        }

        $allContacts = [];

        foreach ($contactsPages as $contacts) {
            foreach ($contacts as $contact) {
                array_push($allContacts, $contact);
            }
        }

        return collect($allContacts)->map(function ($contact) {
            return [
                'id' => $contact->id,
                'firstName' => $contact->firstName,
                'lastName' => $contact->lastName,
                'phone' => $contact->phone
            ];
        });
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
                        'firstName' => isset($contact['firstName'])
                            ? $contact['firstName']
                            : '',
                        'lastName' => isset($contact['lastName'])
                            ? $contact['lastName']
                            : ''
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
            if (
                strpos($e, 'Phone number already exists in your contacts.') !==
                false
            ) {
                $this->updateExistingContact($contact);
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

    public function updateExistingContact($contact)
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
            SMSList::where('store_id', $this->store->id)
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
            'store_id' => $this->store->id,
            'contact_id' => $contactId
        ])->first();
        if (!$contactExists) {
            $newContact = new SMSContact();
            $newContact->store_id = $this->store->id;
            $newContact->contact_id = $contactId;
            $newContact->save();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SMSContact  $sMSContact
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Instead of deleting the contact entirely from TextMagic as it will delete it for other stores that use the same contact, just remove contact from the database and remove from all existing lists associated with the store.

        $lists = SmsList::where('store_id', $this->store->id)->get();

        foreach ($lists as $list) {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'DELETE',
                'https://rest.textmagic.com/api/v2/lists' .
                    '/' .
                    $list->list_id .
                    '/contacts',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'contacts' => $id
                    ]
                ]
            );
        }

        $contact = SmsContact::where('contact_id', $id)->first();
        $contact->delete();
    }
}
