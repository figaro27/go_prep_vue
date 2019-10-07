<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net')
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1')
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET')
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300)
        ]
    ],

    'cloudflare' => [
        'user' => env('CLOUDFLARE_USER'),
        'key' => env('CLOUDFLARE_KEY'),
        'zone' => env('CLOUDFLARE_ZONE', 'goprep.com')
    ],

    'authorize' => [
        'login_id' => env('AUTHORIZE_LOGIN_ID'),
        'transaction_key' => env('AUTHORIZE_TRANSACTION_KEY'),
        'public_key' => env('AUTHORIZE_PUBLIC_KEY')
    ]
];
