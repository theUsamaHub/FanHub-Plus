# Homepage sections

## Setup

Start the configured database, then run:

```sh
php artisan migrate
php artisan storage:link
npm ci
npm run build
php artisan serve
```

Two additive migrations add nullable `merchandise_items.release_date` and `contents.release_label`. Existing records are preserved. Set a content release label such as `Premiere`, `Launch`, or `In theaters` in the database; unset labels display `New release`. Merchandise labels use its existing `tag`. Undated upcoming merchandise displays `Date TBA`.

## Data and assets

`HomeController` and `HomepageService` supply data to the existing section partials in the brief's order. Both guests and signed-in users see the same public sections.

- Trending: six published records ordered by popularity, views, and ID.
- Featured: three published, featured articles; the first is the main story. Categories, tags, author, publication date, and calculated reading time come from the records.
- Character spotlight: up to six character profiles, most recently updated first, with category and image media eager-loaded. No demo profiles are inserted. The empty state is shown when there are no records. Explore Character opens `/characters/{slug}`, with a database biography and only publicly visible related stories.
- Upcoming: at most six content/merchandise records merged chronologically, beginning today; unknown dates come last. Category filters query the database again, including records outside the first six. Homepage filter pills follow the reference's four main categories plus merchandise; the full listing supports every available category.
- Draft and future-publication content is excluded, including direct story URLs.
- `View All` for releases is paginated at 12 records per page. Content and merchandise cards have real public detail routes. Merchandise is a showcase only.

`config/homepage.php` centralizes the five supplied default artwork paths. They currently reside in `storage/app/public/images`, including both theme backgrounds. Only artwork is fixed; record copy is not embedded in Blade or JavaScript. These are intentionally the user's default images, rather than the distinct posters shown in the UI references.

Homepage queries cache for 60 seconds. Saving or deleting content, a category, merchandise, a character profile, or media changes the cache version immediately. Direct SQL/bulk query updates and tag-pivot changes become visible after the cache TTL or `php artisan cache:clear`.

## Interaction and animation

The homepage alone loads `resources/js/pages/home.js` and `resources/css/pages/home.css`. GSAP/ScrollTrigger handles shared heading/card reveals, subtle desktop story parallax, the timeline line, and character spreading. Lenis uses the GSAP ticker. Swiper is scoped to the character carousel; the homepage remains a normal vertically scrolling page.

## Character spotlight

`resources/js/modules/character-spotlight.js` owns the stack → row → Swiper lifecycle. Cards start tightly overlapping at the center. A scrubbed reveal spreads those same elements into their eventual carousel positions. Desktop pins only if the section fits below navigation, for 460px of scrolling (320px on tablet). Mobile uses a short 180px hold so even direct section links show the initial stack. Short desktop screens use an unpinned reveal. Scrolling back reverses the spread and restores the center stack. Resizing across breakpoints cleans up and rebuilds the carousel/animation for the current scroll position.

Cards use taller portrait proportions with close spacing. Each entire card links to its character page; the name and category appear only on hover, keyboard focus, or touch press. There is no separate Explore button. The centered carousel advances every 2.6 seconds with a 900ms transition. It loops only with enough spare slides; short lists rewind. Arrows, pagination, dragging, touch, and keyboard-accessible links work without capturing vertical wheel scrolling. Autoplay stops offscreen, in hidden tabs, on hover/focus, or via its pause button. Reduced motion skips the reveal, smooth scrolling, and autoplay. Without JavaScript, the cards remain a native horizontal scrolling list.

Theme artwork uses the supplied `public/images/characters/cdark_back.png` and `c_light_back.png`. Database image media takes priority; absent/non-image media uses `character1.jpg`. The homepage also swaps broken image URLs to that fallback. Paths are configurable under `homepage.images`. One supplied fallback means multiple records without their own artwork show the same picture; upload distinct portraits in the character admin to differentiate them.

`home-characters.css` contains the spotlight styling. `home-compact.css` reduces the existing hero/section headings and oversized trending/story cards while preserving readable body copy and usable controls.

Implementation references: [Swiper API](https://swiperjs.com/swiper-api) and [ScrollTrigger documentation](https://gsap.com/docs/v3/Plugins/ScrollTrigger/).

Release arrows and keyboard navigation scroll a native horizontal timeline. Mobile uses a vertical timeline. Filters progressively enhance normal links with abortable fetch requests, loading/error feedback, an accessible result announcement, and browser Back/Forward support. Without JavaScript the filters reload the server-rendered page. Reduced-motion preference disables Lenis, reveal animations, and parallax.

Card reveals use each card's viewport position, so cards further down a mobile layout do not animate before they are visible. Section heading parts reveal in sequence, the featured image moves subtly with scroll on desktop, and timeline nodes appear alongside the drawing line. Switching the operating system's reduced-motion preference also cleans up active animations without requiring a reload.

If MySQL reports `Unknown column 'release_date'` on `merchandise_items`, apply the pending migrations with `php artisan migrate`. Do not reset or reseed the database. The two new columns are nullable, so existing merchandise stays intact and displays an unknown release date until one is entered.

## Validation

```sh
php artisan test --filter="CharacterSpotlightTest|HomepageSectionsTest|PublicNavigationTest"
```

Tests cover ordering/limits, database copy, publication visibility, cache invalidation, merged release dates, real release labels, TBA dates, filters beyond the initial six, pagination, empty states, and shared guest/user access. Browser checks cover desktop/mobile layouts, both themes, dropdown compatibility, timeline arrows and filters, and reduced-motion/no-JavaScript behavior.
