<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\ChatbotFaq;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotFaqTest extends TestCase
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

    public function test_admin_can_create_faq(): void
    {
        $this->actingAs($this->admin)->post(route('admin.chatbot.faqs.store'), [
            'category_id' => $this->category->id,
            'question' => 'What is FanHub Plus?',
            'answer' => 'An anime and fandom community.',
        ])->assertRedirect(route('admin.chatbot.faqs.index'));

        $this->assertDatabaseHas('chatbot_faqs', [
            'category_id' => $this->category->id,
            'question' => 'What is FanHub Plus?',
            'answer' => 'An anime and fandom community.',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_faq_requires_question_and_answer(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.chatbot.faqs.store'), [])
            ->assertSessionHasErrors(['question', 'answer']);
    }

    public function test_admin_can_view_and_update_faq(): void
    {
        $faq = ChatbotFaq::create([
            'category_id' => $this->category->id,
            'question' => 'Old question?',
            'answer' => 'Old answer.',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.chatbot.faqs.show', $faq))
            ->assertOk()
            ->assertSee('Old question?')
            ->assertSee('Old answer.');

        $this->actingAs($this->admin)->put(route('admin.chatbot.faqs.update', $faq), [
            'category_id' => $this->category->id,
            'question' => 'Updated question?',
            'answer' => 'Updated answer.',
        ])->assertRedirect(route('admin.chatbot.faqs.index'));

        $this->assertDatabaseHas('chatbot_faqs', [
            'id' => $faq->id,
            'question' => 'Updated question?',
            'answer' => 'Updated answer.',
        ]);
    }

    public function test_admin_can_delete_faq(): void
    {
        $faq = ChatbotFaq::create([
            'question' => 'Delete this?',
            'answer' => 'Yes.',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.chatbot.faqs.destroy', $faq))
            ->assertRedirect(route('admin.chatbot.faqs.index'));

        $this->assertDatabaseMissing('chatbot_faqs', ['id' => $faq->id]);
    }

    public function test_admin_can_filter_faqs(): void
    {
        ChatbotFaq::create([
            'category_id' => $this->category->id,
            'question' => 'Anime question?',
            'answer' => 'Anime answer.',
        ]);
        ChatbotFaq::create([
            'question' => 'General question?',
            'answer' => 'General answer.',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.chatbot.faqs.index', ['category_id' => $this->category->id]))
            ->assertOk()
            ->assertSee('Anime question?')
            ->assertDontSee('General question?');
    }

    public function test_non_admin_cannot_manage_faqs(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.chatbot.faqs.index'))
            ->assertRedirect(route('user.dashboard'));
    }
}
