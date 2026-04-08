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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_APP_ID'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
    ],

    'paytm-wallet' => [
        'env' => env('PAYTM_ENVIRONMENT'), // values : (local | production)
        'merchant_id' => env('PAYTM_MERCHANT_ID'),
        'merchant_key' => env('PAYTM_MERCHANT_KEY'),
        'merchant_website' => env('PAYTM_MERCHANT_WEBSITE'),
        'channel' => env('PAYTM_CHANNEL'),
        'industry_type' => env('PAYTM_INDUSTRY_TYPE'),
    ],

    // SMS Channel
    "msg91" => [
        'key' => '', // set from Channel
    ],

    /*
    |--------------------------------------------------------------------------
    | WordPress / LearnPress Sync
    |--------------------------------------------------------------------------
    |
    | These values are used when sending courses from Laravel (Rocket LMS)
    | to a separate WordPress + LearnPress installation.
    |
    | WORDPRESS_SYNC_BASE_URL should be your WP site URL (e.g. https://example.com)
    | WORDPRESS_SYNC_API_TOKEN should match the token configured in the WP plugin.
    |
    */
    'wordpress_sync' => [
        'base_url'  => env('WORDPRESS_SYNC_BASE_URL'),
        'api_token' => env('WORDPRESS_SYNC_API_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Tag Manager
    |--------------------------------------------------------------------------
    |
    | container_id: e.g. GTM-XXXXXXX
    | load_strategy:
    |   - idle (default): inject gtm.js after window load + requestIdleCallback — better PageSpeed,
    |     tags fire within idle_timeout_ms. dataLayer exists immediately for early pushes.
    |   - eager: official async snippet in <head> — best for capturing fastest bounces / earliest hits.
    |
    */
    'gtm' => [
        'enabled' => filter_var(env('GTM_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'container_id' => env('GTM_CONTAINER_ID', 'GTM-NB3BZ2JT'),
        'load_strategy' => env('GTM_LOAD_STRATEGY', 'idle'),
        'idle_timeout_ms' => (int) env('GTM_IDLE_TIMEOUT_MS', 2500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Turnstile
    |--------------------------------------------------------------------------
    */
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],
];
