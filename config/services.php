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

    /*
    |--------------------------------------------------------------------------
    | IMAP Configuration (Hostinger)
    |--------------------------------------------------------------------------
    */
    'imap' => [
        'host'          => env('IMAP_HOST', 'imap.hostinger.com'),
        'port'          => env('IMAP_PORT', 993),
        'encryption'    => env('IMAP_ENCRYPTION', 'ssl'),
        'validate_cert' => env('IMAP_VALIDATE_CERT', true),
        'username'      => env('IMAP_USERNAME'),
        'password'      => env('IMAP_PASSWORD'),
        'protocol'      => env('IMAP_PROTOCOL', 'imap'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenRouter AI Configuration
    |--------------------------------------------------------------------------
    */
    'openrouter' => [
        'api_key'  => env('OPENROUTER_API_KEY'),
        'model'    => env('OPENROUTER_MODEL', 'qwen/qwen3-8b'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'timeout'  => env('OPENROUTER_TIMEOUT', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | OTA Email Whitelist
    |--------------------------------------------------------------------------
    */
    'ota' => [
        'whitelist_domains' => env('OTA_WHITELIST_DOMAINS', 'tiket.com,traveloka.com'),
        'whitelist_senders' => env('OTA_WHITELIST_SENDERS', 'info.partner@tiket.com,hotel@traveloka.com'),
    ],

];
