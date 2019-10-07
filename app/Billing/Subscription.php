<?php

namespace App\Billing;

class Subscription
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

    /**
     * @var string
     */
    public $period;

    /**
     * @var Carbon
     */
    public $startDate;
}
