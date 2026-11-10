<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InquiryProcessed extends Notification implements ShouldQueue
{
    use Queueable;

    private const STATUS_LABELS = [
        'pending' => 'pendiente',
        'contacted' => 'marcada como contactado',
        'approved' => 'aprobada',
        'rejected' => 'rechazada',
        'declined' => 'rechazada',
        'closed' => 'cerrada',
    ];

    public function __construct(public readonly Inquiry $inquiry) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $label = self::STATUS_LABELS[$this->inquiry->status] ?? $this->inquiry->status;

        return [
            'icon' => '📋',
            'title' => 'Actualización de tu solicitud',
            'message' => sprintf(
                'Tu solicitud de compra de "%s" ha sido %s.',
                $this->inquiry->painting->title,
                $label,
            ),
            'url' => route('inquiries.index', absolute: false),
        ];
    }
}
