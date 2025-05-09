<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Route pour Sanctum
Route::get('/sanctum/csrf-cookie', function() {
    return new Response('', 204);
});

// Route pour la compatibilité avec le frontend actuel
Route::get('/csrf-token', function() {
    return new JsonResponse(['token' => csrf_token()]);
});

require __DIR__.'/auth.php';
