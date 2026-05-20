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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'sa-east-1'),
    ],

    'api' => [
        'token' => env('API_TOKEN'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'sendy' => [
        'api_base_url' => env('SENDY_API_BASE_URL'),
        'api_token' => env('SENDY_API_TOKEN'),
        'list_id' => env('SENDY_LIST_ID'),
        'list_internal_id' => env('SENDY_LIST_INTERNAL_ID'),
        'brand_id' => env('SENDY_BRAND_ID', 1),
        'silent_authenticated' => env('SENDY_SILENT_AUTHENTICATED', true),
        'silent_visitor' => env('SENDY_SILENT_VISITOR', false),
        // false em dev local (Mac sem acesso à DB Sendy de prod); true em prod
        'db_enabled' => env('SENDY_DB_ENABLED', true),
    ],

];
