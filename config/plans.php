<?php

return [
    'free_trial' => [
        'title' => "2 Week Free Trial then Basic plan",
        'monthly' => [
            'price' => 11900,
            'stripe_id' => env(
                'PLAN_FREE_TRIAL',
                'price_1Hej6QFJbkXmjRjE9miOKHlt'
            )
        ]
    ],
    'pay-as-you-go' => [
        'title' => "Pay as you go - 5% Transaction Fee",
        'monthly' => [
            'price' => 0,
            'price_upfront' => 9500
        ],
        'annually' => [
            'price' => 0,
            'price_upfront' => 9500
        ]
    ],
    'basic' => [
        'title' => 'Basic - Up to 50 Orders Per Month',
        'monthly' => [
            'price' => 11900,
            'stripe_id' => env('PLAN_BASIC_MONTHLY', 'plan_GwJScnWs2JdFx1'),
            'orders' => 50
        ],
        'annually' => [
            'price' => 118800,
            'stripe_id' => env('PLAN_BASIC_ANNUALLY'),
            'orders' => 50
        ]
    ],
    'basic-2' => [
        'title' => 'Basic 2 - Up to 100 Orders Per Month',
        'monthly' => [
            'price' => 19500,
            'stripe_id' => env('PLAN_BASIC2_MONTHLY', 'plan_GwJTjqaiawE8qc'),
            'orders' => 100
        ],
        'annually' => [
            'price' => 178800,
            'stripe_id' => env('PLAN_BASIC2_ANNUALLY'),
            'orders' => 100
        ]
    ],
    'standard' => [
        'title' => 'Standard - Up to 150 Orders Per Month',
        'monthly' => [
            'price' => 24900,
            'stripe_id' => env('PLAN_STANDARD_MONTHLY', 'plan_GwJTLtm1N0U8q5'),
            'orders' => 150
        ],
        'annually' => [
            'price' => 238800,
            'stripe_id' => env('PLAN_STANDARD_ANNUALLY'),
            'orders' => 150
        ]
    ],
    'standard-2' => [
        'title' => 'Standard 2 - Up to 225 Orders Per Month',
        'monthly' => [
            'price' => 33900,
            'stripe_id' => env('PLAN_STANDARD2_MONTHLY', 'plan_GwJUEiUT00zVIB'),
            'orders' => 225
        ],
        'annually' => [
            'price' => 328800,
            'stripe_id' => env('PLAN_STANDARD2_ANNUALLY'),
            'orders' => 225
        ]
    ],
    'premium' => [
        'title' => 'Premium - Up to 300 Orders Per Month',
        'monthly' => [
            'price' => 39900,
            'stripe_id' => env('PLAN_PREMIUM_MONTHLY', 'plan_GwJURTBcXketCG'),
            'orders' => 300
        ],
        'annually' => [
            'price' => 406800,
            'stripe_id' => env('PLAN_PREMIUM_ANNUALLY'),
            'orders' => 300
        ]
    ],
    'premium-2' => [
        'title' => 'Premium 2 - Up to 400 Orders Per Month',
        'monthly' => [
            'price' => 53500,
            'stripe_id' => env('PLAN_PREMIUM2_MONTHLY', 'plan_GwJYr0uxUQhL2B'),
            'orders' => 400
        ],
        'annually' => [
            'price' => 502800,
            'stripe_id' => env('PLAN_PREMIUM2_ANNUALLY'),
            'orders' => 400
        ]
    ],
    'enterprise' => [
        'title' => 'Enterprise - Up to 500 Orders Per Month',
        'monthly' => [
            'price' => 59900,
            'stripe_id' => env(
                'PLAN_ENTERPRISE_MONTHLY',
                'plan_GwJVn4VA9IONBR'
            ),
            'orders' => 500
        ],
        'annually' => [
            'price' => 598800,
            'stripe_id' => env('PLAN_ENTERPRISE_ANNUALLY'),
            'orders' => 500
        ]
    ],
    'smsNumber' => [
        'title' => 'Monthly SMS phone number',
        'price' => 800,
        'stripe_id' => env('PLAN_SMS_NUMBER', 'price_1ItE5BFJbkXmjRjECV0Yuy87')
    ]
];
