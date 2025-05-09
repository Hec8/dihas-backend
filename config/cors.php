<?php

return [
    'paths' => ['*'], // Autoriser toutes les routes
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        env('FRONTEND_URL', 'https://dihas.vercel.app'),
        'http://localhost:3000',
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => [
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'X-Requested-With',
        'Accept',
        'Accept-Version',
        'Content-Length',
        'Content-MD5',
        'Content-Type',
        'Date',
        'X-Api-Version',
        'Authorization',
    ],
    
    'exposed_headers' => [
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
    ],
    
    'max_age' => 60 * 60 * 24, // 24 heures
    
    'supports_credentials' => true,
];