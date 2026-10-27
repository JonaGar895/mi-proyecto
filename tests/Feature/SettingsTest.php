<?php

namespace Tests\Feature;

use App\Models\Painting;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_settings(): void
    {
        $this->get('/settings')->assertRedirect('/login');
    }

    public function test_settings_shows_user_stats_and_data(): void
    {
        $user = User::factory()->create();
        Painting::factory()->for($user)->create();
        $painting = Painting::factory()->create();
        $user->favoritePaintings()->attach($painting);
        Publication::factory()->for($user)->create();

        $this->actingAs($user)
            ->get('/settings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Settings')
                ->where('stats.paintings', 1)
                ->where('stats.favorites', 1)
                ->where('stats.publications', 1)
                ->has('favoritePaintings')
                ->has('myPublications'));
    }
}
