<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Exhibition;
use App\Models\Inquiry;
use App\Models\Painting;
use App\Models\Publication;
use App\Models\PublicationComment;
use App\Models\Rating;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isAdmin(), 403, 'Solo los administradores pueden acceder al panel.');

        return Inertia::render('Admin/Dashboard', [
            'stats' => $this->totals(),
            'salesByMonth' => $this->salesByMonth(),
            'categories' => $this->categories(),
            'topArtists' => $this->topArtists(),
            'pendingInquiries' => $this->pendingInquiries(),
            'recentSales' => $this->recentSales(),
        ]);
    }

    /** @return array<string, int|float> */
    private function totals(): array
    {
        return [
            'users' => User::count(),
            'paintings' => Painting::count(),
            'public_paintings' => Painting::where('is_public', true)->count(),
            'sold_paintings' => Painting::where('status', 'sold')->count(),
            'publications' => Publication::count(),
            'exhibitions' => Exhibition::count(),
            'comments' => Comment::count() + PublicationComment::count(),
            'favorites' => Painting::query()->has('favorites')->count(),
            'ratings' => Rating::count(),
            'pending_inquiries' => Inquiry::where('status', 'pending')->count(),
            'approved_inquiries' => Inquiry::where('status', 'approved')->count(),
            'revenue' => (float) AccountTransaction::whereIn('type', ['sale_income', 'income'])->sum('amount'),
        ];
    }

    /**
     * Ventas e ingresos de los últimos seis meses.
     *
     * @return list<array{month: string, sales: int, revenue: float}>
     */
    private function salesByMonth(): array
    {
        $start = now()->startOfMonth()->subMonths(5);

        $sales = Inquiry::query()
            ->where('status', 'approved')
            ->where('approved_at', '>=', $start)
            ->get(['approved_at'])
            ->groupBy(fn (Inquiry $inquiry) => $inquiry->approved_at?->format('Y-m') ?? 'unknown');

        $revenue = AccountTransaction::query()
            ->where('created_at', '>=', $start)
            ->get(['amount', 'created_at'])
            ->groupBy(fn (AccountTransaction $transaction) => $transaction->created_at->format('Y-m'));

        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            /** @var Carbon $month */
            $month = now()->startOfMonth()->subMonths($i);
            $key = $month->format('Y-m');

            $months[] = [
                'month' => $month->translatedFormat('M Y'),
                'sales' => $sales->get($key)?->count() ?? 0,
                'revenue' => (float) $revenue->get($key)?->sum('amount'),
            ];
        }

        return $months;
    }

    /**
     * Distribución de obras por categoría.
     *
     * @return list<array{name: string, paintings: int}>
     */
    private function categories(): array
    {
        return Category::query()
            ->withCount('paintings')
            ->orderByDesc('paintings_count')
            ->limit(8)
            ->get(['id', 'name'])
            ->map(fn (Category $category) => [
                'name' => $category->name,
                'paintings' => $category->paintings_count,
            ])
            ->all();
    }

    /**
     * Artistas con más obras publicadas.
     *
     * @return list<array{id: int, name: string, paintings: int, sold: int}>
     */
    private function topArtists(): array
    {
        return User::query()
            ->withCount([
                'paintings',
                'soldPaintings as paintings_sold_count',
            ])
            ->orderByDesc('paintings_count')
            ->limit(5)
            ->get(['id', 'name'])
            ->map(fn (User $artist) => [
                'id' => $artist->id,
                'name' => $artist->name,
                'paintings' => $artist->paintings_count,
                'sold' => $artist->paintings_sold_count,
            ])
            ->all();
    }

    /**
     * Solicitudes de compra pendientes de revisión.
     *
     * @return list<array<string, mixed>>
     */
    private function pendingInquiries(): array
    {
        return Inquiry::query()
            ->with(['painting:id,title,emoji,image_path,image_url,price', 'buyer:id,name', 'seller:id,name'])
            ->where('status', 'pending')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Inquiry $inquiry) => [
                'id' => $inquiry->id,
                'created_at' => $inquiry->created_at->diffForHumans(),
                'price' => $inquiry->painting->price,
                'painting' => [
                    'id' => $inquiry->painting->id,
                    'title' => $inquiry->painting->title,
                    'emoji' => $inquiry->painting->emoji,
                    'image_url' => ImageService::url($inquiry->painting->image_path, $inquiry->painting->image_url),
                ],
                'buyer' => $inquiry->buyer?->name,
                'seller' => $inquiry->seller?->name,
            ])
            ->all();
    }

    /**
     * Últimas ventas aprobadas.
     *
     * @return list<array<string, mixed>>
     */
    private function recentSales(): array
    {
        return Inquiry::query()
            ->with(['painting:id,title,emoji,image_path,image_url,price', 'buyer:id,name', 'seller:id,name'])
            ->where('status', 'approved')
            ->latest('approved_at')
            ->limit(6)
            ->get()
            ->map(fn (Inquiry $inquiry) => [
                'id' => $inquiry->id,
                'approved_at' => $inquiry->approved_at?->diffForHumans(),
                'price' => $inquiry->painting->price,
                'painting' => [
                    'id' => $inquiry->painting->id,
                    'title' => $inquiry->painting->title,
                    'emoji' => $inquiry->painting->emoji,
                    'image_url' => ImageService::url($inquiry->painting->image_path, $inquiry->painting->image_url),
                ],
                'buyer' => $inquiry->buyer?->name,
                'seller' => $inquiry->seller?->name,
            ])
            ->all();
    }
}
