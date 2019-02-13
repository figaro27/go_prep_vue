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

     /**
     * Pause
     *
     * @return \Illuminate\Http\Response
     */
    public function pause($id) {
      $sub = $this->user->subscriptions()->find($id);

      if(!$sub) {
        return response()->json([
          'error' => 'Meal plan not found'
        ], 404);
      }

      try {
        $sub->pause();
      }
      catch(\Exception $e) {
        return response()->json([
          'error' => 'Failed to pause Meal Plan'
        ], 404); 
      }
    }

    /**
     * Pause
     *
     * @return \Illuminate\Http\Response
     */
    public function resume($id) {
      $sub = $this->user->subscriptions()->find($id);

      if(!$sub) {
        return response()->json([
          'error' => 'Meal plan not found'
        ], 404);
      }

      try {
        $sub->resume();
      }
      catch(\Exception $e) {
        return response()->json([
          'error' => 'Failed to resume Meal Plan'
        ], 404); 
      }
    }
}
