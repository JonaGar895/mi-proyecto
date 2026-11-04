<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ThemePreferenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_cannot_update_theme(): void
    {
        $this->post('/mi-cuenta/tema', ['theme' => 'dark'])
            ->assertRedirect('/login');
    }

    public function test_user_can_save_theme(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/mi-cuenta/tema', ['theme' => 'dark'])
            ->assertNoContent();

        $this->assertSame('dark', $user->fresh()->theme);
    }

    public function test_theme_is_shared_with_inertia(): void
    {
        $user = User::factory()->create(['theme' => 'dark']);

        $this->actingAs($user)
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.user.theme', 'dark'));
    }

    public function test_invalid_theme_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/mi-cuenta/tema', ['theme' => 'blue'])
            ->assertSessionHasErrors('theme');
    }
}
