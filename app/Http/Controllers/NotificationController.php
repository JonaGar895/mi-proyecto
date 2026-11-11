<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Notificaciones/Index', [
            'notifications' => [
                'data' => $notifications->getCollection()->map(fn (DatabaseNotification $notification) => [
                    'id' => $notification->id,
                    'icon' => $notification->data['icon'] ?? 'bell',
                    'title' => $notification->data['title'] ?? 'Notificación',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? null,
                    'read_at' => $notification->read_at?->diffForHumans(),
                    'created_at' => $notification->created_at->diffForHumans(),
                    'unread' => $notification->read_at === null,
                ])->all(),
                'links' => $notifications->links(),
                'has_pages' => $notifications->hasPages(),
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    public function read(Request $request, string $notification)
    {
        $user = $request->user();

        /** @var DatabaseNotification|null $notification */
        $notification = $user->notifications()->whereKey($notification)->first();

        abort_unless($notification !== null, 404);

        $notification->markAsRead();

        if ($request->boolean('open') && ($url = $notification->data['url'] ?? null)) {
            return redirect()->to($url);
        }

        return back();
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Notificaciones marcadas como leídas.');
    }

    public function destroy(Request $request, string $notification)
    {
        $user = $request->user();

        /** @var DatabaseNotification|null $notification */
        $notification = $user->notifications()->whereKey($notification)->first();

        abort_unless($notification !== null, 404);

        $notification->delete();

        return back()->with('success', 'Notificación eliminada.');
    }

    public function destroyAll(Request $request)
    {
        $request->user()->notifications()->delete();

        return back()->with('success', 'Notificaciones eliminadas.');
    }
}
