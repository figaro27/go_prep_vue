<?php

namespace App\Traits;

trait LocalizesDates
{
    public function localizeDate($date)
    {
        $timezone = config('app.timezone_display');
        $date->timezone($timezone);
        return $date;
    }
}
