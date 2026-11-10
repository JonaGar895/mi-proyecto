<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Painting;
use App\Models\Publication;
use App\Models\PublicationComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CommentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_comment_authors_can_edit_their_comments(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'painting_id' => $painting->id,
            'body' => 'Comentario original',
        ]);

        $this->actingAs($user)
            ->put(route('paintings.comments.update', [$painting, $comment]), ['body' => 'Comentario editado'])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Comentario editado',
        ]);
    }

    public function test_users_cannot_edit_someone_elses_comment(): void
    {
        $author = User::factory()->create();
        $intruder = User::factory()->create();
        $painting = Painting::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $author->id,
            'painting_id' => $painting->id,
            'body' => 'Comentario original',
        ]);

        $this->actingAs($intruder)
            ->put(route('paintings.comments.update', [$painting, $comment]), ['body' => 'Censurado'])
            ->assertForbidden();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Comentario original',
        ]);
    }

    public function test_admins_can_moderate_any_comment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $author = User::factory()->create();
        $painting = Painting::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $author->id,
            'painting_id' => $painting->id,
            'body' => 'Comentario original',
        ]);

        $this->actingAs($admin)
            ->put(route('paintings.comments.update', [$painting, $comment]), ['body' => 'Comentario moderado'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->delete(route('paintings.comments.destroy', [$painting, $comment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_comment_authors_can_delete_their_comments(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'painting_id' => $painting->id,
        ]);

        $this->actingAs($user)
            ->delete(route('paintings.comments.destroy', [$painting, $comment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_deleting_a_comment_also_deletes_its_replies(): void
    {
        $painting = Painting::factory()->create();
        $parent = Comment::factory()->create(['painting_id' => $painting->id]);
        $reply = Comment::factory()->create([
            'painting_id' => $painting->id,
            'parent_id' => $parent->id,
        ]);

        $this->actingAs($parent->user)
            ->delete(route('paintings.comments.destroy', [$painting, $parent]))
            ->assertRedirect();

        $this->assertDatabaseMissing('comments', ['id' => $parent->id]);
        $this->assertDatabaseMissing('comments', ['id' => $reply->id]);
    }

    public function test_users_can_reply_to_a_top_level_comment(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create();
        $parent = Comment::factory()->create(['painting_id' => $painting->id]);

        $this->actingAs($user)
            ->post(route('paintings.comments.store', $painting), [
                'body' => 'Mi respuesta al comentario.',
                'parent_id' => $parent->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'painting_id' => $painting->id,
            'parent_id' => $parent->id,
            'body' => 'Mi respuesta al comentario.',
        ]);
    }

    public function test_replies_cannot_be_answered_again(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create();
        $parent = Comment::factory()->create(['painting_id' => $painting->id]);
        $reply = Comment::factory()->create([
            'painting_id' => $painting->id,
            'parent_id' => $parent->id,
        ]);

        $this->actingAs($user)
            ->post(route('paintings.comments.store', $painting), [
                'body' => 'Respuesta a la respuesta.',
                'parent_id' => $reply->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('comments', [
            'body' => 'Respuesta a la respuesta.',
        ]);
    }

    public function test_replies_must_belong_to_the_same_painting(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create();
        $otherPainting = Painting::factory()->create();
        $foreignComment = Comment::factory()->create(['painting_id' => $otherPainting->id]);

        $this->actingAs($user)
            ->post(route('paintings.comments.store', $painting), [
                'body' => 'Respuesta cruzada.',
                'parent_id' => $foreignComment->id,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_painting_detail_shows_comments_with_their_replies(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create();
        $parent = Comment::factory()->create([
            'painting_id' => $painting->id,
            'body' => 'Comentario principal',
        ]);
        Comment::factory()->create([
            'painting_id' => $painting->id,
            'parent_id' => $parent->id,
            'body' => 'Respuesta anidada',
        ]);

        $this->actingAs($user)
            ->get(route('paintings.show', $painting))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Paintings/Show')
                    ->has('comments.data', 1)
                    ->where('comments.data.0.body', 'Comentario principal')
                    ->has('comments.data.0.replies', 1)
                    ->where('comments.data.0.replies.0.body', 'Respuesta anidada'),
            );
    }

    public function test_edited_comments_cannot_duplicate_an_existing_one(): void
    {
        $user = User::factory()->create();
        $painting = Painting::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'painting_id' => $painting->id,
            'body' => 'Comentario original',
        ]);
        $other = Comment::factory()->create([
            'user_id' => $user->id,
            'painting_id' => $painting->id,
            'body' => 'Otro comentario',
        ]);

        $this->actingAs($user)
            ->put(route('paintings.comments.update', [$painting, $comment]), ['body' => 'otro  comentario'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Comentario original',
        ]);
    }

    public function test_publication_comments_can_be_edited_by_their_authors(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->create();
        $comment = PublicationComment::factory()->create([
            'user_id' => $user->id,
            'publication_id' => $publication->id,
            'body' => 'Comentario original',
        ]);

        $this->actingAs($user)
            ->put(route('publications.comments.update', [$publication, $comment]), ['body' => 'Comentario editado'])
            ->assertRedirect();

        $this->assertDatabaseHas('publication_comments', [
            'id' => $comment->id,
            'body' => 'Comentario editado',
        ]);
    }

    public function test_publication_comments_can_be_deleted_by_their_authors(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->create();
        $comment = PublicationComment::factory()->create([
            'user_id' => $user->id,
            'publication_id' => $publication->id,
        ]);

        $this->actingAs($user)
            ->delete(route('publications.comments.destroy', [$publication, $comment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('publication_comments', ['id' => $comment->id]);
    }

    public function test_publication_comments_can_be_replied(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->create();
        $parent = PublicationComment::factory()->create(['publication_id' => $publication->id]);

        $this->actingAs($user)
            ->post(route('publications.comments.store', $publication), [
                'body' => 'Mi respuesta en la publicación.',
                'parent_id' => $parent->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('publication_comments', [
            'user_id' => $user->id,
            'publication_id' => $publication->id,
            'parent_id' => $parent->id,
            'body' => 'Mi respuesta en la publicación.',
        ]);
    }
}
