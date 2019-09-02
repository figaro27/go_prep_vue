<?php

namespace App\Billing;

class Billing
{
    public static function init($gateway, $store)
    {
        switch ($gateway) {
            case Constants::GATEWAY_AUTHORIZE:
                return new Authorize($store);
        }

        throw new \Exception('Unrecognized gateway');
    }
}
