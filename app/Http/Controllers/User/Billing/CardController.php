<?php

namespace App\Http\Controllers\User\Billing;

use App\Http\Controllers\User\UserController;
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
        $card = $token['card'];

        if (!$this->user->hasCustomer()) {
            $customer = $this->user->createCustomer($token['id']);
        } else {
            $customer = \Stripe\Customer::retrieve($this->user->stripe_id);

            try {
                $this->user->createCard($token['id']);
            } catch (\Stripe\Error\Card $e) {
                return response()->json([
                    'error' => 'Your card was declined. Please verify the entered information and try again.',
                ], 400);
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
            'country' => $card['country'],
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
        $card = $this->user->cards()->find($id);

        if (!$card) {
            return response('', 404);
        }

        $card->delete();
    }
}
