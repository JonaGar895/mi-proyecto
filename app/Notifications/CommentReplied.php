<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\PublicationComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommentReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Comment|PublicationComment $reply,
        public readonly Comment|PublicationComment $parent,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isPaintingComment = $this->parent instanceof Comment;

        return [
            'icon' => '↩️',
            'title' => 'Respuesta a tu comentario',
            'message' => sprintf(
                '%s ha respondido a tu comentario en "%s".',
                $this->reply->user->name,
                $isPaintingComment
                    ? $this->parent->painting?->title
                    : $this->parent->publication?->title,
            ),
            'url' => $isPaintingComment
                ? route('paintings.show', $this->parent->painting, absolute: false)
                : route('publications.show', $this->parent->publication, absolute: false),
        ];
    }
}
