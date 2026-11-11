<?php

namespace App\Notifications;

use App\Models\Publication;
use App\Models\PublicationComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PublicationCommented extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PublicationComment $comment,
        public readonly Publication $publication,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'chat-bubble-left',
            'title' => 'Nuevo comentario',
            'message' => sprintf(
                '%s ha comentado tu publicación "%s".',
                $this->comment->user->name,
                $this->publication->title,
            ),
            'url' => route('publications.show', $this->publication, absolute: false),
        ];
    }
}
