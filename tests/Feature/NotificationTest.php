<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Painting;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_the_notifications_page(): void
    {
        $this->get('/notificaciones')->assertRedirect('/login');
    }

    public function test_commenting_a_painting_notifies_the_owner(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($visitor)
            ->post(route('paintings.comments.store', $painting), ['body' => 'Preciosa obra.'])
            ->assertRedirect();

        $this->assertSame(1, $owner->unreadNotifications()->count());
        $this->assertSame(0, $visitor->unreadNotifications()->count());
    }

    public function test_the_owner_is_not_notified_for_their_own_comments(): void
    {
        $owner = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->post(route('paintings.comments.store', $painting), ['body' => 'Comentario propio.'])
            ->assertRedirect();

        $this->assertSame(0, $owner->unreadNotifications()->count());
    }

    public function test_commenting_a_publication_notifies_its_author(): void
    {
        $author = User::factory()->create();
        $visitor = User::factory()->create();
        $publication = Publication::factory()->create(['user_id' => $author->id]);

        $this->actingAs($visitor)
            ->post(route('publications.comments.store', $publication), ['body' => 'Gran artículo.'])
            ->assertRedirect();

        $this->assertSame(1, $author->unreadNotifications()->count());
    }

    public function test_favoriting_a_painting_notifies_the_owner(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($visitor)
            ->post(route('paintings.favorite', $painting))
            ->assertRedirect();

        $this->assertSame(1, $owner->unreadNotifications()->count());
    }

    public function test_replying_to_a_comment_notifies_its_author(): void
    {
        $owner = User::factory()->create();
        $parentAuthor = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $owner->id]);

        $parent = $painting->comments()->create([
            'user_id' => $parentAuthor->id,
            'body' => 'Comentario original',
        ]);

        $this->actingAs($owner)
            ->post(route('paintings.comments.store', $painting), [
                'body' => 'Gracias por tu comentario.',
                'parent_id' => $parent->id,
            ])
            ->assertRedirect();

        // El autor del comentario original recibe la notificación de respuesta;
        // el dueño que respondió no recibe ninguna (evitamos auto-avisos).
        $this->assertSame(1, $parentAuthor->unreadNotifications()->count());
        $this->assertSame(0, $owner->unreadNotifications()->count());
    }

    public function test_a_new_inquiry_notifies_the_seller(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($buyer)
            ->post(route('paintings.inquiries.store', $painting), ['message' => 'Me interesa la obra.'])
            ->assertRedirect();

        $this->assertSame(1, $seller->unreadNotifications()->count());
    }

    public function test_an_inquiry_status_change_notifies_the_buyer(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $painting = Painting::factory()->create(['user_id' => $seller->id]);

        $inquiry = Inquiry::create([
            'painting_id' => $painting->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'message' => 'Me interesa la obra.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('inquiries.update', $inquiry), ['status' => 'contacted'])
            ->assertRedirect();

        $this->assertSame(1, $buyer->unreadNotifications()->count());
    }

    public function test_the_notifications_page_lists_them_with_the_unread_count(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($visitor)
            ->post(route('paintings.comments.store', $painting), ['body' => 'Hola.']);

        $this->actingAs($owner)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Notificaciones/Index')
                    ->has('notifications.data', 1)
                    ->where('notifications.data.0.unread', true)
                    ->where('notifications.unread_count', 1),
            );
    }

    public function test_a_notification_can_be_marked_as_read(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($visitor)
            ->post(route('paintings.comments.store', $painting), ['body' => 'Hola.']);

        $notification = $owner->unreadNotifications()->first();
        $this->assertNotNull($notification);

        $this->actingAs($owner)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertSame(0, $owner->unreadNotifications()->count());
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_all_notifications_can_be_marked_as_read_at_once(): void
    {
        $owner = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $owner->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('paintings.comments.store', $painting), ['body' => 'Primer comentario.']);

        $this->actingAs(User::factory()->create())
            ->post(route('paintings.comments.store', $painting), ['body' => 'Segundo comentario.']);

        $this->assertSame(2, $owner->unreadNotifications()->count());

        $this->actingAs($owner)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $owner->unreadNotifications()->count());
    }

    public function test_users_cannot_read_someone_elses_notification(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $painting = Painting::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($visitor)
            ->post(route('paintings.comments.store', $painting), ['body' => 'Hola.']);

        $notification = $owner->unreadNotifications()->first();

        $this->actingAs(User::factory()->create())
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertSame(1, $owner->unreadNotifications()->count());
    }
}
