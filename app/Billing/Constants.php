<?php

namespace App\Billing;

class Constants
{
    const GATEWAY_STRIPE = 'stripe';
    const GATEWAY_AUTHORIZE = 'authorize';
    const GATEWAY_CASH = 'cash';

    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';

    const PERIOD = [
        'week' => 'weekly',
        'month' => 'monthly'
    ];

    const INTERVAL_WEEK = 'week';
    const INTERVAL_MONTH = 'month';
}
