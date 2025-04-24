<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $password;
    public $role;

    public function __construct($email, $password, $role)
    {
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function build()
{
    return $this->subject('Vos accès ' . config('app.name'))
                ->markdown('emails.employee-credentials', [
                    'email' => $this->email,
                    'password' => $this->password,
                    'role' => $this->role // Ajout du rôle dans l'email
                ]);
}
}