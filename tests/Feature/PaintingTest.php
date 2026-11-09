<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUploadedImages;
use Tests\TestCase;

class PaintingTest extends TestCase
{
    use CreatesUploadedImages;
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

    public function test_paintings_with_duplicate_title_or_description_are_rejected(): void
    {
        $user = User::factory()->create();

        Painting::factory()->create([
            'title' => 'Título original',
            'description' => 'Descripción original',
        ]);

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => 'título original',
                'description' => 'Otra descripción',
            ])
            ->assertSessionHasErrors('title');

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => 'Otro título',
                'description' => 'descripción original',
            ])
            ->assertSessionHasErrors('description');

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => '  titulo   original ',
                'description' => 'Descripción diferente',
            ])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('paintings', 1);
    }

    public function test_painting_with_a_duplicate_image_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('obra.jpg', 800, 600);

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => 'Primera obra',
                'image' => $image,
            ])
            ->assertRedirect(route('paintings.index'));

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => 'Segunda obra',
                'image' => $image,
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('paintings', 1);
    }

    public function test_painting_with_a_near_duplicate_image_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => 'Obra original',
                'image' => $this->patternedImage('original.png'),
            ])
            ->assertRedirect(route('paintings.index'));

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => 'Obra casi igual',
                'image' => $this->resizedPatternedImage('copia.png'),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('paintings', 1);
    }

    public function test_solid_color_images_are_not_treated_as_near_duplicates(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => 'Obra negra',
                'image' => $this->solidColorImage('negra.png', 200, 200, [0, 0, 0]),
            ])
            ->assertRedirect(route('paintings.index'));

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => 'Obra blanca',
                'image' => $this->solidColorImage('blanca.png', 200, 200, [255, 255, 255]),
            ])
            ->assertRedirect(route('paintings.index'));

        $this->assertDatabaseCount('paintings', 2);
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

    public function test_a_user_cannot_repeat_the_same_comment_on_the_same_painting(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create();

        $this->actingAs($user)
            ->post(route('paintings.comments.store', $painting), [
                'body' => 'Me encanta esta obra.',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('paintings.comments.store', $painting), [
                'body' => '  me   ENCANTA esta obra. ',
            ])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('comments', 1);
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
