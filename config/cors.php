<?php

return [


    'paths' => ['api/*', 'sanctum/csrf-cookie'],


    'allowed_methods' => ['*'],


    'allowed_origins' => [
        'https://cms-gogrouprealestate.prismcloudhosting.com',
        'https://www.gogrouprealestate.com',
        'https://gogrouprealestate.com',
    ],


    'allowed_origins_patterns' => [],


    'allowed_headers' => ['*'],


    'exposed_headers' => [],


    'max_age' => 0,


    'supports_credentials' => false,

];