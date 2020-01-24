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
            'price' => 29900,
            'stripe_id' => env('PLAN_STANDARD_MONTHLY', 'plan_GbnhRLKcDytQaE')
        ],
        'annually' => [
            'price' => 286800,
            'stripe_id' => env('PLAN_STANDARD_ANNUALLY')
        ]
    ],
    'premium' => [
        'title' => 'Premium - 300 Orders Per Month',
        'monthly' => [
            'price' => 49900,
            'stripe_id' => env('PLAN_PREMIUM_MONTHLY', 'plan_GbnhSdBkKyIZT4')
        ],
        'annually' => [
            'price' => 478800,
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
    ],
    'basic-2' => [
        'title' => 'Basic - 50 Orders Per Month',
        'monthly' => [
            'price' => 21900,
            'stripe_id' => env('PLAN_BASIC2_MONTHLY', 'plan_GbnnRReEpGCEHf')
        ],
        'annually' => [
            'price' => 210000,
            'stripe_id' => env('PLAN_BASIC2_ANNUALLY')
        ]
    ],
    'standard-2' => [
        'title' => 'Standard - 150 Orders Per Month',
        'monthly' => [
            'price' => 39900,
            'stripe_id' => env('PLAN_STANDARD2_MONTHLY', 'plan_Gbnn0dqseBr1jq')
        ],
        'annually' => [
            'price' => 383000,
            'stripe_id' => env('PLAN_STANDARD2_ANNUALLY')
        ]
    ],
    'premium-2' => [
        'title' => 'Premium - 300 Orders Per Month',
        'monthly' => [
            'price' => 61900,
            'stripe_id' => env('PLAN_PREMIUM2_MONTHLY', 'plan_GbnouMHiThsBuz')
        ],
        'annually' => [
            'price' => 594000,
            'stripe_id' => env('PLAN_PREMIUM2_ANNUALLY')
        ]
    ]
];
