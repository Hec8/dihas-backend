<?php

namespace App\Notifications;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewContactMessage extends Notification
{
    use Queueable;

    protected $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'contact_message',
            'title' => 'Nouveau message de contact',
            'message' => "Message reçu de {$this->contact->name}",
            'data' => [
                'contact_id' => $this->contact->id,
                'name' => $this->contact->name,
                'email' => $this->contact->email,
                'subject' => $this->contact->subject
            ]
        ];
    }
}
