@component('mail::message')
# Bienvenue dans notre équipe !

Voici vos identifiants pour accéder à la plateforme :

**Email:** {{ $email }}
**Mot de passe:** {{ $password }}
**Votre rôle:** {{ $role }}

@component('mail::button', ['url' => url('/login')])
Se connecter
@endcomponent

Nous vous recommandons de changer votre mot de passe après votre première connexion.

Merci,
{{ config('app.name') }}
@endcomponent