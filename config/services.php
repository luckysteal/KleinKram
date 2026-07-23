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

    'german_verbs' => [
        'url' => env('GERMAN_VERBS_API_URL', 'https://german-verbs-api.onrender.com/german-verbs-api'),
    ],

    'ocr_space' => [
        'api_key' => env('OCR_SPACE_API_KEY', 'K89317752788957'),
    ],

    'tomtom' => [
        'key' => env('TOMTOM_API_KEY'),
        'base_url' => env('TOMTOM_API_URL', 'https://api.tomtom.com'),
    ],

    'routexl' => [
        'username' => env('ROUTEXL_USERNAME'),
        'password' => env('ROUTEXL_PASSWORD'),
        'base_url' => env('ROUTEXL_API_URL', 'https://api.routexl.com'),
    ],

    'sck_media' => [
        'max_upload_mb' => env('SCK_PHOTO_MAX_MB', 15),
        'max_photos_per_stop' => env('SCK_PHOTO_MAX_PER_STOP', 30),
    ],

];
