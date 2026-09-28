<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['filesystems.disks.detail_preview' => ['driver'=>'local', 'root'=>public_path('images'), 'url'=>'http://127.0.0.1:8000/images']]);
$view = app(App\Http\Controllers\PublicSiteController::class)->content(App\Models\Content::visibleToPublic()->firstOrFail());
$data = $view->getData();
view()->share('errors', new Illuminate\Support\ViewErrorBag());
$content = clone $data['content'];
$content->title = 'Eclipse of Aether';
$content->excerpt = 'In a world where floating nations are sustained by a mysterious force known as Aether, a young outcast discovers he can wield the power that others fear. As ancient secrets awaken and the sky begins to fracture, he must choose between his own destiny and the fate of a world on the edge of collapse.';
$content->body = '<p>Temporary visual QA fixture. The live content database is unchanged.</p>';
$paths = ['fandoms/anime.png', 'fandoms/gaming.png', 'fandoms/cinema.png', 'characters/character1.jpg', 'hero/fandom-cards.png'];
$gallery = collect($paths)->map(fn ($path, $i) => new App\Models\Media(['disk'=>'detail_preview','path'=>$path,'media_type'=>'image','alt_text'=>'Gallery scene '.($i+1)]));
$content->setRelation('media', collect([$gallery[0]]));
$data['content'] = $content;
$data['gallery'] = $gallery;
$data['characters'] = collect(['Kael Ardyn','Lyria Solen','Darian Voss','Selene','Mira','Neris','Tahl'])->map(function ($name, $i) use ($gallery, $content) {
    $character = new App\Models\CharacterProfile(['name'=>$name,'slug'=>Illuminate\Support\Str::slug($name),'bio'=>'Character in Eclipse of Aether']);
    $character->setRelation('imageMedia', $gallery[$i % 5]);
    $character->setRelation('category', $content->category);
    return $character;
});
$data['merchandise'] = collect(range(1, 8))->map(function ($i) use ($gallery, $content) {
    $item = new App\Models\MerchandiseItem(['name'=>'Aether collectible '.$i,'slug'=>'fixture-'.$i,'tag'=>'collectible']);
    $item->id = $i;
    $item->setRelation('imageMedia', $gallery[$i % 5]);
    $item->setRelation('category', $content->category);
    $item->setRelation('character', null);
    return $item;
});
$data['trailers'] = collect([new App\Models\Media(['disk'=>'public','path'=>'../videos/splash screen video.mp4','media_type'=>'video','duration'=>10])]);
$data['audioClips'] = collect([new App\Models\Media(['disk'=>'public','path'=>'preview-only.mp3','media_type'=>'audio','alt_text'=>'Eclipse of Aether (Original Soundtrack)','duration'=>225])]);
$data['attachments'] = collect([new App\Models\Media(['disk'=>'public','path'=>'preview-only.pdf','media_type'=>'document','mime_type'=>'application/pdf','size_bytes'=>12400000,'alt_text'=>'Complete World Guide: The Nations of Aether'])]);
file_put_contents(public_path('_content-detail-preview.html'), str_replace('http://localhost:8000', 'http://127.0.0.1:8000', view('public.content', $data)->render()));
