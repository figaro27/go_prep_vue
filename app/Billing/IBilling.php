<?php

namespace App\Billing;

use Illuminate\Support\Collection;
use App\Store;
use App\Customer;
use App\Billing\Subscription;

interface IBilling
{
    public function setAuthContext(Store $store);

    /**
     * @return string
     */
    public function charge(Charge $charge);

    /**
     * @return App\Billing\Card
     */
    public function createCard(Customer $customer, string $token);

    /**
     * @return App\Billing\Subscription
     */
    public function subscribe(Subscription $subscription);
}
