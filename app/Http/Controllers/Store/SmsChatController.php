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

    public function getNewChats()
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $this->baseURL, [
            'headers' => $this->headers
        ]);
        $body = $res->getBody();
        $chats = json_decode($body)->resources;

        foreach ($chats as $chat) {
            $chatStoreId = SmsContact::where(
                'contact_id',
                $chat->contact ? $chat->contact->id : null
            )
                ->pluck('store_id')
                ->first();

            if ($chatStoreId) {
                $smsChat = SmsChat::where('chat_id', $chat->id)->first();
                if ($smsChat) {
                    if (
                        $chat->direction === 'i' &&
                        $chat->updatedAt > $smsChat->updatedAt
                    ) {
                        $smsChat->unread = 1;
                        $smsChat->updatedAt = $chat->updatedAt;
                        $smsChat->update();
                    }
                } else {
                    $smsChat = new SmsChat();
                    $smsChat->store_id = $chatStoreId;
                    $smsChat->chat_id = $chat->id;
                    $smsChat->unread = 1;
                    $smsChat->updatedAt = $chat->updatedAt;
                    $smsChat->save();
                }
            }
        }
    }

    public function index()
    {
        $this->getNewChats();

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
                $chat->phone = json_decode($body)->phone;
                $chat->unread = $unread;
                $chat->updatedAt = json_decode($body)->updatedAt;
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
        $message = $request->get('message');
        $phone = (int) $request->get('phone');

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
        $smsSettings->balance += 0.05;
        $smsSettings->update();
        if ($smsSettings->balance >= 0.5) {
            $charge = \Stripe\Charge::create([
                'amount' => round($smsSettings->balance * 100),
                'currency' => $store->settings->currency,
                'source' => $store->settings->stripe_id,
                'description' =>
                    'SMS fee balance for ' . $store->storeDetail->name
            ]);
            $smsSettings->balance = 0;
            $smsSettings->update();
        }

        return $body;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SmsChat  $smsChat
     * @return \Illuminate\Http\Response
     */
    public function show(SmsChat $smsChat)
    {
        //
    }

    public function getChatMessages(Request $request)
    {
        $phone = $request->get('phone');
        $chatId = $request->get('id');

        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $this->baseURL . '/' . $phone, [
            'headers' => $this->headers
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody();

        $smsChat = SmsChat::where('chat_id', $chatId)->first();
        $smsChat->unread = 0;
        $smsChat->update();

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

    public function incomingSMS(Request $request)
    {
        $smsChat = new SmsChat();
        $smsChat->store_id = 13;
        $smsChat->chat_id = $request->get('id');
        $smsChat->unread = 1;
        $smsChat->updatedAt = $request->get('messageTime');
        $smsChat->save();

        // Look for an existing chat ID by checking the sender phone number (make new column in chats).
        // Update messageTime as updatedAt & unread to 1
        // If no existing chat, add new one.

        // $chatStoreId = SmsContact::where(
        //     'contact_id',
        //     $chat->contact ? $chat->contact->id : null
        // )
        //     ->pluck('store_id')
        //     ->first();

        // if ($chatStoreId) {
        //     $smsChat = SmsChat::where('chat_id', $chat->id)->first();
        //     if ($smsChat) {
        //         if (
        //             $chat->direction === 'i' &&
        //             $chat->updatedAt > $smsChat->updatedAt
        //         ) {
        //             $smsChat->unread = 1;
        //             $smsChat->updatedAt = $chat->updatedAt;
        //             $smsChat->update();
        //         }
        //     } else {
        //         $smsChat = new SmsChat();
        //         $smsChat->store_id = $chatStoreId;
        //         $smsChat->chat_id = $chat->id;
        //         $smsChat->unread = 1;
        //         $smsChat->updatedAt = $chat->updatedAt;
        //         $smsChat->save();
        //     }
        // }
    }
}
