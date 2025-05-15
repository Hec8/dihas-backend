@component('mail::message')
# 🎉 Bienvenue dans notre équipe !

Nous sommes ravis de vous accueillir dans notre équipe.  
Voici vos informations de connexion à la plateforme :

@component('mail::panel')
**📧 Email:** {{ $email }}  
**🔐 Mot de passe:** {{ $password }}  
**👤 Rôle:** {{ ucfirst($role) }}
@endcomponent

@component('mail::button', ['url' => env('FRONTEND_URL', 'http://localhost:3000') . '/login'])
➡️ Se connecter
@endcomponent

---

🔒 Pour votre sécurité, nous vous recommandons de **changer votre mot de passe** dès votre première connexion.

Si vous avez besoin d'aide, n'hésitez pas à nous contacter.

Merci et à bientôt,  
**Notre équipe DIHAS**
@endcomponent
