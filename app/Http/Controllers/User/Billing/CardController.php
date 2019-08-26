<?php

namespace App\Http\Controllers\User\Billing;

use App\Billing\Authorize;
use App\Billing\Constants;
use App\Http\Controllers\User\UserController;
use Http\Client\Exception\RequestException;
use Illuminate\Http\Request;

class CardController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->user->cards()->get();
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
        $token = $request->get('token');
        $gateway = $request->get('payment_gateway');
        $card = $request->get('card');

        if ($gateway === Constants::GATEWAY_STRIPE) {
            if (!$this->user->hasCustomer()) {
                $customer = $this->user->createCustomer($token);
            } else {
                $customer = \Stripe\Customer::retrieve($this->user->stripe_id);

                try {
                    $this->user->createCard($token);
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

            $sources = $customer->sources->all()->getIterator();
            $source = end($sources);

            return $this->user->cards()->create([
                'stripe_id' => $source->id,
                'brand' => $card['brand'],
                'exp_month' => $card['exp_month'],
                'exp_year' => $card['exp_year'],
                'last4' => $card['last4'],
                'country' => $card['country']
            ]);
        } elseif ($gateway === Constants::GATEWAY_AUTHORIZE) {
            $authorize = new Authorize($this->store);

            if (
                !$this->user->hasStoreCustomer(
                    $this->store->id,
                    'USD',
                    Constants::GATEWAY_AUTHORIZE
                )
            ) {
                $this->user->createStoreCustomer(
                    $this->store->id,
                    'USD',
                    Constants::GATEWAY_AUTHORIZE
                );
            }

            $customer = $this->user->getStoreCustomer($this->store->id, false);

            $source = $authorize->createCard($customer, $token['id']);

            return $this->user->cards()->create([
                'authorize_id' => $source->id,
                'brand' => $card['brand'],
                'exp_month' => $card['exp_month'],
                'exp_year' => $card['exp_year'],
                'last4' => $card['last4'],
                'country' => $card['country']
            ]);
        } else {
            return response()->json('Unrecognized gateway', 400);
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
    public function destroy(Request $request, $id)
    {
        $card = $this->user->cards()->find($id);

        if (!$card) {
            return response('', 404);
        }

        $card->delete();
    }
}
