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
        $charge = $request->get('charge');

        $phones = json_decode(
            collect($request->get('phones'))->map(function ($phone) {
                $phone = (int) 1 . preg_replace('/[^0-9]/', '', $phone);
                return $phone;
            })
        );

        $lists = json_decode(
            collect($request->get('lists'))->map(function ($list) {
                return $list['id'];
            })
        );

        $contacts = json_decode(
            collect($request->get('contacts'))->map(function ($contact) {
                return $contact['id'];
            })
        );

        $phones = implode(',', $phones);
        $lists = implode(',', $lists);
        $contacts = implode(',', $contacts);

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $this->baseURL, [
            'headers' => $this->headers,
            'form_params' => [
                'lists' => $lists,
                'contacts' => $contacts,
                'text' => $message,
                'phones' => $phones
            ]
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody();

        $smsMessage = new SmsMessage();
        $smsMessage->store_id = $this->store->id;
        $smsMessage->message_id = json_decode($body)->messageId;
        $smsMessage->save();

        $store = $this->store;
        if ($charge >= 0.5) {
            $charge = \Stripe\Charge::create([
                'amount' => round($charge * 100),
                'currency' => $store->settings->currency,
                'source' => $store->settings->stripe_id,
                'description' =>
                    $store->storeDetail->name .
                    ' SMS fee for SMS ID #' .
                    $smsMessage->id .
                    ' - ' .
                    $smsMessage->message_id
            ]);
        }

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
