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

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],
    'xendit' => [
        'secret_key' => env('XENDIT_SECRET_KEY', env('XENDIT_API_KEY')),
        'webhook_secret' => env('XENDIT_WEBHOOK_SECRET'),
    ],

    'rajaongkir' => [
        'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
        'api_key' => env('RAJAONGKIR_API_KEY'),
        'origin_id' => env('RAJAONGKIR_ORIGIN_ID', 17693),
        'price_type' => env('RAJAONGKIR_PRICE_TYPE', 'lowest'),
        'cache_ttl' => env('RAJAONGKIR_CACHE_TTL', 86400),
    ],

];
