<?php

namespace Tests\Feature;

use App\Models\BalanceTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_a_user_can_add_balance_to_their_account(): void
    {
        $user = User::factory()->create(['balance' => 100]);

        $this->actingAs($user)
            ->post(route('wallet.deposit'), ['amount' => 50.5])
            ->assertRedirect(route('account'))
            ->assertSessionHas('success');

        $this->assertSame('150.50', $user->fresh()->balance);

        $this->assertDatabaseHas('balance_transactions', [
            'user_id' => $user->id,
            'created_by' => $user->id,
            'type' => BalanceTransaction::TYPE_DEPOSIT,
            'amount' => 50.5,
            'balance_after' => 150.5,
        ]);
    }

    public function test_the_deposit_amount_must_be_between_the_allowed_limits(): void
    {
        $user = User::factory()->create(['balance' => 100]);

        foreach ([0, -10, 100001, 'abc'] as $amount) {
            $this->actingAs($user)
                ->post(route('wallet.deposit'), ['amount' => $amount])
                ->assertSessionHasErrors('amount');
        }

        $this->assertSame('100.00', $user->fresh()->balance);
        $this->assertDatabaseCount('balance_transactions', 0);
    }

    public function test_guests_cannot_add_balance(): void
    {
        $this->post(route('wallet.deposit'), ['amount' => 50])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('balance_transactions', 0);
    }

    public function test_the_account_page_lists_the_balance_movements(): void
    {
        $user = User::factory()->create(['balance' => 100]);

        $this->actingAs($user)->post(route('wallet.deposit'), ['amount' => 25]);
        $user->refresh();

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Settings')
                ->where('balance', '125.00')
                ->has('walletTransactions', 1)
                ->where('walletTransactions.0.type', BalanceTransaction::TYPE_DEPOSIT)
                ->where('walletTransactions.0.amount', '25.00')
                ->where('walletTransactions.0.balance_after', '125.00'));
    }
}
