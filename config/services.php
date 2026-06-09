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

    'django_auth' => [
        'base_url'    => env('API_AUTH'),
        'secret_key'  => env('SECRET_KEY'),
        'system_id'   => env('ID_SISTEMA'),
        'verify_tk'   => env('API_VERIFY_TK'),
        'inactivate'  => env('API_INA_TK'),
        'default_email_domain' => env('DEFAULT_EMAIL_DOMAIN', 'grupo-iai.com.mx'),
    ],

    'workflow' => [
        'base_url' => env('API_WF_URL'),
        'token' => env('TOKEN_WF'),
    ],

];