<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Painting;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUploadedImages;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use CreatesUploadedImages;
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

    public function test_publications_with_duplicate_title_summary_or_content_are_rejected(): void
    {
        $user = User::factory()->create();

        Publication::factory()->for($user)->create([
            'title' => 'Título original',
            'summary' => 'Resumen original',
            'content' => 'Contenido original',
        ]);

        $this->actingAs($user)
            ->post('/publicaciones', [
                'title' => 'título original',
                'summary' => 'Otro resumen',
                'content' => 'Otro contenido',
            ])
            ->assertSessionHasErrors('title');

        $this->actingAs($user)
            ->post('/publicaciones', [
                'title' => 'Otro título',
                'summary' => 'resumen original',
                'content' => 'Otro contenido',
            ])
            ->assertSessionHasErrors('summary');

        $this->actingAs($user)
            ->post('/publicaciones', [
                'title' => 'Otro título',
                'summary' => 'Otro resumen',
                'content' => 'contenido original',
            ])
            ->assertSessionHasErrors('content');

        $this->actingAs($user)
            ->post('/publicaciones', [
                'title' => ' titulo   original ',
                'summary' => 'Otro resumen distinto',
                'content' => 'Otro contenido distinto',
            ])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('publications', 1);
    }

    public function test_publication_with_a_duplicate_image_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('noticia.jpg', 800, 600);

        $this->actingAs($user)
            ->post('/publicaciones', [
                'title' => 'Primera publicación',
                'content' => 'Contenido de la primera publicación.',
                'image' => $image,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post('/publicaciones', [
                'title' => 'Segunda publicación',
                'content' => 'Contenido de la segunda publicación.',
                'image' => $image,
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('publications', 1);
    }

    public function test_publication_with_a_near_duplicate_image_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/publicaciones', [
                'title' => 'Primera publicación',
                'content' => 'Contenido de la primera publicación.',
                'image' => $this->patternedImage('original.png'),
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post('/publicaciones', [
                'title' => 'Segunda publicación',
                'content' => 'Contenido de la segunda publicación.',
                'image' => $this->resizedPatternedImage('copia.png'),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('publications', 1);
    }

    public function test_publication_image_similar_to_a_painting_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/cuadros', [
                'title' => 'Obra con imagen',
                'image' => $this->patternedImage('obra.png'),
            ])
            ->assertRedirect(route('paintings.index'));

        $this->actingAs($user)
            ->post('/publicaciones', [
                'title' => 'Publicación con imagen similar',
                'content' => 'Contenido distinto para la publicación.',
                'image' => $this->resizedPatternedImage('noticia.png'),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('publications', 0);
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

    public function test_editing_a_publication_does_not_treat_its_own_data_as_a_duplicate(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->for($user)->create([
            'title' => 'Mi título',
            'content' => 'Mi contenido',
        ]);

        $this->actingAs($user)
            ->put(route('publications.update', $publication), [
                'title' => 'MI TÍTULO',
                'content' => 'mi  contenido',
                'is_published' => true,
            ])
            ->assertRedirect(route('publications.show', $publication));

        $this->assertDatabaseCount('publications', 1);
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

    public function test_a_user_cannot_repeat_the_same_comment_on_the_same_publication(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->create();

        $this->actingAs($user)
            ->post(route('publications.comments.store', $publication), [
                'body' => 'Muy interesante.',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('publications.comments.store', $publication), [
                'body' => 'muy   INTERESANTE. ',
            ])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('publication_comments', 1);
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

    public function test_settings_shows_only_the_authenticated_users_publications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $publication = Publication::factory()->for($user)->create([
            'title' => 'Mi publicación',
        ]);

        Publication::factory()->for($other)->create([
            'title' => 'Publicación ajena',
        ]);

        $this->actingAs($user)
            ->get('/settings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Settings')
                ->where('stats.publications', 1)
                ->has('myPublications', 1)
                ->where('myPublications.0.id', $publication->id)
                ->where('myPublications.0.title', 'Mi publicación')
                ->where('myPublications.0.is_published', true));
    }
}
