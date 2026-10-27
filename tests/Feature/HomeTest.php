<?php

namespace Tests\Feature;

use App\Models\Painting;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_home(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_home_page_with_featured_data(): void
    {
        $user = User::factory()->create();
        Painting::factory()->create(['is_public' => true, 'title' => 'Pintura Destacada']);
        Publication::factory()->for(User::factory())->create(['title' => 'Publicación Destacada']);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->has('featuredPaintings')
                ->has('latestPublications'));
    }
}
