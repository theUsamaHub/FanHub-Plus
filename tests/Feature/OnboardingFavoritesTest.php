<?php

namespace Tests\Feature;

use App\Models\{Category, CharacterProfile, Content, Event, MerchandiseItem, Role, User};
use App\Services\HomepageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OnboardingFavoritesTest extends TestCase
{
    use RefreshDatabase;

    private function categories(int $count = 6): array
    {
        return collect(range(1, $count))->map(fn ($i) => Category::create(['name' => 'Fandom '.$i, 'slug' => 'fandom-'.$i])->id)->all();
    }

    private function member(bool $complete = false): User
    {
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([Role::firstOrCreate(['slug' => 'registered-user'], ['name' => 'Member'])->id]);
        if ($complete) $user->profile()->create(['onboarding_completed_at' => now()]);

        return $user;
    }

    public function test_incomplete_existing_members_get_a_blocking_modal_at_requested_urls(): void
    {
        $this->categories();
        $user = $this->member();
        $this->actingAs($user);
        foreach (['/', '/events', '/user/dashboard', '/explore', '/profile'] as $url) {
            $this->get($url)->assertOk()->assertViewIs('auth.onboarding')
                ->assertSee('data-onboarding', false)->assertSee('Fandom 6')->assertSee('aria-modal="true"', false);
        }
        $this->post('/profile/newsletter-preferences', [])->assertRedirect(route('onboarding.create'));
        $this->postJson(route('events.nearby.search'), ['latitude' => 0, 'longitude' => 0])->assertForbidden();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_only_registered_users_require_onboarding_and_admin_role_takes_precedence(): void
    {
        $ids = $this->categories();
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        foreach ([[], [$adminRole->id]] as $roles) {
            $user = $this->member();
            $user->roles()->sync($roles);
            $this->actingAs($user)->get('/events')->assertOk()->assertViewIs('events.index');
            $this->get('/onboarding')->assertRedirect(route('dashboard'));
            $this->post('/onboarding', ['favorites' => array_slice($ids, 0, 3)])->assertRedirect(route('dashboard'));
            $this->assertFalse($user->fresh()->hasCompletedOnboarding());
            $this->assertCount(0, $user->fresh()->favoriteCategories);
        }

        $admin = $this->member();
        $admin->roles()->attach($adminRole);
        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->get('/events')->assertOk()->assertViewIs('events.index');
        $this->get('/onboarding')->assertRedirect(route('dashboard'));
        $this->post('/onboarding', ['favorites' => array_slice($ids, 0, 3)])->assertRedirect(route('dashboard'));
        $this->assertCount(0, $admin->fresh()->favoriteCategories);

        $member = $this->member();
        $this->actingAs($member)->get('/events')->assertViewIs('auth.onboarding');
    }

    public function test_login_and_registration_lead_to_modal_without_requiring_email_verification(): void
    {
        $ids = $this->categories();
        $user = $this->member();
        $user->forceFill(['email_verified_at' => null])->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
        $this->get('/dashboard')->assertViewIs('auth.onboarding');
        $this->post('/logout');
        $this->post('/register', ['name' => 'New Fan', 'email' => 'newfan@example.test', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertRedirect(route('onboarding.create'));
        $this->get('/events')->assertViewIs('auth.onboarding');
        $newUser = User::where('email', 'newfan@example.test')->firstOrFail();
        $this->assertFalse($newUser->hasVerifiedEmail());
        $this->post('/onboarding', ['favorites' => array_slice($ids, 0, 3)])->assertRedirect(route('dashboard'));
        $this->get('/events')->assertViewIs('events.index');
        $this->get('/user/dashboard')->assertOk();
        $this->assertFalse($newUser->fresh()->hasVerifiedEmail());
    }

    public function test_saves_three_to_five_favorites_atomically_and_does_not_repeat(): void
    {
        $ids = $this->categories();
        $user = $this->member();
        $this->actingAs($user)->post('/onboarding', ['favorites' => array_slice($ids, 0, 3)])
            ->assertRedirect(route('dashboard'))->assertSessionHas('onboarding-success');
        $this->assertTrue($user->fresh()->hasCompletedOnboarding());
        $this->assertSame(3, $user->fresh()->profile->favorite_categories_count);
        $this->assertCount(3, $user->fresh()->favoriteCategories);
        $this->get('/dashboard')->assertRedirect(route('user.dashboard'));
        $this->get('/user/dashboard')->assertOk()->assertSee('Your universe is ready.');
        $this->get('/events')->assertOk()->assertViewIs('events.index');
        $this->get('/onboarding')->assertRedirect(route('dashboard'));
        $this->post('/onboarding', ['favorites' => array_slice($ids, 1, 5)])->assertRedirect();
        $this->assertCount(3, $user->fresh()->favoriteCategories);

        $second = $this->member();
        $this->actingAs($second)->post('/onboarding', ['favorites' => array_slice($ids, 0, 5)])->assertRedirect();
        $this->assertCount(5, $second->fresh()->favoriteCategories);
    }

    public function test_rejects_invalid_duplicate_deleted_and_too_many_choices(): void
    {
        $ids = $this->categories();
        $user = $this->member();
        $this->actingAs($user);
        foreach ([[], [$ids[0]], array_slice($ids, 0, 2), $ids, [$ids[0], $ids[0], $ids[1]], [$ids[0], $ids[1], 9999], 'invalid'] as $favorites) {
            $this->from('/onboarding')->post('/onboarding', compact('favorites'))->assertSessionHasErrors();
            $this->get('/onboarding')->assertOk();
            $this->assertFalse($user->fresh()->hasCompletedOnboarding());
            $this->assertCount(0, $user->fresh()->favoriteCategories);
        }
        Category::find($ids[2])->delete();
        $this->post('/onboarding', ['favorites' => array_slice($ids, 0, 3)])->assertSessionHasErrors('favorites.2');
    }

    public function test_guests_cannot_submit_and_small_catalog_does_not_lock_users_out(): void
    {
        $this->get('/onboarding')->assertRedirect(route('login'));
        $this->post('/onboarding')->assertRedirect(route('login'));
        $this->categories(2);
        $user = $this->member();
        $this->actingAs($user)->get('/events')->assertViewIs('events.index');
        $this->assertFalse($user->fresh()->hasCompletedOnboarding());
        Category::create(['name' => 'Third world', 'slug' => 'third']);
        $this->get('/events')->assertViewIs('auth.onboarding');
        $user->forceFill(['email_verified_at' => null])->save();
        $this->get('/onboarding')->assertViewIs('auth.onboarding');
    }

    public function test_all_content_scopes_prioritize_favorites_without_hiding_other_categories(): void
    {
        $ids = $this->categories();
        $user = $this->member(true);
        $user->favoriteCategories()->attach($ids[1]);
        foreach ([Content::class, Event::class, CharacterProfile::class, MerchandiseItem::class] as $model) {
            $base = match ($model) {
                Content::class => ['title' => 'Story', 'type' => 'article', 'status' => 'published', 'published_at' => now()],
                Event::class => ['title' => 'Event', 'city' => 'City', 'status' => 'published', 'start_at' => now()->addDay()],
                MerchandiseItem::class => ['name' => 'Item', 'content_id' => Content::first()->id],
                default => ['name' => 'Item'],
            };
            $general = $model::create([...$base, 'slug' => 'general-item', 'category_id' => $ids[0]]);
            $favorite = $model::create([...$base, 'slug' => 'favorite-item', 'category_id' => $ids[1]]);
            $this->assertSame([$favorite->id, $general->id], $model::forUser($user)->orderBy('id')->pluck('id')->all());
            $this->assertSame([$general->id, $favorite->id], $model::forUser(null)->orderBy('id')->pluck('id')->all());
        }
        $this->actingAs($user)->get('/events')->assertOk()->assertViewHas('events', fn ($events) => $events->first()->category_id === $ids[1]);
        $this->get('/explore')->assertOk()->assertViewHas('contents', fn ($items) => $items->first()->category_id === $ids[1]);
        $this->get('/discover/characters')->assertOk()->assertViewHas('items', fn ($items) => $items->first()->category_id === $ids[1]);
        $this->get('/discover/merchandise')->assertOk()->assertViewHas('items', fn ($items) => $items->first()->category_id === $ids[1]);
    }

    public function test_homepage_personalization_does_not_leak_between_users_or_guests(): void
    {
        $ids = $this->categories();
        $first = Content::create(['title' => 'Popular general story', 'category_id' => $ids[0], 'type' => 'article', 'status' => 'published', 'published_at' => now(), 'popularity_score' => 100]);
        $second = Content::create(['title' => 'Favorite story', 'category_id' => $ids[1], 'type' => 'article', 'status' => 'published', 'published_at' => now(), 'popularity_score' => 1]);
        Cache::flush();
        $service = app(HomepageService::class);
        $this->assertSame($first->id, $service->sections()['trending']->first()->id);
        $one = $this->member(true);
        $one->favoriteCategories()->attach($ids[1]);
        $this->actingAs($one);
        $this->assertSame($second->id, $service->sections()['trending']->first()->id);
        $two = $this->member(true);
        $two->favoriteCategories()->attach($ids[0]);
        $this->actingAs($two);
        $this->assertSame($first->id, $service->sections()['trending']->first()->id);
        auth()->logout();
        $this->assertSame($first->id, $service->sections()['trending']->first()->id);
    }

    public function test_upcoming_feeds_rank_favorites_across_content_and_merchandise(): void
    {
        $ids = $this->categories();
        $user = $this->member(true);
        $user->favoriteCategories()->attach($ids[1]);
        $content = Content::create(['title' => 'General release', 'category_id' => $ids[0], 'type' => 'article',
            'status' => 'published', 'published_at' => now(), 'release_date' => now()->addDay()]);
        MerchandiseItem::create(['name' => 'Favorite collectible', 'category_id' => $ids[1], 'content_id' => $content->id,
            'is_upcoming' => true, 'release_date' => now()->addWeek()]);
        $this->actingAs($user);
        $service = app(HomepageService::class);
        $this->assertSame('Favorite collectible', $service->releases()->first()['title']);
        $this->assertSame('Favorite collectible', $service->paginatedReleases('all')->first()['title']);
        $this->assertSame(2, $service->paginatedReleases('all')->total());
    }
}
