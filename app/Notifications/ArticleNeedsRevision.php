<?php

namespace App\Notifications;

use App\Models\Blog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ArticleNeedsRevision extends Notification
{
    use Queueable;

    protected $article;
    protected $reason;

    public function __construct(Blog $article,)
    {
        $this->article = $article;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'article_revision',
            'title' => 'Article à réviser',
            'message' => "Votre article '{$this->article->title}' nécessite une révision",
            'data' => [
                'article_id' => $this->article->id,
                'title' => $this->article->title,
            ]
        ];
    }
}
