<?php

return [
    'pay-as-you-go' => [
        'title' => "Pay as you go - 5% Transaction Fee",
        'monthly' => [
            'price' => 0,
            'price_upfront' => 19500
        ],
        'annually' => [
            'price' => 0,
            'price_upfront' => 19500
        ]
    ],
    'basic' => [
        'title' => 'Basic - 50 Orders Per Month',
        'monthly' => [
            'price' => 14900,
            'stripe_id' => env('PLAN_BASIC_MONTHLY', 'plan_Fc4eOyFTAH2ZWn')
        ],
        'annually' => [
            'price' => 142800,
            'stripe_id' => env('PLAN_BASIC_ANNUALLY')
        ]
    ],
    'standard' => [
        'title' => 'Standard - 150 Orders Per Month',
        'monthly' => [
            'price' => 34900,
            'stripe_id' => env('PLAN_STANDARD_MONTHLY', 'plan_Fc4fnXEBXrWlOO')
        ],
        'annually' => [
            'price' => 334800,
            'stripe_id' => env('PLAN_STANDARD_ANNUALLY')
        ]
    ],
    'premium' => [
        'title' => 'Premium - 300 Orders Per Month',
        'monthly' => [
            'price' => 54900,
            'stripe_id' => env('PLAN_PREMIUM_MONTHLY', 'plan_Fc4fTbTtTr7LaN')
        ],
        'annually' => [
            'price' => 526800,
            'stripe_id' => env('PLAN_PREMIUM_ANNUALLY')
        ]
    ],
    'enterprise' => [
        'title' => 'Enterprise - 500 Orders Per Month',
        'monthly' => [
            'price' => 74900,
            'stripe_id' => env('PLAN_ENTERPRISE_MONTHLY', 'plan_Fc4fKkp5DzxqU8')
        ],
        'annually' => [
            'price' => 718800,
            'stripe_id' => env('PLAN_ENTERPRISE_ANNUALLY')
        ]
    ]
];
