# Trending, featured stories, and upcoming releases

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

`HomeController` and `HomepageService` supply data to the existing section partials in the brief's order. Guests and signed-in users share public content; the Join invitation appears only for guests.

- Trending: six published records ordered by popularity, views, and ID.
- Featured: three published, featured articles; the first is the main story. Categories, tags, author, publication date, and calculated reading time come from the records.
- Upcoming: at most six content/merchandise records merged chronologically, beginning today; unknown dates come last. Category filters query the database again, including records outside the first six. Homepage filter pills follow the reference's four main categories plus merchandise; the full listing supports every available category.
- Draft and future-publication content is excluded, including direct story URLs.
- `View All` for releases is paginated at 12 records per page. Content and merchandise cards have real public detail routes. Merchandise is a showcase only.

`config/homepage.php` centralizes the five supplied default artwork paths. They currently reside in `storage/app/public/images`, including both theme backgrounds. Only artwork is fixed; record copy is not embedded in Blade or JavaScript. These are intentionally the user's default images, rather than the distinct posters shown in the UI references.

Homepage queries cache for 60 seconds. Saving or deleting content, a category, or merchandise changes the cache version immediately. Direct SQL/bulk query updates and tag-pivot changes become visible after the cache TTL or `php artisan cache:clear`.

## Interaction and animation

The homepage alone loads `resources/js/pages/home.js` and `resources/css/pages/home.css`. GSAP/ScrollTrigger handles shared heading/card reveals, subtle desktop story parallax, and the timeline line. Lenis uses the GSAP ticker. Merchandise uses Swiper with touch/drag and arrows; it does not capture the mousewheel, autoplay, or pin the page.

Release arrows and keyboard navigation scroll a native horizontal timeline. Mobile uses a vertical timeline. Filters progressively enhance normal links with abortable fetch requests, loading/error feedback, an accessible result announcement, and browser Back/Forward support. Without JavaScript the filters reload the server-rendered page. Reduced-motion preference disables Lenis, reveal animations, and parallax.

Card reveals use each card's viewport position, so cards further down a mobile layout do not animate before they are visible. Section heading parts reveal in sequence, the featured image moves subtly with scroll on desktop, and timeline nodes appear alongside the drawing line. Switching the operating system's reduced-motion preference also cleans up active animations without requiring a reload.

If MySQL reports `Unknown column 'release_date'` on `merchandise_items`, apply the pending migrations with `php artisan migrate`. Do not reset or reseed the database. The two new columns are nullable, so existing merchandise stays intact and displays an unknown release date until one is entered.

## Merchandise and guest invitation

Merchandise reads names, categories, tags, descriptions, status and artwork from the database. Each homepage filter queries up to 24 records; View All provides a paginated collection. Filters use abortable partial requests, animated swaps, live feedback and browser history, while ordinary links work without JavaScript. Uploaded artwork falls back to the supplied `public/images/merch-deafult.jpg` when absent or when an image request fails. Tags reflect real values; `standard` upcoming items display Coming Soon.

Cards show the name, artwork, tag and heart at rest. Desktop hover/focus reveals category and the compact detail link; touch/mobile always shows both. Swiper displays 1.2 cards on mobile, 4.5 at desktop and 5 on wide screens. Reduced motion uses short fades with no scale/translation or smooth page scrolling.

Authenticated bookmarks persist per user with idempotent save/remove requests; guests receive a login dialog. Detail pages show status, release information and related merchandise. No commerce actions are provided. The Join FanHub Plus section is rendered only for guests, with sequenced reveals, four benefits and a registration link. Both new sections inherit the existing homepage background in both themes.

## Validation

```sh
php artisan test --filter="HomeMerchandiseTest|HomepageSectionsTest|PublicNavigationTest"
```

Tests cover ordering/limits, database copy, publication visibility, cache invalidation, merged release dates, real release labels, TBA dates, filters beyond the initial six, pagination, empty states, and shared guest/user access. Browser checks cover desktop/mobile layouts, both themes, dropdown compatibility, timeline arrows and filters, and reduced-motion/no-JavaScript behavior.
