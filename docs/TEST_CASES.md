# FanHubPlus — Test Case Catalogue

**What this is:** every "what happens when…" scenario for the site — the success path, the error
path, and the edge case — written as a QA checklist. Every message string is quoted **verbatim from
the source code**, so QA can compare character-for-character.

- **Stack:** Laravel 11 · PHP 8.2+ · Bootstrap 5 · Alpine.js · Vite
- **Automated test frameworks available:** PHPUnit 11 (`php artisan test`) and Node's built-in runner
  (`node --test tests/js/`)
- **Test DB:** SQLite in-memory (`phpunit.xml` sets `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`)
- **Scope:** 8 route groups, ~180 routes, 57 controllers

> ### Read this before writing any test
> `CONTEXT.md` is **out of date**. It claims Laravel 13, 18 tables, `verified` middleware on admin
> routes, and a role literally named `user`. None of that is true in the current checkout. The route
> files and controllers are the only source of truth.

---

## Table of contents

| § | Area | Prefix | Cases |
|---|------|--------|-------|
| [0](#0-conventions-and-test-data) | Conventions & test data | — | — |
| [1](#1-public-site) | Public site | `PUB` | 51 |
| [2](#2-authentication--account) | Authentication & account | `AUT` | 98 |
| [3](#3-onboarding-fandom-picker) | Onboarding fandom picker | `ONB` | 18 |
| [4](#4-member-area--my-account) | Member area (my account) | `MBR` | 72 |
| [5](#5-community-interactions) | Community interactions | `COM` | 50 |
| [6](#6-newsletter--subscribers) | Newsletter / subscribers | `NWS` | 16 |
| [7](#7-chatbot) | Chatbot | `CHT` | 13 |
| [8](#8-media-serving) | Media serving | `MED` | 10 |
| [9](#9-admin-access-control--dashboard) | Admin access control & dashboard | `ADM-ACC` | 24 |
| [10](#10-admin-content-catalog) | Admin content catalog | `ADM-CAT` | 122 |
| [11](#11-admin-moderation) | Admin moderation | `ADM-MOD` | 38 |
| [12](#12-admin-people) | Admin people | `ADM-PPL` | 44 |
| [13](#13-admin-system--tools) | Admin system & tools | `ADM-SYS` | 200+ |
| [14](#14-api-v1) | API v1 | `API` | 48 |
| [15](#15-global-error--cross-cutting-behaviour) | Global error behaviour | `GLB` | 26 |
| [16](#16-security-regression-suite) | Security regression | `SEC` | 22 |
| [17](#17-known-gaps--risk-register) | Known gaps & risk register | — | 9 |

---

## 0. Conventions and test data

### 0.1 How each case is written

| Column | Meaning |
|---|---|
| **ID** | Stable identifier. Cite it in bug reports. |
| **Precondition** | Data and state that must exist first. |
| **Given → When** | The navigation and the single action taken. |
| **Then** | The observable result: HTTP status, redirect target, DB change, side effect. |
| **Message shown** | The **exact** string a user reads, or the exact JSON / CSV / header output. |

### 0.2 Message channels — the single most important table in this document

The app uses several flash channels. Confusing them is the #1 source of false test failures.

| Channel | Rendered where | Style | Auto-dismiss | Used by |
|---|---|---|---|---|
| `session('success')` | 13 Blade sites | `alert alert-success`, or custom `.contact-alert--success` / `.member-notice` on public + member pages | **5000 ms** (Alpine) | almost every write action |
| `session('error')` | 6 Blade sites | `alert alert-danger`, or custom `.contact-alert--error` / `.feedback-alert--error` | **5000 ms** | guard clauses only |
| `session('status')` | 6 Blade sites | `alert alert-success` / `alert alert-info` | varies | auth flows only — carries a **raw token**, not prose |
| `session('onboarding-success')` | `layouts/public.blade.php` only | `.onboarding-toast` | never | onboarding only |
| `$errors` (default bag) | every form + `<x-flash-message>` | `alert alert-danger` | **never** | `validate()` / FormRequests |
| `$errors` (named bag) | the specific view only | per-field `.invalid-feedback` | n/a | `subscribe`, `updatePassword`, `userDeletion` |

**Six facts QA must know:**

1. **Only `success` and `error` are ever flashed by a controller.** `warning` and `info` are declared
   in `resources/views/components/flash-message.blade.php` but **no controller in the entire app ever
   sets them**, and that component is **never rendered** — zero Blade files include
   `<x-flash-message />`. Every layout hand-rolls its own flash block. There is no `warning`/`info`
   behaviour to test.
2. **Two messages read like errors but are not.** `routes/web.php:37` flashes `error`, and
   `Admin\SubmissionController@reject` flashes **`success`** with the text `Submission rejected.`
   Assert on the **key** as well as the text.
3. **`session('status')` is a token, not a sentence.** It holds `password-updated` and
   `verification-link-sent`. The **admin layout renders it raw** (`layouts/app.blade.php:46`) — an
   admin who changes their password sees the literal string `password-updated` in the alert. The user
   layout translates it to `Password updated.` This is a live bug — see [RISK-01](#17-known-gaps--risk-register).
4. **Success alerts vanish after 5 seconds.** Assert with `assertSessionHas('success', …)` in
   automated tests, not on visible DOM after a delay.
5. **Named error bags are invisible to the global error block.** `<x-flash-message>` only reads
   `$errors->all()` (the default bag). A `subscribe` bag error therefore appears **only** in the
   field, never as a page-level alert. The same applies to `updatePassword` and `userDeletion`.
6. **Most guard failures are bare `abort()` with no message.** A large number of 403s and 404s render
   Laravel's default error page with no explanation. Do not assert a friendly string where the
   controller does not supply one.

### 0.3 Test data — seeded accounts

`php artisan db:seed`. Every account uses password **`password`** and is already email-verified.

| # | Email | Role slug | Notes |
|---|---|---|---|
| 1 | `admin@example.com` | `admin` | full admin panel access |
| 2 | `user@example.com` | `registered-user` | main member-area test account |
| 3 | `alex.rivera@example.com` | `editor` | no admin access; the "has a role but not the right one" case |
| 4 | `samantha.chen@example.com` | `moderator` | no admin access |
| 5 | `marcus.vance@example.com` | `vip-member` | no admin access |
| 6 | `elena.rostova@example.com` | `contributor` | |
| 7 | `daisuke.sato@example.com` | `creator` | |
| 8 | `chloe.bennett@example.com` | `reviewer` | |
| 9 | `liam.oconnor@example.com` | `subscriber` | |
| 10 | `zahra.ahmed@example.com` | `registered-user` | second member, for cross-user isolation tests |

**Roles:** `admin`, `registered-user`, `editor`, `moderator`, `vip-member`, `contributor`, `creator`,
`reviewer`, `subscriber`, `guest`. The `permissions` column exists but the seeder leaves it `null` for
all ten, so `hasPermission()` cannot grant anything to a non-admin.

**Categories (ids 1–10, all `icon_media_id = null`):** Anime `anime`, Gaming `gaming`, Movies `movies`,
TV Shows `tv-shows`, K-Pop `k-pop`, Comics `comics`, Manga `manga`, Cosplay `cosplay`, Music `music`,
Esports `esports`.

**Other seeded data:** 10 tags · 10 media · 21 contents (10 upcoming, 7 trending/featured, 4 user
submissions) · 10 characters · 10 merchandise · 7 events · 10 reviews (all approved) · 10 feedback ·
10 contacts · 10 subscribers · 10 chatbot FAQs · 10 chatbot queries · 23 settings.

**Two seeding gotchas:**

- All user profiles have `onboarding_completed_at = now()`, so **onboarding never triggers against a
  freshly seeded database**. Null that column to test §3.
- `maintenance_mode` is **not** seeded. `Setting::get('maintenance_mode', false)` defaults to off. You
  must insert the setting yourself to test maintenance behaviour.

**Fandom config keys** (`config/fandoms.php`), used for filters and the 404 checks in PUB-13/15/16:
`anime`, `gaming`, `movies`, `tv-shows`, `k-pop`, `comics`, `manga`, `cosplay`.

**Event config** (`config/events.php`): `types` keys = `convention`, `meetup`, `screening`,
`premiere`, `gaming`, `festival`, `cosplay`, `community`. `nearby_radii` = `[5, 10, 25, 50]`.

### 0.4 Throttle limits — a hard test boundary

| Route | Limit | After exceeding |
|---|---|---|
| `POST /login` | **5 attempts** per `email + IP` | `Too many login attempts. Please try again in :seconds seconds (:minutes min).` |
| `GET /verify-email/{id}/{hash}` | 6 / min | 429 |
| `POST /email/verification-notification` | 6 / min | 429 |
| `POST /onboarding` | 10 / min | 429 |
| `POST /chatbot/message` | 12 / min | 429 |
| `GET /chatbot/faqs` | 60 / min | 429 |
| `POST /collection/{slug}/bookmark` | 60 / min | 429 |
| `POST /subscribe` | 60 / min | 429 |
| all `routes/member.php` **writes** | 30 / min | 429 |
| `POST /contact` | **none** | — |
| `POST /api/v1/auth/login` | **none** | — (the API has no rate limiting at all) |

⚠️ When writing automated tests, clear the limiter between cases (`RateLimiter::clear()`), or throttle
tests will poison every later test in the same class.

### 0.5 Validation messages you will see over and over

Six controllers share the same password policy messages (`Auth\RegisteredUserController`,
`Auth\NewPasswordController`, and — via the same `Rules\Password::defaults()` — every other password
field in the app):

| Trigger | Message |
|---|---|
| `password.confirmed` | `Password confirmation does not match your password.` |
| `password.min` | `Password must be at least :min characters.` |
| `password.mixed` | `Password must contain both uppercase and lowercase letters.` |
| `password.numbers` | `Password must contain at least one number.` |
| `password.symbols` | `Password must contain at least one symbol.` |
| `password.required` | `Please enter a password.` |

And two shared email messages (registration, forgot-password, reset-password, login):

| Trigger | Message |
|---|---|
| empty email | `Please enter your email address.` |
| malformed email | `Please enter a valid email address.` |

---

## 1. Public site

Guest-visible unless stated otherwise. All return **200** and render `layouts/public.blade.php`
with the public navbar and footer.

### 1.1 Home — `GET /` (`home`)

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| PUB-01 | Empty DB | Visit `/` | 200. Hero, features, newsletter form, footer render. Homepage section cache is built. | No flash |
| PUB-02 | 7 published featured contents | Visit `/` | 200. Featured rail shows exactly the 7 DB records, ranked, no duplicates. | No flash |
| PUB-03 | Featured cover media uploaded | Visit `/` | Covers resolve to the uploaded file URL, not a placeholder. | No flash |
| PUB-04 | Featured content with no cover | Visit `/` | Local fallback image renders. No broken image. | No flash |
| PUB-05 | 5 published + active events | Visit `/` | Events section shows 5. A `draft` or `cancelled` event is excluded. | No flash |
| PUB-06 | Page cached, then an event is edited | Visit `/` again | Homepage cache invalidated — new data appears. | No flash |
| PUB-07 | 6 upcoming + 3 merchandise | Visit `/` | Upcoming feed merges releases and merchandise by date. | No flash |
| PUB-08 | 6 upcoming records | Click "View all" | Paginated `/discover/upcoming`; the 7th record is reachable. | No flash |
| PUB-09 | Guest vs signed-in `registered-user` | Visit `/` as each | **Identical sections.** No personalisation leaks to guests. | No flash |
| PUB-10 | Completely empty DB | Visit `/` | 200. Each section renders a readable empty state — no blank rails, no fabricated cards. | No flash |
| PUB-11 | — | Visit `/?release_category=all` | 200, unfiltered | No flash |
| PUB-12 | — | Visit `/?release_category=anime` | 200, filtered to that fandom | No flash |
| PUB-13 | — | Visit `/?release_category=bogus` | **404** (`abort_unless(in_array(...))`) | Laravel 404 page |
| PUB-14 | — | Visit `/?merch_category=all` and `?merch_category=cosplay` | 200, filtered | No flash |
| PUB-15 | — | Visit `/?merch_category=anime` (a valid fandom, not a merch key) | **404** | Laravel 404 page |
| PUB-16 | — | Visit `/?merch_category=nope` | **404** | Laravel 404 page |
| PUB-17 | — | Visit `/?release_category[]=a` (array instead of string) | **404** — the `is_string()` guard rejects it | Laravel 404 page |
| PUB-18 | Guest | Click a featured story | 302 → `/login` with the destination preserved in `intended` | Silent |
| PUB-19 | Single record in a section | Visit `/` | The section renders one card — no "1 of 1" nonsense, no duplicate padding | No flash |

### 1.2 Navigation & explore — `GET /explore`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| PUB-20 | 10 seeded categories | Guest visits `/` | Public navbar + all fandom entries render. No account menu. | — |
| PUB-21 | Signed in | Visit `/` | Account menu replaces the login/register links. | — |
| PUB-22 | Guest | Click a private nav link | 302 → `/login`; the original URL is in `intended` | Silent |
| PUB-23 | — | `/explore?q=anime` | 200. Only Anime matches. | — |
| PUB-24 | Draft + future-scheduled contents exist | `/explore?q=<term>` | Neither appears. | — |
| PUB-25 | — | `/explore?sort=latest` / `popular` / `alphabetical` | 200, ordering changes | — |
| PUB-26 | — | `/explore?sort=sideways` | **422** | `The selected sort is invalid.` |
| PUB-27 | — | `/explore?type=video` / `article` / `audio` / `image` | 200, filtered | — |
| PUB-28 | — | `/explore?type=podcast` | **422** | `The selected type is invalid.` |
| PUB-29 | — | `/explore?year=1999` | 200, filtered to that release year | — |
| PUB-30 | — | `/explore?year=1800` or `year=2300` | **422** | `The year must be between 1900 and 2200.` |
| PUB-31 | — | `/explore?tag=3` (valid tag id) | 200, filtered | — |
| PUB-32 | — | `/explore?tag=99999` | **422** | `The selected tag is invalid.` |
| PUB-33 | — | `/explore?featured=1` | 200, featured only | — |
| PUB-34 | — | `/explore?q=<121-char string>` | **422** | `The q field must not be greater than 120 characters.` |
| PUB-35 | — | `/explore?category=<121-char string>` | **422** | `The category field must not be greater than 100 characters.` |
| PUB-36 | Empty catalogue | `/explore` | 200 with a readable empty state, not a blank grid | — |
| PUB-37 | Many results | Page to 2 | Filters survive pagination; no duplicates, no drops | — |

### 1.3 Fandom pages — `GET /fandom/{category:slug}`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| PUB-38 | Anime category + 2 published contents | Visit `/fandom/anime` | 200. Page has its own title, description, and visual identity — distinct from every other fandom. | — |
| PUB-39 | — | `/fandom/anime?q=<term>` | 200. Search scoped to Anime only. | — |
| PUB-40 | — | `/fandom/anime?sort=latest` / `popular` / `trending` / `alphabetical` | 200, ordering changes | — |
| PUB-41 | — | `/fandom/anime?sort=sideways` | **422** | `The selected sort is invalid.` |
| PUB-42 | — | `/fandom/anime?year=1800` | **422** | `The year must be between 1900 and 2200.` |
| PUB-43 | Contents also exist in Gaming | View Anime "Related stories" | **Only Anime** contents appear. No cross-fandom bleed, no drafts, no future-scheduled. | — |
| PUB-44 | Admin publishes with a **future** `published_at` | Visit the fandom | Content stays hidden. Future `published_at` ⇒ still scheduled. | — |
| PUB-45 | Admin publishes with `published_at = now()` | Visit the fandom | Content appears immediately. The local publish timestamp is shown everywhere. | — |
| PUB-46 | Admin unpublishes | Reload | Content disappears | — |
| PUB-47 | Category with 0 contents | Visit its fandom page | 200 with an explicit empty state | — |
| PUB-48 | — | `/fandom/does-not-exist` | **404** | Laravel 404 page |
| PUB-49 | All 8 fandoms | Visit each fandom page | Each is reachable and visually distinct — no shared/duplicated template | — |
| PUB-50 | — | `/fandom/anime?tag=99999` | **422** | `The selected tag is invalid.` |

### 1.4 Story detail — `GET /stories/{content:slug}`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| PUB-51 | Published article with rich HTML | Visit the story | 200. **Unsafe HTML/JS is stripped** — no `<script>`, no `onerror=`, no `javascript:` hrefs. | — |
| PUB-52 | Guest visits | Visit the story | **No `viewed` activity row is written** — there is no user to attach it to. | — |
| PUB-53 | Signed-in member visits | Visit the story | `viewed` activity recorded, de-duplicated within a 30-minute window | — |
| PUB-54 | Draft content | Visit `/stories/{draft-slug}` | **404** | Laravel 404 page |
| PUB-55 | `published_at` in the future | Visit the story | **404** | Laravel 404 page |
| PUB-56 | Content whose category the member favourited | Member views detail | Related/feed rails rank that category's content first | — |
| PUB-57 | — | `/stories/nope` | **404** | Laravel 404 page |

### 1.5 Character pages — `GET /characters/{character:slug}`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| PUB-58 | 7 characters in the spotlight | Visit the spotlight | 200. Exactly 7 DB cards, ranked. | — |
| PUB-59 | Edit a character's name/bio | Reload the spotlight | The change appears (cache refreshed). | — |
| PUB-60 | Character with a DB image | View detail | The DB image wins over the fallback | — |
| PUB-61 | Character with a missing or non-image media id | View detail | Fallback renders. No broken image. | — |
| PUB-62 | Character linked to a draft story | View detail | The draft is **excluded** from related stories | — |
| PUB-63 | 0 characters | Visit the spotlight | Empty state, **no fabricated cards** | — |
| PUB-64 | — | `/characters/nope` | **404** | Laravel 404 page |
| PUB-65 | Member favourited the character's category | Visit the spotlight | Favourite categories rank first | — |

### 1.6 Merchandise — `GET /collection/{merchandise:slug}`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| PUB-66 | 10 items, all 4 tags used | Visit the merchandise section | Category + tag + search filters combine correctly | — |
| PUB-67 | — | `?tag=limited_edition` | 200, filtered | — |
| PUB-68 | — | `?tag=banana` | **422** | `The selected tag is invalid.` |
| PUB-69 | — | `?status=released` / `upcoming` | 200, filtered | — |
| PUB-70 | — | `?status=maybe` | **422** | `The selected status is invalid.` |
| PUB-71 | — | `?sort=newest` / `popular` / `name` | 200, ordering changes | — |
| PUB-72 | — | `?sort=sideways` | **422** | `The selected sort is invalid.` |
| PUB-73 | Sorting + filtering + paging | Combine all three | Filters and eager loads survive pagination — no duplicate or dropped rows | — |
| PUB-74 | Item linked to a story | Open the item detail | The related rail prioritises the **same story** and **excludes the item itself** | — |
| PUB-75 | Item linked to a **private** story | Open the item detail | The private story is **hidden** | — |
| PUB-76 | Item is `is_upcoming` | Open the item detail | Status reads as upcoming. **No buy / cart / price action anywhere on the page.** | — |
| PUB-77 | 0 items | Visit the section | Readable empty state | — |
| PUB-78 | — | `/collection/nope` | **404** | Laravel 404 page |
| PUB-79 | Item in a deleted category | Open the item | No error, no crash; the category link is absent | — |

### 1.7 Discover, events index, sitemap, static pages

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| PUB-80 | — | `/discover/characters` | 200, spotlight + multimedia | — |
| PUB-81 | — | `/discover/multimedia` | 200, **public** content only — no drafts leak | — |
| PUB-82 | — | `/discover/events` | **301 → `/events`** | — |
| PUB-83 | — | `/discover/upcoming` | 200, merged release + merchandise feed | — |
| PUB-84 | — | `/discover/upcoming?category=bogus` | **404** | Laravel 404 page |
| PUB-85 | — | `/discover/feedback` | 200 | — |
| PUB-86 | — | `/discover/privacy` / `terms` | 200, static content | — |
| PUB-87 | — | `/discover/nonsense` | **404** (`isset(self::SECTIONS[$section])` guard) | Laravel 404 page |
| PUB-88 | 7 published events | `/events` | 200. Featured bounded + grid paginated with no duplicates | — |
| PUB-89 | — | `/events?q=<term>` | 200. Search covers **featured too**, not just page 1 of the grid | — |
| PUB-90 | — | `/events?sort=popular` / `soonest` / `latest` | 200, ordering changes | — |
| PUB-91 | — | `/events?when=upcoming` / `past` / `all` | 200, filtered | — |
| PUB-92 | — | `/events?date=2026-12-25` | 200, filtered | — |
| PUB-93 | — | `/events?date=25-12-2026` | **422** | `The date field must match the format Y-m-d.` |
| PUB-94 | — | `/events?city=<term>` | 200, filtered | — |
| PUB-95 | — | `/events?type=festival` (and each of the 8) | 200, filtered | — |
| PUB-96 | — | `/events?type=spaceflight` | **422** | `The selected type is invalid.` |
| PUB-97 | Draft + cancelled events exist | `/events` | Neither leaks — not in the grid, not in featured, not in the count | — |
| PUB-98 | Published event | `/events/{slug}` | 200. Metadata, gallery, **safe link rendering**, and a fallback all render | — |
| PUB-99 | `ticket_url = javascript:alert(1)` | View the detail page | The link is **not** rendered as a clickable href | — |
| PUB-100 | Event with no cover media | View the detail page | Fallback image renders. No broken image. | — |
| PUB-101 | Draft event | `/events/{draft-slug}` | **404** | Laravel 404 page |
| PUB-102 | Cancelled event | `/events/{cancelled-slug}` | **404** | Laravel 404 page |
| PUB-103 | — | `/events/nope` | **404** | Laravel 404 page |
| PUB-104 | — | `/sitemap` | 200, XML sitemap | — |
| PUB-105 | — | `/about` / `/services` / `/pricing` / `/contact` | 200, each with navbar + footer | — |

### 1.8 Nearby events

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| PUB-106 | No coords supplied | `GET /events/nearby` | 200. Default radius, no distance shown, and the results are the same as `/events`. | — |
| PUB-107 | `latitude` only | `GET /events/nearby?latitude=35.6` | **422** | `The longitude field is required when latitude is present.` |
| PUB-108 | `longitude` only | `GET /events/nearby?longitude=139.7` | **422** | `The latitude field is required when longitude is present.` |
| PUB-109 | — | `?latitude=95&longitude=139.7` | **422** | `The latitude field must be between -90 and 90.` |
| PUB-110 | — | `?latitude=35.6&longitude=200` | **422** | `The longitude field must be between -180 and 180.` |
| PUB-111 | — | `?radius=7` (not in `[5,10,25,50]`) | **422** | `The selected radius is invalid.` |
| PUB-112 | `radius=10` | `?latitude=35.6&longitude=139.7&radius=10` | 200. Only events within 10 km. | — |
| PUB-113 | Valid coords | `POST /events/nearby` | **200 JSON** with `Cache-Control: no-store, private` | `{"html":"…","total":3,"nearest_distance_km":4.2}` — **no `message` key** |
| PUB-114 | Nearest match just outside the radius | `POST` with a small radius | 200. The result is **not** expanded — but `nearest_distance_km` still reports the true distance, and the UI says so. | `nearest_distance_km` present |
| PUB-115 | 0 matches | `POST` | 200, `total = 0`, `nearest_distance_km = null`, HTML shows an empty state | `{"html":"…","total":0,"nearest_distance_km":null}` |
| PUB-116 | `sort=popular` | `POST …&sort=popular` | Sorted by `popularity_score` | — |
| PUB-117 | `sort=soonest` | `POST …&sort=soonest` | Sorted by `start_at` | — |
| PUB-118 | Filters + page 2 | `POST …&page=2&q=<term>` | Filters and pagination both preserved | — |
| PUB-119 | Event at the **date line** (lon 179.9 vs -179.9) | `POST` | Distance computed **across** the date line, not 359.8° | — |
| PUB-120 | Event at the **poles** (lat 89.9) | `POST` | Distance finite, no NaN, no divide-by-zero | — |
| PUB-121 | A configured `google_maps_location` | View the result card | The map link renders safely (`http`/`https` only) | — |
| PUB-122 | Draft / cancelled / past events | `POST` | **Never** returned, and never counted in `total` | — |
| PUB-123 | — | `?page=0` | **422** | `The page field must be at least 1.` |
| PUB-124 | Client-side | Open the page, click "use my location" | **The browser location prompt is opt-in** — nothing is requested until the button is clicked | — |
| PUB-125 | Client-side | Set radius to the max | The control disables further increases | — |
| PUB-126 | Client-side | Apply filters, then hit "clear" | All filters reset and the unfiltered list returns | — |

### 1.9 Public contact form — `POST /contact`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| PUB-125 | Guest | Submit name + email + message | 302 back. `contacts` row created, an in-app **and** email notification is dispatched **synchronously**. | `Thank you for your message! We will get back to you soon.` |
| PUB-126 | — | Submit with no name | 422, error under `name` | `The name field is required.` |
| PUB-127 | — | `name` 256 chars | 422 | `The name field must not be greater than 255 characters.` |
| PUB-128 | — | Submit with no email | 422 | `The email field is required.` |
| PUB-129 | — | Malformed email | 422 | `The email field must be a valid email address.` |
| PUB-130 | — | `subject` 256 chars | 422 | `The subject field must not be greater than 255 characters.` |
| PUB-131 | — | Submit with no message | 422 | `The message field is required.` |
| PUB-132 | — | `message` 5001 chars | 422 | `The message field must not be greater than 5000 characters.` |
| PUB-133 | **Omit `subject`** | Submit | 302, row created. The notification email's subject falls back to the literal `No Subject`. | `Thank you for your message! We will get back to you soon.` |
| PUB-134 | Queue worker stopped | Submit | **Still succeeds.** Contact notifications are intentionally **not queued**, so in-app delivery never depends on a worker. | `Thank you for your message! We will get back to you soon.` |
| PUB-135 | Success | Observe the alert | Rendered in `.contact-alert--success` with a check icon and `role="alert"` | — |
| PUB-136 | Field error | Observe the alert | Rendered in `.contact-alert--error` with a close icon; each field error is a `<p role="alert">` with `id="{field}-error"` | — |
| PUB-137 | — | Submit 100 times in a minute | **All succeed** — this endpoint has no throttle | `Thank you for your message! …` each time |

### 1.10 Merchandise bookmark (public-facing, auth-gated)

`POST /collection/{merchandise:slug}/bookmark` — `auth` + `throttle:60,1`. Dual transport: the same
string is delivered as JSON `message` **or** as a `success` flash.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| PUB-138 | Member | `saved=1` | 302 back, bookmark row created, `saved` activity logged | `Saved to bookmarks` (no full stop) |
| PUB-139 | Already bookmarked | `saved=1` | **Idempotent** | `Saved to bookmarks` |
| PUB-140 | Bookmarked | `saved=0` | 302, row removed | `Removed from bookmarks` (no full stop) |
| PUB-141 | Member + `Accept: application/json` | `saved=1` | **200 JSON** | `{"saved":true,"message":"Saved to bookmarks"}` |
| PUB-142 | Member + `Accept: application/json` | `saved=0` | **200 JSON** | `{"saved":false,"message":"Removed from bookmarks"}` |
| PUB-143 | — | Omit `saved` | 422 | `The saved field is required.` |
| PUB-144 | — | `saved=maybe` | 422 | `The saved field must be true or false.` |
| PUB-145 | Guest | POST | 302 → `/login` | — |
| PUB-146 | Member A bookmarks | Member B posts the same item | B gets their **own** bookmark; A's is untouched | `Saved to bookmarks` |
| PUB-147 | Client-side | Click the bookmark toggle | The result message is announced in a `[data-bookmark-status]` live region (`aria-live="polite"`) so screen readers hear it | — |
| PUB-148 | 60 bookmarks in a minute | 61st | **429** | Laravel 429 page |
| PUB-149 | Unpublished merchandise | Bookmark it | No public card is rendered from it, and nothing leaks | — |

### 1.11 `/account/{section}` (legacy route)

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| PUB-150 | Guest | `GET /account/bookmarks` | 302 → `/login` | — |
| PUB-151 | Member | `GET /account/bookmarks` | **302 → `/user/bookmarks`.** This route always redirects; it has no views of its own. | — |
| PUB-152 | Member | `GET /account/nonsense` | 302 → the member 404 page | — |

---

## 2. Authentication & account

Breeze-style flows. See `routes/auth.php` and the `Auth/` controllers.

### 2.1 Registration — `GET|POST /register`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| AUT-01 | Guest | Visit `/register` | 200, form renders | — |
| AUT-02 | Guest | Submit a valid name / email / password + confirmation | **302 → `/onboarding`** — **silently, no flash.** User created, role `registered-user` assigned, `Registered` event fired, session authenticated. | No flash |
| AUT-03 | — | Submit an **empty** name | 422 | `Please enter your name.` |
| AUT-04 | — | Name of 256 chars | 422 | `Name may not be longer than 255 characters.` |
| AUT-05 | — | Submit an **empty** email | 422 | `Please enter your email address.` |
| AUT-06 | — | Email with **uppercase** letters | 422 | `Please enter your email in lowercase letters.` |
| AUT-07 | — | Email `not-an-email` | 422 | `Please enter a valid email address.` |
| AUT-08 | Email already registered | Submit a duplicate email | 422 | `An account with this email already exists. Try logging in instead.` |
| AUT-09 | — | Submit an **empty** password | 422 | `Please enter a password.` |
| AUT-10 | — | Password ≠ confirmation | 422 | `Password confirmation does not match your password.` |
| AUT-11 | — | Password of 7 chars | 422 | `Password must be at least 8 characters.` |
| AUT-12 | — | Password with no mixed case | 422 | `Password must contain both uppercase and lowercase letters.` |
| AUT-13 | — | Password with no number | 422 | `Password must contain at least one number.` |
| AUT-14 | — | Password with no symbol | 422 | `Password must contain at least one symbol.` |
| AUT-15 | Password of exactly 8 chars, mixed, numeric, symbolised | Submit | **Succeeds** — the boundary is inclusive | 302 → `/onboarding` |
| AUT-16 | Already signed in | Visit `/register` | **302 → dashboard** (`guest` middleware) | — |
| AUT-17 | DB has 0 categories | Register | Redirects to `/dashboard`; onboarding is **skipped, not blocked** | — |
| AUT-18 | Validation fails | Observe the page | Errors render through `<x-input-error>` as `invalid-feedback d-block`, one div per message, `id="{field}-error"`, `role="alert"` | — |
| AUT-19 | Validation fails | Observe the input | The submitted value is repopulated — except the password, which is cleared | — |

### 2.2 Login — `GET|POST /login`

No route-level throttle; throttling lives inside `LoginRequest` at **5 attempts** keyed on
`Str::transliterate(Str::lower(email)).'|'.ip()`.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| AUT-20 | Guest | Visit `/login` | 200, form renders | — |
| AUT-21 | `user@example.com` exists | Submit the correct email + password | **302 → `intended` (`/dashboard`)**. Rate-limiter counter cleared, `login` activity log written. | **No flash — success is silent** |
| AUT-22 | — | Submit a **wrong** password | 422, stays on `/login`. Note the error is attached to the **email** field, not the password field. | `Invalid email or password. Please check your credentials and try again.` |
| AUT-23 | — | Unknown email, any password | 422 — same message as a wrong password (no account enumeration) | `Invalid email or password. Please check your credentials and try again.` |
| AUT-24 | — | Email field empty | 422 | `Please enter your email address.` |
| AUT-25 | — | Email malformed | 422 | `Please enter a valid email address.` |
| AUT-26 | — | Password field empty | 422 | `Please enter the password.` → verbatim: `Please enter your password.` |
| AUT-27 | 4 previous failures | Submit a 5th bad attempt | 422, still no lockout | `Invalid email or password. …` |
| AUT-28 | 5 previous failures | Submit a 6th attempt | **429, locked out** | `Too many login attempts. Please try again in :seconds seconds (:minutes min).` |
| AUT-29 | Locked out, **correct** password | Submit correct credentials | **Still 429** — the limiter is checked *before* authentication, so a valid login is also blocked | `Too many login attempts. …` |
| AUT-30 | Locked out, window elapsed | Submit again | 200, login succeeds, counter cleared | — |
| AUT-31 | Lockout on `a@b.com` | Submit credentials for `c@d.com` from the **same IP** | **Succeeds** — the key is `email + IP`, not IP alone | — |
| AUT-32 | Lockout on `a@b.com` from IP 1 | Submit `a@b.com` from IP 2 | **Succeeds** — the key includes the IP | — |
| AUT-33 | `email` with mixed case and unicode | Submit the same account | Still counts as the same throttle key (lower-cased + transliterated) | — |
| AUT-34 | — | Submit without `remember` | 302, session cookie only (not persistent) | — |
| AUT-35 | — | Submit with `remember` | 302, persistent remember-me cookie set | — |
| AUT-36 | Already signed in | Visit `/login` | **302 → dashboard** | — |
| AUT-37 | Submitted form | Inspect the response | The submitted email is repopulated; the password is cleared | — |
| AUT-38 | Was redirected from a private URL | Log in successfully | Lands on the **originally intended** URL, not `/dashboard` | — |

### 2.3 Logout — `POST /logout`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| AUT-39 | Signed in | `POST /logout` | **302 → `/`**, session invalidated, token rotated, all remember-me cookies cleared | **No flash.** There is no "you have been logged out" message anywhere in the app. |
| AUT-40 | Guest | `POST /logout` | 302 → `/login` (`auth` middleware) | — |
| AUT-41 | Then revisit `/profile` | — | 302 → `/login` — the session really is gone | — |

### 2.4 Email verification

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| AUT-42 | Unverified user | Visit `/verify-email` | 200, notice page with a resend button | — |
| AUT-43 | Unverified user | `POST /email/verification-notification` | 302 back, `status` flashed | `status = 'verification-link-sent'`, rendered as **`A new verification link has been sent to your email.`** (verify page) · `…to your email address.` (profile) · `…to your email.` (member layout) — **three different strings for one flash** |
| AUT-44 | Already verified | `POST /email/verification-notification` | 302 to the intended dashboard, **no flash at all** | — |
| AUT-45 | Unverified user | Click the emailed signed link | 302 → `/dashboard?verified=1`. `Verified` event fired. | The `?verified=1` query param drives the UI banner |
| AUT-46 | Already verified | Click the link again | 302 → `/dashboard?verified=1` — **idempotent** | — |
| AUT-47 | — | Tamper with the `hash` segment | **403** | Framework email-verification exception page |
| AUT-48 | — | Drop the `id` and `hash` segments entirely | **403** | Framework email-verification exception page |
| AUT-49 | — | Use a valid link for **another user's** id + hash pair | **403** | Framework email-verification exception page |
| AUT-50 | 6 clicks in a minute | 7th click on `verify-email/{id}/{hash}` | **429** | Laravel 429 page |
| AUT-51 | 6 resends in a minute | 7th resend | **429** | Laravel 429 page |
| AUT-52 | Verified user | Visit `/verify-email` | 302 → dashboard | — |
| AUT-53 | Guest | Visit `/verify-email` | 302 → `/login` | — |
| AUT-54 | Unverified user | Visit `/admin` | **200 — verification is not enforced anywhere.** See [RISK-02](#17-known-gaps--risk-register). | — |

### 2.5 Password reset

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| AUT-55 | Guest | Visit `/forgot-password` | 200, form renders | — |
| AUT-56 | Real email | Submit it | 302, `status` flashed, reset email sent | `We have emailed your password reset link. Please check your inbox.` |
| AUT-57 | — | Submit an **empty** email | 422 | `Please enter your email address.` |
| AUT-58 | — | Submit a malformed email | 422 | `Please enter a valid email address.` |
| AUT-59 | Unknown email | Submit `nobody@example.com` | 302 back with an error on `email`. ⚠️ **this message confirms the account does not exist** — a deliberate trade-off, but flag it in a security review. | `We can't find an account with that email address.` |
| AUT-60 | Requested twice inside the throttle window | Request again | 302 back with an error on `email` | `Please wait a moment before requesting another reset link.` |
| AUT-61 | Valid token | Open `/reset-password/{token}` | 200, reset form with the email pre-filled | — |
| AUT-62 | Valid token | Submit a new password + confirmation | **302 → `/login`**, `status` flashed, all other sessions invalidated | `Your password has been reset. You can now sign in with your new password.` |
| AUT-63 | Tampered or expired token | Submit a new password | 302 back, error on `email`, input preserved | `This password reset link is invalid or has expired. Please request a new one.` |
| AUT-64 | Valid token, unknown email | Submit | 302 back, error on `email` | `We can't find an account with that email address.` |
| AUT-65 | — | New password of 7 chars | 422 | `Password must be at least 8 characters.` |
| AUT-66 | — | Confirmation mismatch | 422 | `Password confirmation does not match your password.` |
| AUT-67 | Password already reset | Try the **old** link again | 302 back, error on `email` | `This password reset link is invalid or has expired. Please request a new one.` |
| AUT-68 | Password already reset | Log in with the **old** password | 422 | `Invalid email or password. Please check your credentials and try again.` |
| AUT-69 | Password already reset | Log in with the **new** password | 302 → dashboard | — |
| AUT-70 | Signed in | Visit `/forgot-password` | 302 → dashboard (`guest`) | — |
| AUT-71 | — | Request a reset while `MAIL_MAILER=log` | 302, `status` flashed, link written to the log | `We have emailed your password reset link. Please check your inbox.` |

### 2.6 Password change & confirmation

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| AUT-72 | Signed in | `PUT /password` with the correct current password + new + confirmation | 302 back, `status` flashed | `status = 'password-updated'` → **user layout** shows `Password updated.` · **profile form** shows `Saved.` · **admin layout shows the raw literal `password-updated`** ⚠️ [RISK-01](#17-known-gaps--risk-register) |
| AUT-73 | — | Wrong `current_password` | 422, bag `updatePassword` | `The password is incorrect.` |
| AUT-74 | — | Empty `current_password` | 422, bag `updatePassword` | `The current password field is required.` |
| AUT-75 | — | New password fails the policy | 422, bag `updatePassword` | e.g. `Password must be at least 8 characters.` |
| AUT-76 | — | Confirmation mismatch | 422, bag `updatePassword` | `The password field confirmation does not match.` |
| AUT-77 | Signed in | `GET /confirm-password` | 200, confirmation form | — |
| AUT-78 | — | `POST /confirm-password` with the correct password | 302 → `intended`; `auth.password_confirmed_at` set | No flash |
| AUT-79 | — | `POST /confirm-password` with a wrong password | 302 back, error on `password` | `The provided password is incorrect.` |
| AUT-80 | — | `POST /confirm-password` with an empty password | 302 back, error on `password` | `The provided password is incorrect.` |
| AUT-81 | Just changed the password | `POST /confirm-password` | Confirmation is **not** required again | — |
| AUT-82 | Guest | `PUT /password` | 302 → `/login` | — |

### 2.7 Profile — `GET|PATCH|DELETE /profile`

`ProfileUpdateRequest` branches on role: **admins** get only name/email/avatar/remove_avatar;
**registered users** additionally get display name, bio, map URL, media-picker avatar, and favourites.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| AUT-83 | Signed in | `GET /profile` | 200, form renders | — |
| AUT-84 | — | `PATCH /profile` with a new name | 302 → `/profile` | `Your profile and preferences have been saved.` |
| AUT-85 | — | Submit an **unchanged** email | Verified status is **preserved** — `email_verified_at` is not cleared | — |
| AUT-86 | — | Change the email | `email_verified_at` cleared; the user must re-verify | — |
| AUT-87 | Another user owns that email | Submit it | 422 | `The email has already been taken.` |
| AUT-88 | — | Name empty | 422 | `The name field is required.` |
| AUT-89 | — | Name of 256 chars | 422 | `The name field must not be greater than 255 characters.` |
| AUT-90 | — | Email malformed | 422 | `The email field must be a valid email address.` |
| AUT-91 | — | Email with uppercase letters | 422 | `The email field must be lowercase.` |
| AUT-92 | Member (not admin) | Submit `display_name`, `bio`, `google_maps_location` | All three **saved** | `Your profile and preferences have been saved.` |
| AUT-93 | Member | `display_name` of 101 chars | 422 | `The display name field must not be greater than 100 characters.` |
| AUT-94 | Member | `bio` of 2001 chars | 422 | `The bio field must not be greater than 2000 characters.` |
| AUT-95 | Member | `google_maps_location = not-a-url` | 422 | `The google maps location field must be a valid URL.` |
| AUT-96 | Member | `google_maps_location` = 501-char URL | 422 | `The google maps location field must not be greater than 500 characters.` |
| AUT-97 | Member | Upload a valid avatar (jpg/png/webp ≤ 2 MB) | 302, avatar stored and linked | `Your profile and preferences have been saved.` |
| AUT-98 | Member | Upload a 3 MB image | 422 | `The avatar field must not be greater than 2048 kilobytes.` |
| AUT-99 | Member | Upload a `.pdf` as an avatar | 422 | `The avatar field must be a file of type: jpg, jpeg, png, gif, webp.` |
| AUT-100 | Member | `remove_avatar=1` | 302, avatar detached, `avatar_media_id` nulled | `Your profile and preferences have been saved.` |
| AUT-101 | Member | `avatar_media_id` pointing at **another user's** media | 422 | `The selected avatar media id is invalid.` |
| AUT-102 | Member | `avatar_media_id` pointing at a **non-image** media row | 422 | `The selected avatar media id is invalid.` |
| AUT-103 | Member | `avatar_media_id = 99999` | 422 | `The selected avatar media id is invalid.` |
| AUT-104 | Member | `favorites[]` containing a **duplicate** id | 422 | `The favorites.0 field is distinct.` |
| AUT-105 | Member | `favorites[]` containing a **soft-deleted** category | 422 | `The selected favorites.0 is invalid.` |
| AUT-106 | Member | 101 favourites | 422 | `The favorites field must not have more than 100 items.` |
| AUT-107 | Member | `favorites[]` containing a non-integer | 422 | `The favorites.0 must be an integer.` |
| AUT-108 | Member | A valid favourites array | 302, `user_favorite_categories` **synced** (removed ids are deleted, not left orphaned) | `Your profile and preferences have been saved.` |
| AUT-109 | **Admin** | `PATCH /profile` including `display_name` / `bio` / `favorites` | Those fields are **silently ignored** (the FormRequest returns early). Only name/email/avatar change. | `Your profile and preferences have been saved.` |
| AUT-110 | Signed in, media disk unwritable | Upload an avatar | 302 back with input preserved, error on `avatar` | `Avatar upload failed. Please try again.` |
| AUT-111 | Signed in | `DELETE /profile` with the correct password | 302 → `/`, logged out, session invalidated, account deleted, all sessions revoked | **No flash — silent** |
| AUT-112 | — | `DELETE /profile` with a wrong password | 302 back, error in bag `userDeletion` | `The password is incorrect.` |
| AUT-113 | — | `DELETE /profile` with an empty password | 302 back, error in bag `userDeletion` | `The password field is required.` |
| AUT-114 | Guest | `GET` / `PATCH` / `DELETE /profile` | 302 → `/login` | — |
| AUT-115 | Admin sees the profile page | Inspect the form | **Registered-user fields are absent** — no display name, bio, map URL, or favourites picker | — |

### 2.8 Newsletter preferences — `POST /profile/newsletter-preferences`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| AUT-116 | No subscriber row | `subscribe=1` | 302, subscriber created, welcome email queued | `Newsletter preferences saved. You are now subscribed!` |
| AUT-117 | Active subscriber | `subscribe=1` | 302, categories + frequency saved | `Newsletter preferences saved. You are now subscribed!` |
| AUT-118 | Subscriber | `subscribe=0` | 302, `unsubscribed_at` set | `You have been unsubscribed from the newsletter.` |
| AUT-119 | **No** subscriber row | `subscribe=0` | 302, **no row created** (a deliberate no-op) | `You are not subscribed to the newsletter.` |
| AUT-120 | — | `frequency=instant` / `daily` / `weekly` | 302, frequency saved | one of the three messages above |
| AUT-121 | — | `frequency=hourly` | 422 | `The selected frequency is invalid.` |
| AUT-122 | — | `categories[]` with a bad id | 422 | `The selected categories.0 is invalid.` |
| AUT-123 | Guest | `POST` | 302 → `/login` | — |

### 2.9 The role-based `/dashboard` router

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| AUT-124 | `admin` | `GET /dashboard` | 302 → `/admin` | — |
| AUT-125 | `registered-user` | `GET /dashboard` | 302 → `/user/dashboard` | — |
| AUT-126 | User with a non-matching role (e.g. `editor`) | `GET /dashboard` | 302 → `/profile` with `error` | `Your account has no role assigned. Please contact an administrator.` |
| AUT-127 | User with **no** role at all | `GET /dashboard` | 302 → `/profile` with `error` | `Your account has no role assigned. Please contact an administrator.` |
| AUT-128 | Just finished onboarding | `GET /dashboard` | `session()->keep('onboarding-success')` preserves the key across the redirect, so the toast still renders on the destination page | — |
| AUT-129 | Guest | `GET /dashboard` | 302 → `/login` | — |

---

## 3. Onboarding (fandom picker)

`GET /onboarding` + `POST /onboarding` (`throttle:10,1`). Users pick **3–5** fandoms. All other
protected routes stay reachable — the onboarding middleware renders a **modal over the page**, it does
not block navigation.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ONB-01 | `user@example.com`, `onboarding_completed_at = null`, ≥3 categories | Visit `/onboarding` | 200, the picker renders with `Cache-Control: no-store, private` | — |
| ONB-02 | Same | Visit any protected page (`/user/dashboard`, `/profile`, …) | The **modal** overlays the page until onboarding completes | `Complete your fandom setup before continuing.` (403 body) |
| ONB-03 | `admin`, onboarding incomplete | Visit `/admin` | **Not blocked.** The admin role wins over onboarding. | — |
| ONB-04 | New registration, unverified | Register | Lands on `/onboarding`. **Email verification is not required first.** | — |
| ONB-05 | Fresh login, onboarding incomplete | Log in | Redirected to the picker, not the dashboard | — |
| ONB-06 | — | Select 3 fandoms, submit | 302 → `/dashboard`. 3 `user_favorite_categories` rows written **atomically inside a `lockForUpdate` transaction**; `onboarding_completed_at` set. | `Your universe is ready. Welcome to FanHub Plus!` (key `onboarding-success`) |
| ONB-07 | — | Select 5 fandoms, submit | 302, 5 rows written | `Your universe is ready. Welcome to FanHub Plus!` |
| ONB-08 | — | Submit the **same 3** twice | **Idempotent** — no duplicate rows, no error, no second toast | `Your universe is ready. Welcome to FanHub Plus!` |
| ONB-09 | — | Submit **0** fandoms | 422, error on `favorites` | `Choose at least 3 fandoms to make this space yours.` |
| ONB-10 | — | Submit **2** fandoms | 422 | `Choose at least 3 fandoms to continue.` |
| ONB-11 | — | Submit **6** fandoms | 422 | `Choose up to 5 fandoms. Deselect one to make room.` |
| ONB-12 | — | Submit the **same id twice** | 422 | `The favorites.0 field is distinct.` |
| ONB-13 | — | Submit a **soft-deleted** category id | 422 | `The selected favorites.0 is invalid.` |
| ONB-14 | — | Submit a **non-integer** id | 422 | `The favorites.0 must be an integer.` |
| ONB-15 | One of 3 ids is invalid | Submit | 422. **No favourites are written** — the transaction rolls back entirely. | `The selected favorites.2 is invalid.` and the 2 valid rows are **absent** |
| ONB-16 | Guest | `POST /onboarding` | 302 → `/login` | — |
| ONB-17 | Fewer than 3 categories in the DB | Visit `/onboarding` | 302 → `/dashboard`. A small catalogue must **not lock anyone out**. | — |
| ONB-18 | `onboarding_completed_at` already set | Visit or `POST /onboarding` | 302 → `/dashboard` | — |
| ONB-19 | 10 submissions in a minute | 11th | **429** | Laravel 429 page |

**Client-side strings** — assert these in `tests/js/onboarding.test.js` against
`resources/js/modules/onboarding-state.js`:

| Trigger | Text |
|---|---|
| 0–2 selected, submit pressed | `Please choose between 3 and 5 fandoms.` |
| 3–4 selected | `Pick ${n} more to continue.` |
| 5 selected | `All five chosen. Ready when you are.` |
| 5 selected, then one deselected | `Five favorites selected. Deselect one to choose another.` |
| 3–4 selected, then one deselected | `Great choices. Add more or make it yours.` |
| Submit in flight | `Saving your favorites...` |
| Submit pressed twice rapidly | The **second** submit is blocked — no duplicate request |
| `Escape` pressed while the modal is open | Blocked — the modal does not close |
| `Tab` at the last focusable element | Focus **traps** to the first element |
| `Shift+Tab` at the first focusable element | Focus **traps** to the last element |
| Back button after completing onboarding | Recovery path returns the user forward to the dashboard, not into a broken state |

**Personalisation that must follow onboarding** — after picking favourites, **every** content scope
ranks those fandoms first:

- Homepage sections (featured, upcoming, multimedia, spotlight)
- `/explore` listings
- Fandom landing pages
- Story / character / merchandise detail-page related rails
- Upcoming feed merges

Additional guarantees:

- Homepage personalisation **must not leak** one user's favourites to another user or to a guest.
- A guest visit is **never** recorded in `dashboard.visited`.
- Deleting a favourite category reverts that scope to the **default** ordering.
- A user whose favourites all point at deleted categories falls back to default ordering.

---

## 4. Member area (my account)

All routes are `auth` + prefix `/user` + name `user.*`. **Every write is throttled 30/min.** Note
these routes are `auth` only — they are **not** gated on `role:registered-user`, so an `editor`-only
account can reach all of them. Only `/user/dashboard` is role-gated.

### 4.1 Dashboard — `GET /user/dashboard`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| MBR-01 | `user@example.com` | Visit `/user/dashboard` | 200. Real KPIs: favourites, bookmarks, in-progress, recent activity. | — |
| MBR-02 | First ever dashboard visit | Visit | Greeting uses the first-visit variant; a `dashboard.visited` activity row is created with subject `Dashboard welcome` and description `Opened the dashboard for the first time.` | See the banner table in §3 |
| MBR-03 | Second visit | Visit | Greeting switches to `Welcome back,` | — |
| MBR-04 | Deleted every favourite | Visit | Banner falls back to the 0-favourite variant — **no stale names** | — |
| MBR-05 | Guest | Visit | 302 → `/login` | — |
| MBR-06 | `editor` role | Visit | 302 → `/profile/edit` with `error` | `You do not have permission to access that page.` |
| MBR-07 | `admin` | Visit | 302 → `/admin` | — |
| MBR-08 | Member A has bookmarks, Member B does not | B visits the dashboard | **No cross-user leakage** — B sees only B's data | — |
| MBR-09 | Homepage cache warmed by A | B visits the homepage | B is served the **default** variant, not A's personalised cache | — |
| MBR-10 | Empty DB (no favourites, no history) | Visit | 200, all counters `0`, no division by zero | — |
| MBR-11 | Visit the dashboard twice in the same session | — | `dashboard.visited` is created **once** | — |

### 4.2 Read-only pages

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| MBR-12 | Bookmarks of several types | `GET /user/bookmarks` | 200. All bookmarks listed, private notes visible to their owner | — |
| MBR-13 | — | `?type=content` / `character` / `merchandise` / `event` / `fandom` | 200, filtered | — |
| MBR-14 | — | `?type=spaceship` | **422** | `The selected type is invalid.` |
| MBR-15 | Favourites exist | `GET /user/favorites` | 200. All favourite fandoms listed with their artwork | — |
| MBR-16 | History rows exist | `GET /user/activity` | 200. `watched`, `viewed`, `saved`, `favorited`, `rated`, `reviewed`, `submitted` all render | — |
| MBR-17 | — | `?type=watched` (and each of the 7) | 200, filtered | — |
| MBR-18 | — | `?type=teleported` | **422** | `The selected type is invalid.` |
| MBR-19 | Reviews exist | `GET /user/reviews` | 200. A review is visible to its **author** in any state, publicly only after approval. | — |
| MBR-20 | Submissions exist | `GET /user/submissions` | 200. `draft` / `pending_review` / `published` / `rejected` states all shown | — |
| MBR-21 | Member | `GET /user/submissions/create` | 200, form renders | — |
| MBR-22 | Member owns a draft | `GET /user/submissions/{id}/edit` | 200, form pre-filled | — |
| MBR-23 | Member does **not** own the submission | `GET /user/submissions/{other-id}/edit` | **403** (bare, no message) | Laravel 403 page |
| MBR-24 | Submission is already **published** | `GET /user/submissions/{id}/edit` | **403** | `Published submissions are managed by moderators.` |
| MBR-25 | Submission is already **rejected** | `GET /user/submissions/{id}/edit` | **403** | `Published submissions are managed by moderators.` |
| MBR-26 | Member | `GET /user/feedback` | 200, form renders | — |
| MBR-27 | Member A has activity, B does not | B visits `/user/activity` | **Only B's rows.** No leakage | — |
| MBR-28 | Deleted bookmark target | Visit `/user/bookmarks` | The card is dropped or shown as an orphan — **no crash, no 500** | — |
| MBR-29 | Deleted submission target | Visit `/user/submissions` | Same — no crash | — |
| MBR-30 | Guest | Any member read page | 302 → `/login` | — |

### 4.3 Submissions — write operations

`POST /user/submissions` and `PUT /user/submissions/{id}` share the same rules. A member **can never
self-publish** — `intent` has no `publish` option and editing a published submission is 403.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| MBR-31 | Valid payload, `intent=submit` | `POST /user/submissions` | 302 back. `is_user_submitted = 1`, `submitted_by = me`, `status = pending_review`, an activity row written. | `Submitted for moderator approval.` |
| MBR-32 | Valid payload, `intent=draft` | `POST /user/submissions` | 302 back, `status = draft` | `Draft saved. You can finish it any time.` |
| MBR-33 | — | `title` empty | 422 | `The title field is required.` |
| MBR-34 | — | `title` of 181 chars | 422 | `The title field must not be greater than 180 characters.` |
| MBR-35 | — | `title` non-string | 422 | `The title field must be a string.` |
| MBR-36 | — | `category_id` missing | 422 | `The category id field is required.` |
| MBR-37 | — | `category_id` soft-deleted | 422 | `The selected category id is invalid.` |
| MBR-38 | — | `type=blog` | 422 | `The selected type is invalid.` |
| MBR-39 | — | `type=article` / `image` / `video` / `audio` | 200 (with a matching attachment where required) | — |
| MBR-40 | — | `body` of 29 chars | 422 | `The body field must be at least 30 characters.` |
| MBR-41 | — | `body` of 30 chars (boundary) | 200 | — |
| MBR-42 | — | `body` of 50 001 chars | 422 | `The body field must not be greater than 50000 characters.` |
| MBR-43 | — | `body` empty | 422 | `The body field is required.` |
| MBR-44 | — | `intent=publish` | **422** — self-publishing is impossible | `The selected intent is invalid.` |
| MBR-45 | — | `intent` missing | 422 | `The intent field is required.` |
| MBR-46 | — | `excerpt` of 501 chars | 422 | `The excerpt field must not be greater than 500 characters.` |
| MBR-47 | `type=image`, no file, `intent=submit` | Submit | 422 on `attachment` | `Please attach your image, video or audio before submitting.` |
| MBR-48 | `type=video`, no file, `intent=submit` | Submit | 422 on `attachment` | `Please attach your image, video or audio before submitting.` |
| MBR-49 | `type=article`, no file, `intent=submit` | Submit | **Succeeds** — articles need no attachment | `Submitted for moderator approval.` |
| MBR-50 | `type=video`, attach a `.mp3` | Submit | 422 on `attachment` | `Choose a file matching the selected content type.` |
| MBR-51 | `type=image`, attach a `.mp4` | Submit | 422 | `Choose a file matching the selected content type.` |
| MBR-52 | `type=video`, attach a 25 MB `.mp4` | Submit | 422 | `The attachment field must not be greater than 20480 kilobytes.` |
| MBR-53 | `type=image`, attach a 6 MB `.jpg` as cover | Submit | 422 | `The cover field must not be greater than 5120 kilobytes.` |
| MBR-54 | — | Attach a `.exe` | 422 | `The attachment field must be a file of type: jpg, jpeg, png, webp, gif, mp4, webm, mp3, wav, ogg, m4a.` |
| MBR-55 | — | Attach a `.txt` as cover | 422 | `The cover field must be an image.` |
| MBR-56 | Valid attachment, media disk throws | Submit | 302 back, input preserved, error on `attachment`, **partially uploaded media cleaned up** so no orphan rows remain | `We could not save your submission. Please try again.` |
| MBR-57 | Member owns a draft | `PUT /user/submissions/{id}` | 302, updated | `Draft saved. You can finish it any time.` |
| MBR-58 | Member owns a **pending_review** submission | `PUT /user/submissions/{id}` | **403** | `Published submissions are managed by moderators.` |
| MBR-59 | Member owns a **published** submission | `PUT /user/submissions/{id}` | **403** | `Published submissions are managed by moderators.` |
| MBR-60 | Member does **not** own it | `PUT /user/submissions/{other-id}` | **403** (bare) | Laravel 403 page |
| MBR-61 | Member owns a draft | `DELETE /user/submissions/{id}` | 302, row deleted | `Submission removed.` |
| MBR-62 | Member does **not** own it | `DELETE /user/submissions/{other-id}` | **403** (bare) | Laravel 403 page |
| MBR-63 | Member owns a **published** submission | `DELETE /user/submissions/{id}` | **403** | `Published submissions are managed by moderators.` |
| MBR-64 | Guest | `POST` / `PUT` / `DELETE /user/submissions` | 302 → `/login` | — |
| MBR-65 | Member | Any member write | 302 back, throttle applied | — |
| MBR-66 | 30 writes in a minute | 31st | **429** | Laravel 429 page |
| MBR-67 | Payload with an `id` field pointing at another user's submission | `POST` | The `id` is **ignored** — a new row is created. No mass assignment of ownership. | `Submitted for moderator approval.` |

### 4.4 Feedback — `POST /user/feedback`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| MBR-68 | `type=bug`, `message` ≥10 chars | Submit | 302, `feedback` row created with `status = open`, `user_id = me` | `Thanks. Your feedback has been sent to the FanHub team.` |
| MBR-69 | — | `type=suggestion` | 302, created | `Thanks. Your feedback has been sent to the FanHub team.` |
| MBR-70 | — | `type=query` | 302, created | `Thanks. Your feedback has been sent to the FanHub team.` |
| MBR-71 | — | `type=complaint` | **422** | `The selected type is invalid.` |
| MBR-72 | — | `type` missing | 422 | `The type field is required.` |
| MBR-73 | — | `message` of 9 chars | 422 | `The message field must be at least 10 characters.` |
| MBR-74 | — | `message` of 5001 chars | 422 | `The message field must not be greater than 5000 characters.` |
| MBR-75 | — | `message` empty | 422 | `The message field is required.` |
| MBR-76 | Guest | `POST /user/feedback` | 302 → `/login` | — |
| MBR-77 | Member A submits feedback | Member B visits `/user/feedback` | B **cannot see** A's feedback. Feedback is private to its author. | — |
| MBR-78 | Success | Observe the alert | Rendered in `.feedback-alert--success` with a check icon; field errors are `<p role="alert">` with `id="message-error"` / `id="type-error"` | — |
| MBR-79 | Validation failure | Observe the alert | Rendered in `.feedback-alert--error` with a close icon | — |
| MBR-80 | Admin created feedback attributed to a user with no email | Admin resolves it | No email attempt, no error, no crash | — |

### 4.5 Preferences — `PATCH /user/preferences`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| MBR-81 | — | `theme_preference=dark` | **200 JSON** — note: **no `message` key** | `{"saved":true}` |
| MBR-82 | — | `theme_preference=light` / `system` | 200 | `{"saved":true}` |
| MBR-83 | — | `theme_preference=neon` | **422** | `{"message":"The selected theme preference is invalid.","errors":{…}}` |
| MBR-84 | — | `theme_preference` missing | 422 | `{"message":"The theme preference field is required.","errors":{…}}` |
| MBR-85 | Theme save while offline | Toggle the theme with no network | Fails silently in JS **by design** — the local toggle still works offline. No visible error is intentional. | No visible error |
| MBR-86 | Guest | `PATCH` | 302 → `/login` | — |

### 4.6 Member write throttle

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| MBR-87 | 30 member writes in a minute across **different** member routes | 31st write | **429** — the limit is per-user across the whole member write group, not per route | Laravel 429 page |
| MBR-88 | 30 member **reads** in a minute | — | **Not throttled.** Reads carry no `throttle` middleware. | — |

---

## 5. Community interactions

All in `routes/member.php`: `auth` + `throttle:30,1`. `MemberLibrary::TYPES` = `content`, `character`,
`merchandise`, `event`, `fandom`.

### 5.1 Bookmarks

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| COM-01 | Member | `POST /user/items/content/5/bookmark` with `saved=1` | 302, `bookmarks` row created, `saved` activity logged | `Saved to your bookmarks.` |
| COM-02 | Already bookmarked | `saved=1` again | **Idempotent** — no duplicate row, no error | `Saved to your bookmarks.` |
| COM-03 | Bookmarked | `saved=0` | 302, row deleted | `Bookmark removed.` |
| COM-04 | Not bookmarked | `saved=0` | **Idempotent** — no error | `Bookmark removed.` |
| COM-05 | — | `saved` missing | 422 | `The saved field is required.` |
| COM-06 | — | `saved=maybe` | 422 | `The saved field must be true or false.` |
| COM-07 | — | `POST /user/items/spaceship/5/bookmark` (unknown type) | **404** (`abort_unless(isset(self::TYPES[$type]))`) | Laravel 404 page |
| COM-08 | — | `POST /user/items/content/99999/bookmark` (missing id) | **404** | `No query results for model [App\Models\Content] 99999.` |
| COM-09 | Member A's bookmark | B sends `PATCH /user/bookmarks/{A's id}` | **403** (bare) | Laravel 403 page |
| COM-10 | Own bookmark | `PATCH /user/bookmarks/{id}` with `note` ≤2000 chars | 302, private note saved | `Private note saved.` |
| COM-11 | — | `note` of 2001 chars | 422 | `The note field must not be greater than 2000 characters.` |
| COM-12 | — | `note` empty (clearing it) | 302, note nulled | `Private note saved.` |
| COM-13 | Member A's bookmark | B sends `DELETE /user/bookmarks/{A's id}` | **403** (bare) | Laravel 403 page |
| COM-14 | Own bookmark | `DELETE /user/bookmarks/{id}` | 302, deleted | `Bookmark removed.` |
| COM-15 | **Unpublished** content | `POST /user/items/content/{draft}/bookmark` | `MemberLibrary::card()` returns `null` — nothing renders publicly. The bookmark may persist but leaks nothing. | — |
| COM-16 | **Cancelled** event | Bookmark it | `card()` returns `null` — nothing renders | — |
| COM-17 | Guest | Any bookmark write | 302 → `/login` | — |
| COM-18 | Member A's bookmark note | Member B visits `/user/bookmarks` | **B never sees A's note** | — |
| COM-19 | Each of the 5 types | Bookmark `content`, `character`, `merchandise`, `event`, `fandom` | All five succeed and all appear on `/user/bookmarks` with the right card | — |
| COM-20 | Bookmarked item then deleted | Visit `/user/bookmarks` | Card is dropped or orphaned — **no 500** | — |

### 5.2 Favourite fandoms

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| COM-21 | Member | `POST /user/favorites/3` with `saved=1` | 302, `user_favorite_categories` row created, `favorited` activity logged | `Added to your favorite fandoms.` |
| COM-22 | Already a favourite | `saved=1` | Idempotent | `Added to your favorite fandoms.` |
| COM-23 | Favourite | `saved=0` | 302, row deleted | `Favorite removed.` |
| COM-24 | Not a favourite | `saved=0` | Idempotent | `Favorite removed.` |
| COM-25 | — | `saved` missing | 422 | `The saved field is required.` |
| COM-26 | — | `saved=maybe` | 422 | `The saved field must be true or false.` |
| COM-27 | `?` soft-deleted category id | `POST /user/favorites/{deleted id}` | **404** | Laravel 404 page |
| COM-28 | Member A favourites a fandom | Member B views the homepage | B sees the **default** ranking, not A's | — |
| COM-29 | Guest | View the homepage after A personalised it | **Default** ranking. No personalised cache leak. | — |
| COM-30 | All favourites soft-deleted | View the homepage | Falls back to default ordering. No empty rails. | — |
| COM-31 | Guest | `POST /user/favorites/{id}` | 302 → `/login` | — |
| COM-32 | Favourites exist | Reload the homepage | Cache reflects the change — the personalised variant is rebuilt | — |

### 5.3 Ratings

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| COM-33 | Rated item 5 with 3 stars | Rate it **4** stars | 302. **The same single `ratings` row is updated** — a second row is not created. Redirect includes the `#community` fragment. | `Your rating has been saved.` |
| COM-34 | — | `stars=0` | 422 | `The stars must be between 1 and 5.` |
| COM-35 | — | `stars=6` | 422 | `The stars must be between 1 and 5.` |
| COM-36 | — | `stars=1` and `stars=5` (boundaries) | 200 | `Your rating has been saved.` |
| COM-37 | — | `stars` missing | 422 | `The stars field is required.` |
| COM-38 | — | `stars=3.7` | 422 | `The stars must be an integer.` |
| COM-39 | Member A rated 5 stars | Check the public star summary | The average **includes** the rating; the **thumbs-up count is excluded** from the star average. | — |
| COM-40 | A thumbs-up rating exists | Check the star summary | It does **not** skew the star average | — |
| COM-41 | Unknown type | `POST /user/items/spaceship/5/rating` | **404** | Laravel 404 page |
| COM-42 | Guest | Rate | 302 → `/login` | — |
| COM-43 | Rating on a `fandom` (category) | Rate it | 200 — all 5 types are ratable | `Your rating has been saved.` |

### 5.4 Reviews (moderated)

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| COM-44 | Member | `POST /user/items/content/5/review` with `title` + `body` ≥10 chars | 302 → `#community`. `reviews` row created with `status = pending` — **not publicly visible** | `Review submitted. It will appear after moderator approval.` |
| COM-45 | — | `body` of 9 chars | 422 | `The body field must be at least 10 characters.` |
| COM-46 | — | `body` of 5001 chars | 422 | `The body field must not be greater than 5000 characters.` |
| COM-47 | — | `body` empty | 422 | `The body field is required.` |
| COM-48 | — | `title` of 151 chars | 422 | `The title field must not be greater than 150 characters.` |
| COM-49 | — | `title` empty | **Accepted** — the title is optional | `Review submitted. It will appear after moderator approval.` |
| COM-50 | Author visits the story | — | The author **can** see their own pending review | — |
| COM-51 | Member B visits the story | — | B **cannot** see A's pending review | — |
| COM-52 | Admin approves the review | Visit the story | Now **public on every page** that renders reviews | — |
| COM-53 | Admin rejects the review | Visit the story | Not public; the author can still see it as rejected on `/user/reviews` | — |
| COM-54 | Member A's review | B sends `DELETE /user/reviews/{A's id}` | **403** (bare) | Laravel 403 page |
| COM-55 | Own review | `DELETE /user/reviews/{id}` | 302, deleted | `Review removed.` |
| COM-56 | Many approved reviews | View the review list | Pagination **preserves active filters**; no duplicates or drops | — |
| COM-57 | Guest | Submit a review | 302 → `/login` | — |
| COM-58 | Review target deleted | View `/user/reviews` | Dropped or orphaned — **no 500** | — |

### 5.5 Watched history

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| COM-59 | A **video** content | `POST /user/watched/{id}` with `watched=1` | 302, history row created | `Added to your watched history.` |
| COM-60 | An **audio** content | `POST /user/watched/{id}` with `watched=1` | 200 — both video and audio are allowed | `Added to your watched history.` |
| COM-61 | Already watched | `watched=1` | Idempotent | `Added to your watched history.` |
| COM-62 | Watched | `watched=0` | 302, row deleted | `Removed from watched history.` |
| COM-63 | An **article** content | `POST /user/watched/{id}` | **404** — `abort_unless(in_array($content->type, ['video','audio']))` | Laravel 404 page |
| COM-64 | An **image** content | `POST /user/watched/{id}` | **404** | Laravel 404 page |
| COM-65 | — | `watched` missing | 422 | `The watched field is required.` |
| COM-66 | — | `watched=maybe` | 422 | `The watched field must be true or false.` |
| COM-67 | Guest | `POST /user/watched/{id}` | 302 → `/login` | — |
| COM-68 | History exists | Visit `/user/activity?type=watched` | Every row corresponds to a **real** watched record, not fabricated | — |
| COM-69 | Content deleted | Visit `/user/activity` | Row dropped or orphaned — **no 500** | — |

---

## 6. Newsletter / subscribers

### 6.1 Subscribe — `POST /subscribe` (`throttle:60,1`)

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| NWS-01 | Never subscribed | Submit a new email | 302. `subscribers` row created, `unsubscribe_token` generated, welcome email sent. | `Thank you for subscribing!` |
| NWS-02 | Already **active** | Submit the same email | 302. **Idempotent — no duplicate row and no second welcome email.** | `You are already subscribed!` |
| NWS-03 | Currently **unsubscribed** | Submit the same email | 302. Row reactivated with a **brand new token**. | `Welcome back! Your subscription is active again.` |
| NWS-04 | — | Email empty | 422, error in **bag `subscribe`** on `email` | `The email field is required.` |
| NWS-05 | — | Email malformed | 422, bag `subscribe` | `The email field must be a valid email address.` |
| NWS-06 | — | Email of 256 chars | 422, bag `subscribe` | `The email field must not be greater than 255 characters.` |
| NWS-07 | — | `name` of 256 chars | 422, bag `subscribe` | `The name field must not be greater than 255 characters.` |
| NWS-08 | Mailer throws | Submit a new email | The subscriber row is **still created**; only a `Log::warning` is written | `Thank you for subscribing!` (no user-visible error) |
| NWS-09 | Email with uppercase | Submit | 302, stored as-is — **no lowercase normalisation** on this endpoint | one of the three messages |
| NWS-10 | Homepage | Scroll to the newsletter form | Posts to `public.subscribe`; success renders in `alert alert-success`, error in `alert alert-danger`, both with `role="alert"` and a dismiss button | — |
| NWS-11 | Any public page footer | Submit from the footer | Same behaviour, rendered in `.fh-footer-lite__news-msg` (custom CSS, not Bootstrap) | — |
| NWS-12 | 60 requests in a minute | 61st | **429** | Laravel 429 page |
| NWS-13 | Bag `subscribe` error | Observe the page | The page-level error block shows **nothing** — the error is visible only in the field. Named bags are never read by the global block. | — |
| NWS-14 | Success | Observe the alert | It **auto-dismisses after 5000 ms** | — |
| NWS-15 | Success, then reload the page | — | The message is **gone** — flashes are consumed on first render | — |
| NWS-16 | Two different emails | Subscribe both | Two rows, two distinct tokens, two independent unsubscribe links | — |

### 6.2 Unsubscribe — `GET /unsubscribe/{token}`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| NWS-17 | Active subscriber, valid token | Open the unsubscribe link | 200, `unsubscribed_at` set, `status = unsubscribed`, confirmation view | `subscriber.unsubscribed` view |
| NWS-18 | — | Open a **random/invalid** token | 200 with the invalid-token view — **not** a 404, so the status code alone does not confirm token validity | `subscriber.invalid-token` view |
| NWS-19 | Already unsubscribed | Open the link again | 200, still unsubscribed. **No crash, no email.** | `subscriber.unsubscribed` view |
| NWS-20 | Unsubscribed user | Subscribe again from `/` | Reactivated with a **fresh token**; the old link stays dead | `Welcome back! Your subscription is active again.` |
| NWS-21 | Reactivated user | Opens the **old** token | The invalid-token view | `subscriber.invalid-token` view |
| NWS-22 | Token with special characters | Open `/unsubscribe/%20%21%21` | 200 invalid-token view, no 500 | `subscriber.invalid-token` view |
| NWS-23 | Very long token | Open it | 200 invalid-token view, no 500 | `subscriber.invalid-token` view |

---

## 7. Chatbot

`GET /chatbot/faqs` (`throttle:60,1`) · `POST /chatbot/message` (`throttle:12,1`). All responses are
JSON. The API convention is a **`message` key**, never an `error` key.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| CHT-01 | 10 seeded FAQs | `GET /chatbot/faqs` | **200** with exactly 6 (hard limit) | `{"faqs":[{"id":1,"question":"…"}, …]}` — only `id` and `question` |
| CHT-02 | Fewer than 6 FAQs | `GET /chatbot/faqs` | 200 with however many exist — no error, no padding | `{"faqs":[…]}` |
| CHT-03 | **No** `GEMINI_API_KEY` | `GET /chatbot/faqs` | **200** — the FAQ list works with **no API key at all** | `{"faqs":[…]}` |
| CHT-04 | A question matching an FAQ | `POST /chatbot/message` | **200**, `source = "faq"`. The answer is saved to `chatbot_queries`. | `{"answer":"…","source":"faq"}` |
| CHT-05 | No FAQ match, API key present | `POST /chatbot/message` | 200, `source = "gemini"`. The **matched FAQ answers are injected as context** into the system prompt. Row saved. | `{"answer":"…","source":"gemini"}` |
| CHT-06 | Session A asks about topic X | Session B asks about topic X | B's prompt contains **no history from A**. Sessions never share history. | — |
| CHT-07 | No API key | `POST /chatbot/message` | **503**. **Nothing is saved** to `chatbot_queries`. The message is actionable. | `AI chat is not available yet. You can still use the frequently asked questions.` |
| CHT-08 | API key set, provider returns an error | `POST /chatbot/message` | **503**, not saved | `Our AI is busy right now. Please try again shortly or choose an FAQ.` |
| CHT-09 | API key set, empty or garbage response | `POST /chatbot/message` | **503**, not saved | `I could not answer that question. Try rephrasing it.` |
| CHT-10 | API key set, provider times out | `POST /chatbot/message` | **503**, not saved | `The connection timed out. Please try again.` |
| CHT-11 | — | `message` empty | **422** with `message` + `errors` | `{"message":"The message field is required.","errors":{"message":["The message field is required."]}}` |
| CHT-12 | — | `message` of 1501 chars | **422** | `{"message":"The message may not be greater than 1500 characters.","errors":{…}}` |
| CHT-13 | — | `message` of exactly 1500 chars (boundary) | 200 | — |
| CHT-14 | — | `page` of 501 chars | 422 | `{"message":"The page field must not be greater than 500 characters.","errors":{…}}` |
| CHT-15 | 12 messages in a minute | 13th | **429** | Laravel 429 / JSON 429 |
| CHT-16 | `page=explore` | `POST /chatbot/message` | 200 — the page context line `The user is currently on this page: explore.` is added to the prompt | — |
| CHT-17 | Prompt-injection attempt in `message` | `POST /chatbot/message` | The CRITICAL LANGUAGE RULE block still applies; the reply comes back in the expected language | — |
| CHT-18 | Guest | `POST /chatbot/message` | 200 — the chatbot is **public**. The row is saved with a generated `session_id`, not a `user_id`. | — |

---

## 8. Media serving

`GET /storage/{path}` → `MediaServeController`. One-year cache. Range requests are honoured for
video and audio.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| MED-01 | Valid media path | `GET /storage/media/anime_poster.jpg` | 200. `Content-Type: image/jpeg`, `Cache-Control: public, max-age=31536000` | — |
| MED-02 | Video media + `Range: bytes=0-1023` | Request | **206 Partial Content** with `Accept-Ranges: bytes` | — |
| MED-03 | Audio media + `Range` header | Request | **206 Partial Content** | — |
| MED-04 | Image + `Range` header | Request | **200** — range is ignored for images | — |
| MED-05 | — | `GET /storage/media/does-not-exist.jpg` | **404** | `Media file not found.` |
| MED-06 | — | `GET /storage/../../.env` | **404** — path traversal is blocked | `Media file not found.` |
| MED-07 | — | `GET /storage/` (empty path) | **404** | Laravel 404 page |
| MED-08 | Media row exists but the file was deleted from disk | Request | **404** | `Media file not found.` |
| MED-09 | — | `GET /storage/%2e%2e%2f%2e%2e%2f.env` | **404** — encoded traversal is blocked | `Media file not found.` |
| MED-10 | Guest | Request any media | 200 — media is **public**, no auth required | — |
| MED-11 | A media row whose `path` is null or malformed | Request | **404** | `Media file not found.` |
| MED-12 | A `document` media row (PDF) | Request | 200 with the correct `application/pdf` content type | — |

---

## 9. Admin access control & dashboard

Prefix `/admin`, name `admin.*`, middleware `['auth', 'role:admin', 'ip-restrict']`.
⚠️ There is **no `verified` middleware** in the current checkout, and `RoleMiddleware` lives at
`App\Middleware\RoleMiddleware` (not `App\Http\Middleware` as `CONTEXT.md` claims).

### 9.1 Role enforcement

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-ACC-01 | `registered-user` | `GET /admin` | **302 → `/user/dashboard`** with `error` | `You do not have permission to access that page.` |
| ADM-ACC-02 | `editor` (a role, but not admin or registered-user) | `GET /admin` | **302 → `/profile/edit`** with `error` | `You do not have permission to access that page.` |
| ADM-ACC-03 | `registered-user` + `Accept: application/json` | `GET /admin/categories` | **403 JSON** | `{"message":"Forbidden. You do not have permission to access this resource."}` |
| ADM-ACC-04 | User with **no role** at all | `GET /admin` when the target **is** their resolved home route | **403** | `Your account does not have a role assigned. Please contact an administrator.` |
| ADM-ACC-05 | Guest | `GET /admin` | 302 → `/login` | — |
| ADM-ACC-06 | `registered-user` | Every `/admin/*` **write** (POST / PUT / PATCH / DELETE) | **All blocked.** Every one returns the same 302 + message. | `You do not have permission to access that page.` |
| ADM-ACC-07 | `admin` | `GET /admin` | **200**, dashboard renders | — |
| ADM-ACC-08 | `admin`, `ip-restrict` **empty** | `GET /admin` | 200 — an empty whitelist means **allow all** (fail-open by design) | — |
| ADM-ACC-09 | Whitelist `203.0.113.5`, request from another IP | `GET /admin` | **403** | `Access denied: IP not whitelisted.` |
| ADM-ACC-10 | Whitelist `203.0.113.5` | Request from `203.0.113.5` | 200 | — |
| ADM-ACC-11 | Whitelist `203.0.113.*` | Request from `203.0.113.99` | 200 — wildcard supported | — |
| ADM-ACC-12 | Whitelist `203.0.113.0/24` | Request from `203.0.113.55` | 200 — CIDR supported | — |
| ADM-ACC-13 | Whitelist containing a malformed CIDR | Request from a nearby IP | Falls back to an exact-string compare. The IP is **not** matched. | `Access denied: IP not whitelisted.` |
| ADM-ACC-14 | Whitelist contains blank lines and spaces | Request | Blanks are dropped and entries trimmed before matching | — |
| ADM-ACC-15 | Whitelist set | A **public** page (`/`, `/events`) | **200** — IP restriction applies to `/admin/*` **only** | — |
| ADM-ACC-16 | Any role | `GET /up` | 200 — the health probe is outside `/admin` and not IP-restricted | — |
| ADM-ACC-17 | The `permission` middleware alias | — | **Registered but no route uses it.** `CheckPermission` is unreachable — nothing to test. | — |
| ADM-ACC-18 | `admin` with `maintenance_mode = true` | `GET /admin` | 200 — `admin/*` is in the default bypass list | — |
| ADM-ACC-19 | Guest, `maintenance_mode = true` | `GET /` | **503** HTML page | `We are currently performing scheduled maintenance. We will be back shortly.` |
| ADM-ACC-20 | `maintenance_mode = true` | `GET /up` | **200** — always allowed | — |
| ADM-ACC-21 | `maintenance_mode = true` | `GET /health` | **200** — always allowed | — |
| ADM-ACC-22 | `maintenance_mode = true` | `GET /api/v1/health` | **503 HTML, not JSON.** `/api/v1/health` does **not** match the `health` bypass pattern, so the API health endpoint is **not** exempt from maintenance mode. | HTML 503 page |
| ADM-ACC-23 | `registered-user` | `GET /admin/notifications/{another user's id}/read` | **403** (bare, from `authorizeNotification()`) | Laravel 403 page |
| ADM-ACC-24 | `editor` | `GET /admin/users/1/edit` | 302 → `/profile/edit` + `You do not have permission to access that page.` | — |

### 9.2 Admin dashboard — `GET /admin`

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-ACC-25 | Seeded DB | `GET /admin` | 200. KPI cards render: `Total Users`, `Total Content`, `Published Content`, `Pending Submissions`, `Total Categories`, `Upcoming Events`, `Pending Reviews`, `Open Feedback`, `Total Media` | — |
| ADM-ACC-26 | Pending items exist | Visit | The "needs attention" widgets list the real pending submissions, reviews, and open feedback | — |
| ADM-ACC-27 | Empty DB | Visit | 200, every counter `0`. No division by zero, no crash. | — |
| ADM-ACC-28 | — | Visit | **No flash message** — this is a read-only page | — |
| ADM-ACC-29 | A content is published / unpublished | Visit | `Published Content` reflects the real count, not the total | — |

---

## 10. Admin content catalog

Categories, Content, Characters, Merchandise, Events, and the JSON relation lookups. All
`auth` + `role:admin` + `ip-restrict`.

### 10.1 Categories

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-CAT-01 | — | `GET /admin/categories` | 200, list with stats + filters | — |
| ADM-CAT-02 | — | `POST /admin/categories` with a valid name | 302 → index, row created, slug auto-derived from the name | `Category created successfully.` |
| ADM-CAT-03 | — | `POST` with **no** name | 422 | `Please enter a category name.` |
| ADM-CAT-04 | — | Name of 256 chars | 422 | `Category name cannot exceed 255 characters.` |
| ADM-CAT-05 | Name `anime` exists | `POST` the same name | 422 | `A category with this name already exists.` |
| ADM-CAT-06 | Slug `anime` exists | `POST` a different name with `slug=anime` | 422 | `A category with this slug already exists.` |
| ADM-CAT-07 | — | Slug containing a space | 422 | `The slug field must only contain letters, numbers, dashes, and underscores.` |
| ADM-CAT-08 | — | Slug of 256 chars | 422 | `The slug field must not be greater than 255 characters.` |
| ADM-CAT-09 | — | Slug of 0 characters (explicitly empty after `prepareForValidation`) | 422 | `The slug field must be at least 1 character.` |
| ADM-CAT-10 | — | Name filled, slug left blank | 302 — `prepareForValidation()` fills the slug from the name before validating | `Category created successfully.` |
| ADM-CAT-11 | — | `description` of 501 chars | 422 | `Description cannot exceed 500 characters.` |
| ADM-CAT-12 | — | `icon` = a `.txt` file | 422 | `The file must be an image.` |
| ADM-CAT-13 | — | `icon` = a 3 MB image | 422 | `The icon must not be larger than 2MB.` |
| ADM-CAT-14 | — | `icon` = a `.svg` | 200 — SVG **is** an allowed icon mime | `Category created successfully.` |
| ADM-CAT-15 | — | `icon_media_id = 99999` | 422 | `The selected media file does not exist.` |
| ADM-CAT-16 | Disk write throws | `POST` with an icon | 302 back, input preserved, error on `icon` | `Icon upload failed. Please try again.` |
| ADM-CAT-17 | A DB unique violation races past validation | `POST` | 302 back, input preserved, error on `name` (a `QueryException` catch) | `A category with this name or slug already exists.` |
| ADM-CAT-18 | Existing category | `PUT /admin/categories/{id}` | 302, updated, slug re-derived if the name changed | `Category updated successfully.` |
| ADM-CAT-19 | Editing the **same** record | `PUT` keeping its own name and slug | **Succeeds** — `unique` ignores the current record | `Category updated successfully.` |
| ADM-CAT-20 | Icon set | `PUT` with `remove_icon=1` | 302, `icon_media_id` nulled | `Category updated successfully.` |
| ADM-CAT-21 | Icon upload throws on update | `PUT` with a new icon | 302 back, input preserved, error on `icon`, **old icon kept** | `Icon upload failed. Please try again.` |
| ADM-CAT-22 | Existing category | `DELETE /admin/categories/{id}` | 302 — **soft** delete. It disappears from `/fandom/{slug}` and from every picker. | `Category moved to trash.` ⚠️ says "moved to trash", not "deleted" |
| ADM-CAT-23 | Soft-deleted category | `GET /admin/categories/trashed` | 200, trashed records listed | — |
| ADM-CAT-24 | Soft-deleted category | `POST /admin/categories/{id}/restore` | 302, record restored and back in the main list | `Category restored successfully.` |
| ADM-CAT-25 | Already-active category | `POST …/restore` | 302, no-op, **no error** | `Category restored successfully.` |
| ADM-CAT-26 | Soft-deleted and **unreferenced** | `DELETE …/force-delete` | 302, row gone permanently | `Category permanently deleted.` |
| ADM-CAT-27 | Soft-deleted but **referenced** by content, a character, or merchandise | `DELETE …/force-delete` | **302, deletion refused.** Nothing is destroyed. | `This category cannot be permanently deleted because content, characters, or merchandise still reference it.` |
| ADM-CAT-28 | — | Force-delete a non-existent id | **404** | Laravel 404 page |
| ADM-CAT-29 | Soft-deleted category | `GET /fandom/{its-slug}` | **404** | Laravel 404 page |
| ADM-CAT-30 | `registered-user` | Any `/admin/categories/*` write | 302 + permission error | `You do not have permission to access that page.` |
| ADM-CAT-31 | Guest | Any `/admin/categories/*` | 302 → `/login` | — |

### 10.2 Content

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-CAT-32 | — | `GET /admin/contents` | 200, with filters (category, status, type, search) | — |
| ADM-CAT-33 | Media library non-empty | `GET /admin/contents/create` | 200; the "view all media" modal renders | — |
| ADM-CAT-34 | Content with media | `GET /admin/contents/{id}/edit` | 200; the media picker shows the **current selection** | — |
| ADM-CAT-35 | — | `POST` with a valid payload | 302, row created, tag and media pivots synced | `Content created successfully.` |
| ADM-CAT-36 | — | `POST` with **no** title | 422 | `Please enter a title.` |
| ADM-CAT-37 | — | `POST` with **no** category | 422 | `Please select a category.` |
| ADM-CAT-38 | — | `category_id = 99999` | 422 | `The selected category does not exist.` |
| ADM-CAT-39 | — | `POST` with a duplicate slug | 422 | `This slug is already taken.` |
| ADM-CAT-40 | — | Slug of 281 chars | 422 | `The slug field must not be greater than 280 characters.` |
| ADM-CAT-41 | — | `type = blog` | 422 | `Type must be article, video, audio, or image.` |
| ADM-CAT-42 | — | `status = archived` | 422 | `Status must be draft, pending review, published, or rejected.` |
| ADM-CAT-43 | — | `cover_media_id = 99999` | 422 | `The selected cover media does not exist.` |
| ADM-CAT-44 | — | `tags[]` with a bad id | 422 | `One or more selected tags do not exist.` |
| ADM-CAT-45 | — | `gallery_media_ids[]` with a bad id | 422 | `The selected gallery media ids.0 is invalid.` |
| ADM-CAT-46 | — | `attachment_media_ids[]` with a bad id | 422 | `The selected attachment media ids.0 is invalid.` |
| ADM-CAT-47 | — | `trailer_media_id = 99999` | 422 | `The selected trailer media id is invalid.` |
| ADM-CAT-48 | — | `audio_clip_media_id = 99999` | 422 | `The selected audio clip media id is invalid.` |
| ADM-CAT-49 | — | `release_date = "not-a-date"` | 422 | `The release date field must be a valid date.` |
| ADM-CAT-50 | — | `release_label` of 61 chars | 422 | `The release label field must not be greater than 60 characters.` |
| ADM-CAT-51 | — | `excerpt` of 501 chars | 422 | `The excerpt field must not be greater than 500 characters.` |
| ADM-CAT-52 | — | `title` of 256 chars | 422 | `The title field must not be greater than 255 characters.` |
| ADM-CAT-53 | — | `POST` with `status=published` | 302, `published_at` set to now | `Content created successfully.` |
| ADM-CAT-54 | — | `POST` with `status=draft` | 302, `published_at` stays null | `Content created successfully.` |
| ADM-CAT-55 | — | `POST` with `status=pending_review` | 302 | `Content created successfully.` |
| ADM-CAT-56 | Editing the same record | `PUT` keeping its own slug | Succeeds | `Content updated successfully.` |
| ADM-CAT-57 | Content with tags | `PUT` with a different tag set | 302, pivot rows **replaced** (not appended) | `Content updated successfully.` |
| ADM-CAT-58 | Content with gallery media | `PUT` with a different gallery set | 302, `content_media` rows replaced, `sort_order` renumbered | `Content updated successfully.` |
| ADM-CAT-59 | `is_featured = 0` | `PATCH /admin/contents/{id}/feature` | 302, flag toggled, the homepage rail updates | `Featured status updated.` |
| ADM-CAT-60 | `is_featured = 1` | `PATCH …/feature` again | 302, flag toggled back | `Featured status updated.` |
| ADM-CAT-61 | — | `PATCH /admin/contents/{id}/status` with `status=rejected` | 302 | `Content status updated.` |
| ADM-CAT-62 | — | `PATCH …/status` with `status=deleted` | **422**, no custom message | `The selected status is invalid.` |
| ADM-CAT-63 | — | `PATCH …/status` with no status | 422 | `The status field is required.` |
| ADM-CAT-64 | Unpublished content | After `status=published` | Appears on the homepage, `/explore`, its fandom page, and its detail page | — |
| ADM-CAT-65 | Published content | After `status=draft` | Disappears from all four of the above | — |
| ADM-CAT-66 | Published content with a **future** `published_at` | Visit its detail page | **404** — still scheduled | — |
| ADM-CAT-67 | — | `DELETE /admin/contents/{id}` | 302, **hard** delete. There is **no recycle bin for content** (unlike categories). | `Content deleted successfully.` |
| ADM-CAT-68 | Content with a `viewed` history row | `DELETE` | Orphaned history rows do not crash `/user/activity` | — |
| ADM-CAT-69 | Content referenced by a character | `DELETE` | 302, deleted. The character survives with fewer related contents. | `Content deleted successfully.` |
| ADM-CAT-70 | Many contents | Filter by status / category / type, then page | Filters combine and pagination holds | — |
| ADM-CAT-71 | Content with tags | Edit the form | Every tag is pre-selected | — |
| ADM-CAT-72 | `registered-user` | Any `/admin/contents/*` write | 302 + permission error | `You do not have permission to access that page.` |

### 10.3 Characters

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-CAT-73 | Content in category 1 | `POST /admin/characters` with `content_ids` **in category 1** | 302, character + pivots created | `Character created successfully.` |
| ADM-CAT-74 | — | `POST` with **no** name | 422 | `Please enter a character name.` |
| ADM-CAT-75 | — | `name` of 151 chars | 422 | `The name field must not be greater than 150 characters.` |
| ADM-CAT-76 | — | **no** `content_ids` key | 422 | `Please select at least one related content.` |
| ADM-CAT-77 | — | `content_ids = []` | 422 | `A character must belong to at least one content.` |
| ADM-CAT-78 | — | `content_ids = [99999]` | 422 | `One or more selected contents do not exist.` |
| ADM-CAT-79 | `category_id=1`, content in **category 2** | `POST` pairing them | 422 on the **specific index** (`content_ids.0`) | `Selected content does not belong to the chosen category.` |
| ADM-CAT-80 | Mixed valid + invalid contents | `POST` | 422 listing **every** bad index, not just the first | `Selected content does not belong to the chosen category.` (one per index) |
| ADM-CAT-81 | Duplicate slug | `POST` | 422 | `This slug is already taken.` |
| ADM-CAT-82 | Slug of 181 chars | `POST` | 422 | `The slug field must not be greater than 180 characters.` |
| ADM-CAT-83 | — | `POST` with **no** category | 422 | `Please select a category.` |
| ADM-CAT-84 | — | `image_media_id = 99999` | 422 | `The selected image does not exist.` |
| ADM-CAT-85 | Editing the same record | `PUT` with its own slug | Succeeds | `Character updated successfully.` |
| ADM-CAT-86 | Character + content | `PUT` with a new content set | 302, pivots **replaced** | `Character updated successfully.` |
| ADM-CAT-87 | Character linked to content 5 | `POST /admin/characters/{id}/contents` with `content_ids=[6]` | 302, content 6 linked | `Related content attached.` |
| ADM-CAT-88 | — | Attach with no `content_ids` key | 422 | `The content ids field is required.` |
| ADM-CAT-89 | — | Attach with `content_ids = []` | 422 | `The content ids field must be an array.` |
| ADM-CAT-90 | — | Attach `content_ids=[99999]` | 422 | `The selected content ids.0 is invalid.` |
| ADM-CAT-91 | Character linked to content 6 | `DELETE …/contents/6` | 302, pivot removed, **the content itself survives** | `Related content removed.` |
| ADM-CAT-92 | — | Detach a content that is not linked | 302, no-op, **no error** | `Related content removed.` |
| ADM-CAT-93 | Character with content | `DELETE /admin/characters/{id}` | 302. Character deleted, **all of its contents survive** | `Character deleted successfully.` |
| ADM-CAT-94 | Duplicate attachment | Attach content 6 twice | 302, **idempotent** — no duplicate pivot row | `Related content attached.` |
| ADM-CAT-95 | `registered-user` | Any `/admin/characters/*` write | 302 + permission error | `You do not have permission to access that page.` |

### 10.4 Merchandise

No commerce fields are stored anywhere — no price, stock, cart, or checkout.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-CAT-96 | Content in category 1 | `POST /admin/merchandise` with all required fields | 302. **No commerce fields persisted.** | `Merchandise created successfully.` |
| ADM-CAT-97 | — | `POST` with **no** name | 422 | `Please enter a merchandise name.` |
| ADM-CAT-98 | — | `name` of 201 chars | 422 | `The name field must not be greater than 200 characters.` |
| ADM-CAT-99 | — | `POST` with **no** category | 422 | `Please select a category.` |
| ADM-CAT-100 | — | `POST` with **no** content | 422 | `Please select a content. Merchandise must belong to a content.` |
| ADM-CAT-101 | — | `content_id = 99999` | 422 | `The selected content does not exist.` |
| ADM-CAT-102 | `category_id=1`, content in **category 2** | `POST` | 422 on `content_id` | `Selected content does not belong to the chosen category.` |
| ADM-CAT-103 | `tag=limited_edition` / `pre_order` / `collectible` / `standard` | `POST` each | All four succeed | `Merchandise created successfully.` |
| ADM-CAT-104 | — | `tag = cheap` | 422 | `Tag must be limited edition, pre-order, collectible, or standard.` |
| ADM-CAT-105 | — | `tag` missing | 422 | `The tag field is required.` |
| ADM-CAT-106 | `character_id` **not** linked to `content_id` | `POST` | 422 on `character_id` | `Selected character is not related to the chosen content.` |
| ADM-CAT107 | `character_id` linked to `content_id` | `POST` | Succeeds | `Merchandise created successfully.` |
| ADM-CAT-108 | Duplicate slug | `POST` | 422 | `This slug is already taken.` |
| ADM-CAT-109 | Slug of 221 chars | `POST` | 422 | `The slug field must not be greater than 220 characters.` |
| ADM-CAT-110 | — | `image_media_id = 99999` | 422 | `The selected image does not exist.` |
| ADM-CAT-111 | `is_upcoming=1` | `POST` | 302. The public item page shows an upcoming status and still has no commerce action. | `Merchandise created successfully.` |
| ADM-CAT-112 | Existing item | `PUT /admin/merchandise/{id}` | 302 | `Merchandise updated successfully.` |
| ADM-CAT-113 | Editing the same record | `PUT` keeping its own slug | Succeeds | `Merchandise updated successfully.` |
| ADM-CAT-114 | — | `DELETE /admin/merchandise/{id}` | 302. Item removed; the linked content **survives** | `Merchandise deleted successfully.` |
| ADM-CAT-115 | — | Filter by category / tag / upcoming | Filters combine correctly | — |
| ADM-CAT-116 | `registered-user` | Any `/admin/merchandise/*` write | 302 + permission error | `You do not have permission to access that page.` |

### 10.5 Events

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-CAT-117 | — | `POST /admin/events` with a valid payload | 302, event created | `Event created successfully.` |
| ADM-CAT-118 | — | `POST` with **no** title | 422 | `Please enter an event title.` |
| ADM-CAT-119 | — | `title` of 201 chars | 422 | `The title field must not be greater than 200 characters.` |
| ADM-CAT-120 | — | `POST` with **no** city | 422 | `Please enter a city.` |
| ADM-CAT-121 | — | `city` of 101 chars | 422 | `The city field must not be greater than 100 characters.` |
| ADM-CAT-122 | — | `POST` with **no** start date | 422 | `Please enter a start date and time.` |
| ADM-CAT-123 | — | `POST` with **no** category | 422 | `Please select a category.` |
| ADM-CAT-124 | `start_at = 2026-10-01 10:00`, `end_at = 2026-10-01 12:00` | `POST` | Succeeds (end after start) | `Event created successfully.` |
| ADM-CAT-125 | `end_at` **before** `start_at` | `POST` | 422 | `End date and time cannot be before the start.` |
| ADM-CAT-126 | `end_at` **equal** to `start_at` | `POST` | Succeeds — the rule is `after_or_equal` | `Event created successfully.` |
| ADM-CAT-127 | `end_at` empty | `POST` | Succeeds — `end_at` is nullable | `Event created successfully.` |
| ADM-CAT-128 | `latitude = 91` | `POST` | 422 | `Latitude must be between -90 and 90.` |
| ADM-CAT-129 | `latitude = -91` | `POST` | 422 | `Latitude must be between -90 and 90.` |
| ADM-CAT-130 | `longitude = 181` | `POST` | 422 | `Longitude must be between -180 and 180.` |
| ADM-CAT-131 | `latitude = -90`, `longitude = -180` | `POST` | Succeeds — boundaries are inclusive | `Event created successfully.` |
| ADM-CAT-132 | `latitude = "abc"` | `POST` | 422 | `The latitude field must be a number.` |
| ADM-CAT-133 | `ticket_url = not-a-url` | `POST` | 422 | `Ticket URL must be a valid URL.` |
| ADM-CAT-134 | `ticket_url = javascript:alert(1)` | `POST` | 422 — only `http`/`https` are accepted | `Ticket URL must be a valid URL.` |
| ADM-CAT-135 | `ticket_url` 501 chars | `POST` | 422 | `The ticket url field must not be greater than 500 characters.` |
| ADM-CAT-136 | `google_maps_location = not-a-url` | `POST` | 422 | `The google maps location field must be a valid URL.` |
| ADM-CAT-137 | `event_type = spaceflight` | `POST` | 422 | `The selected event type is invalid.` |
| ADM-CAT-138 | Each of `convention, meetup, screening, premiere, gaming, festival, cosplay, community` | `POST` each | All eight succeed | `Event created successfully.` |
| ADM-CAT-139 | `event_type` empty | `POST` | Succeeds — nullable | `Event created successfully.` |
| ADM-CAT-140 | `status = archived` | `POST` | 422 | `Status must be draft, published, or cancelled.` |
| ADM-CAT-141 | `status` missing | `POST` | 422 | `The status field is required.` |
| ADM-CAT-142 | `gallery_media_ids` with 13 entries | `POST` | 422 | `The gallery media ids field must not have more than 12 items.` |
| ADM-CAT-143 | `gallery_media_ids` with a duplicate | `POST` | 422 | `The gallery media ids.0 field is distinct.` |
| ADM-CAT-144 | `gallery_media_ids` pointing at a **non-image** media row | `POST` | 422 | `The selected gallery media ids.0 is invalid.` |
| ADM-CAT-145 | `content_id` from another category | `POST` | 422 | `Selected content does not belong to the chosen category.` |
| ADM-CAT-146 | `content_id = 99999` | `POST` | 422 | `The selected content does not exist.` |
| ADM-CAT-147 | `cover_media_id = 99999` | `POST` | 422 | `The selected cover media does not exist.` |
| ADM-CAT-148 | `popularity_score = -1` | `POST` | 422 | `The popularity score field must be at least 0.` |
| ADM-CAT-149 | `popularity_score = 1000001` | `POST` | 422 | `The popularity score field must not be greater than 1000000.` |
| ADM-CAT-150 | `short_description` of 401 chars | `POST` | 422 | `The short description field must not be greater than 400 characters.` |
| ADM-CAT-151 | `venue` of 256 chars | `POST` | 422 | `The venue field must not be greater than 255 characters.` |
| ADM-CAT-152 | Existing event | `PUT /admin/events/{id}` | 302, updated | `Event updated successfully.` |
| ADM-CAT-153 | Published event | `PUT` with `status=cancelled` | 302. **Still visible to admins** in the admin list. | `Event updated successfully.` |
| ADM-CAT-154 | Cancelled event | Visit `/events` (public) | **Hidden** from listings, featured rails, and the count | — |
| ADM-CAT-155 | Draft event | Visit `/events/{slug}` | **404** | Laravel 404 page |
| ADM-CAT-156 | Published event | Visit `/events/{slug}/calendar` | 200, `text/calendar` iCalendar body download | — |
| ADM-CAT-157 | Draft or cancelled event | Visit `/events/{slug}/calendar` | **404** | Laravel 404 page |
| ADM-CAT-158 | Event with an `end_at` | Download the calendar file | Both `DTSTART` and `DTEND` are present and correct | — |
| ADM-CAT-159 | Existing event | `DELETE /admin/events/{id}` | 302 | `Event deleted successfully.` |
| ADM-CAT-160 | `registered-user` | Any `/admin/events/*` write | 302 + permission error | `You do not have permission to access that page.` |

### 10.6 Relation lookups (JSON — no flash, no `message` key)

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-CAT-161 | Category 1 with contents | `GET /admin/categories/1/contents` | 200 JSON array of contents | No `message` key |
| ADM-CAT-162 | Content 5 with characters | `GET /admin/contents/5/characters` | 200 JSON array | No `message` key |
| ADM-CAT-163 | Category 1 | `GET /admin/categories/1/characters` | 200 JSON array | No `message` key |
| ADM-CAT-164 | — | Any of the three with an unknown id | **404** JSON | `{"message":"No query results for model [App\Models\…] 999."}` |
| ADM-CAT-165 | Category with 0 related rows | Query it | 200 with an **empty array**, not `null` and not a 404 | `[]` |
| ADM-CAT-166 | `registered-user` | Any lookup | 302 + permission error | `You do not have permission to access that page.` |

---

## 11. Admin moderation

### 11.1 Submissions queue

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-MOD-01 | 4 pending user submissions | `GET /admin/submissions` | 200, all four queued | — |
| ADM-MOD-02 | Pending submission | `GET /admin/submissions/{id}` | 200, preview renders | — |
| ADM-MOD-03 | Pending submission | `PATCH /admin/submissions/{id}/approve` | 302. `status=published`, `reviewed_by` set, and the **submitter is emailed**. | `Submission approved and published.` |
| ADM-MOD-04 | Pending submission | `PATCH /admin/submissions/{id}/reject` | 302. `status=rejected`, `reviewed_by` set, submitter emailed. | `Submission rejected.` ⚠️ flashed under the **`success`** key, not `error` |
| ADM-MOD-05 | Already-published submission | `PATCH …/approve` again | 302, **no second email** (the status did not change) | `Submission approved and published.` |
| ADM-MOD-06 | Already-rejected submission | `PATCH …/reject` again | 302, **no second email** | `Submission rejected.` |
| ADM-MOD-07 | Submission with **no submitter email** | Approve | 302, moderates fine. **No email attempted, no error, no crash.** | `Submission approved and published.` |
| ADM-MOD-08 | Mailer throws | Approve | Moderation still commits; a `Log::warning` is written. The user sees success. | `Submission approved and published.` |
| ADM-MOD-09 | Admin-created content (`is_user_submitted = 0`) | `GET /admin/submissions/{id}` | **404** | Laravel 404 page |
| ADM-MOD-10 | Admin-created content | `PATCH …/approve` or `…/reject` | **404** — admins cannot moderate their own content through the queue | Laravel 404 page |
| ADM-MOD-11 | `registered-user` | `PATCH …/approve` | 302 + permission error | `You do not have permission to access that page.` |
| ADM-MOD-12 | Approved submission | Visit `/stories/{slug}` | Now publicly visible | — |
| ADM-MOD-13 | Approved submission | Author visits `/user/submissions` | Status now `published`; the edit form returns 403 | `Published submissions are managed by moderators.` |
| ADM-MOD-14 | Rejected submission | Author visits `/user/submissions` | Status reads `rejected`, with the reviewer's name shown | — |
| ADM-MOD-15 | Queue with many items | Filter by status / category | Filters combine and paginate | — |

### 11.2 Reviews

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-MOD-16 | Pending reviews | `GET /admin/reviews` | 200, filterable by status and target | — |
| ADM-MOD-17 | Review 7 | `GET /admin/reviews/7` | 200, detail renders | — |
| ADM-MOD-18 | Review whose target was deleted | `GET /admin/reviews/7` | **404** — the page must not crash on a missing target | Laravel 404 page |
| ADM-MOD-19 | Pending review | `PATCH /admin/reviews/7/approve` | 302, `status=approved` | `Review approved.` |
| ADM-MOD-20 | Pending review | `PATCH /admin/reviews/7/reject` | 302, `status=rejected` | `Review rejected.` |
| ADM-MOD-21 | Approved review | Visit the story | The review appears publicly **on every page** that renders reviews | — |
| ADM-MOD-22 | Rejected review | Visit the story | Not public. The author still sees it on `/user/reviews`. | — |
| ADM-MOD-23 | Review 7 | `DELETE /admin/reviews/7` | 302, removed from the story page | `Review deleted successfully.` |
| ADM-MOD-24 | Review 99999 | `DELETE` | **404** | Laravel 404 page |
| ADM-MOD-25 | `registered-user` | Any `/admin/reviews/*` write | 302 + permission error | `You do not have permission to access that page.` |

### 11.3 Ratings

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-MOD-26 | Ratings exist | `GET /admin/ratings` | 200, filterable by type and target | — |
| ADM-MOD-27 | Rating 3 | `DELETE /admin/ratings/3` | 302, removed; the story's star average recomputes | `Rating deleted successfully.` |
| ADM-MOD-28 | Rating 99999 | `DELETE` | **404** | Laravel 404 page |
| ADM-MOD-29 | Only one rating existed | Delete it, then check the story page | The average block shows an empty state, not `0` stars or a division error | — |
| ADM-MOD-30 | `registered-user` | `DELETE` | 302 + permission error | `You do not have permission to access that page.` |

### 11.4 Feedback

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-MOD-31 | Feedback in all 4 states | `GET /admin/feedback` | 200, filterable | — |
| ADM-MOD-32 | Feedback 2 | `GET /admin/feedback/2` | 200, detail renders | — |
| ADM-MOD-33 | `status=open` | `PATCH /admin/feedback/2/status` | 302, status changed. **No email** — only `resolved` triggers one. | `Feedback status updated.` |
| ADM-MOD-34 | `status=in_review` | `PATCH` | 302 | `Feedback status updated.` |
| ADM-MOD-35 | `status=resolved` | `PATCH` | 302, status changed **and the reporter is emailed** (only when the status actually changed) | `Feedback status updated.` |
| ADM-MOD-36 | Already `resolved` | `PATCH` with `resolved` again | 302, **no second email** | `Feedback status updated.` |
| ADM-MOD-37 | `status=closed` | `PATCH` | 302 — no email | `Feedback status updated.` |
| ADM-MOD-38 | — | `status=banished` | **422**, no custom message | `The selected status is invalid.` |
| ADM-MOD-39 | — | `status` missing | 422 | `The status field is required.` |
| ADM-MOD-40 | Reporter has no email | `PATCH` with `resolved` | 302, no email attempted, no error | `Feedback status updated.` |
| ADM-MOD-41 | Mailer throws | `PATCH` with `resolved` | 302, the status persists, a `Log::warning` is written | `Feedback status updated.` |
| ADM-MOD-42 | Feedback 2 | `DELETE /admin/feedback/2` | 302 | `Feedback deleted successfully.` |
| ADM-MOD-43 | Feedback 99999 | `DELETE` | **404** | Laravel 404 page |
| ADM-MOD-44 | `registered-user` | Any `/admin/feedback/*` write | 302 + permission error | `You do not have permission to access that page.` |

---

## 12. Admin people

### 12.1 Users

`Route::resource('users')->only(['index','create','store','show','destroy'])` — **there is no
`admin.users.edit` or `admin.users.update` route.** Admins are edited via their own `/profile`.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-PPL-01 | 10 seeded users | `GET /admin/users` | 200, list + filters (search, role) | — |
| ADM-PPL-02 | — | `GET /admin/users/{id}` | 200, detail with related activity | — |
| ADM-PPL-03 | — | `GET /admin/users/create` | 200 | — |
| ADM-PPL-04 | — | `POST /admin/users` valid | 302. The `admin` role is `firstOrCreate`d if missing, then assigned. | `Admin user created successfully.` ⚠️ says "Admin user", not "User" |
| ADM-PPL-05 | — | `POST` with **no** name | 422 | `The name field is required.` |
| ADM-PPL-06 | — | Name of 256 chars | 422 | `The name field must not be greater than 255 characters.` |
| ADM-PPL-07 | Email exists | `POST` with a duplicate email | 422 | `The email has already been taken.` |
| ADM-PPL-08 | — | `POST` with a malformed email | 422 | `The email field must be a valid email address.` |
| ADM-PPL-09 | — | `POST` with a 7-char password | 422 | `The password field must be at least 8 characters.` |
| ADM-PPL-10 | — | `POST` without a password confirmation | 422 | `The password field confirmation does not match.` |
| ADM-PPL-11 | — | `POST` with no password | 422 | `The password field is required.` |
| ADM-PPL-12 | Admin A, signed in | `DELETE /admin/users/{A's own id}` | **302, refused.** Nothing is deleted. | `You cannot delete your own account.` |
| ADM-PPL-13 | Admin A, signed in | `DELETE /admin/users/{B's id}` | 302, deleted | `User deleted successfully.` |
| ADM-PPL-14 | User 99999 | `DELETE` | **404** | Laravel 404 page |
| ADM-PPL-15 | — | `GET /admin/users/{id}/edit` | **404 — no such route.** Admins use `/profile`. | Laravel 404 page |
| ADM-PPL-16 | — | `PUT /admin/users/{id}` | **404** | Laravel 404 page |
| ADM-PPL-17 | — | `POST /admin/users/{id}` | **405 Method Not Allowed** | Laravel 405 page |
| ADM-PPL-18 | `registered-user` | Any `/admin/users/*` write | 302 + permission error | `You do not have permission to access that page.` |
| ADM-PPL-19 | Deleted user | `GET /admin/users/{deleted id}` | **404** | Laravel 404 page |
| ADM-PPL-20 | An `editor` account | Assign it the `admin` role via the API, then reload | The role change is reflected in `/admin/users` filters | — |

### 12.2 Contacts

`Route::resource('contacts')->only(['index','show','destroy'])`.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-PPL-21 | 10 seeded contacts | `GET /admin/contacts` | 200, list + filters (search, status, date range) | — |
| ADM-PPL-22 | Contact with `status = new` | `GET /admin/contacts/{id}` | 200. **Silently** flips to `read` — no message shown. | — |
| ADM-PPL-23 | Contact already `read` | `GET /admin/contacts/{id}` | 200, no further change | — |
| ADM-PPL-24 | Contact with `status = replied` | `GET /admin/contacts/{id}` | 200, status left as `replied` — only `new` is auto-read | — |
| ADM-PPL-25 | Contact 1 | `DELETE /admin/contacts/1` | 302 | `Contact message deleted successfully.` |
| ADM-PPL-26 | Contact 99999 | `DELETE` | **404** | Laravel 404 page |
| ADM-PPL-27 | — | `GET /admin/contacts/create` | **404** — no create route; contacts come from the public form | Laravel 404 page |
| ADM-PPL-28 | — | `POST /admin/contacts` | **405 Method Not Allowed** | Laravel 405 page |
| ADM-PPL-29 | — | `PUT /admin/contacts/{id}` | **405 Method Not Allowed** | Laravel 405 page |
| ADM-PPL-30 | Statuses `new` / `read` / `replied` | Filter by status | Counts and rows match | — |
| ADM-PPL-31 | — | Export contacts | **No export route exists** for contacts. Only subscribers, activity logs, and chatbot queries export. | — |
| ADM-PPL-32 | `registered-user` | `DELETE` | 302 + permission error | `You do not have permission to access that page.` |
| ADM-PPL-33 | A new contact is submitted publicly | Visit `/admin/contacts` | It appears with `status = new` and the IP address is recorded | — |

### 12.3 Subscribers

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-PPL-34 | 10 subscribers | `GET /admin/subscribers` | 200, list + stats (Total / Active / Unsubscribed) | — |
| ADM-PPL-35 | Subscriber 1 | `GET /admin/subscribers/1` | 200, detail | — |
| ADM-PPL-36 | Subscriber 1 | `GET /admin/subscribers/1/edit` | 200, form | — |
| ADM-PPL-37 | — | `PUT /admin/subscribers/1` with a new name and status | 302 | `Subscriber updated successfully.` |
| ADM-PPL-38 | Editing the same record | `PUT` keeping its own values | Succeeds | `Subscriber updated successfully.` |
| ADM-PPL-39 | — | `PUT` with `status=pending` | 422 | `The selected status is invalid.` |
| ADM-PPL-40 | — | `PUT` with no `status` | 422 | `The status field is required.` |
| ADM-PPL-41 | — | `name` of 256 chars | 422 | `The name field must not be greater than 255 characters.` |
| ADM-PPL-42 | — | `PUT` with `preferences.categories[]` bad id | 422 | `The selected preferences.categories.0 is invalid.` |
| ADM-PPL-43 | Active subscriber | `PATCH …/status` with `unsubscribed` | 302. `unsubscribed_at` set (only if it was previously null) | `Subscriber status updated.` |
| ADM-PPL-44 | Unsubscribed | `PATCH …/status` with `active` | 302. `unsubscribed_at` cleared, `subscribed_at` backfilled if null, and **a fresh unsubscribe token is issued** | `Subscriber status updated.` |
| ADM-PPL-45 | — | `PATCH …/status` with `bounced` | 302, status stored | `Subscriber status updated.` |
| ADM-PPL-46 | — | `PATCH …/status` with `complained` | 302, status stored | `Subscriber status updated.` |
| ADM-PPL-47 | — | `PATCH …/status` with no status | 422 | `The status field is required.` |
| ADM-PPL-48 | 12 selected | `POST /admin/subscribers/bulk` with `action=delete` | 302, 12 rows deleted | `Bulk action 'delete' applied to 12 subscriber(s).` ⚠️ dynamic, `(s)` is literal |
| ADM-PPL-49 | — | `bulk` with `action=activate` | 302, all reactivated | `Bulk action 'activate' applied to 12 subscriber(s).` |
| ADM-PPL-50 | — | `bulk` with `action=unsubscribe` | 302 | `Bulk action 'unsubscribe' applied to 12 subscriber(s).` |
| ADM-PPL-51 | — | `bulk` with `action=bounce` | 302 | `Bulk action 'bounce' applied to 12 subscriber(s).` |
| ADM-PPL-52 | — | `bulk` with `action=complain` | 302 | `Bulk action 'complain' applied to 12 subscriber(s).` |
| ADM-PPL-53 | 1 selected | `bulk` with `action=delete` | 302 — still uses the literal `(s)` | `Bulk action 'delete' applied to 1 subscriber(s).` |
| ADM-PPL-54 | — | `bulk` with `action=explode` | 422 | `The selected action is invalid.` |
| ADM-PPL-55 | — | `bulk` with no `action` | 422 | `The action field is required.` |
| ADM-PPL-56 | — | `bulk` with no `subscriber_ids` | 422 | `The subscriber ids field is required.` |
| ADM-PPL-57 | — | `bulk` with `subscriber_ids=[99999]` | 422 | `The selected subscriber ids.0 is invalid.` |
| ADM-PPL-58 | — | `GET /admin/subscribers/export` | 200 CSV. Filename `subscribers-YYYY-MM-DD-HHMMSS.csv`; header `Email,Name,Status,Subscribed At,IP Address,Categories` | — |
| ADM-PPL-59 | Filters active | `export` with the same filters | **The export respects the filters** | — |
| ADM-PPL-60 | A subscriber whose name contains a comma or a newline | `export` | The CSV stays well-formed — quoting is applied, rows do not merge | — |
| ADM-PPL-61 | Subscriber 1 | `DELETE /admin/subscribers/1` | 302 | `Subscriber deleted.` ⚠️ no "successfully" |
| ADM-PPL-62 | Subscriber 99999 | `DELETE` | **404** | Laravel 404 page |
| ADM-PPL-63 | `registered-user` | Any `/admin/subscribers/*` write | 302 + permission error | `You do not have permission to access that page.` |
| ADM-PPL-64 | A status filter is active | `GET /admin/subscribers` | The stat cards reflect the filtered set, or clearly the global set — **be explicit about which**, and assert it | — |

---

## 13. Admin system & tools

### 13.1 Media library

Limits: 10 files per batch, 512 MB per file. Type limits are enforced per **category of file**.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-01 | 10 seeded media | `GET /admin/media` | 200, with search and MIME filter | — |
| ADM-SYS-02 | — | `POST /admin/media` with 3 valid files | 302, 3 `media` rows created | `Files uploaded successfully.` |
| ADM-SYS-03 | — | `POST` with 10 valid files (boundary) | 302, all 10 created | `Files uploaded successfully.` |
| ADM-SYS-04 | — | `POST` with no `files` key | 422 | `The files field is required.` |
| ADM-SYS-05 | — | `POST` with `files` as a string | 422 | `The files field must be an array.` |
| ADM-SYS-06 | — | `POST` with 11 files | 422 | `The files field must not have more than 10 items.` |
| ADM-SYS-07 | — | `POST` with a `.exe` | 302 back. **The whole batch is aborted on the first bad file** — the good files are not saved. | `File type .exe is not allowed.` |
| ADM-SYS-08 | — | `POST` with a 600 MB file | 302 back, batch aborted | `File "movie.mp4" exceeds the limit for video.` |
| ADM-SYS-09 | Disk write throws | `POST` with a valid file | 302 back, input preserved, error on `files` | `Failed to upload "poster.png". Please try again.` |
| ADM-SYS-10 | — | `POST` with `category_id` soft-deleted | 422 | `The selected category id is invalid.` |
| ADM-SYS-11 | — | `POST` with `category_id = 99999` | 422 | `The selected category id is invalid.` |
| ADM-SYS-12 | — | `POST` with **no** category | 302, saved as **general** (uncategorised) | `Files uploaded successfully.` |
| ADM-SYS-13 | — | `POST` with `duration_hours=24` | 422 | `The duration hours field must be between 0 and 23.` |
| ADM-SYS-14 | — | `POST` with `duration_hours=-1` | 422 | `The duration hours field must be between 0 and 23.` |
| ADM-SYS-15 | — | `POST` with `duration_minutes=60` | 422 | `The duration minutes field must be between 0 and 59.` |
| ADM-SYS-16 | — | `POST` with `duration_seconds=60` | 422 | `The duration seconds field must be between 0 and 59.99.` |
| ADM-SYS-17 | — | `POST` with `duration_hours=1, duration_minutes=30, duration_seconds=45` | 302, `duration` stored as `5400.00` seconds | `Files uploaded successfully.` |
| ADM-SYS-18 | — | `POST` with `alt_text` of 256 chars | 422 | `The alt text field must not be greater than 255 characters.` |
| ADM-SYS-19 | Media 3 | `GET /admin/media/3` | 200 — this route is an **alias of `edit`**, so it renders the **edit form**, not a read-only view. | — |
| ADM-SYS-20 | Media 3 | `GET /admin/media/3/edit` | 200, edit form with a category selector | — |
| ADM-SYS-21 | Media 3 | `PUT /admin/media/3` with new alt text | 302 | `Media updated successfully.` |
| ADM-SYS-22 | General media | `PUT` with a `category_id` | 302, media assigned to that category | `Media updated successfully.` |
| ADM-SYS-23 | Categorised media | `PUT` with no `category_id` | 302, media returned to **general** | `Media updated successfully.` |
| ADM-SYS-24 | Media 3 | `GET /admin/media/3/download` | 200, file download | — |
| ADM-SYS-25 | Media row exists, file missing on disk | `download` | **404** | Laravel 404 page |
| ADM-SYS-26 | Media row with a null or invalid path | `download` | **404** | Laravel 404 page |
| ADM-SYS-27 | Media 99999 | `download` | **404** | Laravel 404 page |
| ADM-SYS-28 | **Unreferenced** media | `DELETE /admin/media/3` | 302, file and row both deleted | `File deleted successfully.` |
| ADM-SYS-29 | Media referenced by a category, content, character, merchandise, or event | `DELETE` | **302, refused.** The file survives. | `This file is still referenced by other records and cannot be deleted.` |
| ADM-SYS-30 | Search + category filter | `GET /admin/media?category_id=2` | 200 — that category's media **plus** general media | — |
| ADM-SYS-31 | — | `GET /admin/media?q=hero` | 200, filtered by filename | — |
| ADM-SYS-32 | — | `GET /admin/media?type=image` / `video` / `audio` | 200, filtered by MIME type | — |
| ADM-SYS-33 | `registered-user` | Any `/admin/media/*` write | 302 + permission error | `You do not have permission to access that page.` |
| ADM-SYS-34 | Guest | `POST /admin/media` | 302 → `/login` | — |
| ADM-SYS-35 | — | `GET /admin/media/chunk` (GET on a chunk path) | Resolves to `media.show` — beware this route-ordering collision when writing tests | — |

### 13.2 Chunked upload (4 JSON endpoints, no flash)

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-36 | — | `POST /admin/media/chunk/init` with a valid descriptor | 200, `media` row with `status=uploading` and `total_chunks` set | No `message` key |
| ADM-SYS-37 | — | `init` with `filename` missing | 422 | `{"message":"The filename field is required.","errors":{…}}` |
| ADM-SYS-38 | — | `init` with `filename` 256 chars | 422 | `{"message":"The filename field must not be greater than 255 characters."}` |
| ADM-SYS-39 | — | `init` with `total_size=0` | 422 | `{"message":"The total size field must be at least 1."}` |
| ADM-SYS-40 | — | `init` with `total_chunks=1001` | 422 | `{"message":"The total chunks field must not be greater than 1000."}` |
| ADM-SYS-41 | — | `init` with `chunk_size=500000` (below 1 MB) | 422 | `{"message":"The chunk size field must be at least 1048576."}` |
| ADM-SYS-42 | — | `init` with `chunk_size=20971520` (above 10 MB) | 422 | `{"message":"The chunk size field must not be greater than 10485760."}` |
| ADM-SYS-43 | — | `init` with `mime_type` missing | 422 | `{"message":"The mime type field is required."}` |
| ADM-SYS-44 | — | `init` with `category_id` soft-deleted | 422 | `{"message":"The selected category id is invalid."}` |
| ADM-SYS-45 | Init'd upload, 5 chunks | `POST /admin/media/chunk/upload` chunk 0 | 200, `uploaded_chunks` increments, bytes appended | — |
| ADM-SYS-46 | Init'd upload | Upload chunks 0–4 | `uploaded_chunks == total_chunks` | — |
| ADM-SYS-47 | — | `upload` with `media_id=99999` | 422 | `{"message":"The selected media id is invalid."}` |
| ADM-SYS-48 | — | `upload` with `chunk_index` missing | 422 | `{"message":"The chunk index field is required."}` |
| ADM-SYS-49 | — | `upload` with `chunk_index=-1` | 422 | `{"message":"The chunk index field must be at least 0."}` |
| ADM-SYS-50 | — | `upload` with no `chunk` file | 422 | `{"message":"The chunk field is required."}` |
| ADM-SYS-51 | Already `complete` | `upload` another chunk | **400** | `Upload not in progress` |
| ADM-SYS-52 | Already `cancelled` | `upload` a chunk | **400** | `Upload not in progress` |
| ADM-SYS-53 | All chunks uploaded | `POST /admin/media/chunk/complete` | 200, `status=complete`, the file is assembled on disk | `{"message":"Upload completed successfully"}` ⚠️ **no trailing period** |
| ADM-SYS-54 | 3 of 5 chunks uploaded | `complete` | **400** — refuses to finalise an incomplete upload | `Not all chunks uploaded` |
| ADM-SYS-55 | Already `complete` | `complete` again | **400** | `Upload not in progress` |
| ADM-SYS-56 | — | `complete` with `media_id=99999` | 422 | `{"message":"The selected media id is invalid."}` |
| ADM-SYS-57 | — | `complete` with `duration_hours=99` | 422 | `{"message":"The duration hours field must be between 0 and 23."}` |
| ADM-SYS-58 | In-progress upload | `POST /admin/media/chunk/cancel` | 200, the partial file is removed and the row discarded | `{"message":"Upload cancelled"}` |
| ADM-SYS-59 | — | `cancel` with no `media_id` | 422 | `{"message":"The media id field is required."}` |
| ADM-SYS-60 | Abandoned upload | Cancel, then re-init with the same filename | 200 — a fresh upload row is created cleanly | — |
| ADM-SYS-61 | Init'd upload | Cancel, then `complete` | **400** | `Upload not in progress` |
| ADM-SYS-62 | `registered-user` | Any chunk endpoint | 302 + permission error | `You do not have permission to access that page.` |
| ADM-SYS-63 | Init'd upload | Inspect the browser tab after a partial upload | The in-progress row is `status=uploading` with `uploaded_chunks` < `total_chunks`, and the partial file is **not** listed in `/admin/media` | — |

### 13.3 Settings

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-64 | 23 seeded settings | `GET /admin/settings` | 200, grouped: General / SEO / Social / Mail | — |
| ADM-SYS-65 | — | `PUT /admin/settings` with a valid map | 302, values stored, the `settings` **cache is cleared** | `Settings updated successfully.` ⚠️ plural "Settings" |
| ADM-SYS-66 | — | `PUT` with an **unknown** key in the map | 302, the unknown key is **silently skipped**. No error, no row created. | `Settings updated successfully.` |
| ADM-SYS-67 | `mail_password` is set | `PUT` with `mail_password` **empty** | 302, the existing password is **preserved**, not wiped | `Settings updated successfully.` |
| ADM-SYS-68 | — | `PUT` with no `settings` key | 422 | `The settings field is required.` |
| ADM-SYS-69 | — | `PUT` with `settings` as a string | 422 | `The settings field must be an array.` |
| ADM-SYS-70 | — | `POST /admin/settings` valid | 302, row created, cache cleared | `Setting created successfully.` ⚠️ singular |
| ADM-SYS-71 | Key exists | `POST` the same key | 422 | `The key has already been taken.` |
| ADM-SYS-72 | — | `POST` with `type=color` | 422 | `The selected type is invalid.` (allowed: `text, textarea, number, boolean, image, json`) |
| ADM-SYS-73 | — | `POST` with no `group` | 422 | `The group field is required.` |
| ADM-SYS-74 | — | `POST` with `group` 256 chars | 422 | `The group field must not be greater than 255 characters.` |
| ADM-SYS-75 | — | `POST` with no `key` | 422 | `The key field is required.` |
| ADM-SYS-76 | — | `POST` with no `type` | 422 | `The type field is required.` |
| ADM-SYS-77 | Setting 5 | `DELETE /admin/settings/5` | 302, deleted, cache cleared | `Setting deleted successfully.` |
| ADM-SYS-78 | Setting 99999 | `DELETE` | **404** | Laravel 404 page |
| ADM-SYS-79 | Update `site_name` | Reload the public homepage | The new name is used site-wide | — |
| ADM-SYS-80 | Update `meta_title` / `meta_description` | View the homepage `<head>` | The new values are rendered | — |
| ADM-SYS-81 | Update `mail_from_address` | Trigger a notification email | The new from-address is used | — |
| ADM-SYS-82 | Update `mail_additional_emails` to a valid JSON array | Trigger a notification | All additional addresses receive it | — |
| ADM-SYS-83 | Update `mail_additional_emails` to malformed JSON | Trigger a notification | No crash. The bad value is ignored; the mail still sends to the primary address. | — |
| ADM-SYS-84 | — | `PUT` then immediately read `Setting::get()` | The **cache is cleared**, so the new value is returned — no stale cache | — |
| ADM-SYS-85 | `registered-user` | Any `/admin/settings/*` write | 302 + permission error | `You do not have permission to access that page.` |

### 13.4 Roles

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-86 | — | `GET /admin/roles` | 200, list of the 10 seeded roles | — |
| ADM-SYS-87 | — | `GET /admin/roles/create` | 200 | — |
| ADM-SYS-88 | — | `POST /admin/roles` valid | 302, slug stored as given (no auto-derivation) | `Role created successfully.` |
| ADM-SYS-89 | — | `POST` with no name | 422 | `The name field is required.` |
| ADM-SYS-90 | — | `POST` with no slug | 422 | `The slug field is required.` |
| ADM-SYS-91 | — | `name` of 51 chars | 422 | `The name field must not be greater than 50 characters.` |
| ADM-SYS-92 | `admin` exists | `POST` with `name=Admin` | 422 | `The name has already been taken.` |
| ADM-SYS-93 | `admin` exists | `POST` with `slug=admin` | 422 | `The slug has already been taken.` |
| ADM-SYS-94 | — | `slug` of 51 chars | 422 | `The slug field must not be greater than 50 characters.` |
| ADM-SYS-95 | — | `description` of 501 chars | 422 | `The description field must not be greater than 500 characters.` |
| ADM-SYS-96 | Role 3 | `GET /admin/roles/3/edit` | 200. **The slug is not editable** — absent or read-only. | — |
| ADM-SYS-97 | Role 3 | `PUT /admin/roles/3` | 302, name and description updated, **slug unchanged even if a new one is posted** | `Role updated successfully.` |
| ADM-SYS-98 | Editing the same role | `PUT` keeping its own name | Succeeds — `unique` ignores itself | `Role updated successfully.` |
| ADM-SYS-99 | **Unassigned** role | `DELETE /admin/roles/{id}` | 302, deleted | `Role deleted successfully.` |
| ADM-SYS-100 | Role with **users assigned** | `DELETE /admin/roles/{id}` | **302, refused.** The role and its pivot rows survive. | `Cannot delete role with assigned users.` |
| ADM-SYS-101 | The `admin` role, which has users | `DELETE /admin/roles/1` | **302, refused** | `Cannot delete role with assigned users.` |
| ADM-SYS-102 | Role 99999 | `DELETE` | **404** | Laravel 404 page |
| ADM-SYS-103 | — | `GET /admin/roles/3` (show) | **404** — `show` is excluded from the resource | Laravel 404 page |
| ADM-SYS-104 | — | `POST /admin/roles/{id}` | **405 Method Not Allowed** | Laravel 405 page |
| ADM-SYS-105 | Delete a role, then assign a user to it | — | A **new** role row is created by `firstOrCreate` rather than failing | — |
| ADM-SYS-106 | `registered-user` | Any `/admin/roles/*` write | 302 + permission error | `You do not have permission to access that page.` |

### 13.5 Tags

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-107 | 10 seeded tags | `GET /admin/tags` | 200, with stats (Total Tags / Categories Tagged) | — |
| ADM-SYS-108 | — | `GET /admin/tags/create` | 200 | — |
| ADM-SYS-109 | — | `POST /admin/tags` with a new name | 302, slug auto-derived via `Str::slug` | `Tag created successfully.` |
| ADM-SYS-110 | — | `POST` with a name containing spaces/punctuation | 302, the slug is slugified, the original name is preserved | `Tag created successfully.` |
| ADM-SYS-111 | `Action` exists | `POST` with `name=Action` | 422 | `The name has already been taken.` |
| ADM-SYS-112 | — | `POST` with no name | 422 | `The name field is required.` |
| ADM-SYS-113 | — | `name` of 256 chars | 422 | `The name field must not be greater than 255 characters.` |
| ADM-SYS-114 | Tag 3 | `GET /admin/tags/3/edit` | 200 | — |
| ADM-SYS-115 | Tag 3 | `PUT /admin/tags/3` | 302, slug re-derived from the new name | `Tag updated successfully.` |
| ADM-SYS-116 | Editing the same tag | `PUT` keeping its own name | Succeeds | `Tag updated successfully.` |
| ADM-SYS-117 | Tag attached to categories | `DELETE /admin/tags/3` | 302. **Pivots are detached first**, then the tag is deleted — the categories **survive** and remain fully functional. | `Tag deleted successfully.` |
| ADM-SYS-118 | Tag 99999 | `DELETE` | **404** | Laravel 404 page |
| ADM-SYS-119 | — | `GET /admin/tags/3` (show) | **404** — `show` is excluded | Laravel 404 page |
| ADM-SYS-120 | Search active | `GET /admin/tags?q=sci` | 200, filtered; the **Reset** button clears it | — |
| ADM-SYS-121 | Delete a tag, then re-create it with the same name | — | A new tag with a new id is created and re-attach cleanly | `Tag created successfully.` |
| ADM-SYS-122 | `registered-user` | Any `/admin/tags/*` write | 302 + permission error | `You do not have permission to access that page.` |

### 13.6 Activity logs

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-123 | — | Create a model | An `activity_logs` row with `event=created`, a subject, and a description | — |
| ADM-SYS-124 | — | Update a model | `old_values` **and** `new_values` both recorded | — |
| ADM-SYS-125 | — | Delete a model | `event=deleted` | — |
| ADM-SYS-126 | — | Restore a soft-deleted model | `event=restored` | — |
| ADM-SYS-127 | — | `timestamps`, `created_by`, `updated_by`, or `deleted_at` change | **Never logged** — bookkeeping attributes are excluded | — |
| ADM-SYS-128 | — | `password` changes | **Never logged** — the value is redacted, and the key does not appear at all | — |
| ADM-SYS-129 | Log with a subject | `GET /admin/activity-logs` | 200; the human-readable message appears in the list | — |
| ADM-SYS-130 | — | `GET /admin/activity-logs/{id}` | 200, detail with old and new values | — |
| ADM-SYS-131 | Log 99999 | `GET /admin/activity-logs/99999` | **404** | Laravel 404 page |
| ADM-SYS-132 | Logs of many types | Filter by `event` | 200, filtered | — |
| ADM-SYS-133 | Multiple actors | Filter by `user_id` | 200, filtered | — |
| ADM-SYS-134 | — | Filter by auditable `type` | 200, filtered | — |
| ADM-SYS-135 | — | `?search=<term>` matching a subject | 200, filtered | — |
| ADM-SYS-136 | — | `?from` later than `?to` | 422 | `The to field must be a date after or equal to from.` |
| ADM-SYS-137 | — | `?from=not-a-date` | 422 | `The from field must be a valid date.` |
| ADM-SYS-138 | — | `?event=nonsense` | 422, attribute labelled `event` | `The selected event is invalid.` |
| ADM-SYS-139 | — | `?user_id=99999` | 422, attribute labelled **`user`** (custom label mapping) | `The selected user is invalid.` |
| ADM-SYS-140 | — | `?search=` 121 chars | 422 | `The search field must not be greater than 120 characters.` |
| ADM-SYS-141 | Filters active | `GET /admin/activity-logs/export` | 200 CSV. **The export preserves the filters.** Filename `activity-logs-YYYY-MM-DD-HHMMSS.csv`; header `Time,Actor,Event,Model,Record,Summary,Changes,IP Address,Browser` | — |
| ADM-SYS-142 | A log with no `auditable_id` | View the `Record` column | Renders a safe `#id` fallback — no error, no empty cell | — |
| ADM-SYS-143 | A log with no `user_id` | View the `Actor` column | Renders a safe fallback, not an empty cell | — |
| ADM-SYS-144 | Log values containing arrays, nulls, and booleans | View the detail page | Values format readably; nulls and booleans render correctly | — |
| ADM-SYS-145 | Log in | Check the list | A `login` event is recorded | — |
| ADM-SYS-146 | Log out | Check the list | A `login`-class event is recorded | — |
| ADM-SYS-147 | 7 selected | `DELETE /admin/activity-logs` | 302, all removed — **pluralised via `trans_choice`** | `7 activity logs removed.` |
| ADM-SYS-148 | 1 selected | `DELETE` | 302 — the singular branch | `1 activity log removed.` |
| ADM-SYS-149 | 0 selected | `DELETE` | 302, nothing removed | `0 activity logs removed.` |
| ADM-SYS-150 | A log whose `old_values`/`new_values` contain a raw string with a pipe character | `export` | The CSV stays well-formed — rows do not split | — |
| ADM-SYS-151 | `registered-user` | `GET /admin/activity-logs` | 302 + permission error | `You do not have permission to access that page.` |

### 13.7 Sessions

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-152 | 2+ active sessions | `GET /admin/sessions` | 200, sessions listed with IP, user agent, and last activity | — |
| ADM-SYS-153 | Another user's session | `DELETE /admin/sessions/{id}` | 302, revoked; that user is logged out on their next request | `Session revoked successfully.` |
| ADM-SYS-154 | **Own** session | `DELETE /admin/sessions/{own id}` | **302, refused** | `Cannot revoke your own session.` |
| ADM-SYS-155 | — | `DELETE /admin/sessions/nonexistent` | **302, refused** (not a 404) | `Session not found.` |
| ADM-SYS-156 | A revoked session's cookie is replayed | Make a request | 302 → `/login` | — |
| ADM-SYS-157 | Revoke a session | Check `/admin/sessions` | The row is gone and the count drops | `Session revoked successfully.` |
| ADM-SYS-158 | `registered-user` | `DELETE` | 302 + permission error | `You do not have permission to access that page.` |

### 13.8 IP restrictions

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-159 | — | `GET /admin/ip-restrictions` | 200, current whitelist in a textarea, one entry per line | — |
| ADM-SYS-160 | — | `PUT` with `ips="203.0.113.5\n203.0.113.0/24"` | 302, both stored | `IP whitelist updated.` ⚠️ no "successfully" |
| ADM-SYS-161 | — | `PUT` with a **malformed** IP like `999.999.999.999` | 302, stored with **no validation error**. The only sanitising is `trim` + `array_filter`. | `IP whitelist updated.` ⚠️ [RISK-05](#17-known-gaps--risk-register) |
| ADM-SYS-162 | Whitelist non-empty | `PUT` with an **empty** `ips` | 302, the whitelist is cleared → **the whole admin panel is open to everyone** | `IP whitelist updated.` |
| ADM-SYS-163 | Whitelist with blank lines and trailing spaces | `PUT` | 302, blanks dropped and entries trimmed | `IP whitelist updated.` |
| ADM-SYS-164 | Whitelist set | Re-verify ADM-ACC-09…14 | Matching works for exact, wildcard, and CIDR forms | `Access denied: IP not whitelisted.` for non-matches |
| ADM-SYS-165 | Whitelist set, then a new browser session | Request `/admin` | The block applies **per request**, not per session | — |
| ADM-SYS-166 | Whitelist set | Request `/admin` as a **blocked** IP using a `role:admin` account | **403 from IP first** — IP restriction runs after auth but before the controller. Order does not leak the dashboard. | `Access denied: IP not whitelisted.` |
| ADM-SYS-167 | Whitelist set | Request a **public** page from a blocked IP | 200 — public pages are unaffected | — |
| ADM-SYS-168 | `ips` missing from the payload | `PUT` | 302, no-op (`nullable`). Existing values are **not** cleared. | `IP whitelist updated.` |
| ADM-SYS-169 | Whitelist set | Reload `/admin/ip-restrictions` | The textarea shows the values one per line, in the stored form | — |
| ADM-SYS-170 | `registered-user` | `PUT` | 302 + permission error | `You do not have permission to access that page.` |

### 13.9 Notifications

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-171 | Unread notifications | `GET /admin/notifications` | 200; the topbar bell shows the unread count badge | — |
| ADM-SYS-172 | — | Filter by `all` / `unread` | 200, filtered | — |
| ADM-SYS-173 | Own unread notification | `POST /admin/notifications/{id}/read` | 302, `read_at` set, the badge decrements | `Notification marked as read.` |
| ADM-SYS-174 | Own already-read notification | `POST …/read` | 302, no-op | `Notification marked as read.` |
| ADM-SYS-175 | **Another user's** notification | `POST /admin/notifications/{their id}/read` | **403** (bare, no message) | Laravel 403 page |
| ADM-SYS-176 | Own notification | `DELETE /admin/notifications/{id}` | 302, removed | `Notification deleted.` |
| ADM-SYS-177 | **Another user's** notification | `DELETE /admin/notifications/{their id}` | **403** (bare) | Laravel 403 page |
| ADM-SYS-178 | Notification 99999 | `DELETE` | **404** | Laravel 404 page |
| ADM-SYS-179 | 5 unread | `POST /admin/notifications/read-all` | 302, all read | `All notifications marked as read.` |
| ADM-SYS-180 | 0 unread | `POST …/read-all` | 302, no-op | `All notifications marked as read.` |
| ADM-SYS-181 | A has 5 unread | B calls `read-all` | **Only B's** notifications are read — the scope is `auth()->user()->unreadNotifications()`. A's are untouched. | `All notifications marked as read.` |
| ADM-SYS-182 | A new contact is submitted publicly | Sign in as admin | A `contact_message` notification arrives **synchronously** with a working `action_url` to the admin contact inbox | — |
| ADM-SYS-183 | Notification with an `action_url` | Click it in the bell dropdown | It navigates to the right admin page | — |
| ADM-SYS-184 | `registered-user` | Any write | 302 + permission error | `You do not have permission to access that page.` |

### 13.10 Newsletters

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-185 | 10 active subscribers | `GET /admin/newsletters` | 200, list | — |
| ADM-SYS-186 | — | `GET /admin/newsletters/create?category_id=2` | 200, the form is **pre-filled from the category** | — |
| ADM-SYS-187 | — | `POST /admin/newsletters` valid | 302, `status=draft`, `sent_count=0` | `Newsletter draft created successfully.` |
| ADM-SYS-188 | — | `POST` with no subject | 422 | `The subject field is required.` |
| ADM-SYS-189 | — | `subject` 256 chars | 422 | `The subject field must not be greater than 255 characters.` |
| ADM-SYS-190 | — | `POST` with no body | 422 | `The body field is required.` |
| ADM-SYS-191 | — | `POST` with `type=telepathy` | 422 | `The selected type is invalid.` (allowed: `content, event, character, merchandise, category, custom`) |
| ADM-SYS-192 | — | `POST` with no `type` | 302, type is nullable | `Newsletter draft created successfully.` |
| ADM-SYS-193 | — | `POST` with `recipient_filters.categories[]` bad id | 422 | `The selected recipient filters.categories.0 is invalid.` |
| ADM-SYS-194 | — | `POST` with `recipient_filters` as a string | 422 | `The recipient filters field must be an array.` |
| ADM-SYS-195 | Draft | `PUT /admin/newsletters/{id}` | 302 | `Newsletter updated successfully.` |
| ADM-SYS-196 | Editing the same draft | `PUT` keeping its own subject | Succeeds | `Newsletter updated successfully.` |
| ADM-SYS-197 | **`status = sent`** | `GET /admin/newsletters/{id}/edit` | 302 → **show**. The edit form never renders. | `Sent newsletters cannot be edited.` |
| ADM-SYS-198 | **`status = sent`** | `PUT /admin/newsletters/{id}` | 302 back, **the change is refused** | `Sent newsletters cannot be edited.` |
| ADM-SYS-199 | `status = sent` | `POST /admin/newsletters/{id}/send` | 302 back, refused | `Newsletter already sent.` |
| ADM-SYS-200 | `status = sending` | `POST …/send` | 302 back, refused | `Newsletter already sent.` |
| ADM-SYS-201 | 0 active subscribers | `POST …/send` | 302 back, **no send attempted at all** | `No active subscribers match these filters.` |
| ADM-SYS-202 | Category filter matches nobody | `POST …/send` | 302 back | `No active subscribers match these filters.` |
| ADM-SYS-203 | Only unsubscribed subscribers exist | `POST …/send` | 302 back — only `active` are eligible | `No active subscribers match these filters.` |
| ADM-SYS-204 | 10 active subscribers | `POST …/send` | 302. **Only `active` subscribers** receive it. `status=sent`, `sent_at` set, `sent_count=10`. | `Newsletter sent to 10 subscribers.` |
| ADM-SYS-205 | 3 bounces during the send | `POST …/send` | 302, failures counted, `sent_count=7`, `failed_count=3` | `Newsletter sent to 7 subscribers. 3 failed.` ⚠️ **dynamic, two sentences, a leading space before "3 failed."** |
| ADM-SYS-206 | 0 successes | `POST …/send` | 302. `status=failed`, **`sent_at` reset to `null`**. | `Newsletter could not be sent to any subscriber. Check the mail configuration.` |
| ADM-SYS-207 | A `failed` newsletter | `POST …/send` again | 302 — `failed` is **not** in the block list, so a **retry is allowed** | `Newsletter sent to N subscribers.` |
| ADM-SYS-208 | Body with `{{name}}`, `{{content_url}}`, `{{unsubscribe_url}}` | Send, then inspect the delivered body | All three placeholders are **replaced**, and the unsubscribe link is a working, per-recipient token | — |
| ADM-SYS-209 | Body with an unknown `{{placeholder}}` | Send | The unknown placeholder is left as-is or blanked — **no exception, no raw PHP** | — |
| ADM-SYS-210 | `recipient_filters.categories` set | `POST …/send` | Only subscribers matching those categories receive it | `Newsletter sent to N subscribers.` |
| ADM-SYS-211 | Draft | `GET /admin/newsletters/{id}/preview` | 200, rendered preview with placeholders substituted | — |
| ADM-SYS-212 | Draft | `GET /admin/newsletters/{id}` | 200, detail with recipient and sent stats | — |
| ADM-SYS-213 | Newsletter 99999 | `GET /admin/newsletters/99999` | **404** | Laravel 404 page |
| ADM-SYS-214 | Draft | `DELETE /admin/newsletters/{id}` | 302 | `Newsletter deleted.` ⚠️ no "successfully" |
| ADM-SYS-215 | `status = sent` | `DELETE /admin/newsletters/{id}` | 302 — **there is no guard against deleting a sent newsletter.** Flag this. | `Newsletter deleted.` |
| ADM-SYS-216 | `registered-user` | Any `/admin/newsletters/*` write | 302 + permission error | `You do not have permission to access that page.` |

### 13.11 Chatbot administration — FAQs

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-217 | 10 seeded FAQs | `GET /admin/chatbot/faqs` | 200, filterable | — |
| ADM-SYS-218 | — | `GET /admin/chatbot/faqs/create` | 200 | — |
| ADM-SYS-219 | — | `POST /admin/chatbot/faqs` valid | 302, `created_by` set to the acting admin | `FAQ created successfully.` |
| ADM-SYS-220 | — | `POST` with no question | 422 | `Please enter a question.` |
| ADM-SYS-221 | — | `question` of 501 chars | 422 | `The question may not be greater than 500 characters.` |
| ADM-SYS-222 | — | `POST` with no answer | 422 | `Please enter an answer.` |
| ADM-SYS-223 | — | `answer` of 50 001 chars | 422 | `The answer may not be greater than 50,000 characters.` |
| ADM-SYS-224 | — | `category_id = 99999` | 422 | `The selected category does not exist.` |
| ADM-SYS-225 | — | No `category_id` | 302, `category_id` is nullable | `FAQ created successfully.` |
| ADM-SYS-226 | FAQ 1 | `GET /admin/chatbot/faqs/1` | 200, detail | — |
| ADM-SYS-227 | FAQ 1 | `GET /admin/chatbot/faqs/1/edit` | 200 | — |
| ADM-SYS-228 | FAQ 1 | `PUT /admin/chatbot/faqs/1` | 302 | `FAQ updated successfully.` |
| ADM-SYS-229 | Editing the same FAQ | `PUT` keeping its own question | Succeeds (no unique rule on question) | `FAQ updated successfully.` |
| ADM-SYS-230 | FAQ 1 | `DELETE /admin/chatbot/faqs/1` | 302 | `FAQ deleted successfully.` |
| ADM-SYS-231 | FAQ 99999 | `GET` / `PUT` / `DELETE` | **404** | Laravel 404 page |
| ADM-SYS-232 | — | Filter the FAQ list | Filters apply; the **Reset** button clears them | — |
| ADM-SYS-233 | A newly created FAQ | Ask the matching question in the public chatbot | `source = "faq"` and the new answer is returned | `{"answer":"…","source":"faq"}` |
| ADM-SYS-234 | `registered-user` | Any FAQ write | 302 + permission error | `You do not have permission to access that page.` |

### 13.12 Chatbot administration — query log

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-235 | 10 seeded queries | `GET /admin/chatbot` | 200, the conversation log renders | — |
| ADM-SYS-236 | Queries from users and guests | View the list | Guest rows show the literal `Guest` in the `User` column — not `null`, not an error | — |
| ADM-SYS-237 | Query 1 | `DELETE /admin/chatbot/{query}` | 302 | `Chatbot query deleted.` ⚠️ no "successfully" |
| ADM-SYS-238 | Query 99999 | `DELETE` | **404** | Laravel 404 page |
| ADM-SYS-239 | — | `GET /admin/chatbot/export` | 200 CSV. Filename `chatbot-queries-YYYY-MM-DD-HHMMSS.csv`; header `ID,User,Session,Message,Response,Date` | — |
| ADM-SYS-240 | A response containing a comma or a newline | `export` | The CSV stays well-formed — quoting is applied, rows do not merge | — |
| ADM-SYS-241 | `registered-user` | `DELETE` | 302 + permission error | `You do not have permission to access that page.` |

### 13.13 Maintenance mode

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-242 | — | `GET /admin/maintenance` | 200, with a toggle, a message field, and a bypass-routes field | — |
| ADM-SYS-243 | Off | `POST /admin/maintenance/toggle` | 302, `maintenance_mode = true` | `Maintenance mode enabled successfully.` ⚠️ **dynamic** |
| ADM-SYS-244 | On | `POST /admin/maintenance/toggle` | 302, `maintenance_mode = false` | `Maintenance mode disabled successfully.` |
| ADM-SYS-245 | — | `PUT /admin/maintenance/message` | 302 | `Maintenance message updated successfully.` |
| ADM-SYS-246 | — | `PUT` message with 501 chars | 422 | `The message field must not be greater than 500 characters.` |
| ADM-SYS-247 | — | `PUT` message empty | 422 | `The message field is required.` |
| ADM-SYS-248 | — | `PUT /admin/maintenance/bypass-routes` | 302 | `Bypass routes updated successfully.` |
| ADM-SYS-249 | — | `PUT` bypass routes with 501 chars | 422 | `The bypass routes field must not be greater than 500 characters.` |
| ADM-SYS-250 | — | `PUT` bypass routes empty | 422 | `The bypass routes field is required.` |
| ADM-SYS-251 | On, default message (setting never set) | Visit `/` as a guest | **503** page | `We are currently performing scheduled maintenance. We will be back shortly.` |
| ADM-SYS-252 | On, custom message | Visit `/` | **503** showing the **custom** text | the custom message |
| ADM-SYS-253 | On | Visit `/login`, `/register`, `/forgot-password`, `/reset-password/anything` | **200** — the default bypass list works | — |
| ADM-SYS-254 | On, signed-in **admin** | Visit `/` | **200** — admins always bypass | — |
| ADM-SYS-255 | On, signed-in **non-admin** | Visit `/` | **503** — non-admins are **not** exempt | the maintenance message |
| ADM-SYS-256 | On | Visit `/up` or `/health` | **200** — always allowed | — |
| ADM-SYS-257 | On, bypass routes narrowed | Visit a now-blocked path | **503** | the maintenance message |
| ADM-SYS-258 | On, bypass routes narrowed | Visit a newly allowed path | **200** | — |
| ADM-SYS-259 | On | `POST /api/v1/auth/login` | **503** — the API is **not** covered by the `health` bypass | HTML 503 page |
| ADM-SYS-260 | On | `POST /contact` | **503** | HTML 503 page |
| ADM-SYS-261 | On | `POST /chatbot/message` | **503** | HTML 503 or JSON 503 |
| ADM-SYS-262 | `registered-user` | Any maintenance write | 302 + permission error | `You do not have permission to access that page.` |
| ADM-SYS-263 | `maintenance_mode` is not seeded at all | Visit any public page | 200 — `Setting::get('maintenance_mode', false)` defaults to **off** | — |

### 13.14 System health

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-264 | Healthy DB | `GET /admin/health` | 200. The database check reads `connected`. | — |
| ADM-SYS-265 | DB unreachable | `GET /admin/health` | 200 (the page still renders). The check reads an error string with an `error: ` prefix. | `error: <driver message>` |
| ADM-SYS-266 | Cache broken | `GET /admin/health` | 200. The cache check reads `false`; **no exception escapes.** | — |
| ADM-SYS-267 | Server software unparseable | `GET /admin/health` | 200, that row reads `N/A` | `N/A` |
| ADM-SYS-268 | Uptime unavailable | `GET /admin/health` | 200, that row reads `N/A` | `N/A` |
| ADM-SYS-269 | Disk usage unavailable | `GET /admin/health` | 200, that row reads `N/A` | `N/A` |
| ADM-SYS-270 | Failed jobs in the queue | `GET /admin/health` | 200, the count is real | — |
| ADM-SYS-271 | `failed_jobs` query throws | `GET /admin/health` | 200, count reads `0` — swallowed silently, no 500 | `0` |
| ADM-SYS-272 | A key table missing | `GET /admin/health` | 200, `tableExists` returns `false`; **no crash** | — |
| ADM-SYS-273 | — | `GET /admin/health` | **No flash message** — read-only page | — |
| ADM-SYS-274 | `registered-user` | `GET /admin/health` | 302 + permission error | `You do not have permission to access that page.` |

### 13.15 Log viewer

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-275 | A log file exists | `GET /admin/logs` | 200, the last 200 lines render, newest first | — |
| ADM-SYS-276 | No log file | `GET /admin/logs` | 200, a graceful empty state. **Silent no-op** — no error message. | — |
| ADM-SYS-277 | A log file exists | `DELETE /admin/logs` | 302, the log is truncated | `Logs cleared successfully.` |
| ADM-SYS-278 | No log file | `DELETE /admin/logs` | 302, **no-op, no error** | `Logs cleared successfully.` |
| ADM-SYS-279 | — | `GET /admin/logs/download` | 200. Filename pattern `laravel-YYYY-MM-DD-HHMMSS.log` | — |
| ADM-SYS-280 | No log file | `GET /admin/logs/download` | 200 with an **empty or missing body** — there is **no existence guard** on `download`. Flag this. | — |
| ADM-SYS-281 | Log lines containing HTML | `GET /admin/logs` | Rendered **escaped** — no XSS from log content | — |
| ADM-SYS-282 | Log lines containing a very long line | `GET /admin/logs` | No layout breakage | — |
| ADM-SYS-283 | `registered-user` | `DELETE /admin/logs` | 302 + permission error | `You do not have permission to access that page.` |

### 13.16 Backups

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-284 | — | `GET /admin/backup` | 200, existing backups listed | — |
| ADM-SYS-285 | — | `POST /admin/backup` | 302, a `.sql` dump is created | `Backup created: backup-2026-09-28-143012.sql` ⚠️ **dynamic filename interpolated** |
| ADM-SYS-286 | The dump silently fails or is 0 bytes | `POST /admin/backup` | 302, no file left behind | `Backup failed: file was not created.` |
| ADM-SYS-287 | A backup exists | `GET /admin/backup/{filename}` | 200, file download | — |
| ADM-SYS-288 | — | `GET /admin/backup/does-not-exist.sql` | **404** (bare, no message) | Laravel 404 page |
| ADM-SYS-289 | — | `GET /admin/backup/../../.env` | **404** — traversal blocked | Laravel 404 page |
| ADM-SYS-290 | A backup exists | `DELETE /admin/backup/{filename}` | 302, the file is deleted | `Backup deleted successfully.` |
| ADM-SYS-291 | **The file does not exist** | `DELETE /admin/backup/{filename}` | 302, **silently succeeds** and still claims success. Flag this. | `Backup deleted successfully.` |
| ADM-SYS-292 | A completed backup | Inspect the file | Header includes `-- FurShield Database Backup`, `-- Date:`, `-- Driver:`, plus `SET FOREIGN_KEY_CHECKS`, `SET NAMES utf8mb4` / `SET client_encoding`, per-table markers, and a closing `SET FOREIGN_KEY_CHECKS = 1`. **One table that throws is skipped silently** — the dump continues. | — |
| ADM-SYS-293 | A completed backup | Open the dump in a SQL client | The schema and rows are restorable | — |
| ADM-SYS-294 | `getCreateTableStatement()` throws for one table | `POST /admin/backup` | 302, that table is **omitted**, the rest of the dump completes, **no error surfaced** | `Backup created: backup-….sql` |
| ADM-SYS-295 | — | `POST /admin/backup` repeatedly | Each dump has a unique second-resolution filename; no collision within a second | — |
| ADM-SYS-296 | `registered-user` | `POST /admin/backup` | 302 + permission error | `You do not have permission to access that page.` |
| ADM-SYS-297 | `registered-user` | `GET /admin/backup/{filename}` | 302 + permission error — **a non-admin cannot download backups** | `You do not have permission to access that page.` |

### 13.17 Analytics

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| ADM-SYS-298 | — | `GET /admin/analytics` | 200, charts render | — |
| ADM-SYS-299 | Empty DB | `GET /admin/analytics` | 200, zeroed charts. **No division by zero, no NaN, no 500.** | — |
| ADM-SYS-300 | — | `GET /admin/analytics` | **No flash message** — read-only page | — |
| ADM-SYS-301 | A view count exists | `GET /admin/analytics` | Counts reflect real `view_count` values, not fabricated numbers | — |
| ADM-SYS-302 | `registered-user` | `GET /admin/analytics` | 302 + permission error | `You do not have permission to access that page.` |

---

## 14. API v1

Base path `/api/v1`. JSON errors are forced for **all** `api/*` paths by
`shouldRenderJsonWhen`, so an `Accept: application/json` header is not required — but the
`RoleMiddleware` 403 branch **does** require it.

**Response conventions:**

- The key is always **`message`**. The key `error` is **never** used in any JSON response anywhere in
  the codebase.
- `errors` appears **only** on 422 validation failures.
- Success payloads use `user`, `token`, `data`, `links`, `meta`, `status`, `timestamp`, or `saved`.
- Resource routes are **`data`-wrapped**; message routes are not. So `GET /categories/{id}` returns
  `{"data":{…}}` while `DELETE /categories/{id}` returns `{"message":"…"}`.

### 14.1 Health

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| API-01 | — | `GET /api/v1/health` | **200** | `{"status":"ok","timestamp":"2026-09-28T12:00:00+00:00"}` — no `message` key |
| API-02 | Maintenance mode **on** | `GET /api/v1/health` | **503 HTML, not JSON.** This path does **not** match the `health` bypass pattern. | HTML 503 page |

### 14.2 Authentication

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| API-03 | — | `POST /api/v1/auth/register` with a valid payload | **201**. Role `registered-user` assigned. Token issued. | `{"message":"Registration successful.","user":{…},"token":"1|…"}` ⚠️ the `user` object is the **raw model**, not `UserResource`, and `roles` comes back as **`[]`** even though the role was assigned (the relation was cached before `attach()`). Assert accordingly. |
| API-04 | — | `register` with no name | **422** | `{"message":"The name field is required.","errors":{"name":["The name field is required."]}}` |
| API-05 | — | `register` with a malformed email | 422 | `{"message":"The email field must be a valid email address.","errors":{…}}` |
| API-06 | Email exists | `register` with a duplicate email | 422 | `{"message":"The email has already been taken.","errors":{…}}` |
| API-07 | — | `register` with a 7-char password | 422 | `{"message":"The password field must be at least 8 characters.","errors":{…}}` |
| API-08 | — | `register` without a matching confirmation | 422 | `{"message":"The password field confirmation does not match.","errors":{"password":["The password field confirmation does not match.","The password field must be at least 8 characters."]}}` — **both** errors on the same key |
| API-09 | — | `register` with `name` 256 chars | 422 | `{"message":"The name field must not be greater than 255 characters."}` |
| API-10 | Already authenticated | `register` or `login` | **302** → the `guest` middleware redirects. ⚠️ **not** a JSON 401 — verify what your client sees. | Laravel redirect |
| API-11 | Real credentials | `POST /api/v1/auth/login` | **200**. Token issued. The `user` object is the **raw model with no `roles` key**. | `{"message":"Login successful.","user":{…},"token":"2|…"}` |
| API-12 | — | `login` with a wrong password | **401** — no `errors` key | `{"message":"The provided credentials do not match our records."}` ⚠️ **hardcoded**, not `__('auth.failed')`, and deliberately vaguer than the web login message |
| API-13 | — | `login` with a malformed email | **422** | `{"message":"The email field must be a valid email address.","errors":{…}}` |
| API-14 | — | `login` with no password | 422 | `{"message":"The password field is required.","errors":{…}}` |
| API-15 | **No throttle middleware** | 100 bad logins in a minute | **All 100 return 401. There is no 429 on the API.** | `{"message":"The provided credentials do not match our records."}` |
| API-16 | Valid token | `POST /api/v1/auth/logout` | **200**. Only the **current** token is deleted. | `{"message":"Logged out successfully."}` |
| API-17 | Two valid tokens | `logout` with token 1 | Token 2 **still works** | `{"message":"Logged out successfully."}` |
| API-18 | No token | `POST /api/v1/auth/logout` | **401** | `{"message":"Unauthenticated."}` |
| API-19 | A revoked token | Any authenticated call | **401** | `{"message":"Unauthenticated."}` |
| API-20 | Valid token | `GET /api/v1/user` | **200**. Raw model, **no `roles` key**, no `message` key. | `{"user":{"id":1,"name":"…","email":"…","email_verified_at":…,"created_at":…,"updated_at":…}}` |
| API-21 | No token | `GET /api/v1/user` | **401** | `{"message":"Unauthenticated."}` |
| API-22 | Valid token | `PUT /api/v1/profile` with a new name | **200** | `{"message":"Profile updated successfully.","user":{…}}` — no `roles` key (re-queried with `fresh()`) |
| API-23 | Valid token | `PUT /api/v1/profile` with `{}` | **200** — both keys are `sometimes`, so an empty body is valid. No change is made. | `{"message":"Profile updated successfully.","user":{…}}` |
| API-24 | — | `PUT /api/v1/profile` with a duplicate email | 422 | `{"message":"The email has already been taken.","errors":{…}}` |
| API-25 | — | `PUT /api/v1/profile` with `name` 256 chars | 422 | `{"message":"The name field must not be greater than 255 characters."}` |
| API-26 | — | `PATCH /api/v1/profile` | **405 Method Not Allowed** — **PUT only** | Laravel 405 |
| API-27 | Valid token | Attempt a password change | **No such endpoint.** The API has no password-change route. | — |

### 14.3 Categories (admin only)

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| API-28 | Admin token | `GET /api/v1/categories` | **200** paginated. Default `per_page=15`. Sorted by name. | `{"data":[…],"links":{…},"meta":{…}}` — the full pagination envelope |
| API-29 | — | `?search=anime` | 200, matched against name **or** description | — |
| API-30 | — | `?per_page=100000` | 200 — **there is no upper bound** on `per_page`. Confirm this is acceptable. | — |
| API-31 | — | `?page=999` | 200 with `data: []` and `current_page: 999` | — |
| API-32 | — | Inspect a category object | `contents_count` is **always absent** — `whenCounted` is never satisfied. **Do not assert on it.** | — |
| API-33 | — | Inspect `icon_url` | `null` for every seeded category, since none has `icon_media_id` | — |
| API-34 | Admin token | `POST /api/v1/categories` valid | **201**, `data`-wrapped, **no `message` key** | `{"data":{"id":11,"name":"…","slug":"…","description":"…","icon_media_id":null,"icon_url":null,"created_at":…,"updated_at":…,"deleted_at":null}}` |
| API-35 | — | `POST` with no name | 422 | `{"message":"Please enter a category name.","errors":{"name":["Please enter a category name."]}}` |
| API-36 | — | `POST` with a duplicate name | 422 | `{"message":"A category with this name already exists.","errors":{…}}` |
| API-37 | — | `POST` with a duplicate slug | 422 | `{"message":"A category with this slug already exists.","errors":{"slug":[…]}}` |
| API-38 | — | `POST` with `name` only (slug blank) | **201** — `prepareForValidation()` fills the slug | `{"data":{…}}` |
| API-39 | — | `POST` with a 501-char description | 422 | `{"message":"Description cannot exceed 500 characters.","errors":{…}}` |
| API-40 | — | `POST` with a non-image `icon` | 422 | `{"message":"The file must be an image.","errors":{…}}` |
| API-41 | — | `POST` with a 3 MB icon | 422 | `{"message":"The icon must not be larger than 2MB.","errors":{…}}` |
| API-42 | — | `POST` with `icon_media_id=99999` | 422 | `{"message":"The selected media file does not exist.","errors":{…}}` |
| API-43 | — | `POST` with a bad slug charset | 422 | `{"message":"The slug field must only contain letters, numbers, dashes, and underscores.","errors":{…}}` — the only API validation message **not** customised |
| API-44 | — | `POST` with `remove_icon = "maybe"` | 422 | `{"message":"The remove icon field must be true or false.","errors":{…}}` |
| API-45 | Admin token | `GET /api/v1/categories/1` | **200**, `data`-wrapped, `iconMedia` loaded | `{"data":{…}}` |
| API-46 | — | `GET /api/v1/categories/99999` | **404**, no `errors` key | `{"message":"No query results for model [App\\Models\\Category] 99999."}` |
| API-47 | — | `GET /api/v1/categories/{soft-deleted id}` | **404** — implicit binding excludes soft-deleted rows | `{"message":"No query results for model [App\\Models\\Category] N."}` |
| API-48 | Admin token | `PUT /api/v1/categories/1` | **200**, `data`-wrapped, **no `message` key** | `{"data":{…}}` |
| API-49 | — | `PUT` with `remove_icon=1` | 200, `icon_media_id` nulled | `{"data":{…}}` |
| API-50 | Editing the same record | `PUT` keeping its own name/slug | 200 — `unique` ignores itself | `{"data":{…}}` |
| API-51 | Admin token | `DELETE /api/v1/categories/1` | **200** — **soft** delete only, never a hard delete | `{"message":"Category deleted successfully."}` |
| API-52 | Already soft-deleted | `DELETE` again | **404** | `{"message":"No query results for model [App\\Models\\Category] N."}` |
| API-53 | **Non-admin** token | `GET /api/v1/categories` | **403** | `{"message":"Forbidden. You do not have permission to access this resource."}` |
| API-54 | **Non-admin** token | Any category write | **403** | `{"message":"Forbidden. …"}` |
| API-55 | No token | `GET /api/v1/categories` | **401** | `{"message":"Unauthenticated."}` |
| API-56 | Non-admin token, **no** `Accept: application/json` | `GET /api/v1/categories` | The middleware falls through to a **302 redirect with a flash** instead of a 403 JSON. **Always send the header.** | 302 → login |

### 14.4 Users (admin only)

⚠️ **Four different `user` shapes exist** across the API. Do not write one shared assertion for all of
them.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| API-57 | Admin token | `GET /api/v1/users` | **200** paginated. Ordered by `created_at DESC`. Default `per_page=15`. | `{"data":[…],"links":{…},"meta":{…}}`; each user is a `UserResource` with `roles` trimmed to exactly `id`, `name`, `slug` |
| API-58 | — | `?search=<term>` | 200, matched against name **or** email | — |
| API-59 | Admin token | `GET /api/v1/users/2` | **200**, `data`-wrapped. `UserResource`, **3-key roles** | `{"data":{"id":2,…,"roles":[{"id":2,"name":"Registered User","slug":"registered-user"}],…}}` |
| API-60 | — | `GET /api/v1/users/99999` | **404** | `{"message":"No query results for model [App\\Models\\User] 99999."}` |
| API-61 | Admin token | `PUT /api/v1/users/2` with a new name | **200** | `{"message":"User updated successfully.","user":{…}}` ⚠️ the `user` is the **raw model with fully hydrated 7-key roles** — a different shape from API-59 |
| API-62 | — | `PUT` with `roles: ["admin"]` | 200, roles **synced**. ⚠️ submitting `roles` **removes every other role** — it is a `sync`, not an append | `{"message":"User updated successfully.","user":{…}}` |
| API-63 | — | `PUT` with `roles: ["nonexistent-slug"]` | 422. `roles` is validated against **`roles.slug`**, not ids, and the error key is the **index** | `{"message":"The selected roles.0 is invalid.","errors":{"roles.0":["The selected roles.0 is invalid."]}}` |
| API-64 | — | `PUT` with `roles: "admin"` (a string, not an array) | 422 | `{"message":"The roles field must be an array."}` |
| API-65 | — | `PUT` with a duplicate email | 422 | `{"message":"The email has already been taken.","errors":{…}}` |
| API-66 | — | `PUT` with `{}` | 200, no change. All keys are `sometimes`. | `{"message":"User updated successfully.","user":{…}}` |
| API-67 | Admin token | `DELETE /api/v1/users/2` | **200** | `{"message":"User deleted successfully."}` |
| API-68 | Admin deletes **their own** account via a **token** | `DELETE /api/v1/users/{own id}` | **200 — the self-delete guard DOES NOT FIRE.** It compares against `auth()->id()` (the **web** guard), which is unauthenticated for a Bearer token. **An admin can delete their own account over the API.** See [RISK-04](#17-known-gaps--risk-register). | `{"message":"User deleted successfully."}` |
| API-69 | Session-authenticated admin, not a token | `DELETE /api/v1/users/{own id}` | **400** — this is the only way to reach the guard | `{"message":"You cannot delete your own account."}` |
| API-70 | — | `PATCH /api/v1/users/2` | **405 Method Not Allowed** | Laravel 405 |
| API-71 | — | `POST /api/v1/users` | **405 Method Not Allowed** — no create endpoint | Laravel 405 |
| API-72 | **Non-admin** token | `GET /api/v1/users` | **403** | `{"message":"Forbidden. …"}` |
| API-73 | **Non-admin** token | Any user write | **403** | `{"message":"Forbidden. …"}` |
| API-74 | No token | Any user endpoint | **401** | `{"message":"Unauthenticated."}` |

### 14.5 Cross-cutting API behaviour

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| API-75 | Any `api/*` request with **no** `Accept` header | Trigger a 404 / 401 / 422 | **JSON is returned anyway** — `shouldRenderJsonWhen` forces it for `api/*` | JSON |
| API-76 | Session-authenticated request to an API route | — | **419** on POST/PUT/PATCH/DELETE (CSRF stateful middleware) | `{"message":"CSRF token mismatch."}` |
| API-77 | Media disk write fails during a category icon upload | `POST /api/v1/categories` | **500** | `{"message":"Failed to store uploaded file [x.png] on disk [public]."}` — a `RuntimeException` escapes. Flag this as a leak. |
| API-78 | — | `GET /api/v1/nope` (no such route) | 404 with an **empty** message | `{"message":""}` |
| API-79 | Every endpoint | — | The `error` key is **never** used in any JSON response. The `errors` key appears **only** on 422. | — |
| API-80 | — | — | There are **zero `abort()` calls** in any `Api\V1` controller. All errors are `response()->json(…)` or framework exceptions. | — |
| API-81 | Maintenance mode on | Any `api/*` route | **503 HTML**, not JSON | HTML 503 page |

---

## 15. Global error & cross-cutting behaviour

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| GLB-01 | — | Request a non-existent path | **404** | Laravel 404 page (or JSON, per `shouldRenderJsonWhen`) |
| GLB-02 | — | Request `/storage/{path}` with a traversal attempt | **404** | `Media file not found.` |
| GLB-03 | — | Submit any form after the session expires (419) | **419** | Laravel 419 "Page Expired" page |
| GLB-04 | — | Exceed any throttle | **429** | Laravel 429 page |
| GLB-05 | Any unhandled exception | Trigger it | **500** | Laravel 500 page. `APP_DEBUG=true` shows the stack trace — **verify `APP_DEBUG=false` in production.** |
| GLB-06 | Email-verification failure | Click a tampered link | **403** | Framework email-verification exception page |
| GLB-07 | CSRF token missing on a normal web POST | Submit | **419** | Laravel 419 page |
| GLB-08 | — | Any **POST** without a CSRF token in an automated test | **419** | Disable CSRF in the test env or assert the 419 deliberately |
| GLB-09 | Any write | Complete a write, then wait 6 seconds and re-check the DOM | The success/error alert is **gone** (5000 ms auto-dismiss) | — |
| GLB-10 | Any write | Reload the page after a flash | The flash is **consumed** — no duplicate message | — |
| GLB-11 | Any write | Open the resulting URL in a second tab | The message does **not** reappear | — |
| GLB-12 | Any error | Inspect the alert markup | Every alert has `role="alert"`; every field error has a `role="alert"` element with a matching `id="{field}-error"` and `aria-describedby` wiring | — |
| GLB-13 | Any form with a validation error | Inspect the failing field | The input is marked `is-invalid` and the message is reachable by a screen reader | — |
| GLB-14 | Every flash message | Inspect the rendered text | Text is **escaped** — a `success` value containing `<script>` renders as literal text | — |
| GLB-15 | `maintenance_mode = true` | Request any non-bypassed path | **503** with the configured message | the maintenance message |
| GLB-16 | — | Check `/up` | **200** | — |
| GLB-17 | Any list page | Request page 9999 | 200 with an empty-state message, **never a 500** | — |
| GLB-18 | Any detail page | Request a soft-deleted record's slug | **404** | Laravel 404 page |
| GLB-19 | Any detail page | Request a record with a future `published_at` | **404** | Laravel 404 page |
| GLB-20 | Any filterable list | Pass an out-of-range filter value | **422** with a readable message — never a silent 200 | — |
| GLB-21 | Any filterable list | Pass an unknown enum filter value | **422** | — |
| GLB-22 | A successful write | Check the activity log | A `created` / `updated` / `deleted` row exists with old and new values | — |
| GLB-23 | A successful write | Check that `password` and bookkeeping columns are **absent** from the log | Confirmed — never logged | — |
| GLB-24 | A queued job | Run it with `QUEUE_CONNECTION=sync` | Executes inline; no job row is left behind | — |
| GLB-25 | A queued job | Trigger it with the queue worker stopped | Fails loudly. `WelcomeNotification` implements `ShouldQueue`, so anything that dispatches it **needs a running worker**. | — |
| GLB-26 | `cache` driver unavailable | `GET /admin/health` | The page still renders; the check reads a failure, not an exception | — |

---

## 16. Security regression suite

Run these before every release.

| ID | Precondition | Given → When | Then | Message shown |
|---|---|---|---|---|
| SEC-01 | `registered-user` | Request every `/admin/*` route, one by one | **All 403/302.** No admin page renders for a non-admin. | `You do not have permission to access that page.` |
| SEC-02 | Guest | Request every `/admin/*` route | **All 302 to `/login`.** No admin page leaks. | — |
| SEC-03 | Guest | Request every `/user/*` route | **All 302 to `/login`.** | — |
| SEC-04 | Member A | Request a resource ID belonging to Member B — bookmarks, reviews, submissions, notifications, sessions, favourites | **All blocked** (403 or 404). **No IDOR anywhere.** | 403 / 404 |
| SEC-05 | Member A | Read Member B's bookmark note via `GET /user/bookmarks` | The note is **never** rendered | — |
| SEC-06 | Member A | Read Member B's private feedback | **Never rendered** | — |
| SEC-07 | Member A | Read Member A's own **pending** review on a public page | The author may see it; **nobody else may** | — |
| SEC-08 | Any form with a rich-text field | Submit `<script>alert(1)</script>` | Stripped on render — no execution | — |
| SEC-09 | Any form with a rich-text field | Submit `<img src=x onerror=alert(1)>` | Stripped | — |
| SEC-10 | Any form with a rich-text field | Submit `<a href="javascript:alert(1)">` | The `javascript:` href is stripped | — |
| SEC-11 | Event `ticket_url` | `javascript:alert(1)` | Rejected at validation, and not rendered as an href if it slips through | `Ticket URL must be a valid URL.` |
| SEC-12 | Event `google_maps_location` | `javascript:alert(1)` | Rejected at validation | `The google maps location field must be a valid URL.` |
| SEC-13 | Chatbot `message` | A prompt-injection attempt | The CRITICAL LANGUAGE RULE block still holds; the reply stays in the expected language | — |
| SEC-14 | Profile `avatar` | A PHP-disguised image (`evil.jpg.php`) | Rejected — only real image mimes pass | `The avatar field must be a file of type: jpg, jpeg, png, gif, webp.` |
| SEC-15 | Admin media upload | A `.php`, `.phtml`, `.exe`, or double-extension file | Rejected | `File type .php is not allowed.` |
| SEC-16 | `/storage/{path}` | Path traversal, encoded or not | **404** | `Media file not found.` |
| SEC-17 | `/admin/backup/{filename}` | Path traversal | **404** | Laravel 404 page |
| SEC-18 | Any `GET` form | A CSRF token in the query string | Ignored — tokens are only honoured from the body or header | — |
| SEC-19 | Any write | A missing CSRF token | **419** | Laravel 419 page |
| SEC-20 | `registered-user` | Escalate a role via any non-admin channel | No path exists. Roles are admin-only. | — |
| SEC-21 | Unverified user | Access `/admin` | **200 — verification is not enforced.** Confirm this is deliberate. See [RISK-02](#17-known-gaps--risk-register). | — |
| SEC-22 | `POST /login` | 6 bad attempts from one IP with many different emails | Every email has its own limiter, so this **does not** lock out a legitimate user | `Invalid email or password. …` |
| SEC-23 | `POST /contact` | 100 submissions from one IP | **All accepted** — no throttle. Flag for spam/abuse. | `Thank you for your message! …` |
| SEC-24 | `POST /api/v1/auth/login` | 1000 bad attempts | **All 401. No rate limiting at all on the API.** | `{"message":"The provided credentials do not match our records."}` |
| SEC-25 | Password reset | Request a link for a non-existent email | The message **does confirm the account does not exist.** Consider aligning it with the login message. | `We can't find an account with that email address.` |
| SEC-26 | Contact / chatbot / admin log rows | Inspect what is stored | Visitor IP and user agent are recorded where intended, and **not** leaked on public pages | — |
| SEC-27 | Any file download | An unauthorised user | `/admin/media/{id}/download` and `/admin/backup/{filename}` both require `role:admin` | `You do not have permission to access that page.` |
| SEC-28 | Any CSV export | Inject a formula (`=cmd|…`) into a name field | The value is **not** evaluated by spreadsheet software. Check for a leading `'` prefix. | — |

---

## 17. Known gaps & risk register

Findings from reading the source. Each is a place where a test above will pass but the behaviour is
still questionable, or where the test will fail and that is the point.

| ID | Severity | Finding | Where | Related cases |
|---|---|---|---|---|
| RISK-01 | **High** | `session('status')` is a **raw token** (`password-updated`, `verification-link-sent`), but the **admin layout renders it unescaped and untranslated** — `layouts/app.blade.php:46` and `layouts/user/app.blade.php:37` print the literal `password-updated` in an `alert-info`. The user layout and the profile form translate it. | `resources/views/layouts/app.blade.php` | AUT-72, AUT-43 |
| RISK-02 | **High** | Email verification is **not a gate anywhere**. There is no `verified` middleware on any route, including `/admin`. `CONTEXT.md` claims otherwise. An unverified account reaches the full admin panel. | `routes/admin.php` | ADM-ACC-18, AUT-54, SEC-21 |
| RISK-03 | Medium | `<x-flash-message />` is **never rendered** — zero Blade files include it. The `session('warning')` and `session('info')` branches are dead code, and named error bags (`subscribe`, `updatePassword`, `userDeletion`) never appear in a page-level alert. | `resources/views/components/flash-message.blade.php` | §0.2, NWS-13 |
| RISK-04 | **High** | `Api\V1\UserController@destroy` compares `auth()->id()` — the **web** guard — so for a pure Bearer-token request the self-delete guard never fires. **An admin can delete their own account over the API**, locking themselves out. | `app/Http/Controllers/Api/V1/UserController.php` | API-68, API-69 |
| RISK-05 | Medium | `Admin\IpRestrictionController@update` performs **no IP syntax validation** — only `trim` + `array_filter`. Malformed entries are stored silently, and submitting an empty list **clears the whitelist and opens the admin panel to the world**. | `app/Http/Controllers/Admin/IpRestrictionController.php` | ADM-SYS-161, ADM-SYS-162 |
| RISK-06 | Medium | `Admin\LogViewerController@download` has **no file-existence guard**, and `Admin\BackupController@destroy` reports `Backup deleted successfully.` even when the file did not exist. Both are false-success responses. | `app/Http/Controllers/Admin/LogViewerController.php`, `…/BackupController.php` | ADM-SYS-280, ADM-SYS-291 |
| RISK-07 | Medium | The API has **no rate limiting whatsoever** — not on login, not on writes. `POST /contact` is likewise unthrottled. Both are abuse vectors. | `routes/api.php` | API-15, SEC-23, SEC-24 |
| RISK-08 | Low | Several success messages are inconsistently punctuated: `Chatbot query deleted.`, `Newsletter deleted.`, `Subscriber deleted.`, `Submission rejected.` (on the `success` key), `Backup deleted successfully.`, `IP whitelist updated.`, `Upload completed successfully` (JSON, no period), `Saved to bookmarks` (no period). | multiple controllers | throughout §11–§13 |
| RISK-09 | Low | The API returns **four different `user` shapes** (raw model without `roles`; raw model with `roles: []` on register; raw model with fully hydrated 7-key roles on admin update; `UserResource` with 3-key roles on index/show). `register` also reports `roles: []` despite assigning the role, because the relation was loaded before `attach()`. Any shared response contract will break. | `app/Http/Controllers/Api/V1/**` | API-03, API-11, API-20, API-22, API-57, API-59, API-61 |

### Areas with **zero** automated test coverage

Worth prioritising, in this order:

1. **The entire `routes/api.php` surface** — 0 tests. This is the largest untested area and contains
   RISK-04.
2. `Admin\RelationLookupController` — 3 JSON endpoints, 0 tests.
3. `Admin\SettingController`, `RoleController`, `TagController`, `SessionController`,
   `IpRestrictionController`, `NotificationController`, `MaintenanceController`, `HealthController`,
   `LogViewerController`, `BackupController` — 0 tests.
4. `Admin\MediaController` **chunked upload** — 4 endpoints, 0 tests.
5. `Admin\SubscriberController` bulk action / export / edit / status — 0 tests.
6. `ContactController` (public form) end-to-end → `Admin\ContactController` — 0 tests.
7. `MediaServeController` (`/storage/{path}` 404 and range handling) — 0 tests.
8. `EventController@calendar` (iCalendar) — 0 tests.
9. `PublicSiteController@section` and `@account` — only indirectly covered.

### Conventions to keep when adding tests

- PHPUnit 11, class-based, `namespace Tests\Feature\…`, `extends Tests\TestCase`, `use RefreshDatabase;`,
  method names `test_snake_case_description()`.
- `tests/TestCase.php` is **completely empty** — no `CreatesApplication`, no `RefreshDatabase`, no
  `actingAs()` helpers. Each test class sets up its own admin role inline:
  `Role::create(['name' => 'Admin', 'slug' => 'admin'])` then `$user->roles()->attach($role)`.
  Adding a shared `actingAsAdmin()` helper to `TestCase` would remove ~20 duplicated lines per file.
- Clear `RateLimiter` between throttle tests, or every later test in the class fails.
- Use `assertSessionHas('success', '…')` rather than DOM assertions — flashes vanish after 5 s.
- `composer test` runs `config:clear` before `artisan test`, which matters if you have a cached config.
- JS tests use Node's built-in runner (`node:test` + `node:assert/strict`) and are **not wired into
  `npm test`** — there is no `test` script in `package.json`. Run them with `node --test tests/js/`.
