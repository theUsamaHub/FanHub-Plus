<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_get_public_navigation_and_all_eight_fandoms(): void
    {
        $response = $this->get('/')->assertOk()->assertSee('EXPLORE FANDOMS')->assertSee('EVERY UNIVERSE. ONE HOME.');
        foreach (config('fandoms') as $fandom) {
            $response->assertSee('storage/images/'.$fandom['image']);
        }
        $response->assertSee(route('login'))->assertSee(route('register'))->assertDontSee('id="account-menu"', false);
        $this->get('/sitemap')->assertOk();
    }

    public function test_signed_in_users_share_the_homepage_with_an_account_menu(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'Fandom Fan']))->get('/')
            ->assertOk()->assertSee('Welcome, Fandom Fan')->assertSee('My Dashboard')
            ->assertSee('Submit Content')->assertSee('Logout')->assertSee('name="_token"', false);
    }

    public function test_private_navigation_requests_login_and_preserves_destination(): void
    {
        $this->get('/account/bookmarks')->assertRedirect(route('login'));
        $this->assertEquals(url('/account/bookmarks'), session('url.intended'));
    }

    public function test_search_filters_fandoms_and_never_exposes_drafts_or_future_content(): void
    {
        $anime = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $gaming = Category::create(['name' => 'Gaming', 'slug' => 'gaming']);
        foreach ([
            ['title' => 'Moon story', 'category_id' => $anime->id, 'status' => 'published', 'published_at' => now()->subDay()],
            ['title' => 'Moon draft', 'category_id' => $anime->id, 'status' => 'draft'],
            ['title' => 'Moon future', 'category_id' => $anime->id, 'status' => 'published', 'published_at' => now()->addDay()],
            ['title' => 'Moon game', 'category_id' => $gaming->id, 'status' => 'published'],
        ] as $attributes) {
            Content::create($attributes);
        }

        $this->get('/explore?q=MOON&category=anime')->assertOk()->assertSee('Moon story')
            ->assertDontSee('Moon draft')->assertDontSee('Moon future')->assertDontSee('Moon game');
        $this->get('/explore?q=%25')->assertOk()->assertDontSee('Moon story');
        $this->get('/discover/not-a-page')->assertNotFound();
    }
}
