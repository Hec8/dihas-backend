<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->group('web', [
            \App\Http\Middleware\Cors::class
        ]);

        $middleware->group('api', [
            \App\Http\Middleware\Cors::class
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'cors' => \App\Http\Middleware\Cors::class,
        ]);

        $middleware->stateful([
            env('SANCTUM_STATEFUL_DOMAINS', 'dihas.vercel.app'),
            parse_url(env('APP_URL'), PHP_URL_HOST)
        ]);

        $middleware->web(append: [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            '/sanctum/csrf-cookie',
            '/csrf-token',
            '/login',
            '/register',
            '/forgot-password',
            '/reset-password',
            '/email/verification-notification',
            '/logout',
            '/user'
        ]);


        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
