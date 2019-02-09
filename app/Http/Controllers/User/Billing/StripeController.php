<?php

namespace App\Http\Controllers\User\Billing;

use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Subscription;

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

    public function event(Request $request)
    {
        $event = collect($request->json());
        $obj = collect($event->get('data', []));
        $type = $event->get('type', null);

        //$subscriptions = Subscription::all();

        if($type === 'invoice.payment_succeeded') {
          $subId = $obj->get('subscription', null);
          $subscription = null;

          if($subId) {
            $subId = substr($subId, 4);
            $subscription = Subscription::where('stripe_id', $subId)->first();
          }

          if($subscription) {
            $subscription->renew($obj);
          }

          return 'Sub renewed';

        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        print_r($this->user->getCustomer());
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

    public function createCard(Request $request)
    {

    }
}
