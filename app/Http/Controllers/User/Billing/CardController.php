<?php

namespace App\Http\Controllers\User\Billing;

use App\Billing\Authorize;
use App\Billing\Constants;
use App\Http\Controllers\User\UserController;
use Http\Client\Exception\RequestException;
use Illuminate\Http\Request;
use App\Store;
use App\Subscription;
use App\Card;
use App\Error;

class CardController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $store = Store::where('id', $this->user->last_viewed_store_id)->first();
        $storeId = $store ? $store->id : null;
        $gateway = $store ? $store->settings->payment_gateway : null;

        return $this->user
            ->cards()
            ->where('payment_gateway', $gateway)
            ->get()
            ->filter(function ($card) use ($storeId, $gateway) {
                if ($gateway === 'authorize') {
                    return $card->store_id === $storeId;
                } else {
                    return true;
                }
            })
            ->values();
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

        $existingCards = Card::where('user_id', $this->user->id)
            ->where('deleted_at', null)
            ->get();
        foreach ($existingCards as $existingCard) {
            if (
                $existingCard->brand === $card['brand'] &&
                $existingCard->last4 === (int) $card['last4']
            ) {
                return response()->json(
                    [
                        'error' =>
                            'You have already added this card to the system.'
                    ],
                    400
                );
            }
        }

        if ($gateway === Constants::GATEWAY_STRIPE) {
            try {
                if (!$this->user->hasCustomer()) {
                    $customer = $this->user->createCustomer($token);
                } else {
                    $customer = \Stripe\Customer::retrieve(
                        $this->user->stripe_id
                    );
                    $this->user->createCard($token);
                }
            } catch (\Stripe\Error\Card $e) {
                $declineCode = isset($e->jsonBody['error']['decline_code'])
                    ? $e->jsonBody['error']['decline_code']
                    : $e->jsonBody['error']['code'];
                $error = new Error();
                $error->store_id = $this->user->last_viewed_store_id;
                $error->user_id = $this->user->id;
                $error->type = 'Adding Card';
                $error->error =
                    trim(json_encode($e->jsonBody['error']['message']), '"') .
                    ' Decline Code: "' .
                    $declineCode .
                    '"';
                $error->save();

                return response()->json(
                    [
                        'error' => trim(
                            json_encode($e->jsonBody['error']['message']),
                            '"'
                        )
                    ],
                    400
                );
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

            if (
                !$this->user->hasStoreCustomer(
                    $this->store->id,
                    'USD',
                    $gateway
                )
            ) {
                $this->user->createStoreCustomer(
                    $this->store->id,
                    'USD',
                    $gateway
                );
            }

            $customer = $this->user->getStoreCustomer(
                $this->store->id,
                'USD',
                $gateway
            );

            $source = $authorize->createCard($customer, $token);
        } else {
            return response()->json('Unrecognized gateway', 400);
        }

        return $this->user->cards()->create([
            'stripe_id' => $source->id,
            'brand' => $card['brand'],
            'exp_month' => $card['exp_month'],
            'exp_year' => $card['exp_year'],
            'last4' => $card['last4'],
            'country' => $card['country'],
            'payment_gateway' => $gateway,
            'store_id' => $this->store->id,
            'saveCard' => $request->get('saveCard')
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

        $this->destroySubscription($id);

        $card->delete();
    }

    public function cardHasSubscription(Request $request)
    {
        $cardId = $request->get('cardId');

        if (
            Subscription::where('status', '!=', 'cancelled')
                ->where('card_id', $cardId)
                ->count() > 0
        ) {
            return 'true';
        }
        return;
    }

    public function destroySubscription($id)
    {
        $sub = Subscription::where('card_id', $id)->first();
        if (!$sub) {
            return response()->json(
                [
                    'error' => 'Subscription not found'
                ],
                404
            );
        }

        if ($sub->prepaid) {
            if ($sub->paid_order_count % $sub->prepaidWeeks === 0) {
                $sub->cancel();
            } else {
                try {
                    $sub->cancel();
                } catch (\Exception $e) {
                    return response()->json(
                        [
                            'error' => 'Failed to cancel Subscription'
                        ],
                        500
                    );
                }
            }
            return;
        }

        try {
            $sub->cancel();
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to cancel Subscription'
                ],
                500
            );
        }
    }
}
