<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\SmsMessage;
use stdClass;
use App\SmsContact;
use App\SmsSetting;

class SMSMessagesController extends StoreController
{
    // Merging messages & sessions in one controller. Separate if needed.

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
            $res = $client->request(
                'GET',
                'https://rest.textmagic.com/api/v2/sessions/' . $messageId,
                [
                    'headers' => $this->headers
                ]
            );
            $body = $res->getBody();
            $message = new stdClass();
            $message->id = json_decode($body)->id;
            $message->price = json_decode($body)->price;
            $message->numbersCount = json_decode($body)->numbersCount;
            $message->messageTime = json_decode($body)->startTime;
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

        // Store standalone phone numbers are contacts for purposes of retrieving chats
        SmsContact::addNumbersToContacts($phones, $this->store->id);

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

        try {
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
            $smsMessage->message_id = json_decode($body)->sessionId;
            $smsMessage->save();
        } catch (\Exception $e) {
        }

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
        } else {
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
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            'https://rest.textmagic.com/api/v2/sessions/' . $id . '/messages',
            [
                'headers' => $this->headers
            ]
        );
        $status = $res->getStatusCode();
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
        //
    }
}
