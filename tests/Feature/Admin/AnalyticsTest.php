<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Content;
use App\Models\Feedback;
use App\Models\MerchandiseItem;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($role);
    }

    public function test_analytics_reports_use_available_schema_data(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $content = Content::create([
            'category_id' => $category->id,
            'title' => 'Popular Article',
            'slug' => 'popular-article',
            'type' => 'article',
            'status' => 'published',
            'view_count' => 1200,
        ]);
        MerchandiseItem::create([
            'category_id' => $category->id,
            'name' => 'Popular Figure',
            'slug' => 'popular-figure',
            'tag' => 'standard',
            'view_count' => 500,
        ]);
        Review::create([
            'user_id' => $this->admin->id,
            'reviewable_type' => Content::class,
            'reviewable_id' => $content->id,
            'body' => 'Nice',
            'status' => 'pending',
        ]);
        Feedback::create([
            'user_id' => null,
            'type' => 'bug',
            'message' => 'Broken link',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertSee('Top content by views')
            ->assertSee('Popular Article')
            ->assertSee('Popular Figure')
            ->assertSee('Content by category')
            ->assertSee('Anime')
            ->assertSee('Review moderation volume')
            ->assertSee('Feedback volume')
            ->assertSee('User growth')
            ->assertSee('Active users are not shown');
    }

    public function test_non_admin_cannot_access_analytics(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.analytics.index'))->assertRedirect(route('user.dashboard'));
    }
}
