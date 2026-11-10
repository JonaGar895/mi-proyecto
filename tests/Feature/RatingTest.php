<?php

namespace Tests\Feature;

use App\Models\Painting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_user_can_rate_a_painting(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create(['is_public' => true]);

        $this->actingAs($user)
            ->post("/cuadros/{$painting->id}/rating", ['rating' => 4])
            ->assertRedirect();

        $this->assertDatabaseHas('ratings', [
            'user_id' => $user->id,
            'painting_id' => $painting->id,
            'rating' => 4,
        ]);
    }

    public function test_rating_is_updated_when_rated_twice(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create(['is_public' => true]);

        $this->actingAs($user)->post("/cuadros/{$painting->id}/rating", ['rating' => 2]);
        $this->actingAs($user)->post("/cuadros/{$painting->id}/rating", ['rating' => 5]);

        $this->assertDatabaseCount('ratings', 1);
        $this->assertDatabaseHas('ratings', [
            'user_id' => $user->id,
            'painting_id' => $painting->id,
            'rating' => 5,
        ]);
    }

    public function test_owner_cannot_rate_their_own_painting(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post("/cuadros/{$painting->id}/rating", ['rating' => 5])
            ->assertForbidden();

        $this->assertDatabaseCount('ratings', 0);
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create(['is_public' => true]);

        $this->actingAs($user)
            ->post("/cuadros/{$painting->id}/rating", ['rating' => 0])
            ->assertSessionHasErrors('rating');

        $this->actingAs($user)
            ->post("/cuadros/{$painting->id}/rating", ['rating' => 6])
            ->assertSessionHasErrors('rating');
    }

    public function test_painting_detail_includes_rating_information(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create(['is_public' => true]);
        $painting->ratings()->create(['user_id' => $user->id, 'rating' => 5]);

        $other = User::factory()->create();

        $this->actingAs($other)
            ->get("/cuadros/{$painting->id}")
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Paintings/Show')
                    ->where('painting.ratings_avg', 5)
                    ->where('painting.ratings_count', 1),
            );
    }

    public function test_gallery_shows_average_rating_and_sorts_by_rating(): void
    {
        $user = User::factory()->create();

        $best = Painting::factory()->create(['is_public' => true, 'title' => 'La mejor']);
        $worst = Painting::factory()->create(['is_public' => true, 'title' => 'La peor']);

        $best->ratings()->create(['user_id' => $user->id, 'rating' => 5]);
        $worst->ratings()->create(['user_id' => $user->id, 'rating' => 1]);

        $this->actingAs($user)
            ->get('/cuadros?sort=rating')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Paintings')
                    ->where('paintings.data.0.title', 'La mejor')
                    ->where('paintings.data.1.title', 'La peor'),
            );
    }

    public function test_home_page_shows_the_top_rated_painting(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create(['is_public' => true, 'title' => 'Obra estrella']);
        $painting->ratings()->create(['user_id' => $user->id, 'rating' => 5]);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->where('topRatedPainting.title', 'Obra estrella')
                    ->where('topRatedPainting.ratings_avg', 5),
            );
    }
}
