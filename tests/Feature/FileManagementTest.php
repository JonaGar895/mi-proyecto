<?php

namespace Tests\Feature;

use App\Models\Painting;
use App\Models\Publication;
use App\Models\PublicationComment;
use App\Models\User;
use App\Services\FileManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_only_admins_can_view_file_management(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/archivos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Files')
                ->has('report.files')
                ->has('report.records'));

        $this->actingAs($user)
            ->get('/admin/archivos')
            ->assertForbidden();
    }

    public function test_cleanup_removes_orphans_and_duplicate_files(): void
    {
        $content = 'contenido-de-prueba';
        $hash = hash('sha256', $content);

        Storage::disk('public')->put('paintings/referenced.jpg', $content);
        Storage::disk('public')->put('paintings/duplicate.jpg', $content);
        Storage::disk('public')->put('paintings/orphan.jpg', 'otro-contenido');

        Painting::factory()->create([
            'image_path' => 'paintings/referenced.jpg',
            'image_hash' => $hash,
        ]);

        $result = app(FileManager::class)->clean();

        $this->assertSame(1, $result['deleted_duplicate_files']);
        $this->assertSame(1, $result['deleted_orphans']);
        Storage::disk('public')->assertExists('paintings/referenced.jpg');
        Storage::disk('public')->assertMissing('paintings/duplicate.jpg');
        Storage::disk('public')->assertMissing('paintings/orphan.jpg');
    }

    public function test_cleanup_merges_exact_duplicate_publications_and_moves_comments(): void
    {
        $user = User::factory()->create();

        $first = Publication::factory()->for($user)->create([
            'title' => 'Título repetido',
            'summary' => 'Resumen repetido',
            'content' => 'Contenido repetido',
        ]);

        $second = Publication::factory()->for($user)->create([
            'title' => 'Título repetido',
            'summary' => 'Resumen repetido',
            'content' => 'Contenido repetido',
        ]);

        PublicationComment::create([
            'user_id' => $user->id,
            'publication_id' => $second->id,
            'body' => 'Comentario de la publicación duplicada',
        ]);

        $result = app(FileManager::class)->clean();

        $this->assertSame(1, $result['merged_publications']);
        $this->assertDatabaseCount('publications', 1);
        $this->assertDatabaseHas('publication_comments', [
            'publication_id' => $first->id,
        ]);
    }

    public function test_strict_cleanup_merges_repeated_titles_with_different_content(): void
    {
        $user = User::factory()->create();

        Publication::factory()->for($user)->create([
            'title' => 'Mismo título',
            'summary' => 'Resumen uno',
            'content' => 'Contenido uno',
        ]);

        Publication::factory()->for($user)->create([
            'title' => 'Mismo título',
            'summary' => 'Resumen dos',
            'content' => 'Contenido dos',
        ]);

        $result = app(FileManager::class)->clean(strict: true);

        $this->assertSame(1, $result['merged_publications']);
        $this->assertDatabaseCount('publications', 1);
        $this->assertSame([], $result['conflicts']);
    }

    public function test_replacing_an_image_deletes_the_previous_file(): void
    {
        Storage::disk('public')->put('publications/old.jpg', 'imagen-antigua');

        $publication = Publication::factory()->create([
            'image_path' => 'publications/old.jpg',
            'image_hash' => hash('sha256', 'imagen-antigua'),
        ]);

        Storage::disk('public')->put('publications/new.jpg', 'imagen-nueva');

        $publication->update([
            'image_path' => 'publications/new.jpg',
            'image_hash' => hash('sha256', 'imagen-nueva'),
        ]);

        Storage::disk('public')->assertMissing('publications/old.jpg');
        Storage::disk('public')->assertExists('publications/new.jpg');
    }

    public function test_deleting_a_painting_deletes_its_image(): void
    {
        Storage::disk('public')->put('paintings/obra.jpg', 'imagen-de-la-obra');

        $painting = Painting::factory()->create([
            'image_path' => 'paintings/obra.jpg',
            'image_hash' => hash('sha256', 'imagen-de-la-obra'),
        ]);

        $painting->delete();

        Storage::disk('public')->assertMissing('paintings/obra.jpg');
    }
}
