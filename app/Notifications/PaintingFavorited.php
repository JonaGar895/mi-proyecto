<?php

namespace App\Notifications;

use App\Models\Painting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaintingFavorited extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Painting $painting,
        public readonly User $user,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'heart',
            'title' => 'Nueva obra en favoritos',
            'message' => sprintf(
                '%s ha añadido tu obra "%s" a sus favoritos.',
                $this->user->name,
                $this->painting->title,
            ),
            'url' => route('paintings.show', $this->painting, absolute: false),
        ];
    }
}
