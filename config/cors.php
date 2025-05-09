<?php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie', 
        'login',
        'logout',
        'csrf-token', // Ajoutez votre route personnalisée ici
        'user' // Si vous utilisez /api/user
    ],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        'https://dihas.vercel.app',
        'http://localhost:3000' // Pour le développement local
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => [
        'X-CSRF-TOKEN',
        'X-Requested-With',
        'Content-Type',
        'Authorization'
    ],
    
    'exposed_headers' => [
        'X-CSRF-TOKEN' // Important pour Sanctum
    ],
    
    'max_age' => 60 * 60 * 2, // 2 heures
    
    'supports_credentials' => true, // Crucial pour les cookies
];