<?php

return [

    'default' => env('MAIL_MAILER', 'smtp'),

    'mailers' => [

        // SES over SMTP
        'smtp' => [
            'transport'    => 'smtp',
            'host'         => env('MAIL_HOST', 'email-smtp.us-east-1.amazonaws.com'),
            'port'         => env('MAIL_PORT', 587),
            'encryption'   => env('MAIL_ENCRYPTION', 'tls'),
            'username'     => env('MAIL_USERNAME'),
            'password'     => env('MAIL_PASSWORD'),
            'timeout'      => null,

            // STATIC value (no env):
            'local_domain' => 'backend.gogrouprealestate.com',
        ],

        // SES via AWS SDK (optional alternative)
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

            // STATIC value (no env):
            'path' => '/usr/sbin/sendmail -bs -i',
        ],

        'log' => [
            'transport' => 'log',

            // STATIC value (no env): must match a channel in config/logging.php
            'channel' => 'stack',
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers'   => ['smtp', 'log'],
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'info@gogrouprealestate.com'),
        'name'    => env('MAIL_FROM_NAME', 'Global Opportunities Real Estate'),
    ],

    'markdown' => [
        'theme' => 'default',
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];