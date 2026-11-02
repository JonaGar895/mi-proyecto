<?php

namespace App\Http\Controllers;

use App\Models\BalanceTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Añade saldo a la cuenta del usuario autenticado.
     *
     * La recarga es simulada (no hay pasarela de pago real): el objetivo es
     * que el usuario pueda disponer de saldo para comprar obras.
     */
    public function deposit(Request $request)
    {
        $attributes = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'],
        ]);

        $amount = round((float) $attributes['amount'], 2);

        DB::transaction(function () use ($request, $amount) {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);

            $user->increment('balance', $amount);
            $user->refresh();

            BalanceTransaction::create([
                'user_id' => $user->id,
                'created_by' => $user->id,
                'type' => BalanceTransaction::TYPE_DEPOSIT,
                'amount' => $amount,
                'balance_after' => $user->balance,
                'description' => 'Recarga de saldo',
            ]);
        });

        return redirect()
            ->route('account')
            ->with('success', sprintf('Se han añadido %s a tu saldo.', number_format($amount, 2, ',', '.').' €'));
    }
}
