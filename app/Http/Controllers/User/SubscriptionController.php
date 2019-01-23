<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;

class SubscriptionController extends UserController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->user->subscriptions()->with(['orders', 'orders.meals'])->get();
    }

    /**
     * Cancel
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sub = $this->user->subscriptions()->find($id);

        if(!$sub) {
          return response()->json([
            'error' => 'Meal plan not found'
          ], 404);
        }

        $sub->cancel();
    }
}
