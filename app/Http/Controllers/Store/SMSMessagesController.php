<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\SmsMessage;
use stdClass;

class SMSMessagesController extends StoreController
{
    protected $baseURL = 'https://rest.textmagic.com/api/v2/messages';
    protected $headers = [
        'X-TM-Username' => 'mikesoldano',
        'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    public function index()
    {
        $messageIds = SmsMessage::where('store_id', $this->store->id)->pluck(
            'message_id'
        );

        $messages = [];

        foreach ($messageIds as $messageId) {
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $this->baseURL . '/' . $messageId, [
                'headers' => $this->headers
            ]);
            $body = $res->getBody();
            $message = new stdClass();
            $message->id = json_decode($body)->id;
            $message->messageTime = json_decode($body)->messageTime;
            $message->text = json_decode($body)->text;
            array_push($messages, $message);
        }

        return $messages;
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
        $listId = $request->get('listId');
        // $templateId = $request->get('templateId');

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $this->baseURL, [
            'headers' => $this->headers,
            'form_params' => [
                'lists' => $listId,
                'text' => $message
            ]
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody();

        $smsMessage = new SmsMessage();
        $smsMessage->store_id = $this->store->id;
        $smsMessage->message_id = json_decode($body)->messageId;
        $smsMessage->save();

        return $body;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }
}
