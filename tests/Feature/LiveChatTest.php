<?php

namespace Tests\Feature;

use App\Models\ChatChannel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_chat(): void
    {
        $this->get(route('chat.index'))->assertRedirect();
    }

    public function test_authenticated_user_can_see_chat_page(): void
    {
        $user = User::factory()->create();
        $channel = ChatChannel::create(['name' => 'General', 'slug' => 'general', 'is_active' => true, 'sort_order' => 0]);

        $this->actingAs($user)->get(route('chat.index'))
            ->assertOk()
            ->assertSee('General')
            ->assertSee('Live Chat');
    }

    public function test_user_can_send_message(): void
    {
        $user = User::factory()->create();
        $channel = ChatChannel::create(['name' => 'General', 'slug' => 'general', 'is_active' => true, 'sort_order' => 0]);

        $response = $this->actingAs($user)->postJson(route('chat.send'), [
            'channel_id' => $channel->id,
            'body' => 'Hello everyone!',
        ]);

        $response->assertOk()->assertJson(['body' => 'Hello everyone!']);
        $this->assertDatabaseHas('chat_messages', ['channel_id' => $channel->id, 'body' => 'Hello everyone!']);
    }
}
