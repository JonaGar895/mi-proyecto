<?php

namespace App\Notifications;

use App\Models\Painting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaintingRated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Painting $painting,
        public readonly User $user,
        public readonly int $rating,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'star',
            'title' => 'Nueva valoración',
            'message' => sprintf(
                '%s ha valorado tu obra "%s" con %d estrella%s.',
                $this->user->name,
                $this->painting->title,
                $this->rating,
                $this->rating > 1 ? 's' : '',
            ),
            'url' => route('paintings.show', $this->painting, absolute: false),
        ];
    }
}
