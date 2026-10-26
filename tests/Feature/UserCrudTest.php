<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_from_the_user_list(): void
    {
        $this->get('/users/list')->assertRedirect('/login');
    }

    public function test_user_list_page_renders_users(): void
    {
        $user = User::factory()->create(['name' => 'Usuario de prueba']);

        $this->actingAs($user)
            ->get(route('users.list'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Usuario de prueba'));
    }

    public function test_user_view_page_renders_a_single_user(): void
    {
        $user = User::factory()->create(['name' => 'Ana']);

        $this->actingAs($user)
            ->get(route('users.view', $user))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Show')
                ->where('user.id', $user->id)
                ->where('user.name', 'Ana')
                ->where('user.email', $user->email)
                ->where('stats.paintings', 0)
                ->where('stats.favorites', 0)
                ->where('stats.comments', 0));
    }

    public function test_authenticated_user_can_create_a_user(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com',
            'password' => 'password123',
        ]);

        $created = User::where('email', 'nuevo@example.com')->first();

        $this->assertNotNull($created);
        $response->assertRedirect(route('users.view', $created->id));

        $this->assertDatabaseHas('users', [
            'id' => $created->id,
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com',
        ]);

        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_authenticated_user_can_update_a_user(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create([
            'name' => 'Nombre viejo',
            'email' => 'viejo@example.com',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'name' => 'Nombre nuevo',
            'email' => 'nuevo@example.com',
            'password' => '',
        ]);

        $response->assertRedirect(route('users.view', $user->id));

        $user->refresh();

        $this->assertSame('Nombre nuevo', $user->name);
        $this->assertSame('nuevo@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_authenticated_user_can_delete_another_user(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('users.delete', $user));

        $response->assertRedirect(route('users.list'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_cannot_delete_their_own_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('users.delete', $user))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_create_user_requires_valid_data(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_email_must_be_unique_when_creating_a_user(): void
    {
        $admin = User::factory()->create();
        User::factory()->create(['email' => 'repetido@example.com']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Otro',
                'email' => 'repetido@example.com',
                'password' => 'password123',
            ])
            ->assertSessionHasErrors('email');
    }
}
