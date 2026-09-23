<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\CharacterProfile;
use App\Models\Content;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($role);

        $this->category = Category::create(['name' => 'Anime', 'slug' => 'anime']);
    }

    private function content(string $title = 'Related Content'): Content
    {
        return Content::create([
            'category_id' => $this->category->id,
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'type' => 'article',
            'status' => 'published',
        ]);
    }

    public function test_admin_can_create_character_with_related_content(): void
    {
        $content = $this->content();

        $this->actingAs($this->admin)->post(route('admin.characters.store'), [
            'name' => 'Naruto Uzumaki',
            'category_id' => $this->category->id,
            'bio' => 'A ninja from Konoha.',
            'content_ids' => [$content->id],
        ])->assertRedirect(route('admin.characters.index'));

        $character = CharacterProfile::firstWhere('name', 'Naruto Uzumaki');
        $this->assertSame('naruto-uzumaki', $character->slug);
        $this->assertTrue($character->contents->contains($content));
    }

    public function test_character_requires_name_and_category(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.characters.store'), [])
            ->assertSessionHasErrors(['name', 'category_id']);
    }

    public function test_character_slug_must_be_unique(): void
    {
        CharacterProfile::create([
            'category_id' => $this->category->id,
            'name' => 'Existing',
            'slug' => 'existing',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.characters.store'), [
                'name' => 'Another',
                'slug' => 'existing',
                'category_id' => $this->category->id,
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_admin_can_update_character_and_sync_related_content(): void
    {
        $first = $this->content('First');
        $second = $this->content('Second');

        $character = CharacterProfile::create([
            'category_id' => $this->category->id,
            'name' => 'Original',
            'slug' => 'original',
        ]);
        $character->contents()->attach($first->id);

        $this->actingAs($this->admin)->put(route('admin.characters.update', $character), [
            'name' => 'Updated',
            'category_id' => $this->category->id,
            'content_ids' => [$second->id],
        ])->assertRedirect(route('admin.characters.index'));

        $character->refresh();
        $this->assertSame('Updated', $character->name);
        $this->assertFalse($character->contents->contains($first));
        $this->assertTrue($character->contents->contains($second));
    }

    public function test_admin_can_attach_and_detach_related_content(): void
    {
        $character = CharacterProfile::create([
            'category_id' => $this->category->id,
            'name' => 'Linkable',
            'slug' => 'linkable',
        ]);
        $content = $this->content();

        $this->actingAs($this->admin)
            ->post(route('admin.characters.contents.attach', $character), ['content_ids' => [$content->id]])
            ->assertRedirect();

        $this->assertTrue($character->fresh()->contents->contains($content));

        $this->actingAs($this->admin)
            ->delete(route('admin.characters.contents.detach', [$character, $content]))
            ->assertRedirect();

        $this->assertFalse($character->fresh()->contents->contains($content));
    }

    public function test_admin_can_delete_character_and_keeps_content(): void
    {
        $content = $this->content();
        $character = CharacterProfile::create([
            'category_id' => $this->category->id,
            'name' => 'Doomed',
            'slug' => 'doomed',
        ]);
        $character->contents()->attach($content->id);

        $this->actingAs($this->admin)
            ->delete(route('admin.characters.destroy', $character))
            ->assertRedirect(route('admin.characters.index'));

        $this->assertDatabaseMissing('character_profiles', ['id' => $character->id]);
        $this->assertDatabaseHas('contents', ['id' => $content->id]);
    }

    public function test_non_admin_cannot_manage_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.characters.index'))->assertRedirect(route('user.dashboard'));
    }
}
