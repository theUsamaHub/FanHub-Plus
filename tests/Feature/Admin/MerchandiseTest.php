<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\MerchandiseItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MerchandiseTest extends TestCase
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

    public function test_admin_can_create_merchandise_without_commerce_fields(): void
    {
        $this->actingAs($this->admin)->post(route('admin.merchandise.store'), [
            'name' => 'Limited Figure',
            'category_id' => $this->category->id,
            'description' => 'Display only showcase item.',
            'tag' => 'limited_edition',
            'is_upcoming' => 1,
        ])->assertRedirect(route('admin.merchandise.index'));

        $item = MerchandiseItem::firstWhere('name', 'Limited Figure');
        $this->assertSame('limited-figure', $item->slug);
        $this->assertTrue($item->is_upcoming);
        $this->assertSame(0, $item->view_count);
    }

    public function test_merchandise_requires_name_category_and_tag(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.merchandise.store'), [])
            ->assertSessionHasErrors(['name', 'category_id', 'tag']);
    }

    public function test_merchandise_tag_must_be_allowed_value(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.merchandise.store'), [
                'name' => 'Bad Tag Item',
                'category_id' => $this->category->id,
                'tag' => 'on_sale',
            ])
            ->assertSessionHasErrors('tag');
    }

    public function test_admin_can_update_merchandise(): void
    {
        $item = MerchandiseItem::create([
            'category_id' => $this->category->id,
            'name' => 'Old Name',
            'slug' => 'old-name',
            'tag' => 'standard',
        ]);

        $this->actingAs($this->admin)->put(route('admin.merchandise.update', $item), [
            'name' => 'New Name',
            'category_id' => $this->category->id,
            'tag' => 'collectible',
            'is_upcoming' => 1,
        ])->assertRedirect(route('admin.merchandise.index'));

        $item->refresh();
        $this->assertSame('New Name', $item->name);
        $this->assertSame('collectible', $item->tag);
        $this->assertTrue($item->is_upcoming);
    }

    public function test_admin_can_delete_merchandise(): void
    {
        $item = MerchandiseItem::create([
            'category_id' => $this->category->id,
            'name' => 'Doomed',
            'slug' => 'doomed',
            'tag' => 'standard',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.merchandise.destroy', $item))
            ->assertRedirect(route('admin.merchandise.index'));

        $this->assertDatabaseMissing('merchandise_items', ['id' => $item->id]);
    }

    public function test_admin_can_filter_merchandise(): void
    {
        MerchandiseItem::create([
            'category_id' => $this->category->id,
            'name' => 'Standard Tee',
            'slug' => 'standard-tee',
            'tag' => 'standard',
            'is_upcoming' => false,
        ]);
        MerchandiseItem::create([
            'category_id' => $this->category->id,
            'name' => 'Upcoming Statue',
            'slug' => 'upcoming-statue',
            'tag' => 'pre_order',
            'is_upcoming' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.merchandise.index', ['upcoming' => '1', 'tag' => 'pre_order']))
            ->assertOk()
            ->assertSee('Upcoming Statue')
            ->assertDontSee('Standard Tee');
    }

    public function test_non_admin_cannot_manage_merchandise(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.merchandise.index'))->assertRedirect(route('user.dashboard'));
    }
}
