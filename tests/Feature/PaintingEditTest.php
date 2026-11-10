<?php

namespace Tests\Feature;

use App\Models\Painting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUploadedImages;
use Tests\TestCase;

class PaintingEditTest extends TestCase
{
    use CreatesUploadedImages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_owners_can_view_the_edit_page(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $user->id, 'title' => 'Mi obra']);

        $this->actingAs($user)
            ->get(route('paintings.edit', $painting))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Paintings/Edit')
                    ->where('painting.title', 'Mi obra'),
            );
    }

    public function test_owners_can_update_their_paintings(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create([
            'user_id' => $user->id,
            'title' => 'Título original',
            'price' => 100,
        ]);

        $this->actingAs($user)
            ->post(route('paintings.update', $painting), [
                'title' => 'Título actualizado',
                'description' => 'Descripción renovada.',
                'price' => 250,
                'technique' => 'Acrílico sobre lienzo',
            ])
            ->assertRedirect(route('paintings.show', $painting));

        $this->assertDatabaseHas('paintings', [
            'id' => $painting->id,
            'title' => 'Título actualizado',
            'description' => 'Descripción renovada.',
            'price' => 250,
            'technique' => 'Acrílico sobre lienzo',
        ]);
    }

    public function test_non_owners_cannot_edit_a_painting(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->get(route('paintings.edit', $painting))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->post(route('paintings.update', $painting), ['title' => 'Obra robada'])
            ->assertForbidden();

        $this->assertDatabaseHas('paintings', [
            'id' => $painting->id,
            'title' => $painting->title,
        ]);
    }

    public function test_admins_can_edit_any_painting(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $painting = Painting::factory()->create(['title' => 'Obra de otro']);

        $this->actingAs($admin)
            ->post(route('paintings.update', $painting), ['title' => 'Obra editada por admin'])
            ->assertRedirect(route('paintings.show', $painting));

        $this->assertDatabaseHas('paintings', [
            'id' => $painting->id,
            'title' => 'Obra editada por admin',
        ]);
    }

    public function test_sold_paintings_cannot_be_edited(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create([
            'user_id' => $user->id,
            'status' => 'sold',
        ]);

        $this->actingAs($user)
            ->post(route('paintings.update', $painting), ['title' => 'Intento de edición'])
            ->assertForbidden();
    }

    public function test_updates_reject_duplicate_titles(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $user->id]);
        Painting::factory()->create(['title' => 'Obra de otra persona']);

        $this->actingAs($user)
            ->post(route('paintings.update', $painting), ['title' => 'obra  de otra persona'])
            ->assertSessionHasErrors('title');
    }

    public function test_updates_allow_keeping_the_same_title(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create([
            'user_id' => $user->id,
            'title' => 'Mi obra',
        ]);

        $this->actingAs($user)
            ->post(route('paintings.update', $painting), [
                'title' => 'Mi obra',
                'description' => 'Solo cambio la descripción.',
            ])
            ->assertRedirect(route('paintings.show', $painting));
    }

    public function test_owners_can_replace_the_painting_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('paintings.update', $painting), [
                'title' => $painting->title,
                'image' => $this->patternedImage('reemplazo.png'),
            ])
            ->assertRedirect(route('paintings.show', $painting));

        $painting->refresh();

        $this->assertNotNull($painting->image_path);
        $this->assertNotNull($painting->image_hash);
        Storage::disk('public')->assertExists($painting->image_path);
    }

    public function test_updates_reject_an_image_already_used_by_another_painting(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => 'Primera obra',
                'image' => $this->patternedImage('original.png'),
            ])
            ->assertRedirect(route('paintings.index'));

        $second = Painting::factory()->create(['user_id' => $user->id, 'title' => 'Segunda obra']);

        $this->actingAs($user)
            ->post(route('paintings.update', $second), [
                'title' => 'Segunda obra',
                'image' => $this->patternedImage('copia.png'),
            ])
            ->assertSessionHasErrors('image');

        $second->refresh();
        $this->assertNull($second->image_path);
    }

    public function test_updates_allow_reusing_the_paintings_own_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => 'Mi obra',
                'image' => $this->patternedImage('obra.png'),
            ])
            ->assertRedirect(route('paintings.index'));

        $painting = Painting::query()->where('title', 'Mi obra')->first();

        $this->actingAs($user)
            ->post(route('paintings.update', $painting), [
                'title' => 'Mi obra',
                'image' => $this->patternedImage('obra.png'),
            ])
            ->assertRedirect(route('paintings.show', $painting));
    }

    public function test_the_mine_page_only_lists_own_paintings(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Painting::factory()->create(['user_id' => $user->id, 'title' => 'Obra propia']);
        Painting::factory()->create(['user_id' => $other->id, 'title' => 'Obra ajena']);

        $this->actingAs($user)
            ->get(route('paintings.mine'))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Paintings/Mine')
                    ->has('paintings.data', 1)
                    ->where('paintings.data.0.title', 'Obra propia'),
            );
    }
}
