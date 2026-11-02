<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Inquiry;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_approving_a_sale_moves_money_to_the_super_admin_account(): void
    {
        [$admin, $buyer, $seller, $painting, $inquiry] = $this->saleScenario(price: 100, buyerBalance: 500);

        $this->actingAs($admin)
            ->post(route('inquiries.approve', $inquiry))
            ->assertRedirect();

        $this->assertSame('400.00', $buyer->fresh()->balance);
        $this->assertSame('100.00', Account::superAdmin()->balance);
        $this->assertSame('sold', $painting->fresh()->status);
        $this->assertSame($buyer->id, $painting->fresh()->sold_to_user_id);
        $this->assertSame('approved', $inquiry->fresh()->status);

        $this->assertDatabaseHas('account_transactions', [
            'inquiry_id' => $inquiry->id,
            'painting_id' => $painting->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'type' => 'sale_income',
            'amount' => 100,
        ]);

        $this->assertDatabaseHas('balance_transactions', [
            'user_id' => $buyer->id,
            'painting_id' => $painting->id,
            'type' => 'purchase',
            'amount' => 100,
            'balance_after' => 400,
        ]);
    }

    public function test_marking_a_sale_as_sold_also_charges_the_buyer_only_once(): void
    {
        [$admin, $buyer, , $painting, $inquiry] = $this->saleScenario(price: 250, buyerBalance: 1000);

        $this->actingAs($admin)
            ->post(route('inquiries.markSold', $inquiry))
            ->assertRedirect();

        $this->assertSame('750.00', $buyer->fresh()->balance);
        $this->assertSame('250.00', Account::superAdmin()->balance);
        $this->assertSame('sold', $painting->fresh()->status);

        // Intentar cobrar dos veces la misma obra no debe mover el dinero.
        $this->actingAs($admin)
            ->post(route('inquiries.approve', $inquiry))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('750.00', $buyer->fresh()->balance);
        $this->assertSame('250.00', Account::superAdmin()->balance);
        $this->assertSame(1, Account::superAdmin()->transactions()->count());
    }

    public function test_sale_is_not_completed_when_the_buyer_has_insufficient_balance(): void
    {
        [$admin, $buyer, , $painting, $inquiry] = $this->saleScenario(price: 100, buyerBalance: 40);

        $this->actingAs($admin)
            ->post(route('inquiries.approve', $inquiry))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('40.00', $buyer->fresh()->balance);
        $this->assertSame('0.00', Account::superAdmin()->balance);
        $this->assertSame('available', $painting->fresh()->status);
        $this->assertSame('pending', $inquiry->fresh()->status);
        $this->assertDatabaseCount('account_transactions', 0);
    }

    public function test_non_admin_cannot_approve_a_sale(): void
    {
        [$admin, $buyer, , $painting, $inquiry] = $this->saleScenario(price: 100, buyerBalance: 500);

        $this->actingAs($buyer)
            ->post(route('inquiries.approve', $inquiry))
            ->assertForbidden();

        $this->assertSame('500.00', $buyer->fresh()->balance);
        $this->assertSame('0.00', Account::superAdmin()->balance);
        $this->assertSame('available', $painting->fresh()->status);
    }

    public function test_only_the_super_admin_can_view_the_sales_account(): void
    {
        [$admin, $buyer] = $this->saleScenario(price: 100, buyerBalance: 500);

        $this->actingAs($admin)
            ->get(route('super-admin.account'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Account/SuperAdmin')
                ->has('account')
                ->has('stats')
                ->has('transactions.data'));

        $this->actingAs($buyer)
            ->get(route('super-admin.account'))
            ->assertForbidden();
    }

    private function saleScenario(float $price, float $buyerBalance): array
    {
        $admin = User::factory()->create([
            'id' => 1,
            'is_admin' => true,
            'balance' => 0,
        ]);

        $seller = User::factory()->create(['balance' => 0]);
        $buyer = User::factory()->create(['balance' => $buyerBalance]);

        $painting = Painting::factory()->for($seller)->create([
            'price' => $price,
            'status' => 'available',
        ]);

        $inquiry = Inquiry::create([
            'painting_id' => $painting->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => 'pending',
        ]);

        return [$admin, $buyer, $seller, $painting, $inquiry];
    }
}
