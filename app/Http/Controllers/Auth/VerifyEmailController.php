<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->guest(
                config('app.frontend_url').'/login'
            );
        }

        $user = $request->user();
        
        // Si l'email est déjà vérifié
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(
                $user->role === 'admin' 
                    ? config('app.frontend_url').'/dashboard?verified=1'
                    : config('app.frontend_url').'/content-creator-dashboard?verified=1'
            );
        }

        // Marquer l'email comme vérifié
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Rediriger vers le bon tableau de bord en fonction du rôle
        return redirect()->intended(
            $user->role === 'admin'
                ? config('app.frontend_url').'/dashboard?verified=1'
                : config('app.frontend_url').'/content-creator-dashboard?verified=1'
        );
    }
}
