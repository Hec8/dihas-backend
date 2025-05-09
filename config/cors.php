<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-token', 'login', 'csrf-token'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://dihas.vercel.app'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];