<?php

namespace Database\Seeders;

use App\Exceptions\SaleException;
use App\Models\Account;
use App\Models\Inquiry;
use App\Models\Painting;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Crea un par de ventas demo usando el mismo servicio que la aplicación,
     * de modo que se descuente el saldo del comprador y el dinero quede en la
     * cuenta del super administrador con su movimiento correspondiente.
     */
    public function run(SaleService $saleService): void
    {
        $account = Account::superAdmin();

        if ($account->transactions()->exists()) {
            $this->command?->info('La cuenta del super administrador ya tiene movimientos; no se crean ventas demo.');

            return;
        }

        $admin = User::query()
            ->where('id', 1)
            ->where('is_admin', true)
            ->first()
            ?? User::query()->where('is_admin', true)->orderBy('id')->first();

        if (! $admin || ! $admin->canApproveSales()) {
            return;
        }

        $buyers = UserSeeder::demoUsers()
            ->reject(fn (User $user) => $user->isAdmin())
            ->values();

        if ($buyers->isEmpty()) {
            return;
        }

        $paintings = Painting::query()
            ->where('status', 'available')
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->orderBy('price')
            ->take(2)
            ->get();

        $created = 0;

        foreach ($paintings as $painting) {
            $buyer = $buyers
                ->reject(fn (User $user) => $user->id === $painting->user_id)
                ->first(fn (User $user) => (float) $user->balance >= (float) $painting->price);

            if (! $buyer) {
                continue;
            }

            $inquiry = Inquiry::firstOrCreate(
                [
                    'painting_id' => $painting->id,
                    'buyer_id' => $buyer->id,
                ],
                [
                    'seller_id' => $painting->user_id,
                    'message' => 'Me interesa mucho esta obra. ¿Sigue disponible?',
                    'status' => 'pending',
                ],
            );

            try {
                $saleService->complete($inquiry, $admin);
                $created++;
            } catch (SaleException $exception) {
                $this->command?->warn("No se pudo crear la venta demo de \"{$painting->title}\": {$exception->getMessage()}");
            }
        }

        $this->command?->info("Ventas demo creadas: {$created}");
    }
}
