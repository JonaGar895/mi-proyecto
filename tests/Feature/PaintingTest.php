<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PaintingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_the_gallery(): void
    {
        $this->get('/cuadros')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_a_painting(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/cuadros', [
            'title' => 'Noche estrellada',
            'description' => 'Una obra de prueba',
            'category_id' => $category->id,
            'year' => 1889,
            'technique' => 'Óleo sobre lienzo',
            'emoji' => '🌌',
            'is_public' => true,
        ]);

        $response->assertRedirect(route('paintings.index'));
        $this->assertDatabaseHas('paintings', [
            'title' => 'Noche estrellada',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_user_can_favorite_a_painting(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create();

        $this->actingAs($user)
            ->post(route('paintings.favorite', $painting))
            ->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'painting_id' => $painting->id,
        ]);
    }

    public function test_user_can_comment_on_a_painting(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create();

        $this->actingAs($user)
            ->post(route('paintings.comments.store', $painting), [
                'body' => 'Me encanta esta obra.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'painting_id' => $painting->id,
            'body' => 'Me encanta esta obra.',
        ]);
    }

    public function test_gallery_and_detail_pages_render_paintings(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create(['title' => 'Obra de prueba']);

        $this->actingAs($user)
            ->get('/cuadros')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Paintings')
                ->has('paintings.data', 1)
                ->where('paintings.data.0.title', 'Obra de prueba'));

        $this->actingAs($user)
            ->get("/cuadros/{$painting->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Paintings/Show')
                ->where('painting.id', $painting->id));
    }

    public function test_owner_can_delete_their_painting(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('paintings.destroy', $painting))
            ->assertRedirect(route('paintings.index'));

        $this->assertDatabaseMissing('paintings', ['id' => $painting->id]);
    }

    public function test_authenticated_user_cannot_delete_someone_elses_painting(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $painting = Painting::factory()->for($owner)->create();

        $this->actingAs($other)
            ->delete(route('paintings.destroy', $painting))
            ->assertForbidden();

        $this->assertDatabaseHas('paintings', ['id' => $painting->id]);
    }
}
