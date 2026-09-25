# Fandom pages and publishing

All eight navigation fandoms use `/explore?category=<slug>`. Their story cards,
cover images, excerpts, reading pages and related stories come from Content records.
The hero uses the first visible result's cover, with the existing fandom artwork
as a fallback. Each fandom has its own accent color and introduction.

## Add a story

1. Upload images or media in the admin Media Library.
2. Open admin Content and create a record. Select its fandom category and type.
3. Add the title, excerpt and body, then select a cover. Gallery, trailer and audio
   selections also appear on the reading page.
4. Set status to Published. Leave Published at empty to publish now, or choose a
   future date to schedule it. Draft, rejected and pending records remain private.
5. Enable Featured content for the Editor's Pick badge.

Updates appear on the public fandom and story pages on the next request. Returning
a story to Draft also removes its public detail page and related-story links.
The existing search, popularity sort and pagination remain available.

## Upcoming releases

Continue to manage releases through admin Upcoming Releases and its publication
setting. Published content with release dates also uses the existing upcoming feed.
Cards reveal once as they enter the viewport, with hover image and highlight
effects. The reveal is optional: content remains visible with JavaScript disabled,
and reduced-motion preferences disable the effects.

## Validation

`php artisan test --compact --filter='FandomPublishingTest|PublicNavigationTest|ContentTest|UpcomingReleaseTest'`

`npm run build`
