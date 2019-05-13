<?php

namespace App\Http\Controllers\Store;
use Illuminate\Support\Facades\Mail;
use App\Customer;
use App\Mail\Store\CancelledSubscription;

class SubscriptionController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store
            ->subscriptions()
            ->with(['user:id', 'pickup_location'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->store
            ->subscriptions()
            ->with([
                'user',
                'user.userDetail',
                'orders',
                'orders.meals',
                'pickup_location'
            ])
            ->where('id', $id)
            ->first();
    }

    /**
     * Cancel
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sub = $this->store->subscriptions()->find($id);

        if (!$sub) {
            return response()->json(
                [
                    'error' => 'Meal plan not found'
                ],
                404
            );
        }

        $sub->cancel();
    }
}
