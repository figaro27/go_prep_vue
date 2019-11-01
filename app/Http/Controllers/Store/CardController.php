<?php

namespace App\Http\Controllers\Store;

use App\Billing\Constants;
use App\Http\Controllers\Store\StoreController;
use Illuminate\Http\Request;
use App\Customer;
use App\User;
use App\Card;
use App\Billing\Authorize;

class CardController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
        $customerId = $request->get('customer');
        $userId = Customer::where('id', $customerId)
            ->pluck('user_id')
            ->first();
        $user = User::find($userId);
        $token = $request->get('token');
        $gateway = $request->get('payment_gateway');
        $card = $request->get('card');

        if ($gateway === Constants::GATEWAY_STRIPE) {
            if (!$user->hasCustomer()) {
                $customer = $user->createCustomer($token);
            } else {
                $customer = \Stripe\Customer::retrieve($user->stripe_id);

                try {
                    $user->createCard($token);
                } catch (\Stripe\Error\Card $e) {
                    return response()->json(
                        [
                            'error' =>
                                'Your card was declined. Please verify the entered information and try again.'
                        ],
                        400
                    );
                }
            }

            $sources = $customer->sources->all();

            $source = null;

            foreach ($sources as $s) {
                if ($s['id'] === $card['id']) {
                    $source = $s;
                }
            }
        } elseif ($gateway === Constants::GATEWAY_AUTHORIZE) {
            $authorize = new Authorize($this->store);

            if (!$user->hasStoreCustomer($this->store->id, 'USD', $gateway)) {
                $user->createStoreCustomer($this->store->id, 'USD', $gateway);
            }

            $customer = $user->getStoreCustomer(
                $this->store->id,
                'USD',
                $gateway
            );

            $source = $authorize->createCard($customer, $token);
        } else {
            return response()->json('Unrecognized gateway', 400);
        }

        return $user->cards()->create([
            'stripe_id' => $source->id,
            'brand' => $card['brand'],
            'exp_month' => $card['exp_month'],
            'exp_year' => $card['exp_year'],
            'last4' => $card['last4'],
            'country' => $card['country'],
            'payment_gateway' => $gateway
        ]);
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
    public function destroy(Request $request, $id)
    {
        $card = Card::where('id', $id)->first();

        if (!$card) {
            return response('', 404);
        }

        $card->delete();
    }
}
