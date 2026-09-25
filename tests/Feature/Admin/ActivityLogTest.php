<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Content;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::withoutEvents(fn () => Role::create(['name' => 'Admin', 'slug' => 'admin']));
        $this->admin = User::withoutEvents(fn () => User::factory()->create());
        $this->admin->roles()->attach($role);
    }

    public function test_creating_a_model_logs_a_readable_subject_and_description(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);

        $log = ActivityLog::where('event', 'created')->latest('id')->first();

        $this->assertNotNull($log, 'A created event should have been logged.');
        $this->assertSame('Anime', $log->subject);
        $this->assertSame('Fandom "Anime" was created', $log->description);
        $this->assertSame(Category::class, $log->auditable_type);
        $this->assertSame($category->id, $log->auditable_id);
        $this->assertSame($this->admin->id, $log->user_id);
    }

    public function test_updating_a_model_records_both_the_old_and_new_value(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $category->update(['name' => 'Anime & Manga']);

        $log = ActivityLog::where('event', 'updated')->latest('id')->first();

        $this->assertNotNull($log, 'An updated event should have been logged.');
        $this->assertSame(['name' => 'Anime'], $log->old_values);
        $this->assertSame(['name' => 'Anime & Manga'], $log->new_values);
        $this->assertStringContainsString('Name changed', $log->description);

        $change = collect($log->changes)->firstWhere('field', 'name');

        $this->assertSame('changed', $change['type']);
        $this->assertSame('Anime', $change['old']);
        $this->assertSame('Anime & Manga', $change['new']);
        $this->assertStringContainsString('Anime', $log->summary);
        $this->assertStringContainsString('Anime & Manga', $log->summary);
    }

    public function test_deleting_a_model_still_records_what_was_removed(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Doomed', 'slug' => 'doomed']);
        $category->delete();

        $log = ActivityLog::where('event', 'deleted')->latest('id')->first();

        $this->assertNotNull($log, 'A deleted event should have been logged.');
        $this->assertSame('Doomed', $log->subject);
        $this->assertNull($log->new_values);
        $this->assertSame('Doomed', $log->old_values['name']);
        $this->assertSame('Fandom "Doomed" was deleted', $log->description);

        // Regression: the old view read only new_values, so deletes rendered blank.
        $this->assertNotEmpty($log->changes, 'A delete must expose the removed fields.');
        $this->assertSame('removed', collect($log->changes)->firstWhere('field', 'name')['type']);
    }

    public function test_restoring_a_soft_deleted_model_is_logged(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Restorable', 'slug' => 'restorable']);
        $category->delete();
        $category->restore();

        $log = ActivityLog::where('event', 'restored')->latest('id')->first();

        $this->assertNotNull($log, 'A restored event should have been logged.');
        $this->assertSame('Restorable', $log->subject);
        $this->assertSame('Fandom "Restorable" was restored', $log->description);
    }

    public function test_bookkeeping_attributes_are_never_logged(): void
    {
        $this->actingAs($this->admin);

        $content = Content::create([
            'category_id' => Category::create(['name' => 'Anime', 'slug' => 'anime'])->id,
            'title' => 'A Story',
            'type' => 'article',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $content->increment('view_count');

        $log = ActivityLog::where('event', 'updated')->latest('id')->first();

        $this->assertNull($log, 'A view_count bump must not produce an audit entry.');

        $created = ActivityLog::where('event', 'created')->latest('id')->first();
        $this->assertArrayNotHasKey('view_count', $created->new_values);
        $this->assertArrayNotHasKey('updated_at', $created->new_values);
    }

    public function test_passwords_are_never_written_to_the_log(): void
    {
        $this->actingAs($this->admin);

        $user = User::withoutEvents(fn () => User::factory()->create(['password' => 'super-secret-value']));

        $user->update(['name' => 'Renamed Person']);

        $log = ActivityLog::where('event', 'updated')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertArrayNotHasKey('password', $log->new_values);
        $this->assertStringNotContainsString('super-secret-value', json_encode($log->new_values));
    }

    public function test_admin_sees_the_human_readable_message_in_the_list(): void
    {
        $this->actingAs($this->admin);

        Category::create(['name' => 'Cosplay', 'slug' => 'cosplay']);

        $this->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertSee('Fandom')
            ->assertSee('was created')
            ->assertSee('Cosplay');
    }

    public function test_admin_can_open_the_detail_page_for_a_log(): void
    {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Manga', 'slug' => 'manga']);
        $category->update(['description' => 'Japanese manga and manhua']);

        $log = ActivityLog::where('event', 'updated')->latest('id')->first();

        $this->get(route('admin.activity-logs.show', $log))
            ->assertOk()
            ->assertSee('Activity Detail')
            ->assertSee('Field Changes')
            ->assertSee('Description')
            ->assertSee('Japanese manga and manhua');
    }

    public function test_non_admin_cannot_view_activity_logs(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.activity-logs.index'))
            ->assertRedirect(route('user.dashboard'));
    }

    public function test_index_can_be_filtered_by_record_type(): void
    {
        $this->actingAs($this->admin);

        Category::create(['name' => 'Anime', 'slug' => 'anime']);
        Content::create([
            'category_id' => Category::where('slug', 'anime')->value('id'),
            'title' => 'A Story',
            'type' => 'article',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('admin.activity-logs.index', ['type' => Content::class]))
            ->assertOk()
            ->assertSee('A Story')
            ->assertDontSee('Fandom &quot;Anime&quot; was created');
    }

    public function test_index_can_be_filtered_by_actor(): void
    {
        $this->actingAs($this->admin);

        Category::create(['name' => 'Esports', 'slug' => 'esports']);

        $this->get(route('admin.activity-logs.index', ['user_id' => $this->admin->id]))
            ->assertOk()
            ->assertSee('Esports');
    }

    public function test_search_matches_the_record_subject(): void
    {
        $this->actingAs($this->admin);

        Category::create(['name' => 'K-Pop', 'slug' => 'k-pop']);

        $this->get(route('admin.activity-logs.index', ['search' => 'k-pop']))
            ->assertOk()
            ->assertSee('K-Pop');
    }

    public function test_export_preserves_filters_and_includes_the_summary(): void
    {
        $this->actingAs($this->admin);

        Category::create(['name' => 'Comics', 'slug' => 'comics']);

        $response = $this->get(route('admin.activity-logs.export', ['event' => 'created']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Summary', $csv);
        $this->assertStringContainsString('Fandom "Comics" was created', $csv);
        $this->assertStringNotContainsString('login', $csv);
    }

    public function test_clearing_logs_removes_every_row(): void
    {
        $this->actingAs($this->admin);

        Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $this->assertGreaterThan(0, ActivityLog::count());

        $this->delete(route('admin.activity-logs.destroy'))->assertRedirect();

        $this->assertSame(0, ActivityLog::count());
    }

    public function test_logger_formats_values_for_humans(): void
    {
        $logger = app(ActivityLogger::class);

        $this->assertSame('—', $logger->formatValue(null));
        $this->assertSame('Yes', $logger->formatValue(true));
        $this->assertSame('No', $logger->formatValue(false));
        $this->assertSame('Fandom', $logger->fieldLabel('category_id'));
        $this->assertSame('Release date', $logger->fieldLabel('release_date'));
        $this->assertSame('Story', $logger->modelLabel(Content::class));
    }

    public function test_login_event_is_recorded(): void
    {
        // actingAs() bypasses the auth flow, so sign in for real to fire the event.
        auth()->login($this->admin);

        $log = ActivityLog::where('event', 'login')->latest('id')->first();

        $this->assertNotNull($log, 'Signing in should record a login entry.');
        $this->assertSame('Signed in', $log->event_label);
        $this->assertStringContainsString('signed in', $log->description);
        $this->assertSame($this->admin->id, $log->user_id);
    }
}
