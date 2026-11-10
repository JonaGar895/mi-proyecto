<?php

namespace Tests\Feature;

use App\Models\Exhibition;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExhibitionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_the_exhibitions_list(): void
    {
        $this->get('/exposiciones')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_list_exhibitions(): void
    {
        $user = User::factory()->create();
        Exhibition::factory()->count(2)->create();

        $this->actingAs($user)
            ->get('/exposiciones')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Exposiciones/Index')
                    ->has('exhibitions.data', 2),
            );
    }

    public function test_regular_users_cannot_create_exhibitions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/exposiciones/create')
            ->assertForbidden();

        $this->actingAs($user)
            ->post('/exposiciones', ['title' => 'Mi exposición'])
            ->assertForbidden();
    }

    public function test_admin_can_create_an_exhibition_with_paintings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $painting = Painting::factory()->create(['is_public' => true]);

        $response = $this->actingAs($admin)->post('/exposiciones', [
            'title' => 'Luz y color',
            'description' => 'Una exposición sobre el color.',
            'starts_at' => '2026-11-01',
            'ends_at' => '2026-11-30',
            'paintings' => [$painting->id],
        ]);

        $exhibition = Exhibition::query()->where('title', 'Luz y color')->first();

        $this->assertNotNull($exhibition);
        $response->assertRedirect(route('exhibitions.show', $exhibition));
        $this->assertSame($admin->id, $exhibition->user_id);
        $this->assertTrue($exhibition->paintings->contains($painting));
    }

    public function test_vendor_can_create_an_exhibition(): void
    {
        $vendor = User::factory()->create(['is_vendor' => true]);

        $this->actingAs($vendor)
            ->post('/exposiciones', ['title' => 'Exposición del vendedor'])
            ->assertRedirect();

        $this->assertDatabaseHas('exhibitions', [
            'title' => 'Exposición del vendedor',
            'user_id' => $vendor->id,
        ]);
    }

    public function test_duplicate_exhibition_titles_are_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Exhibition::factory()->create(['title' => 'Original']);

        $this->actingAs($admin)
            ->post('/exposiciones', ['title' => 'original'])
            ->assertSessionHasErrors('title');
    }

    public function test_end_date_must_not_be_before_start_date(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post('/exposiciones', [
                'title' => 'Fechas imposibles',
                'starts_at' => '2026-11-10',
                'ends_at' => '2026-11-01',
            ])
            ->assertSessionHasErrors('ends_at');
    }

    public function test_regular_users_cannot_attach_private_paintings(): void
    {
        $vendor = User::factory()->create(['is_vendor' => true]);
        $owner = User::factory()->create();
        $privatePainting = Painting::factory()->create([
            'user_id' => $owner->id,
            'is_public' => false,
        ]);

        $this->actingAs($vendor)
            ->post('/exposiciones', [
                'title' => 'Exposición con obra privada',
                'paintings' => [$privatePainting->id],
            ])
            ->assertRedirect();

        $exhibition = Exhibition::query()->where('title', 'Exposición con obra privada')->first();

        $this->assertFalse($exhibition->paintings->contains($privatePainting));
    }

    public function test_exhibition_state_is_computed_from_dates(): void
    {
        $user = User::factory()->create();

        $active = Exhibition::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        $upcoming = Exhibition::factory()->create([
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(20),
        ]);
        $finished = Exhibition::factory()->create([
            'starts_at' => now()->subDays(20),
            'ends_at' => now()->subDays(10),
        ]);

        $this->actingAs($user)
            ->get('/exposiciones')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Exposiciones/Index')
                    // Las próximas exposiciones se listan primero; después
                    // las activas y finalizadas por fecha de inicio.
                    ->where('exhibitions.data.0.state', $upcoming->state())
                    ->where('exhibitions.data.1.state', $active->state())
                    ->where('exhibitions.data.2.state', $finished->state()),
            );
    }

    public function test_exhibitions_can_be_filtered_by_state(): void
    {
        $user = User::factory()->create();

        Exhibition::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        Exhibition::factory()->create([
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(20),
        ]);

        $this->actingAs($user)
            ->get('/exposiciones?state=active')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Exposiciones/Index')
                    ->has('exhibitions.data', 1)
                    ->where('exhibitions.data.0.state', 'active'),
            );
    }

    public function test_exhibition_detail_shows_its_paintings(): void
    {
        $user = User::factory()->create();
        $exhibition = Exhibition::factory()->create();
        $painting = Painting::factory()->create(['is_public' => true]);
        $exhibition->paintings()->attach($painting);

        $this->actingAs($user)
            ->get("/exposiciones/{$exhibition->id}")
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Exposiciones/Show')
                    ->where('exhibition.paintings_count', 1)
                    ->has('paintings.data', 1),
            );
    }

    public function test_organizer_can_edit_their_exhibition(): void
    {
        $vendor = User::factory()->create(['is_vendor' => true]);
        $exhibition = Exhibition::factory()->create(['user_id' => $vendor->id]);

        $this->actingAs($vendor)
            ->get("/exposiciones/{$exhibition->id}/edit")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Exposiciones/Edit'));

        $this->actingAs($vendor)
            ->post("/exposiciones/{$exhibition->id}", [
                'title' => 'Título actualizado',
            ])
            ->assertRedirect(route('exhibitions.show', $exhibition));

        $this->assertDatabaseHas('exhibitions', [
            'id' => $exhibition->id,
            'title' => 'Título actualizado',
        ]);
    }

    public function test_other_users_cannot_edit_someone_elses_exhibition(): void
    {
        $user = User::factory()->create();
        $exhibition = Exhibition::factory()->create();

        $this->actingAs($user)
            ->get("/exposiciones/{$exhibition->id}/edit")
            ->assertForbidden();

        $this->actingAs($user)
            ->post("/exposiciones/{$exhibition->id}", ['title' => 'No permitido'])
            ->assertForbidden();
    }

    public function test_admin_can_delete_any_exhibition(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $exhibition = Exhibition::factory()->create();

        $this->actingAs($admin)
            ->delete("/exposiciones/{$exhibition->id}")
            ->assertRedirect(route('exhibitions.index'));

        $this->assertDatabaseMissing('exhibitions', ['id' => $exhibition->id]);
    }

    public function test_regular_user_cannot_delete_an_exhibition(): void
    {
        $user = User::factory()->create();
        $exhibition = Exhibition::factory()->create();

        $this->actingAs($user)
            ->delete("/exposiciones/{$exhibition->id}")
            ->assertForbidden();
    }
}
