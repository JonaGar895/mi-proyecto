<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Painting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaintingCommented extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Comment $comment,
        public readonly Painting $painting,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => '💬',
            'title' => 'Nuevo comentario',
            'message' => sprintf(
                '%s ha comentado tu obra "%s".',
                $this->comment->user->name,
                $this->painting->title,
            ),
            'url' => route('paintings.show', $this->painting, absolute: false),
        ];
    }
}
