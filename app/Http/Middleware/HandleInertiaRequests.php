<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => Auth::user() ? [
                    'id' => Auth::user()->id,
                    'username' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'theme' => Auth::user()->theme,
                    'is_admin' => (bool) Auth::user()->is_admin,
                    'is_super_admin' => Auth::user()->isSuperAdmin(),
                    'is_vendor' => Auth::user()->isVendor(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'unreadNotifications' => fn () => Auth::user()
                ? Auth::user()->unreadNotifications()->count()
                : 0,
        ]);
    }
}
