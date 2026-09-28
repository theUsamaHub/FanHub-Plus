<?php

namespace Tests\Feature;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeStoryLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_trending_and_featured_cards_link_to_the_content_detail_page(): void
    {
        $category = \App\Models\Category::create(['name' => 'Anime', 'slug' => 'anime']);
        $content = Content::create(['category_id' => $category->id, 'title' => 'A featured story', 'slug' => 'a-featured-story',
            'type' => 'article', 'status' => 'published', 'published_at' => now(), 'is_featured' => true]);
        $url = route('public.content', $content->slug);
        foreach (['<x-trending-card :content="$content" :rank="1" />', '<x-featured-story-card :story="$content" :primary="true" />'] as $template) {
            $this->blade($template, compact('content'))->assertSee('href="'.$url.'"', false)
                ->assertDontSee('href="#"', false)->assertDontSee('openModal');
        }
        $this->get($url)->assertOk()->assertViewIs('public.content');
    }
}
