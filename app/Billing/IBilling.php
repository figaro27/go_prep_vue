<?php

namespace App\Billing;

use Illuminate\Support\Collection;
use App\Store;
use App\Customer;

interface IBilling
{
    public function setAuthContext(Store $store);

    /**
     * @return bool
     */
    public function charge(Customer $customer, int $amount, Card $card = null);

    /**
     * @return App\Billing\Card
     */
    public function createCard(Customer $customer, string $token);

    /**
     * @return App\Subscription
     */
    public function createSubscription();
}
