<?php

namespace Tests\Feature;

use App\Models\ChatbotFaq;
use App\Models\ChatbotQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    public function test_faqs_work_without_an_api_key_and_save_the_answer(): void
    {
        Http::preventStrayRequests();
        config(['services.gemini.key' => null]);
        ChatbotFaq::create(['question' => 'What is anime?', 'answer' => 'Japanese animation.']);
        $this->getJson('/chatbot/faqs')->assertOk()->assertJsonPath('faqs.0.question', 'What is anime?');
        $this->postJson('/chatbot/message', ['message' => 'WHAT is anime?'])->assertOk()
            ->assertJsonPath('source', 'faq')->assertJsonPath('answer', 'Japanese animation.');
        $this->assertDatabaseHas('chatbot_queries', ['message' => 'WHAT is anime?', 'response' => 'Japanese animation.', 'user_id' => null]);
        Http::assertNothingSent();
    }

    public function test_ai_uses_faq_context_and_does_not_send_another_sessions_history(): void
    {
        config(['services.gemini.key' => 'test-key']);
        ChatbotQuery::create(['session_id' => 'another-session', 'message' => 'Private question', 'response' => 'Private answer']);
        ChatbotFaq::create(['question' => 'Site help?', 'answer' => 'Explore fandoms.']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'Try a fantasy anime.']]]]]])]);
        $this->postJson('/chatbot/message', ['message' => 'Recommend anime'])->assertOk()
            ->assertJsonPath('source', 'gemini')->assertJsonPath('answer', 'Try a fantasy anime.');
        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'test-key')
            && str_contains($request->body(), 'Explore fandoms.')
            && !str_contains($request->body(), 'Private question'));
    }

    public function test_missing_key_and_provider_errors_are_actionable_and_not_saved(): void
    {
        config(['services.gemini.key' => null]);
        $this->postJson('/chatbot/message', ['message' => 'Hello'])->assertStatus(503);
        config(['services.gemini.key' => 'secret-key']);
        Http::fake(['*' => Http::response(['error' => 'sensitive provider details'], 429)]);
        $this->postJson('/chatbot/message', ['message' => 'Hello'])->assertStatus(503)
            ->assertDontSee('secret-key')->assertDontSee('sensitive provider details');
        $this->assertDatabaseCount('chatbot_queries', 0);
    }

    public function test_blank_and_oversized_messages_are_rejected(): void
    {
        $this->postJson('/chatbot/message', ['message' => '   '])->assertStatus(422);
        $this->postJson('/chatbot/message', ['message' => str_repeat('a', 1501)])->assertStatus(422);
    }
}
