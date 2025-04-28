<?php

namespace App\Notifications;

use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewsletterSubscription extends Notification
{
    use Queueable;

    protected $subscriber;

    public function __construct($subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'newsletter_subscription',
            'title' => 'Nouvelle inscription à la newsletter',
            'message' => "Nouvel abonné à la newsletter : {$this->subscriber->email}",
            'data' => [
                'subscriber_id' => $this->subscriber->id,
                'email' => $this->subscriber->email
            ]
        ];
    }
}
