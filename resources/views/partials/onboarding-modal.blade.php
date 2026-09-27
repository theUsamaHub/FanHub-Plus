@php($selectedFavorites = is_array(old('favorites', $selected)) ? old('favorites', $selected) : [])
@php($selectedFavorites = array_values(array_intersect(array_filter($selectedFavorites, 'is_scalar'), $categories->modelKeys())))
<dialog open class="onboarding" aria-modal="true" aria-labelledby="onboarding-title" aria-describedby="onboarding-description"
    x-data="fandomOnboarding" data-onboarding data-selected="{{ json_encode(array_values(array_filter($selectedFavorites, 'is_scalar'))) }}" @cancel.prevent>
    <div class="onboarding__shell">
        <header class="onboarding__top"><span class="onboarding__wordmark">FanHub<span>Plus</span></span><span class="onboarding__step"><i aria-hidden="true"></i> YOUR FIRST CHAPTER <b>01 / 01</b></span></header>
        <div class="onboarding__layout">
            <aside class="onboarding__intro">
                <p class="onboarding__eyebrow">A LITTLE MORE YOU.</p>
                <h1 id="onboarding-title">Big fan<br>of <em>what?</em></h1>
                <p id="onboarding-description">Pick 3 to 5 worlds you love. We'll bring their stories, characters, events and collectibles to the front.</p>
                <div class="onboarding__orbit" aria-hidden="true"><span>YOUR<br><b>UNIVERSE</b></span><i>✦</i><i>+</i><i>♡</i></div>
                <p class="onboarding__note"><x-site-icon name="compass" /> Your favorites come first.<br>Every other world stays open.</p>
            </aside>
            <form method="post" action="{{ route('onboarding.store') }}" class="onboarding__form" @submit="submit($event)" :aria-busy="submitting">
                @csrf
                <div class="onboarding__grid-heading"><div><h2>Find your people.</h2><p>Choose your favorite fandoms to get started.</p></div><span>{{ $categories->count() }} worlds to explore</span></div>
                @if($errors->any())<div class="onboarding__error" role="alert" tabindex="-1" autofocus>{{ $errors->first() }}</div>@endif
                <fieldset class="onboarding__grid"><legend class="onboarding__sr">Choose between 3 and 5 favorite fandoms</legend>
                    @foreach($categories as $category)
                        @php($art = $category->icon_url ?: asset(config('homepage.artwork.'.$category->slug, 'images/fandoms/anime.png')))
                        <label class="onboarding-card" style="--card-order: {{ min($loop->index, 9) }}" :class="{ 'is-selected': selected.includes('{{ $category->id }}'), 'is-muted': selected.length >= 5 && !selected.includes('{{ $category->id }}') }">
                            <input type="checkbox" name="favorites[]" value="{{ $category->id }}" @checked(in_array($category->id, $selectedFavorites))
                                x-model="selected" :disabled="selected.length >= 5 && !selected.includes('{{ $category->id }}')">
                            <img src="{{ $art }}" alt="" width="520" height="340" loading="{{ $loop->index < 6 ? 'eager' : 'lazy' }}" data-image-fallback="{{ asset('images/fandoms/anime.png') }}">
                            <span class="onboarding-card__shade"></span><span class="onboarding-card__check" aria-hidden="true">✓</span>
                            <span class="onboarding-card__copy"><small>FIND YOUR WORLD</small><strong>{{ $category->name }}</strong><span>{{ \Illuminate\Support\Str::limit(strip_tags($category->description ?? ''), 70) ?: 'Stories. People. Possibilities.' }}</span></span>
                        </label>
                    @endforeach
                </fieldset>
                <footer class="onboarding__footer">
                    <div class="onboarding__progress"><div><strong x-text="`${selected.length} of 5 selected`">{{ count($selectedFavorites) }} of 5 selected</strong><span x-text="hint">Choose at least 3 to continue.</span></div><div class="onboarding__segments" aria-hidden="true">@for($i = 1; $i <= 5; $i++)<i :class="{ 'is-filled': selected.length >= {{ $i }} }"></i>@endfor</div></div>
                    <button class="onboarding__submit" type="submit" :disabled="!valid || submitting"><span x-text="submitting ? 'Creating your universe...' : 'Complete Setup'">Complete Setup</span><span aria-hidden="true">↗</span></button>
                    <p class="onboarding__feedback" role="status" aria-live="polite" x-text="message"></p>
                    <noscript><p>Choose 3–5 fandoms, then select Complete Setup. Your choices will be checked when you submit.</p></noscript>
                </footer>
            </form>
        </div>
        <form method="post" action="{{ route('logout') }}" class="onboarding__logout">@csrf <span>Signed in as {{ auth()->user()->email }}</span><button type="submit">Sign out</button></form>
    </div>
</dialog>
