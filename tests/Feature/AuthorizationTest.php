<?php

namespace Tests\Feature;

use App\Models\Painting;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_delete_any_painting(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $painting = Painting::factory()->for($owner)->create();

        $this->actingAs($admin)
            ->delete(route('paintings.destroy', $painting))
            ->assertRedirect(route('paintings.index'));

        $this->assertDatabaseMissing('paintings', ['id' => $painting->id]);
    }

    public function test_admin_can_delete_any_publication(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $publication = Publication::factory()->for($owner)->create();

        $this->actingAs($admin)
            ->delete(route('publications.destroy', $publication))
            ->assertRedirect(route('publications.index'));

        $this->assertDatabaseMissing('publications', ['id' => $publication->id]);
    }

    public function test_admin_can_edit_any_publication(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $publication = Publication::factory()->for($owner)->create(['title' => 'Original']);

        $this->actingAs($admin)
            ->get(route('publications.edit', $publication))
            ->assertOk();

        $this->actingAs($admin)
            ->put(route('publications.update', $publication), [
                'title' => 'Editado por admin',
                'content' => 'Contenido editado.',
                'is_published' => true,
            ])
            ->assertRedirect(route('publications.show', $publication));

        $this->assertDatabaseHas('publications', [
            'id' => $publication->id,
            'title' => 'Editado por admin',
        ]);
    }

    public function test_admin_can_view_any_user_profile(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $other = User::factory()->create(['name' => 'Otro Usuario']);

        $this->actingAs($admin)
            ->get(route('users.view', $other))
            ->assertOk();
    }

    public function test_regular_user_cannot_view_another_user_profile(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $other = User::factory()->create();

        $this->actingAs($user)
            ->get(route('users.view', $other))
            ->assertForbidden();
    }

    public function test_only_admin_can_create_users(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('users.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('users.store'), [
                'name' => 'Test',
                'email' => 'test@test.com',
                'password' => 'password123',
            ])
            ->assertForbidden();
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('users.delete', $admin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_regular_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('users.view', $user))
            ->assertOk();
    }

    public function test_regular_user_can_edit_own_profile(): void
    {
        $user = User::factory()->create(['name' => 'Original']);

        $this->actingAs($user)
            ->get(route('users.edit', $user))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('users.update', $user), [
                'name' => 'Actualizado',
                'email' => $user->email,
                'password' => '',
            ])
            ->assertRedirect(route('users.view', $user->id));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Actualizado']);
    }
}
