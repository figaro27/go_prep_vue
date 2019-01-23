<?php

namespace App\Http\Controllers\Store;

class SubscriptionController extends StoreController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->store->subscriptions()->with(['user', 'orders', 'orders.meals'])->get();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->store->subscriptions()->with(['user', 'user.userDetail', 'orders', 'orders.meals'])->where('id', $id)->first();
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
            return response()->json([
                'error' => 'Meal plan not found',
            ], 404);
        }

        $sub->cancel();
    }
}
