<?php

namespace App\Billing;

class Charge
{
    /**
     * @var int
     */
    public $amount;

    /**
     * @var App\Customer
     */
    public $customer;

    /**
     * @var App\Card
     */
    public $card;

    /**
     * @var string
     */
    public $refId;

    /**
     * @var string
     */
    public $id;
}
