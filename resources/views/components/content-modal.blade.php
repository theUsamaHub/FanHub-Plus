@props(['content' => null])

<x-modal name="content-detail" maxWidth="4xl">
    <div x-data="{
        content: @js($content),
        loading: false,
        init() {
            if (!this.content) {
                this.loadContent();
            }
        },
        async loadContent() {
            this.loading = true;
            try {
                const response = await fetch('/api/v1/content/' + this.content.slug);
                if (response.ok) {
                    this.content = await response.json();
                }
            } catch (e) {
                console.error('Failed to load content:', e);
            } finally {
                this.loading = false;
            }
        }
    }" x-init="init()">
        
        <div x-show="loading" class="modal-loading" style="display: flex; align-items: center; justify-content: center; padding: 60px;">
            <div class="spinner" style="width: 40px; height: 40px; border: 3px solid var(--fh-line); border-top-color: var(--fh-accent); border-radius: 50%; animation: spin 1s linear infinite;"></div>
        </div>

        <template x-if="!loading && content">
            <div class="content-modal">
                <button type="button" class="modal-close" x-on:click="$dispatch('close-modal', { detail: 'content-detail' })" aria-label="Close">
                    <x-site-icon name="x-lg" />
                </button>

                <nav class="cd-breadcrumb" aria-label="Breadcrumb" style="margin-bottom: 16px;">
                    <a href="{{ route('home') }}">Home</a><span>/</span>
                    <a x-bind:href="'{{ route('public.explore', ['category' => '']) }}' + content.category?.slug" x-text="content.category?.name || 'Explore'"></a><span>/</span>
                    <span aria-current="page" x-text="content.title"></span>
                </nav>

                <div class="cd-hero">
                    <figure class="cd-poster">
                        <img x-bind:src="content.artwork_url" x-bind:alt="content.title" fetchpriority="high" width="560" height="620" style="object-fit: cover; border-radius: 16px;">
                    </figure>
                    <header class="cd-intro">
                        <div class="cd-tags" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                            <span class="cd-tag cd-tag--category" x-text="content.category?.name || 'Content'" style="background: var(--fh-accent); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"></span>
                            <template x-for="tag in content.tags" :key="tag.id">
                                <a class="cd-tag" x-bind:href="'{{ route('public.explore', ['tag' => '']) }}' + tag.id" x-text="tag.name" style="background: var(--fh-surface); border: 1px solid var(--fh-line); padding: 4px 12px; border-radius: 20px; font-size: 12px;"></a>
                            </template>
                        </div>
                        <h1 x-text="content.title" style="font-family: 'Rajdhani', sans-serif; font-size: clamp(32px, 5vw, 56px); font-weight: 700; margin: 12px 0; line-height: 1.1;"></h1>
                        <div class="cd-meta" style="display: flex; flex-wrap: wrap; gap: 16px; margin: 12px 0; color: var(--fh-muted); font-size: 14px;">
                            <template x-if="content.release_date">
                                <time x-bind:datetime="content.release_date" x-text="new Date(content.release_date).getFullYear()"></time>
                            </template>
                            <span x-text="content.kind_label"></span>
                            <template x-if="content.release_label">
                                <span x-text="content.release_label"></span>
                            </template>
                            <template x-if="content.release_date && new Date(content.release_date) > new Date()">
                                <span style="color: var(--fh-accent);">Upcoming</span>
                            </template>
                        </div>
                        <p class="cd-synopsis" x-text="content.excerpt" style="color: var(--fh-muted); margin: 16px 0; line-height: 1.7;"></p>
                        <div class="cd-actions" style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 20px;">
                            @auth
                            <form method="post" x-bind:action="'{{ route('user.bookmark', ['content', '']) }}' + content.id" style="display: inline;">
                                @csrf
                                <input type="hidden" name="saved" :value="content.saved ? 0 : 1">
                                <button class="cd-button" :aria-pressed="content.saved ? 'true' : 'false'" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: var(--fh-accent); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                                    <i class="bi" :class="content.saved ? 'bi-bookmark-fill' : 'bi-bookmark'" aria-hidden="true"></i>
                                    <span x-text="content.saved ? 'Saved to Favorites' : 'Add to Favorites'"></span>
                                </button>
                            </form>
                            @else
                            <a x-bind:href="'{{ route('login') }}'" class="cd-button" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: var(--fh-accent); color: white; border: none; border-radius: 8px; font-weight: 600; text-decoration: none;">
                                <i class="bi bi-bookmark" aria-hidden="true"></i>Add to Favorites
                            </a>
                            @endauth
                            <button class="cd-button cd-button--share" type="button" :data-share-url="window.location.origin + '/stories/' + content.slug" :data-share-title="content.title" aria-label="Share" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: transparent; color: var(--fh-text); border: 1px solid var(--fh-line); border-radius: 8px; font-weight: 600; cursor: pointer;">
                                <i class="bi bi-share" aria-hidden="true"></i>
                            </button>
                        </div>
                    </header>
                </div>

                <template x-if="content.trailers && content.trailers.length > 0">
                    <div class="cd-media" style="margin-top: 32px;">
                        <template x-for="trailer in content.trailers" :key="trailer.url">
                            <div class="cd-trailer" data-trailer style="position: relative; border-radius: 16px; overflow: hidden; background: #000;">
                                <video controls playsinline preload="none" :poster="content.artwork_url" :src="trailer.url" :aria-label="content.title + ' trailer'" style="width: 100%; display: block;"></video>
                                <button type="button" class="cd-trailer-cover" data-trailer-play :aria-label="'Play ' + content.title + ' trailer'" style="position: absolute; inset: 0; background: rgba(0,0,0,0.5); border: none; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; cursor: pointer;">
                                    <span class="cd-trailer-label" style="font-weight: 600; font-size: 18px;">Official Trailer</span>
                                    <template x-if="trailer.duration">
                                        <span class="cd-duration" x-text="new Date(trailer.duration * 1000).toISOString().substr(14, 5)" style="font-family: 'Rajdhani', sans-serif;"></span>
                                    </template>
                                    <span class="cd-play" style="width: 80px; height: 80px; background: var(--fh-accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                        <i class="bi bi-play-fill" aria-hidden="true"></i>
                                    </span>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="content.audio_clips && content.audio_clips.length > 0">
                    <div class="cd-media" style="margin-top: 32px;">
                        <template x-for="audio in content.audio_clips" :key="audio.url">
                            <div class="cd-audio" data-audio-player style="display: flex; gap: 16px; padding: 16px; background: var(--fh-surface); border: 1px solid var(--fh-line); border-radius: 12px;">
                                <img :src="content.artwork_url" alt="" loading="lazy" style="width: 80px; height: 80px; border-radius: 8px; object-fit: cover;">
                                <div class="cd-audio-body" style="flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 8px;">
                                    <strong x-text="audio.alt_text || audio.original_filename"></strong>
                                    <span x-text="content.title" style="color: var(--fh-muted); font-size: 14px;"></span>
                                    <audio controls preload="metadata" :src="audio.url" :aria-label="audio.alt_text || content.title + ' audio'" style="width: 100%;"></audio>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="cd-middle" style="display: grid; grid-template-columns: 1fr 320px; gap: 32px; margin-top: 40px;">
                    <section class="cd-gallery-section" aria-labelledby="cd-gallery-title" style="margin-top: 32px;">
                        <h2 class="cd-heading" id="cd-gallery-title" style="display: flex; align-items: center; gap: 10px; font-family: 'Rajdhani', sans-serif; font-size: 28px; margin-bottom: 16px;">
                            <i class="bi bi-images" aria-hidden="true" style="font-size: 24px;"></i>Image Gallery
                        </h2>
                        <template x-if="content.gallery && content.gallery.length > 0">
                            <div class="cd-gallery-wrap">
                                <div class="swiper cd-gallery" data-gallery-slider style="overflow: hidden;">
                                    <div class="swiper-wrapper" style="display: flex; gap: 12px;">
                                        <template x-for="(media, index) in content.gallery" :key="index">
                                            <a class="swiper-slide cd-gallery-image" :href="media.url" data-gallery-image :aria-label="'Open image ' + (index + 1) + ': ' + (media.alt_text || content.title)" style="flex-shrink: 0; width: 360px; border-radius: 12px; overflow: hidden;">
                                                <img :src="media.url" :alt="media.alt_text || content.title + ' — image ' + (index + 1)" loading="lazy" width="360" height="200" style="width: 100%; height: 200px; object-fit: cover;">
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!content.gallery || content.gallery.length === 0">
                            <p class="cd-empty" style="text-align: center; color: var(--fh-muted); padding: 40px;">Gallery images haven't been added yet.</p>
                        </template>
                    </section>

                    <aside class="cd-feature" aria-labelledby="cd-feature-title">
                        <h2 class="cd-heading" id="cd-feature-title" style="display: flex; align-items: center; gap: 10px; font-family: 'Rajdhani', sans-serif; font-size: 24px; margin-bottom: 16px;">
                            <i class="bi bi-file-earmark-richtext-fill" aria-hidden="true"></i>
                            <span x-text="content.attachments && content.attachments.length > 0 ? 'Featured Article' : 'About this Content'"></span>
                        </h2>
                        <div class="cd-feature-card" style="background: var(--fh-surface); border: 1px solid var(--fh-line); border-radius: 16px; overflow: hidden;">
                            <img :src="content.gallery && content.gallery.length > 0 ? content.gallery[content.gallery.length - 1].url : content.artwork_url" alt="" loading="lazy" style="width: 100%; height: 180px; object-fit: cover;">
                            <div style="padding: 20px;">
                                <span class="cd-tag cd-tag--category" :x-text="content.attachments && content.attachments.length > 0 ? 'Read & explore' : (content.category?.name || 'Explore')" style="display: inline-block; background: var(--fh-accent); color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; margin-bottom: 12px;"></span>
                                <h3 x-text="content.attachments && content.attachments.length > 0 ? content.attachments[0].alt_text : content.title" style="font-family: 'Rajdhani', sans-serif; font-size: 22px; margin: 0 0 12px;"></h3>
                                <p x-text="content.excerpt || content.body ? content.body.substring(0, 115) + '...' : ''" style="color: var(--fh-muted); line-height: 1.7; margin-bottom: 16px;"></p>
                                <template x-if="content.attachments && content.attachments.length > 0">
                                    <template x-for="attachment in content.attachments" :key="attachment.url">
                                        <a class="cd-download" :href="attachment.url" download style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: var(--fh-bg); border: 1px solid var(--fh-line); border-radius: 8px; color: var(--fh-text); text-decoration: none; font-size: 13px; margin-bottom: 8px;">
                                            <i class="bi bi-download" aria-hidden="true"></i>
                                            <span>
                                                <span x-text="attachment.mime_type === 'application/pdf' ? 'Download Article (PDF)' : 'Download document'"></span>
                                                <small x-text="attachment.size_formatted" style="display: block; color: var(--fh-muted); font-size: 11px;"></small>
                                            </span>
                                        </a>
                                    </template>
                                </template>
                                <template x-if="!content.attachments || content.attachments.length === 0">
                                    <a :href="'{{ route('public.content', '') }}' + content.slug" class="cd-read" style="display: inline-flex; align-items: center; gap: 8px; color: var(--fh-accent); font-weight: 600; text-decoration: none;">
                                        Read more <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </aside>
                </div>

                <template x-if="content.characters && content.characters.length > 0">
                    <section class="cd-characters" aria-labelledby="cd-characters-title" style="margin-top: 40px;">
                        <h2 class="cd-heading" id="cd-characters-title" style="display: flex; align-items: center; gap: 10px; font-family: 'Rajdhani', sans-serif; font-size: 28px; margin-bottom: 16px;">
                            <i class="bi bi-people-fill" aria-hidden="true" style="font-size: 24px;"></i>Main Characters
                        </h2>
                        <div class="cd-character-stage">
                            <div class="swiper cd-character-slider" data-character-coverflow style="overflow: hidden;">
                                <div class="swiper-wrapper" style="display: flex; gap: 16px;">
                                    <template x-for="character in content.characters" :key="character.id">
                                        <a class="swiper-slide cd-character" :href="'{{ route('public.character', '') }}' + character.slug" style="flex-shrink: 0; width: 380px; text-decoration: none; color: inherit;">
                                            <img :src="character.artwork_url" :alt="character.name" loading="lazy" width="380" height="520" style="width: 100%; height: 520px; object-fit: cover; border-radius: 16px;">
                                            <span class="cd-character-caption" style="position: absolute; bottom: 0; left: 0; right: 0; padding: 20px; background: linear-gradient(transparent, rgba(0,0,0,0.8)); border-radius: 0 0 16px 16px;">
                                                <strong x-text="character.name" style="display: block; margin-bottom: 4px;"></strong>
                                                <span x-text="character.bio ? character.bio.substring(0, 65) + '...' : ''" style="font-size: 13px; color: rgba(255,255,255,0.8);"></span>
                                            </span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </section>
                </template>

                <template x-if="content.merchandise && content.merchandise.length > 0">
                    <section class="cd-merchandise" aria-labelledby="cd-merch-title" style="margin-top: 40px;">
                        <div class="cd-section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <h2 class="cd-heading" id="cd-merch-title" style="display: flex; align-items: center; gap: 10px; font-family: 'Rajdhani', sans-serif; font-size: 28px; margin: 0;">
                                <i class="bi bi-shop" aria-hidden="true" style="font-size: 24px;"></i>Merchandise
                            </h2>
                        </div>
                        <div class="swiper cd-merch-slider" data-merch-slider style="overflow: hidden;">
                            <div class="swiper-wrapper" style="display: flex; gap: 16px;">
                                <template x-for="item in content.merchandise" :key="item.id">
                                    <div class="swiper-slide cd-merch" style="flex-shrink: 0; width: 320px;">
                                        <a :href="'{{ route('public.merchandise', '') }}' + item.slug" style="text-decoration: none; color: inherit; display: block;">
                                            <img :src="item.artwork_url" :alt="item.name" loading="lazy" width="320" height="240" style="width: 100%; height: 240px; object-fit: cover; border-radius: 12px; margin-bottom: 12px;">
                                            <span style="display: block;">
                                                <strong x-text="item.name" style="display: block; margin-bottom: 4px;"></strong>
                                                <small x-text="item.display_tag || item.character?.name || content.category?.name" style="color: var(--fh-muted);"></small>
                                            </span>
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </section>
                </template>

                <template x-if="content.body">
                    <section class="cd-story" id="cd-story" aria-labelledby="cd-story-title" style="margin-top: 40px;">
                        <h2 class="cd-heading" id="cd-story-title" style="display: flex; align-items: center; gap: 10px; font-family: 'Rajdhani', sans-serif; font-size: 28px; margin-bottom: 16px;">
                            <i class="bi bi-book" aria-hidden="true" style="font-size: 24px;"></i><span x-text="'About ' + content.title"></span>
                        </h2>
                        <div class="cd-prose" x-html="content.body" style="line-height: 1.9; color: var(--fh-text);"></div>
                    </section>
                </template>

                <template x-if="content.events && content.events.length > 0">
                    <section class="cd-events" aria-labelledby="cd-events-title" style="margin-top: 40px;">
                        <h2 class="cd-heading" id="cd-events-title" style="display: flex; align-items: center; gap: 10px; font-family: 'Rajdhani', sans-serif; font-size: 28px; margin-bottom: 16px;">
                            <i class="bi bi-calendar-event" aria-hidden="true" style="font-size: 24px;"></i>Upcoming Events
                        </h2>
                        <div class="cd-related-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
                            <template x-for="event in content.events" :key="event.id">
                                <a class="cd-related-card" :href="'{{ route('events.show', '') }}' + event.slug" style="text-decoration: none; color: inherit; display: block; border: 1px solid var(--fh-line); border-radius: 12px; overflow: hidden; background: var(--fh-surface);">
                                    <img :src="event.artwork_url" alt="" loading="lazy" style="width: 100%; height: 160px; object-fit: cover;">
                                    <span style="display: block; padding: 16px;">
                                        <strong x-text="event.title" style="display: block; margin-bottom: 4px;"></strong>
                                        <small x-text="new Date(event.start_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' · ' + event.city" style="color: var(--fh-muted);"></small>
                                    </span>
                                </a>
                            </template>
                        </div>
                    </section>
                </template>
            </div>
        </template>
    </div>
</x-modal>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
.content-modal {
    max-height: 90vh;
    overflow-y: auto;
}
</style>