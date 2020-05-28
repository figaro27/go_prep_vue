<?php

namespace App\Http\Controllers\Store;

use App\SmsChat;
use Illuminate\Http\Request;
use stdClass;
use App\SmsSetting;
use Carbon\Carbon;

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

        $smsSetting = SmsSetting::where('store_id', $this->store->id)->first();
        $lastChatsFetch = $smsSetting->last_chats_fetch;

        foreach ($chats as $chat) {
            if ($chat->updatedAt > $lastChatsFetch) {
                $smsChat = new SmsChat();
                $smsChat->store_id = $this->store->id;
                $smsChat->chat_id = $chat->id;
                $smsChat->created_at = Carbon::now('UTC');
                $smsChat->updated_at = Carbon::now('UTC');
                $smsChat->save();
            }
        }

        $smsSetting->last_chats_fetch = Carbon::now('UTC');
        $smsSetting->update();
    }

    public function index()
    {
        $this->getNewChats();

        $chatIds = SmsChat::where('store_id', $this->store->id)->pluck(
            'chat_id'
        );

        $chats = [];

        foreach ($chatIds as $chatId) {
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
        //
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
}
