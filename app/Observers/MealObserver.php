<?php

namespace App\Observers;

use App\Meal;

class MealObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  \App\Meal  $meal
     * @return void
     */
    public function created(Meal $meal)
    {
        //
    }

    /**
     * @param  \App\Meal  $meal
     * @return void
     */
    public function saving(Meal $meal)
    {
    }

    /**
     * @param  \App\Meal  $meal
     * @return void
     */
    public function saved(Meal $meal)
    {
        Cache::forget('meal_substitutes_' . $meal->id);
    }

    /**
     * Listen to the Meal deleting event.
     *
     * @param  \App\Meal  $meal
     * @return void
     */
    public function deleting(Meal $meal)
    {
        //
    }
}
