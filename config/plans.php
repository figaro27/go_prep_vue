<?php

return [
    'basic' => [
        'title' => 'Basic',
        'monthly' => [
            'price' => 10000,
            'stripe_id' => env('PLAN_BASIC_MONTHLY', 'plan_Fc4eOyFTAH2ZWn')
        ],
        'annually' => [
            'price' => 120000,
            'stripe_id' => env('PLAN_BASIC_ANNUALLY')
        ]
    ],
    'standard' => [
        'title' => 'Standard',
        'monthly' => [
            'price' => 20000,
            'stripe_id' => env('PLAN_STANDARD_MONTHLY', 'plan_Fc4fnXEBXrWlOO')
        ],
        'annually' => [
            'price' => 240000,
            'stripe_id' => env('PLAN_STANDARD_ANNUALLY')
        ]
    ],
    'premium' => [
        'title' => 'Premium',
        'monthly' => [
            'price' => 30000,
            'stripe_id' => env('PLAN_PREMIUM_MONTHLY', 'plan_Fc4fTbTtTr7LaN')
        ],
        'annually' => [
            'price' => 360000,
            'stripe_id' => env('PLAN_PREMIUM_ANNUALLY')
        ]
    ],
    'enterprise' => [
        'title' => 'Enterprise',
        'monthly' => [
            'price' => 40000,
            'stripe_id' => env('PLAN_ENTERPRISE_MONTHLY', 'plan_Fc4fKkp5DzxqU8')
        ],
        'annually' => [
            'price' => 480000,
            'stripe_id' => env('PLAN_ENTERPRISE_ANNUALLY')
        ]
    ]
];
