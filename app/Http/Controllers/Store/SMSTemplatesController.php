<?php

namespace App\Http\Controllers\Store;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\SmsTemplate;
use stdClass;

class SMSTemplatesController extends StoreController
{
    protected $baseURL = 'https://rest.textmagic.com/api/v2/templates';
    protected $headers = [
        'X-TM-Username' => 'mikesoldano',
        'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    public function index()
    {
        $templateIds = SmsTemplate::where('store_id', $this->store->id)->pluck(
            'template_id'
        );

        $templates = [];

        foreach ($templateIds as $templateId) {
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $this->baseURL . '/' . $templateId, [
                'headers' => $this->headers
            ]);
            $body = $res->getBody();
            $template = new stdClass();
            $template->id = json_decode($body)->id;
            $template->name = json_decode($body)->name;
            $template->content = json_decode($body)->content;
            array_push($templates, $template);
        }

        return $templates;
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
            $count =
                SmsTemplate::where('store_id', $this->store->id)->count() + 1;
            $name = 'Template #' . $count;
        }
        $content = $request->get('content');

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $this->baseURL, [
            'headers' => $this->headers,
            'form_params' => [
                'name' => $name,
                'content' => $content
            ]
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody();

        $smsTemplate = new SmsTemplate();
        $smsTemplate->store_id = $this->store->id;
        $smsTemplate->name = $name;
        $smsTemplate->template_id = json_decode($body)->id;
        $smsTemplate->save();

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
        $res = $client->request('GET', $this->baseURL . '/' . $id, [
            'headers' => $this->headers
        ]);
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
        $content = $request->get('content');
        $name = $request->get('name');

        $client = new \GuzzleHttp\Client();
        $res = $client->request('PUT', $this->baseURL . '/' . $id, [
            'headers' => $this->headers,
            'form_params' => [
                'content' => $content,
                'name' => $name
            ]
        ]);
        $status = $res->getStatusCode();
        $body = $res->getBody();

        return $body;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $template = SmsTemplate::where('template_id', $id)->first();
        $template->delete();

        $client = new \GuzzleHttp\Client();
        $res = $client->request('DELETE', $this->baseURL . '/' . $id, [
            'headers' => $this->headers
        ]);
        $body = $res->getBody();

        return $body;
    }
}
