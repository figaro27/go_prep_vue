<?php

namespace App\Http\Controllers\Store;

use App\SmsChat;
use Illuminate\Http\Request;
use stdClass;
use App\SmsSetting;
use Carbon\Carbon;
use App\SmsContact;

class SmsChatController extends StoreController
{
    protected $baseURL = 'https://rest.textmagic.com/api/v2/chats';
    protected $headers = [
        'X-TM-Username' => 'mikesoldano',
        'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    // public function getNewChats()
    // {
    //     $client = new \GuzzleHttp\Client();
    //     $res = $client->request('GET', $this->baseURL, [
    //         'headers' => $this->headers
    //     ]);
    //     $body = $res->getBody();
    //     $chats = json_decode($body)->resources;

    //     foreach ($chats as $chat) {
    //         $chatStoreId = SmsContact::where(
    //             'contact_id',
    //             $chat->contact ? $chat->contact->id : null
    //         )
    //             ->pluck('store_id')
    //             ->first();

    //         if ($chatStoreId) {
    //             $smsChat = SmsChat::where('chat_id', $chat->id)->first();
    //             if ($smsChat) {
    //                 if (
    //                     $chat->direction === 'i' &&
    //                     $chat->updatedAt > $smsChat->updatedAt
    //                 ) {
    //                     $smsChat->unread = 1;
    //                     $smsChat->updatedAt = $chat->updatedAt;
    //                     $smsChat->update();
    //                 }
    //             } else {
    //                 $smsChat = new SmsChat();
    //                 $smsChat->store_id = $chatStoreId;
    //                 $smsChat->chat_id = $chat->id;
    //                 $smsChat->unread = 1;
    //                 $smsChat->updatedAt = $chat->updatedAt;
    //                 $smsChat->save();
    //             }
    //         }
    //     }
    // }

    public function index()
    {
        // $this->getNewChats();

        $smsChats = SmsChat::where('store_id', $this->store->id)->get();

        $chats = [];

        foreach ($smsChats as $smsChat) {
            $chatId = $smsChat['chat_id'];
            $unread = $smsChat['unread'];
            try {
                $client = new \GuzzleHttp\Client();
                $res = $client->request('GET', $this->baseURL . '/' . $chatId, [
                    'headers' => $this->headers
                ]);
                $body = $res->getBody();
                $chat = new stdClass();
                $chat->id = json_decode($body)->id;
                $chat->firstName = json_decode($body)->contact->firstName;
                $chat->lastName = json_decode($body)->contact->lastName;
                $chat->lastMessage = json_decode($body)->lastMessage;
                $chat->phone = json_decode($body)->contact->phone;
                $chat->unread = $unread;
                $chat->updatedAt = json_decode($body)->updatedAt;
                $chat->updated_at = $smsChat->updated_at;
                array_push($chats, $chat);
            } catch (\Exception $e) {
            }
        }

        return $chats;
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
        $chatId = $request->get('chatId');
        $message = $request->get('message');
        $phone = $this->getPhoneNumber($chatId);

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://rest.textmagic.com/api/v2/messages',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'phones' => $phone,
                        'text' => $message
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();
        } catch (\Exception $e) {
        }

        $store = $this->store;

        $smsSettings = SmsSetting::where('store_id', $store->id)->first();
        $smsSettings->balance += 0.06;
        $smsSettings->total_spent += 0.06;
        $smsSettings->update();
        $smsSettings->chargeBalance($store);

        return $body;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SmsChat  $smsChat
     * @return \Illuminate\Http\Response
     */
    public function show($chatId)
    {
        // Get the phone number from the chat ID so you can get the chat...
        $phone = $this->getPhoneNumber($chatId);

        // Get the chat
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $this->baseURL . '/' . $phone, [
            'headers' => $this->headers
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody();

        $smsChat = SmsChat::where('chat_id', $chatId)->first();
        $smsChat->unread = 0;
        $smsChat->update();

        // Avoid conflicts with multiple stores texting the same contact. Text Magic doesn't allow separation even if the FROM numbers are different. You have to get different API keys for each store.
        $numbers = [];
        $resources = json_decode($body)->resources;
        $contactPhone = '';
        foreach ($resources as $resource) {
            if (!in_array($resource->sender, $numbers)) {
                array_push($numbers, $resource->sender);
            }
            if ($resource->direction === 'o') {
                $contactPhone = $resource->sender;
            }
        }

        if (count($numbers) > 2) {
            return [
                'conflict' => true,
                'phone' => $contactPhone
            ];
        }

        return $body;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SmsChat  $smsChat
     * @return \Illuminate\Http\Response
     */
    public function edit(SmsChat $smsChat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SmsChat  $smsChat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SmsChat $smsChat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SmsChat  $smsChat
     * @return \Illuminate\Http\Response
     */
    public function destroy(SmsChat $smsChat)
    {
        //
    }

    public function getPhoneNumber($chatId)
    {
        // Get the phone number from the chat ID so you can get the chat...
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $this->baseURL . '/' . $chatId, [
            'headers' => $this->headers
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody();
        $phone = json_decode($body)->phone;
        return $phone;
    }

    public function incomingSMS(Request $request)
    {
        $phone = $request->get('sender');
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            $this->baseURL . '/' . $phone . '/by/phone',
            [
                'headers' => $this->headers
            ]
        );
        $status = $res->getStatusCode();
        $body = $res->getBody();
        $chatId = json_decode($body)->id;

        $chat = SmsChat::where('chat_id', $chatId)->first();

        if ($chat) {
            // Update existing chat
            $chat->unread = 1;
            $chat->updatedAt = $request->get('messageTime');
            $chat->update();
        } else {
            // Get store ID from looking up the contact by their phone number
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'GET',
                'https://rest.textmagic.com/api/v2/contacts/phone/' . $phone,
                [
                    'headers' => $this->headers
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();
            $contactId = json_decode($body)->id;
            $storeId = SmsContact::where('contact_id', $contactId)
                ->pluck('store_id')
                ->first();

            // Add new chat
            $chat = new SmsChat();
            $chat->store_id = $storeId;
            $chat->chat_id = $chatId;
            $chat->unread = 1;
            $chat->updatedAt = $request->get('messageTime');
            $chat->save();
        }
    }
}
