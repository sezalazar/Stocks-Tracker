<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stock_api' => [
        'token' => env('STOCK_API_TOKEN'),
    ],

    'polygon_api' => [
        'token' => env('POLYGON_API_TOKEN'),
    ],
    'financialmodelingprep_api' => [
        'token' => env('FINANCIALMODELINGPREP_API_TOKEN'),
    ],
    'fear_and_greed_api' => [
        'host' => env('FEAR_AND_GREED_API_HOST'),
        'key' => env('FEAR_AND_GREED_API_KEY'),
    ],
    'merval_api' => [
        'base_url' => env('MATRIZ_BASE_URL'),
        'cookie'   => env('MATRIZ_COOKIE'),
    ],
];
