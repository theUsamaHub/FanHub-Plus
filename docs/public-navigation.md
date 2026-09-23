# Public navbar and footer

This phase implements the shared public shell from the supplied navbar/footer references. The homepage section files remain available for the next phase. The admin layout and assets remain separate.

## Run locally

```sh
npm ci
npm run build
php artisan storage:link
php artisan serve
```

Use the existing application's configured database and migrations. The storage link exposes `storage/app/public/images` at `/storage/images` without changing where existing public-disk uploads are stored.

## Components

- `layouts/public.blade.php` loads the public bundle only.
- `components/navbar.blade.php` supports guests and authenticated users.
- `components/footer.blade.php` contains the reference copy and link groups.
- `config/fandoms.php` maps the eight supplied images, captions, and categories.
- `resources/js/modules/navigation.js` handles hover, touch, keyboard, search, sticky state, and the saved theme.
- `resources/css/components/` contains separate navbar/footer styles.

Saira and Rajdhani are served locally through Vite. Social SVGs come from the installed Bootstrap Icons package. The crown and navigation icons are inline SVG, so they remain sharp at mobile sizes.

## Destinations

Explore, category filtering, trending order, featured filtering, and search use published database content with pagination. Drafts and future publication dates are excluded. Sitemap is available at `/sitemap`. Login, registration, profile, and CSRF-protected logout use the existing authentication system. Registered users return to the shared homepage after the existing dashboard redirect.

Characters, multimedia, events, upcoming releases, merchandise, feedback, privacy, terms, and the account destinations currently show explicit coming-soon pages. Their full features are outside this navbar/footer phase. Account destinations require login and preserve the intended URL.

Official social URLs were not supplied. Set `SOCIAL_DISCORD_URL`, `SOCIAL_X_URL`, `SOCIAL_INSTAGRAM_URL`, `SOCIAL_YOUTUBE_URL`, and `SOCIAL_TIKTOK_URL` to enable the corresponding links. Until then, the icons are labelled coming soon and do not link to invented accounts.

## Verification

```sh
php artisan test --filter=PublicNavigationTest
```

The feature tests cover both authentication states, protected destinations, all eight images, category filtering, literal search wildcards, and unpublished-content exclusion. Browser checks cover hover, touch, Escape, keyboard focus, sticky state, search, saved theme, image loading, and horizontal overflow from 320px through 1672px.
