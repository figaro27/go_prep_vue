<?php

namespace App\Observers;

use App\MealTag;

class MealTagObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  \App\MealTag  $tag
     * @return void
     */
    public function created(MealTag $tag)
    {
        //
    }


    /**
     * @param  \App\MealTag  $tag
     * @return void
     */
    public function saving(MealTag $tag)
    {
        if(!$tag->slug) {
          $tag->slug = str_slug($tag->title, '-');
        }
    }
   

    /**
     * Listen to the MealTag deleting event.
     *
     * @param  \App\MealTag  $tag
     * @return void
     */
    public function deleting(MealTag $tag)
    {
        //
    }
}
