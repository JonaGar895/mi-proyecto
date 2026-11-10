<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Painting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_redirected_from_the_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_non_admins_cannot_access_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admins_see_the_gallery_statistics(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $artist = User::factory()->create();
        $buyer = User::factory()->create();

        $painting = Painting::factory()->create([
            'user_id' => $artist->id,
            'status' => 'sold',
        ]);

        Inquiry::create([
            'painting_id' => $painting->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $artist->id,
            'message' => 'Me interesa la obra.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Admin/Dashboard')
                    ->where('stats.users', 3)
                    ->where('stats.paintings', 1)
                    ->where('stats.public_paintings', 1)
                    ->where('stats.sold_paintings', 1)
                    ->where('stats.pending_inquiries', 1)
                    ->where('stats.exhibitions', 0)
                    ->where('stats.comments', 0)
                    ->where('stats.ratings', 0)
                    ->has('salesByMonth', 6)
                    ->has('pendingInquiries', 1)
                    ->has('categories')
                    ->has('topArtists')
                    ->has('recentSales', 0),
            );
    }

    public function test_the_dashboard_counts_comments_and_ratings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $painting = Painting::factory()->create();

        $painting->comments()->create([
            'user_id' => User::factory()->create()->id,
            'body' => 'Me encanta.',
        ]);

        $painting->ratings()->create([
            'user_id' => User::factory()->create()->id,
            'rating' => 5,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Admin/Dashboard')
                    ->where('stats.comments', 1)
                    ->where('stats.ratings', 1),
            );
    }
}
