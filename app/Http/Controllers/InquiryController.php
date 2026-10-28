<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Painting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InquiryController extends Controller
{
    public function store(Request $request, Painting $painting)
    {
        $user = $request->user();
        abort_unless($painting->is_public || $painting->user_id === $user->id || $user->isAdmin(), 404);
        abort_if($painting->user_id === $user->id, 403);
        abort_if($painting->status === 'sold', 403);

        if (Inquiry::where('painting_id', $painting->id)->where('buyer_id', $user->id)->exists()) {
            return back()->with('success', 'Ya has mostrado interés por esta obra.');
        }

        $attributes = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        Inquiry::create([
            'painting_id' => $painting->id,
            'buyer_id' => $user->id,
            'seller_id' => $painting->user_id,
            'message' => $attributes['message'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Interés enviado al vendedor.');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $received = $user->inquiriesReceived()
            ->with(['painting', 'buyer'])
            ->latest()
            ->get()
            ->map(fn (Inquiry $inquiry) => [
                'id' => $inquiry->id,
                'status' => $inquiry->status,
                'message' => $inquiry->message,
                'created_at' => $inquiry->created_at->diffForHumans(),
                'painting' => [
                    'id' => $inquiry->painting->id,
                    'title' => $inquiry->painting->title,
                    'emoji' => $inquiry->painting->emoji,
                    'image_url' => $inquiry->painting->image_url,
                    'price' => $inquiry->painting->price,
                    'status' => $inquiry->painting->status,
                ],
                'buyer' => [
                    'id' => $inquiry->buyer->id,
                    'name' => $inquiry->buyer->name,
                    'email' => $inquiry->buyer->email,
                ],
            ]);

        $sent = $user->inquiriesSent()
            ->with(['painting', 'seller'])
            ->latest()
            ->get()
            ->map(fn (Inquiry $inquiry) => [
                'id' => $inquiry->id,
                'status' => $inquiry->status,
                'message' => $inquiry->message,
                'created_at' => $inquiry->created_at->diffForHumans(),
                'painting' => [
                    'id' => $inquiry->painting->id,
                    'title' => $inquiry->painting->title,
                    'emoji' => $inquiry->painting->emoji,
                    'image_url' => $inquiry->painting->image_url,
                    'price' => $inquiry->painting->price,
                    'status' => $inquiry->painting->status,
                ],
                'seller' => [
                    'id' => $inquiry->seller->id,
                    'name' => $inquiry->seller->name,
                    'email' => $inquiry->seller->email,
                ],
            ]);

        return Inertia::render('Inquiries/Index', [
            'received' => $received,
            'sent' => $sent,
        ]);
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $user = $request->user();
        abort_unless($inquiry->seller_id === $user->id || $user->isAdmin(), 403);

        $attributes = $request->validate([
            'status' => ['required', 'in:pending,contacted,declined,closed'],
        ]);

        $inquiry->update(['status' => $attributes['status']]);

        return back()->with('success', 'Solicitud actualizada.');
    }

    public function markSold(Request $request, Inquiry $inquiry)
    {
        $user = $request->user();
        $painting = $inquiry->painting;

        abort_unless($inquiry->seller_id === $user->id || $user->isAdmin(), 403);
        abort_if($painting->status === 'sold', 403);

        $painting->update([
            'status' => 'sold',
            'sold_at' => now(),
            'sold_to_user_id' => $inquiry->buyer_id,
        ]);

        $inquiry->update(['status' => 'closed']);

        Inquiry::where('painting_id', $painting->id)
            ->where('id', '!=', $inquiry->id)
            ->update(['status' => 'closed']);

        return back()->with('success', 'Obra marcada como vendida.');
    }
}
