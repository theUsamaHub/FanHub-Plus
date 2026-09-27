<?php

namespace Tests\Feature;

use App\Models\{ActivityLog, Category, Role, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWelcomeTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        $user = User::factory()->create(['name' => 'Alex']);
        $role = Role::firstOrCreate(['slug' => 'registered-user'], ['name' => 'Registered User']);
        $user->roles()->syncWithoutDetaching([$role->id]);
        $user->profile()->updateOrCreate([], ['display_name' => 'Sky', 'onboarding_completed_at' => now()]);

        return $user;
    }

    public function test_first_visit_and_returning_greetings_use_favorites_across_sessions(): void
    {
        $user = $this->member();
        $anime = Category::create(['name' => 'Anime']);
        $gaming = Category::create(['name' => 'Gaming']);
        $user->favoriteCategories()->attach([$gaming->id, $anime->id]);
        $this->actingAs($user)->get('/user/dashboard')->assertOk()->assertSee('Welcome to your fan space,')
            ->assertSee('Sky')->assertSee('Your love for Anime and Gaming has a home here.');
        $this->flushSession();
        $this->actingAs($user)->get('/user/dashboard')->assertOk()->assertSee('Welcome back,')
            ->assertSee('Ready for more from Anime and Gaming?');
        $this->assertSame(1, ActivityLog::where('user_id', $user->id)->where('event', 'dashboard.visited')->count());
        $user->favoriteCategories()->sync([$gaming->id]);
        $this->get('/user/dashboard')->assertOk()->assertViewHas('welcome', fn ($welcome) =>
            str_contains($welcome['message'], 'Ready for more from Gaming?') && $welcome['url'] === route('public.explore', ['category' => $gaming->slug]));
    }

    public function test_fallback_and_first_visit_are_specific_to_each_user(): void
    {
        $first = $this->member();
        $this->actingAs($first)->get('/user/dashboard')->assertOk()->assertSee('Make yourself at home.')
            ->assertSee('Choose your fandoms');
        $this->get('/user/dashboard')->assertOk()->assertSee('Your next favorite world is waiting.');
        $second = $this->member();
        $second->profile()->delete();
        $this->actingAs($second)->get('/user/dashboard')->assertOk()->assertSee('Welcome to your fan space,')->assertSee('Alex');
    }

    public function test_deleted_favorites_use_fallback_and_guest_visits_are_not_recorded(): void
    {
        $this->get('/user/dashboard')->assertRedirect(route('login'));
        $this->assertSame(0, ActivityLog::where('event', 'dashboard.visited')->count());
        $user = $this->member();
        $category = Category::create(['name' => 'Old fandom']);
        $user->favoriteCategories()->attach($category);
        $category->delete();
        $this->actingAs($user)->get('/user/dashboard')->assertOk()->assertViewHas('welcome', fn ($welcome) =>
            $welcome['action'] === 'Choose your fandoms' && !str_contains($welcome['message'], 'Old fandom'));
    }
}
