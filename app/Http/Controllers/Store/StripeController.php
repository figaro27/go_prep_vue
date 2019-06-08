<?php

namespace App\Http\Controllers\Store;

use App\Store;
use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Stripe;

class StripeController extends StoreController
{
    /**
     * Returns the URL to connect
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function connectUrl(Request $request)
    {
        if (env('APP_ENV') === 'production') {
            $ca = 'ca_ER2OUNQq30X2xHMqkWo8ilUSz7Txyn1A';
        } else {
            $ca = 'ca_ER2OYlaTUrWz7LRQvhtKLIjZsRcM8mh9';
        }

        if (in_array($this->store->details->country, ['US', 'CA'])) {
            return "https://connect.stripe.com/express/oauth/authorize?client_id=$ca";
        } else {
            return "https://connect.stripe.com/oauth/authorize?client_id=$ca&response_type=code&scope=read_write";
        }
    }

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

        try {
            $client = new GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://connect.stripe.com/oauth/token',
                [
                    'form_params' => [
                        'client_secret' => config('services.stripe.secret'),
                        'code' => $code,
                        'grant_type' => 'authorization_code'
                    ]
                ]
            );
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

        if (!$settings->stripe_id) {
            return null;
        }

        $cacheId = 'stripe_login_url_' . $this->store->id;

        if (Cache::has($cacheId)) {
            $link = Cache::get($cacheId, null);
            if ($link) {
                return $link;
            }
        }

        try {
            $links = Stripe\Account::createLoginLink($settings->stripe_id);
            Cache::put($cacheId, $links, 60 * 24);
            return $links;
        } catch (\Exception $e) {
            return null;
        }
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
                "email" => $this->store->user->email
            ]);
        }

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
