<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Usuarios demo con los que se puede iniciar sesión.
     *
     * El primer usuario es el super administrador (debe quedar con ID 1
     * en una base de datos nueva).
     *
     * @var list<array{name: string, email: string, password: string, is_admin: bool, is_vendor: bool, balance: float}>
     */
    public const DEMO_USERS = [
        [
            'name' => 'Broderick (Super Admin)',
            'email' => 'broderick14@example.org',
            'password' => 'password',
            'is_admin' => true,
            'is_vendor' => true,
            'balance' => 100000,
        ],
        [
            'name' => 'Ana Administradora',
            'email' => 'ana.admin@galeria.test',
            'password' => 'password',
            'is_admin' => true,
            'is_vendor' => true,
            'balance' => 100000,
        ],
        [
            'name' => 'Javier Vendedor',
            'email' => 'javier.vendedor@galeria.test',
            'password' => 'password',
            'is_admin' => false,
            'is_vendor' => true,
            'balance' => 100000,
        ],
        [
            'name' => 'Lucía Vendedora',
            'email' => 'lucia.vendedora@galeria.test',
            'password' => 'password',
            'is_admin' => false,
            'is_vendor' => true,
            'balance' => 100000,
        ],
        [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => '12345678',
            'is_admin' => false,
            'is_vendor' => false,
            'balance' => 100000,
        ],
        [
            'name' => 'María Compradora',
            'email' => 'maria.compradora@galeria.test',
            'password' => 'password',
            'is_admin' => false,
            'is_vendor' => false,
            'balance' => 100000,
        ],
        [
            'name' => 'Carlos Coleccionista',
            'email' => 'carlos.coleccionista@galeria.test',
            'password' => 'password',
            'is_admin' => false,
            'is_vendor' => false,
            'balance' => 100000,
        ],
        [
            'name' => 'Sofía Visitante',
            'email' => 'sofia.visitante@galeria.test',
            'password' => 'password',
            'is_admin' => false,
            'is_vendor' => false,
            'balance' => 100000,
        ],
    ];

    public function run(): void
    {
        foreach (self::DEMO_USERS as $demoUser) {
            $user = User::firstOrNew(['email' => $demoUser['email']]);

            // Los roles se mantienen sincronizados en cada ejecución.
            $user->name = $demoUser['name'];
            $user->is_admin = $demoUser['is_admin'];
            $user->is_vendor = $demoUser['is_vendor'];

            if (! $user->exists) {
                $user->password = Hash::make($demoUser['password']);
                $user->balance = $demoUser['balance'];
                $user->stripe_token = 'demo_'.Str::random(24);
                $user->email_verified_at = now();
            }

            $user->save();
        }
    }

    /**
     * @return Collection<int, User>
     */
    public static function demoUsers(): Collection
    {
        return User::query()
            ->whereIn('email', array_column(self::DEMO_USERS, 'email'))
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public static function vendors(): Collection
    {
        return self::demoUsers()
            ->filter(fn (User $user) => $user->isVendor())
            ->values();
    }
}
