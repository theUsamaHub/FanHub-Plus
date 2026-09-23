# FanHubPlus Admin Panel — Complete Flow Guide

How the admin panel works from login to day-to-day use.

---

## 1. Getting in

1. Go to `/login` and sign in with an admin account.
2. The app checks your role (`roles` via `role_user`). Only slug `admin` can open `/admin/*`.
3. You land on **Admin Dashboard** (`/admin`).
4. If your account is not admin, you are sent to the normal user dashboard (or profile). Guests go to login.

**Create another admin**

- Sidebar → **Users** → **Add Admin**
- Fill name, email, password
- Role is always **Admin** (assigned automatically)
- Registered users sign up themselves on the public site — do not use this form for them

**What admins cannot do on users**

- There is **no Edit** for users (no name/email/role change form)
- You can only: list, view, create admin, delete (not yourself)

---

## 2. Layout and navigation

| Area | Where | Purpose |
|------|--------|---------|
| Sidebar | Left | All management pages, grouped: Content, Users, System |
| Topbar | Top | User menu, logout |
| Command palette | **Ctrl+K** | Quick jump to any admin page |
| Main | Center | Stats, filters, tables, forms |

**Command palette (Ctrl+K)**

- Type to filter pages (name or category)
- Arrow keys to move, Enter to open, Esc to close
- Includes Content, Submissions, Characters, Merchandise, Events, Reviews, Ratings, Feedback, Users, Analytics, and system tools

---

## 3. Dashboard flow

**Location:** `/admin`

**KPI cards (click to open the filtered list)**

| Card | Goes to |
|------|---------|
| Total Users | Users |
| Total Content | Content |
| Published Content | Content filtered to published |
| Pending Submissions | Submissions queue |
| Total Categories | Categories |
| Upcoming Events | Events (published + upcoming) |
| Pending Reviews | Reviews (pending) |
| Open Feedback | Feedback (open) |
| Total Media | Media Library |

**Quick actions**

Add Content · Upload Media · Add Event · Add Character · Add Merchandise · Review Submissions

**Attention widgets**

- Recent user submissions → Review
- Pending reviews → Moderate
- Recent feedback → Open
- Upcoming events → Edit
- Popular content (by views) → View

**Chart:** User growth, last 30 days.

---

## 4. Content workflow (main publishing path)

**Location:** `/admin/contents`

### Create content

1. **Add Content**
2. Fill **Basic**: title (slug auto from title), category, type (article/video/audio/image), optional release date
3. Fill **Editorial**: excerpt, body
4. **Media** (selected from Media Library — upload files there first):
   - Cover (one image)
   - Gallery (many images)
   - Trailer (one video)
   - Audio clip (one audio)
   - Attachments (many documents)
5. **Discovery**: tags (multi-select)
6. **Publishing**: status, featured, published_at
7. Save

### Status rules

| Status | Meaning |
|--------|---------|
| draft | Not public |
| pending_review | Waiting in submission queue (user submissions) |
| published | Live; `published_at` is set automatically if empty |
| rejected | Not published |

### List tools

- Search: title, excerpt, body
- Filters: category, type, status, featured, source (admin/user), sort (latest/views/title)
- Row actions: View, Edit, Delete (confirm), Star (feature toggle)

### Edit content

Same form as create. Tags and media roles are replaced entirely on save (what you select is what remains).

### Content is not e-commerce

No cart, checkout, price, stock, or orders anywhere.

---

## 5. User submission moderation

**Location:** `/admin/submissions`

**How a submission appears**

1. A registered user submits content on the public site
2. Record is stored with `is_user_submitted = true` and `status = pending_review`
3. It shows in **Submissions** and on Dashboard (Pending Submissions)

**Admin flow**

1. Open **Submissions** (default filter: pending review)
2. **Preview** — read title, body, tags, media, submitter
3. Optionally **Edit** the content in the Content editor (fix typos, category, tags)
4. **Approve and publish** → `status = published`, `published_at = now`, `reviewed_by = you`
5. Or **Reject** → `status = rejected`, `reviewed_by = you`

**Notes**

- You do not edit the submitter’s account or role from this screen
- Admin-only content never appears in this queue (`is_user_submitted = false`)

---

## 6. Categories

**Location:** `/admin/categories`

**Create/Edit**

- Name, optional slug (auto from name), description (max 500), optional icon (upload or Media Library)

**Delete**

- First delete is a **soft delete** (moves to Trash)
- **Trash** → Restore or Force Delete
- **Force delete** fails if content/characters/merchandise/events still reference the category

**Typical order**

Create the eight fandom categories first (Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga, Cosplay), then content.

---

## 7. Media Library (R2 / images & video)

**Location:** `/admin/media`

### Upload

1. Optional alt text (accessibility)
2. Optional duration as **Hours / Minutes / Seconds** (video/audio) — saved automatically as total seconds
3. Choose files (images 5MB, documents 10MB, audio 20MB, video 50MB; up to 10 files)
4. Upload

### Manage

- Filter by type: image, video, audio, document
- Search filename or alt text
- Edit: alt text + duration (H/M/S)
- Delete: blocked if the file is still used (category icon, content, character, merchandise, event, avatar)

### Use elsewhere

Content, categories, characters, merchandise, events, and profile avatars select from this library. Upload here first, then pick the file in the other forms.

### Cloudflare R2

- Storage is S3-compatible (`r2` disk)
- In `.env`: set `R2_ACCESS_KEY_ID`, `R2_SECRET_ACCESS_KEY`, `R2_ENDPOINT`, `R2_BUCKET=fanhubplus`, `R2_PUBLIC_URL`, then `MEDIA_DISK=r2`
- Secrets stay on the server only (never in browser JS)
- Local development can keep `MEDIA_DISK=public`

### Duration example

Hours `1`, Minutes `2`, Seconds `5.5` → stored as `3725.5` seconds → shown as `1:02:05.5`

---

## 8. Tags

**Location:** `/admin/tags`

- Create/edit name and color; slug is generated
- Used on content for discovery/filtering
- Keep tags reusable; avoid near-duplicates

---

## 9. Characters

**Location:** `/admin/characters`

1. Create: name, category (required), bio, optional image from Media Library
2. Attach related content (multi-select)
3. On the detail page: attach more content or remove links
4. Delete character only removes the character and its links — content stays

---

## 10. Merchandise (showcase only)

**Location:** `/admin/merchandise`

**Fields:** name, category, description, image, tag (limited_edition / pre_order / collectible / standard), upcoming flag

**Explicitly not included:** price, stock, cart, checkout, orders, payments

---

## 11. Events

**Location:** `/admin/events`

1. **Basic:** title, description, optional category
2. **Location:** city (required), venue, address, latitude, longitude
3. **Schedule:** start (required), optional end (must not be before start)
4. **Publishing:** status (draft / published / cancelled), ticket URL, cover media

Cancelled events stay visible to admins (filter by status).

---

## 12. Reviews and ratings

**Reviews** `/admin/reviews`

1. List with friendly target labels (e.g. `Content: "Top Anime Openings"`)
2. Missing targets show `Unavailable resource`
3. **Approve** or **Reject** pending reviews
4. Optional delete

**Ratings** `/admin/ratings`

- View only (star and thumbs)
- Filter by type; optional delete
- One rating per user per target (schema unique rule)

---

## 13. Feedback

**Location:** `/admin/feedback`

**Type:** bug · suggestion · query  
**Status flow:** open → in_review → resolved → closed

**Admin flow**

1. Filter by type, status, date range, user vs guest
2. Open an item
3. Change status as you work the issue
4. Delete only if needed

---

## 14. Users (account management)

**Location:** `/admin/users`

| Action | Available |
|--------|-----------|
| List + search/filters (role, verification) | Yes |
| View detail (profile, favorites, submissions, bookmarks, ratings, reviews, feedback) | Yes |
| Create **admin** | Yes (`/admin/users/create`) |
| Edit user / change roles | **No** |
| Delete user (not yourself) | Yes |

Self-delete is blocked.

---

## 15. Analytics

**Location:** `/admin/analytics`

Reports from current schema:

- Top content by views
- Top merchandise by views
- Content by category
- Content by status
- User growth (30 days)
- Review moderation volume
- Feedback volume by type/status

Active users are not shown (no reliable last-seen tracking).

---

## 16. System tools (existing, still available)

| Page | Use |
|------|-----|
| Settings | App/SEO/social/mail settings |
| Roles | Role list management |
| Contacts / Subscribers | Inbox and newsletter list |
| Notifications | In-app inbox |
| Activity | Audit log + CSV export |
| Sessions | Revoke sessions |
| IP Restrictions | Admin IP whitelist |
| Maintenance | Maintenance mode + bypass routes |
| Health | DB/cache/queue/disk checks |
| Logs | Tail and download logs |
| Backup | PostgreSQL dumps |

---

## 17. Admin profile

**Location:** `/profile` (also linked from sidebar)

- Name, email (existing)
- Display name, bio, avatar (from Media Library), theme (light/dark/system), font size (small/medium/large)
- Password change (Laravel password flow)
- Delete own account (password confirm)

Preferences live in `user_profiles`.

---

## 18. Typical day-to-day flow

```text
Login (admin)
  → Dashboard: see pending work
  → Submissions: approve/reject fan content
  → Reviews: moderate written reviews
  → Feedback: move open items to in_review / resolved
  → Content: publish or fix articles/videos
  → Media: upload new assets when content needs them
  → Events / Merchandise / Characters: update discovery pages
  → Analytics: check views and moderation volume
  → Users: add another admin if needed
```

---

## 19. Rules that apply everywhere

- Every `/admin` route requires login + admin role (server-side)
- CSRF on all state-changing forms
- Validation on every create/update
- Status changes preferred over hard deletes where the schema allows
- Media delete is blocked while still referenced
- Category force-delete is blocked while related records exist
- No purchase/checkout flows in the admin panel

---

## 20. Quick route map

| Screen | URL |
|--------|-----|
| Dashboard | `/admin` |
| Content | `/admin/contents` |
| Submissions | `/admin/submissions` |
| Categories | `/admin/categories` |
| Media | `/admin/media` |
| Tags | `/admin/tags` |
| Characters | `/admin/characters` |
| Merchandise | `/admin/merchandise` |
| Events | `/admin/events` |
| Reviews | `/admin/reviews` |
| Ratings | `/admin/ratings` |
| Feedback | `/admin/feedback` |
| Users | `/admin/users` |
| Add Admin | `/admin/users/create` |
| Analytics | `/admin/analytics` |
| Profile | `/profile` |
