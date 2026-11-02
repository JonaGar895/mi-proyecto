<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isSuperAdmin(), 403, 'Solo el super administrador puede consultar esta cuenta.');

        $account = Account::superAdmin();
        $account->load('user:id,name,email');

        $transactions = $account->transactions()
            ->with([
                'painting:id,title,emoji',
                'buyer:id,name',
                'seller:id,name',
            ])
            ->latest()
            ->paginate(15)
            ->through(fn (AccountTransaction $transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'created_at' => $transaction->created_at?->format('d/m/Y H:i'),
                'painting' => $transaction->painting ? [
                    'id' => $transaction->painting->id,
                    'title' => $transaction->painting->title,
                    'emoji' => $transaction->painting->emoji,
                ] : null,
                'buyer' => $transaction->buyer?->name,
                'seller' => $transaction->seller?->name,
            ]);

        $incomeQuery = $account->transactions()
            ->where('type', AccountTransaction::TYPE_SALE_INCOME);

        return Inertia::render('Account/SuperAdmin', [
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'balance' => $account->balance,
                'owner' => $account->user?->only(['id', 'name', 'email']),
            ],
            'stats' => [
                'total_income' => (float) $incomeQuery->sum('amount'),
                'sales_count' => $incomeQuery->count(),
            ],
            'transactions' => $transactions,
        ]);
    }
}
