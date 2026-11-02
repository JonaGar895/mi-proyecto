<?php

namespace Database\Seeders;

use App\Models\AccountTransaction;
use App\Models\BalanceTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class BalanceTransactionSeeder extends Seeder
{
    /**
     * Genera movimientos de saldo de demostración:
     *  - Una recarga inicial para cada usuario demo.
     *  - Los cargos de las ventas ya registradas antes de existir el libro
     *    de movimientos.
     */
    public function run(): void
    {
        $salesByBuyer = AccountTransaction::query()
            ->where('type', AccountTransaction::TYPE_SALE_INCOME)
            ->whereNotNull('buyer_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('buyer_id');

        foreach (UserSeeder::demoUsers() as $user) {
            if ($user->balanceTransactions()->where('type', BalanceTransaction::TYPE_DEPOSIT)->exists()) {
                continue;
            }

            $buyerSales = $salesByBuyer->get($user->id, collect());
            $hasPurchaseLedger = $user->balanceTransactions()
                ->where('type', BalanceTransaction::TYPE_PURCHASE)
                ->exists();

            // Si hay ventas antiguas sin movimientos, el saldo inicial debe
            // incluir esas compras para que el histórico cuadre con el saldo actual.
            $missingPurchases = $hasPurchaseLedger ? 0 : (float) $buyerSales->sum('amount');
            $startingBalance = round((float) $user->balance + $missingPurchases, 2);

            $this->record([
                'user_id' => $user->id,
                'created_by' => $user->id,
                'type' => BalanceTransaction::TYPE_DEPOSIT,
                'amount' => $startingBalance,
                'balance_after' => $startingBalance,
                'description' => 'Saldo inicial de demostración',
            ], now()->subDays(60));
        }

        foreach ($salesByBuyer as $buyerId => $sales) {
            $user = User::query()->find($buyerId);

            if (! $user) {
                continue;
            }

            // Solo rellenamos compradores que aún no tengan movimientos de compra.
            if ($user->balanceTransactions()->where('type', BalanceTransaction::TYPE_PURCHASE)->exists()) {
                continue;
            }

            $runningBalance = round((float) $user->balance + (float) $sales->sum('amount'), 2);

            foreach ($sales as $sale) {
                $runningBalance = round($runningBalance - (float) $sale->amount, 2);

                $this->record([
                    'user_id' => $user->id,
                    'painting_id' => $sale->painting_id,
                    'created_by' => $sale->seller_id,
                    'type' => BalanceTransaction::TYPE_PURCHASE,
                    'amount' => $sale->amount,
                    'balance_after' => $runningBalance,
                    'description' => sprintf('Compra de la obra registrada en la venta #%d', $sale->id),
                ], $sale->created_at);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function record(array $attributes, mixed $createdAt): void
    {
        $transaction = BalanceTransaction::create($attributes);
        $transaction->timestamps = false;
        $transaction->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();
    }
}
