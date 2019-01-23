<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Stripe;
use Illuminate\Support\Facades\Session;

class StripeController extends UserController
{
    public function __construct()
    {
        $this->middleware('store_slug');

        $aaaa = Session::get('store_id');
        $b = Session::all();

        $sid = config('store_id');
        if (defined('STORE_ID')) {
            $this->store = Store::with(['meals', 'settings'])->find(STORE_ID);
        }

        $this->user = auth('api')->user();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        print_r($this->user->getCustomer($this->store->id));
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
