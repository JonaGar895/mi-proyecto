<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Painting;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_publications(): void
    {
        $this->get('/publicaciones')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_a_publication(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $painting = Painting::factory()->for($user)->create();

        $response = $this->actingAs($user)->post('/publicaciones', [
            'title' => 'Nueva exposición',
            'summary' => 'Resumen de prueba',
            'content' => 'Contenido de la publicación.',
            'category_id' => $category->id,
            'painting_id' => $painting->id,
            'is_published' => true,
        ]);

        $publication = Publication::where('title', 'Nueva exposición')->first();

        $this->assertNotNull($publication);
        $response->assertRedirect(route('publications.show', $publication));

        $this->assertDatabaseHas('publications', [
            'title' => 'Nueva exposición',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'painting_id' => $painting->id,
            'is_published' => true,
        ]);

        $this->assertNotNull($publication->published_at);
    }

    public function test_index_renders_publications_with_their_relations(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Noticias']);
        $painting = Painting::factory()->for($user)->create([
            'title' => 'Obra relacionada',
        ]);

        Publication::factory()->for($user)->create([
            'category_id' => $category->id,
            'painting_id' => $painting->id,
            'title' => 'Publicación de prueba',
        ]);

        $this->actingAs($user)
            ->get('/publicaciones')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Publicaciones/Index')
                ->has('publications.data', 1)
                ->where('publications.data.0.title', 'Publicación de prueba')
                ->where('publications.data.0.category.name', 'Noticias')
                ->where('publications.data.0.painting.title', 'Obra relacionada')
                ->where('publications.data.0.author.id', $user->id));
    }

    public function test_show_page_renders_content_and_comments(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->for($user)->create([
            'title' => 'Detalle',
        ]);

        $this->actingAs($user)
            ->get(route('publications.show', $publication))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Publicaciones/Show')
                ->where('publication.id', $publication->id)
                ->where('publication.content', $publication->content)
                ->where('publication.can_edit', true));
    }

    public function test_owner_can_update_a_publication(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->for($user)->create([
            'title' => 'Antes',
        ]);

        $this->actingAs($user)
            ->put(route('publications.update', $publication), [
                'title' => 'Después',
                'content' => 'Contenido actualizado.',
                'is_published' => true,
            ])
            ->assertRedirect(route('publications.show', $publication));

        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'title' => 'Después',
            'content' => 'Contenido actualizado.',
        ]);
    }

    public function test_non_owner_cannot_edit_delete_or_update_a_publication(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $publication = Publication::factory()->for($owner)->create();

        $this->actingAs($other)
            ->get(route('publications.edit', $publication))
            ->assertForbidden();

        $this->actingAs($other)
            ->put(route('publications.update', $publication), [
                'title' => 'Cambio ajeno',
                'content' => 'No debería poder.',
            ])
            ->assertForbidden();

        $this->actingAs($other)
            ->delete(route('publications.destroy', $publication))
            ->assertForbidden();

        $this->assertDatabaseHas('publications', ['id' => $publication->id]);
    }

    public function test_owner_can_delete_a_publication(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('publications.destroy', $publication))
            ->assertRedirect(route('publications.index'));

        $this->assertDatabaseMissing('publications', ['id' => $publication->id]);
    }

    public function test_user_can_comment_on_a_publication(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->create();

        $this->actingAs($user)
            ->post(route('publications.comments.store', $publication), [
                'body' => 'Muy interesante.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('publication_comments', [
            'user_id' => $user->id,
            'publication_id' => $publication->id,
            'body' => 'Muy interesante.',
        ]);
    }

    public function test_drafts_are_only_visible_to_their_author(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $publication = Publication::factory()->for($author)->draft()->create();

        $this->actingAs($other)
            ->get(route('publications.show', $publication))
            ->assertNotFound();

        $this->actingAs($author)
            ->get(route('publications.show', $publication))
            ->assertOk();

        $this->actingAs($other)
            ->get('/publicaciones')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('publications.data', 0));
    }
}
