<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'kora' => [
        'delivery_fees' => [
            'Cotonou' => 1000,
            'Abomey-Calavi' => 1000,
            'Porto-Novo' => 3000,
            'Parakou' => 3000,
        ],
    ],

    'fedapay' => [
        'driver' => env('PAYMENT_DRIVER', 'simulation'),
        'base_url' => env('FEDAPAY_BASE_URL', 'https://sandbox-api.fedapay.com/v1'),
        'secret_key' => env('FEDAPAY_SECRET_KEY'),
        'webhook_secret' => env('FEDAPAY_WEBHOOK_SECRET'),
        'callback_url' => env('FEDAPAY_CALLBACK_URL'),
        'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
    ],

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
