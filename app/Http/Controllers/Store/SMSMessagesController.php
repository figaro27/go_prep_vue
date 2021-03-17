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
            $message->price = (json_decode($body)->price / 4) * 6;
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

        $fromNumber = SmsSetting::where('store_id', $this->store->id)
            ->pluck('phone')
            ->first();

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $this->baseURL, [
                'headers' => $this->headers,
                'form_params' => [
                    'lists' => $lists,
                    'contacts' => $contacts,
                    'text' => $message,
                    'phones' => $phones,
                    'from' => $fromNumber
                ]
            ]);
            $status = $res->getStatusCode();
            $body = $res->getBody();

            $smsMessage = new SmsMessage();
            $smsMessage->store_id = $this->store->id;
            $smsMessage->message_id = json_decode($body)->sessionId;
            $smsMessage->save();

            $store = $this->store;

            $smsSettings = SmsSetting::where('store_id', $store->id)->first();
            $smsSettings->balance += $charge;
            $smsSettings->total_spent += $charge;
            $smsSettings->update();
            $smsSettings->chargeBalance($this->store);

            return $body;
        } catch (\Exception $e) {
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Get recipient count
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET',
            'https://rest.textmagic.com/api/v2/sessions/' . $id,
            [
                'headers' => $this->headers
            ]
        );
        $body = $res->getBody();
        $numbersCount = json_decode($body)->numbersCount;
        $pages = ceil($numbersCount / 100);

        // Get recipients
        $recipientsPages = [];

        for ($i = 1; $i <= $pages; $i++) {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'GET',
                'https://rest.textmagic.com/api/v2/sessions/' .
                    $id .
                    '/messages',
                [
                    'headers' => $this->headers,
                    'query' => [
                        'page' => $i,
                        'limit' => 100
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();
            array_push($recipientsPages, json_decode($body)->resources);
        }

        $allRecipients = [];

        foreach ($recipientsPages as $recipients) {
            foreach ($recipients as $recipient) {
                array_push($allRecipients, $recipient);
            }
        }

        return collect($allRecipients)->map(function ($recipient) {
            return [
                'id' => $recipient->id,
                'phone' => $recipient->receiver,
                'firstName' => $recipient->firstName,
                'lastName' => $recipient->lastName,
                'status' => $recipient->status
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
