<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Content;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\Media;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_dashboard_shows_prd_kpis_and_attention_widgets(): void
    {
        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        Content::create([
            'category_id' => $category->id,
            'title' => 'Published Piece',
            'slug' => 'published-piece',
            'type' => 'article',
            'status' => 'published',
            'view_count' => 50,
        ]);
        Content::create([
            'category_id' => $category->id,
            'title' => 'Fan Draft',
            'slug' => 'fan-draft',
            'type' => 'article',
            'status' => 'pending_review',
            'is_user_submitted' => true,
            'submitted_by' => $this->admin->id,
        ]);
        Event::create([
            'title' => 'Expo',
            'city' => 'Tokyo',
            'start_at' => now()->addDays(5),
            'status' => 'published',
        ]);
        Review::create([
            'user_id' => $this->admin->id,
            'reviewable_type' => Content::class,
            'reviewable_id' => 1,
            'body' => 'Pending review body',
            'status' => 'pending',
        ]);
        Feedback::create([
            'user_id' => null,
            'type' => 'bug',
            'message' => 'Broken thing',
            'status' => 'open',
        ]);
        Media::create([
            'uploaded_by' => $this->admin->id,
            'path' => 'uploads/images/a.png',
            'original_filename' => 'a.png',
            'mime_type' => 'image/png',
            'media_type' => 'image',
            'size_bytes' => 10,
            'disk' => 'public',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total Users')
            ->assertSee('Total Content')
            ->assertSee('Published Content')
            ->assertSee('Pending Submissions')
            ->assertSee('Total Categories')
            ->assertSee('Upcoming Events')
            ->assertSee('Pending Reviews')
            ->assertSee('Open Feedback')
            ->assertSee('Total Media')
            ->assertSee('Add Content')
            ->assertSee('Review Submissions')
            ->assertSee('Recent user submissions')
            ->assertSee('Fan Draft')
            ->assertSee('Popular content')
            ->assertSee('Published Piece')
            ->assertSee('Expo')
            ->assertSee('Broken thing');
    }

    public function test_non_admin_cannot_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('user.dashboard'));
    }
}
