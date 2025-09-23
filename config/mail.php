<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    */
    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | We’ll use SES via SMTP:
    |  - Host: email-smtp.us-east-1.amazonaws.com
    |  - Port: 587
    |  - TLS
    |  - Username/Password are SES SMTP credentials (NOT IAM keys)
    */
    'mailers' => [
        'smtp' => [
            'transport'   => 'smtp',
            'host'        => env('MAIL_HOST', 'email-smtp.us-east-1.amazonaws.com'),
            'port'        => env('MAIL_PORT', 587),
            'encryption'  => env('MAIL_ENCRYPTION', 'tls'),
            'username'    => env('MAIL_USERNAME'),
            'password'    => env('MAIL_PASSWORD'),
            'timeout'     => null,
        ],

        // If you ever switch to AWS SDK transport instead of SMTP:
        'ses' => [
            'transport' => 'ses',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path'      => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel'   => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        // Fallback to log if SMTP fails
        'failover' => [
            'transport' => 'failover',
            'mailers'   => ['smtp', 'log'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | Must be a verified identity in SES. Your domain is verified, so this is OK.
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'info@gogrouprealestate.com'),
        'name'    => env('MAIL_FROM_NAME', 'GO Group Real Estate'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Force ALL Outgoing Mail "To" This Address
    |--------------------------------------------------------------------------
    |
    | This will route every email your app sends to info@gogrouprealestate.com,
    | regardless of what you pass to Mail::to(). Remove this block if you
    | want normal per-recipient delivery.
    */
    'to' => [
        'address' => env('MAIL_TO_ADDRESS', 'info@gogrouprealestate.com'),
        'name'    => env('MAIL_TO_NAME', 'GO Group Real Estate'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    */
    'markdown' => [
        'theme' => 'default',
        'paths' => [resource_path('views/vendor/mail')],
    ],

];