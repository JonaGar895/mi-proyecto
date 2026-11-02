<?php

namespace App\Services;

use App\Exceptions\SaleException;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\BalanceTransaction;
use App\Models\Inquiry;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * Concreta la venta de una obra:
     *  - Descuenta el importe del saldo personal del comprador.
     *  - Ingresa ese mismo importe en la cuenta separada del super administrador.
     *  - Marca la obra como vendida y aprueba la solicitud.
     *
     * Todo se ejecuta dentro de una transacción para evitar que el dinero
     * se descuente sin llegar a la cuenta del super administrador (o al revés).
     *
     * @throws SaleException
     */
    public function complete(Inquiry $inquiry, User $admin): Inquiry
    {
        return DB::transaction(function () use ($inquiry, $admin) {
            $inquiry = Inquiry::query()->lockForUpdate()->findOrFail($inquiry->id);
            $painting = Painting::query()->lockForUpdate()->find($inquiry->painting_id);

            if (! $painting) {
                throw new SaleException('La obra asociada a esta solicitud ya no existe.');
            }

            if ($painting->status === 'sold') {
                throw new SaleException('Esta obra ya ha sido vendida.');
            }

            $price = round((float) ($painting->price ?? 0), 2);
            $buyer = User::query()->lockForUpdate()->find($inquiry->buyer_id);

            if ($price > 0) {
                if (! $buyer) {
                    throw new SaleException('El comprador de esta solicitud ya no existe.');
                }

                if (round((float) $buyer->balance, 2) < $price) {
                    throw new SaleException('El comprador no tiene saldo suficiente para completar la compra.');
                }

                $account = Account::superAdmin();
                $account = Account::query()->lockForUpdate()->findOrFail($account->id);

                $buyer->decrement('balance', $price);
                $account->increment('balance', $price);
                $buyer->refresh();

                BalanceTransaction::create([
                    'user_id' => $buyer->id,
                    'painting_id' => $painting->id,
                    'created_by' => $admin->id,
                    'type' => BalanceTransaction::TYPE_PURCHASE,
                    'amount' => $price,
                    'balance_after' => $buyer->balance,
                    'description' => sprintf('Compra de la obra "%s"', $painting->title),
                ]);

                AccountTransaction::create([
                    'account_id' => $account->id,
                    'inquiry_id' => $inquiry->id,
                    'painting_id' => $painting->id,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $painting->user_id,
                    'type' => AccountTransaction::TYPE_SALE_INCOME,
                    'amount' => $price,
                    'description' => sprintf('Venta de la obra "%s"', $painting->title),
                ]);
            }

            $painting->update([
                'status' => 'sold',
                'sold_at' => now(),
                'sold_to_user_id' => $inquiry->buyer_id,
            ]);

            $inquiry->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
            ]);

            Inquiry::where('painting_id', $painting->id)
                ->where('id', '!=', $inquiry->id)
                ->where('status', '!=', 'approved')
                ->update(['status' => 'closed']);

            return $inquiry->fresh();
        });
    }
}
