<?php

namespace App\Traits;

trait DeliveryDates
{
    public function getDeliveryDateMultipleDelivery(
        $delivery_day,
        $multi_delivery = 0
    ) {
        if (strtotime($delivery_day) && !$multi_delivery) {
            return $delivery_day;
        }
        return date('Y-m-d', strtotime("sunday + {$delivery_day} days"));
    }
}
