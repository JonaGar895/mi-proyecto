<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InquiryReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Inquiry $inquiry) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => '🛒',
            'title' => 'Nueva solicitud de compra',
            'message' => sprintf(
                '%s ha solicitado comprar tu obra "%s".',
                $this->inquiry->buyer->name,
                $this->inquiry->painting->title,
            ),
            'url' => route('inquiries.index', absolute: false),
        ];
    }
}
