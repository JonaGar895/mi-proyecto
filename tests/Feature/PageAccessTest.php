<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_authenticated_user_can_open_the_main_pages(): void
    {
        $user = User::factory()->create();

        foreach ([
            '/',
            '/cuadros',
            '/cuadros/create',
            '/users',
            '/users/create',
            "/users/{$user->id}/edit",
            '/settings',
        ] as $uri) {
            $this->actingAs($user)->get($uri)->assertOk();
        }
    }
}
