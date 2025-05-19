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
    if (!$request->hasValidSignature()) {
        abort(403, 'Invalid signature');
    }

    $user = $request->user();
    
    // Si aucun utilisateur n'est connecté, essayez de le récupérer depuis l'ID
    if (!$user) {
        $user = \App\Models\User::find($request->id);
        if (!$user) {
            abort(404, 'User not found');
        }
        auth()->login($user); // Connectez l'utilisateur temporairement
    }

    if ($user->hasVerifiedEmail()) {
        return redirect()->intended(
            $user->role === 'super_admin' 
                ? config('app.frontend_url').'/dashboard?verified=1'
                : config('app.frontend_url').'/content-creator-dashboard?verified=1'
        );
    }

    if ($user->markEmailAsVerified()) {
        event(new Verified($user));
    }

    return redirect()->intended(
        $user->role === 'super_admin'
            ? config('app.frontend_url').'/dashboard?verified=1'
            : config('app.frontend_url').'/content-creator-dashboard?verified=1'
    );
}
}
