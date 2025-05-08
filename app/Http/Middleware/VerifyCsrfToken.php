<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/newsletter/*',
        'api/newsletter/subscribe',
        'api/blogs/guest',
        'api/blog/guest/*',
        'api/services',
        'api/contact/create',
        'sanctum/csrf-cookie'
    ];

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        // Allow OPTIONS requests
        if ($request->isMethod('OPTIONS')) {
            return true;
        }

        return parent::shouldPassThrough($request);
    }

    /**
     * Détermine si la requête est sécurisée.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isReading($request)
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }
}
