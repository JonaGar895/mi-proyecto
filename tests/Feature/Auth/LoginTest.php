<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_created_by_factory_can_login(): void
    {
        $user = User::factory()->create(['email' => 'factory@example.com']);

        $response = $this->post('/login', [
            'email' => 'factory@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_created_through_the_app_can_login(): void
    {
        $user = User::create([
            'name' => 'Prueba',
            'email' => 'ui@example.com',
            'password' => 'secret123',
        ]);

        $response = $this->post('/login', [
            'email' => 'ui@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_created_from_the_users_page_can_login(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post('/users/create', [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com',
            'password' => 'password123',
        ]);

        $created = User::where('email', 'nuevo@example.com')->first();

        $response->assertRedirect(route('users.view', $created->id));

        auth()->logout();

        $response = $this->post('/login', [
            'email' => 'nuevo@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($created);
    }

    public function test_login_fails_with_incorrect_password(): void
    {
        User::factory()->create(['email' => 'factory@example.com']);

        $response = $this->post('/login', [
            'email' => 'factory@example.com',
            'password' => 'contrasena-incorrecta',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
