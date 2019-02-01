<?php

namespace App\Http\Controllers\Store;

use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Stripe;

class StripeController extends StoreController
{
    /**
     * Connect a stripe account
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function connect(Request $request)
    {
        $code = $request->get('code', null);

        if (!$code) {
            return redirect('/store');
        }

        $user = auth()->user();

        try {

            $client = new GuzzleHttp\Client();
            $res = $client->request('POST', 'https://connect.stripe.com/oauth/token', [
                'form_params' => [
                    'client_secret' => config('services.stripe.secret'),
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                ],
            ]);
        } catch (ClientException $e) {
            return redirect('/store');
        }

        if ($res->getStatusCode() !== 200) {
            return redirect('/store');
        }

        $account = json_decode((string) $res->getBody());

        $this->store->settings->stripe_id = $account->stripe_user_id;
        $this->store->settings->stripe_account = $account;
        $this->store->settings->save();

        return redirect('/store/account/settings');

    }

    public function getLoginLinks()
    {
        $settings = $this->store->settings;
        $links = Stripe\Account::createLoginLink($settings->stripe_id);

        return $links;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!$this->store->hasStripe()) {
            return;
        }

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
        $settings = $this->store->settings;

        if ($this->store->hasStripe()) {
            $account = Stripe\Account::retrieve($settings->stripe_id);
        } else {
            $account = Stripe\Account::create([
                "type" => "custom",
                "country" => "US",
                "email" => $this->store->user->email,
            ]);
        }

        $links = $account->login_links->create();

        print_r($account);
        print_r($links);

        if (!isset($account->id)) {
            return null;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
    }
}
