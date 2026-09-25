# Public events

`/events` lists public events; `/events/{slug}` opens a detail page and `/events/{slug}/calendar` downloads an iCalendar file. The old `/discover/events` URL redirects to `/events`. Navigation and the footer link to the new listing.

## Setup and content

Run `php artisan migrate` and `npm run build`. The additive events migration preserves all existing records, backfills unique slugs and adds event type, short description, featured selection, popularity and gallery relationships. Existing `start_at`, `end_at`, `venue`, cover media and location fields are reused. Dates are displayed in the configured application timezone (currently UTC); calendar downloads convert them to UTC explicitly.

Admin → Events → Create/Edit now includes Featured event, type, short description, popularity and gallery images. No existing events are automatically marked featured. Select up to four published upcoming events for the cinematic spotlight. If more are marked, the four with the highest popularity score, then earliest date, are selected. The configuration is in `config/events.php`.

Only published events appear publicly. Draft and cancelled detail/calendar URLs return 404. Unfiltered browsing excludes the four selected featured records from the grid across all pages. Filters search all published records, including featured events, and hide the cinematic introduction. Pagination loads 12 grid records; later pages do not repeat the spotlight. Date filtering includes events spanning the selected day. Search treats SQL wildcard characters literally.

Cover/gallery artwork comes from media records. Missing covers use matching artwork from `public/images/fandoms`, and failed image requests fall back to the same local image. There are no fabricated events, ticket statuses or galleries. Gallery content appears only when images are assigned. Related events share the category and are upcoming. External event/ticket links allow only HTTP(S). The venue opens an explicit Google Maps link; no embedded map loads third-party assets automatically.

## Motion and accessibility

The listing/detail entry point is `resources/js/modules/events-page.js`, with styles in `resources/css/pages/events.css`. Lenis runs on the GSAP ticker. The intro clears, shrinks and rises with a short scrub. Featured events use one pinned timeline on desktop: alternating X offsets plus perspective, Z, scale, blur and opacity. The previous card recedes while the next advances. Only the active card accepts focus/clicks, and a skip link leads directly to the grid. The duration is bounded to roughly 0.65 viewport heights per featured event.

Tablets use reduced depth. Mobile and short viewports use normal document flow and simple entrances. Reduced motion disables Lenis, pinning, depth and blur. Changing the preference or resizing reverts the cinematic layout, including inert states. The grid uses small viewport batches. Detail pages have simple reveals only.

Mobile filters move the same form into a native modal drawer with focus trapping, Escape, focus return and background scroll locking. Without JavaScript filters and featured cards remain visible inline and all links/pagination work. Both themes inherit the existing site canvas and palette.

## Validation

`php artisan test --filter="PublicEventsTest|EventTest|PublicNavigationTest"`

Tests cover publication boundaries, bounded featured queries, deduplication, filtered featured records, pagination, date/search filters, sorting, slug stability, fallback media, gallery management, admin editing and calendar escaping. Browser verification uses an isolated SQLite fixture database for four featured stories and a multi-page grid without changing live event data.
