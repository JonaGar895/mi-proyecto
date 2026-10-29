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
        abort_if($painting->user_id === $user->id, 403, 'No puedes solicitar comprar tu propia obra.');
        abort_if($painting->status === 'sold', 403, 'Esta obra ya ha sido vendida.');

        if (Inquiry::where('painting_id', $painting->id)->where('buyer_id', $user->id)->exists()) {
            return back()->with('success', 'Ya has enviado una solicitud de compra para esta obra.');
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

        return back()->with('success', 'Solicitud de compra enviada. Quedará pendiente de aprobación por un administrador.');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->canApproveSales()) {
            $allInquiries = Inquiry::with(['painting', 'buyer', 'seller', 'approvedBy', 'rejectedBy'])
                ->latest()
                ->get()
                ->map(fn (Inquiry $inquiry) => [
                    'id' => $inquiry->id,
                    'status' => $inquiry->status,
                    'message' => $inquiry->message,
                    'created_at' => $inquiry->created_at->diffForHumans(),
                    'approved_at' => $inquiry->approved_at?->diffForHumans(),
                    'rejected_at' => $inquiry->rejected_at?->diffForHumans(),
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
                    'seller' => [
                        'id' => $inquiry->seller->id,
                        'name' => $inquiry->seller->name,
                        'email' => $inquiry->seller->email,
                    ],
                    'approved_by' => $inquiry->approvedBy ? [
                        'id' => $inquiry->approvedBy->id,
                        'name' => $inquiry->approvedBy->name,
                    ] : null,
                    'rejected_by' => $inquiry->rejectedBy ? [
                        'id' => $inquiry->rejectedBy->id,
                        'name' => $inquiry->rejectedBy->name,
                    ] : null,
                    'is_super_admin' => $user->isSuperAdmin(),
                ]);

            return Inertia::render('Inquiries/Index', [
                'received' => [],
                'sent' => [],
                'adminAll' => $allInquiries,
                'isAdminView' => true,
            ]);
        }

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
            'adminAll' => [],
            'isAdminView' => false,
        ]);
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $user = $request->user();
        abort_unless($user->canApproveSales(), 403, 'Solo los administradores pueden aprobar o rechazar solicitudes de venta.');

        $attributes = $request->validate([
            'status' => ['required', 'in:pending,contacted,declined,rejected,closed'],
        ]);

        $data = ['status' => $attributes['status']];

        if (in_array($attributes['status'], ['declined', 'rejected'], true)) {
            $data['rejected_by'] = $user->id;
            $data['rejected_at'] = now();
        }

        $inquiry->update($data);

        return back()->with('success', 'Solicitud actualizada.');
    }

    public function approve(Request $request, Inquiry $inquiry)
    {
        $user = $request->user();
        abort_unless($user->canApproveSales(), 403, 'Solo los administradores o el super administrador pueden aprobar ventas.');
        $painting = $inquiry->painting()->lockForUpdate()->first();

        abort_if($painting->status === 'sold', 403, 'Esta obra ya ha sido vendida.');

        $buyer = $inquiry->buyer()->lockForUpdate()->first();
        if ($painting->price && $buyer && $buyer->balance < $painting->price) {
            return back()->with('error', 'El comprador no tiene saldo suficiente para completar la compra.');
        }

        if ($painting->price && $buyer) {
            $buyer->decrement('balance', $painting->price);
        }

        $painting->update([
            'status' => 'sold',
            'sold_at' => now(),
            'sold_to_user_id' => $inquiry->buyer_id,
        ]);

        $inquiry->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
        ]);

        Inquiry::where('painting_id', $painting->id)
            ->where('id', '!=', $inquiry->id)
            ->whereNotIn('status', ['approved'])
            ->update([
                'status' => 'closed',
            ]);

        return back()->with('success', 'Venta aprobada. La obra ha sido marcada como vendida y se ha descontado el importe al comprador.');
    }

    public function reject(Request $request, Inquiry $inquiry)
    {
        $user = $request->user();
        abort_unless($user->canApproveSales(), 403, 'Solo los administradores o el super administrador pueden rechazar solicitudes de venta.');
        $painting = $inquiry->painting;

        if ($painting->status === 'sold') {
            return back()->with('error', 'No se puede rechazar una solicitud si la obra ya está vendida.');
        }

        $inquiry->update([
            'status' => 'rejected',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return back()->with('success', 'Solicitud rechazada. El comprador ha sido notificado en sus solicitudes.');
    }

    public function markSold(Request $request, Inquiry $inquiry)
    {
        $user = $request->user();
        abort_unless($user->canApproveSales(), 403, 'Solo los administradores o el super administrador pueden marcar una venta como realizada.');
        $painting = $inquiry->painting;

        abort_if($painting->status === 'sold', 403);

        $painting->update([
            'status' => 'sold',
            'sold_at' => now(),
            'sold_to_user_id' => $inquiry->buyer_id,
        ]);

        $inquiry->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        Inquiry::where('painting_id', $painting->id)
            ->where('id', '!=', $inquiry->id)
            ->update(['status' => 'closed']);

        return back()->with('success', 'Venta aprobada y obra marcada como vendida.');
    }

    public function history(Request $request)
    {
        $user = $request->user();
        abort_unless($user->canApproveSales(), 403, 'Solo el administrador o el super administrador pueden ver el histórico de compras y ventas.');

        $inquiries = Inquiry::with(['painting', 'buyer', 'seller', 'approvedBy', 'rejectedBy'])
            ->whereIn('status', ['approved', 'rejected', 'declined', 'closed'])
            ->latest('updated_at')
            ->get()
            ->map(fn (Inquiry $inquiry) => [
                'id' => $inquiry->id,
                'status' => $inquiry->status,
                'message' => $inquiry->message,
                'created_at' => $inquiry->created_at,
                'created_at_human' => $inquiry->created_at?->diffForHumans(),
                'created_at_formatted' => $inquiry->created_at?->format('d/m/Y H:i:s'),
                'approved_at' => $inquiry->approved_at,
                'approved_at_formatted' => $inquiry->approved_at?->format('d/m/Y H:i:s'),
                'rejected_at' => $inquiry->rejected_at,
                'rejected_at_formatted' => $inquiry->rejected_at?->format('d/m/Y H:i:s'),
                'updated_at_formatted' => $inquiry->updated_at?->format('d/m/Y H:i:s'),
                'painting' => [
                    'id' => $inquiry->painting?->id,
                    'title' => $inquiry->painting?->title,
                    'price' => $inquiry->painting?->price,
                    'status' => $inquiry->painting?->status,
                ],
                'buyer' => [
                    'id' => $inquiry->buyer?->id,
                    'name' => $inquiry->buyer?->name,
                    'email' => $inquiry->buyer?->email,
                ],
                'seller' => [
                    'id' => $inquiry->seller?->id,
                    'name' => $inquiry->seller?->name,
                    'email' => $inquiry->seller?->email,
                ],
                'approved_by' => $inquiry->approvedBy ? [
                    'id' => $inquiry->approvedBy->id,
                    'name' => $inquiry->approvedBy->name,
                ] : null,
                'rejected_by' => $inquiry->rejectedBy ? [
                    'id' => $inquiry->rejectedBy->id,
                    'name' => $inquiry->rejectedBy->name,
                ] : null,
            ]);

        return Inertia::render('Inquiries/History', [
            'history' => $inquiries,
        ]);
    }
}
