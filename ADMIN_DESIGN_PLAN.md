# FanHubPlus Admin Panel — Anime Design Plan

Purpose: redesign the admin UI with a high-end anime-inspired aesthetic (character mood board below), aligned with the public site, delivered in chunks.

---

## 1. What exists today

### Public site (`resources/css/public.css` + `components/navbar.css` + `footer.css`)
- **Mode:** dark-first, `data-theme="light|dark"`
- **Fonts:** Saira (body), Rajdhani (headings)
- **Tokens:** `--fh-bg #020207`, `--fh-surface`, `--fh-accent #dc63ff`, `--fh-cyan #44d9ff`, `--fh-line`
- **Language:** neon gradient borders (magenta → violet → cyan), glass blur panels, soft glows, italic display brand, crown mark
- **Feel:** late-night anime portal / “Every Universe. One Home.”

### Admin (`resources/css/app.scss` + `layouts/app|sidebar|topbar`)
- **Mode:** light Bootstrap 5 default
- **Fonts:** same Rajdhani + Saira
- **Tokens:** `--bs-primary #6B3FD1`, flat purple, grey `#111827` sidebar
- **Language:** stock Bootstrap cards, tables, forms
- **Gap:** admin does not feel like the public brand; generic SaaS admin

### Target
Admin must feel like the **command deck of the same universe** — same fonts, same neon language, darker and more “tactical” than the public marketing pages.

---

## 2. Concept

**Name:** “Guild Command”  
**Story:** Admin is the HQ where the guild manages every fandom universe — content, events, missions (submissions), intel (analytics).

| Layer | Public | Admin |
|-------|--------|-------|
| Story | Showcase / discovery | HQ / control deck |
| Mode | Dark-first portal | Dark tactical deck (optional light) |
| Energy | Cinematic hero | Dense, precise, powerful |
| Motifs | Crown, orbits, gradient frames | HUD lines, rank badges, spell seals, blade-edge accents |

**AVOID-AI-SLOP constraints we keep:**
- No emojis in UI
- No staggered fade-up on every card
- One accent hierarchy, not neon-everywhere
- Vary card importance (dashboard KPI hero vs quiet tables)
- Spacing scale 4/8/12/16/24/32/48/64
- Z-index tokens only
- Reduced motion respected
- Icons: keep Bootstrap Icons (already in project) — one library only

---

## 3. Character mood board → palette roles

Inspired characters used as **mood + accent roles**, not licensed art.

| Character | Series | Mood | Extracted palette role |
|-----------|--------|------|-------------------------|
| Mikasa | Attack on Titan | Discipline, steel, red scarf | **Steel / Crimson** — primary structure, alerts |
| Levi | Attack on Titan | Clean blades, ODM steel, blue-grey | **Blade Grey** — tables, chrome, topbar |
| Nezuko | Demon Slayer | Soft pink, bamboo, night | **Nezuko Pink** — user/community accents |
| Tanjiro | Demon Slayer | Checkered green/black, water | **Water Green** — success, publish |
| Hinata | Naruto | Byakugan lavender, quiet | **Byakugan Lilac** — focus rings, soft glows |
| Itachi | Naruto | Crow black, sharingan red | **Sharingan Red** — danger, reject |
| Nobara | Jujutsu Kaisen | Straw doll, rose/brown, nails | **Resonance Rose** — featured, ratings |
| Asuna | SAO | White/gold, knight | **Knight Gold** — premium, featured content |
| Gojo | Jujutsu Kaisen | Blindfold, limitless blue | **Limitless Blue** — links, info, charts |
| Luffy | One Piece | Straw gold, red vest | **Straw Gold / Sail Red** — CTAs, upcoming events |

### Admin theme tokens (new `--fh-adm-*`)

```
Core chrome (shared with public DNA)
  --fh-adm-bg          #05050d
  --fh-adm-surface     #0c0d18
  --fh-adm-panel       #121326
  --fh-adm-line        #2a2748
  --fh-adm-text        #f3f0ff
  --fh-adm-muted       #a29bc4

Brand (inherits public)
  --fh-accent          #dc63ff   (Nezuko/Mikasa hybrid magenta)
  --fh-cyan            #44d9ff   (Gojo / limitless)

Character accents (module coding)
  --fh-steel           #8b9bb4   Levi/Mikasa
  --fh-crimson         #c43b3b   Itachi reject / danger
  --fh-water           #2ce8c7   Tanjiro success (already in dark-highlight)
  --fh-lilac           #b49cff   Hinata focus
  --fh-rose            #ff6b9d   Nobara featured/ratings
  --fh-gold            #ffd23f   Asuna/Luffy gold
  --fh-sail            #ff5a5a   Luffy action red
```

**Module accent map (subtle, not a rainbow UI):**
- Content → Gold (Asuna)
- Submissions → Cyan (Gojo) / Crimson on reject
- Characters → Lilac (Hinata)
- Merchandise → Rose (Nobara)
- Events → Sail/Gold (Luffy)
- Reviews → Pink (Nezuko)
- Feedback → Water (Tanjiro)
- Users → Steel (Levi)
- Analytics → Limitless Blue
- System → Steel/Grey

Use accents only on: active nav edge, KPI icon tint, status chips, focus rings, small glows. Never color entire page sections.

---

## 4. Information architecture (unchanged)

Routes, controllers, and business logic stay as-is. **Design-only** work in chunks.

---

## 5. Chunk plan (implementation order)

### Chunk A — Foundation
**Goal:** tokens + admin shell look like FanHubPlus
- `resources/css/admin.css` (or `admin/_tokens.scss` imported by `app.scss`) with `--fh-adm-*`
- Scope: `body.fh-admin` on `layouts/app.blade.php`
- Restyle: sidebar (gradient edge, crown brand, group labels, active rail), topbar (glass strip, avatar, Ctrl+K hint), main bg
- Command palette: same panel language as public search dialog
- Files: `app.scss` or new `admin.css`, `layouts/app.blade.php`, `layouts/sidebar.blade.php`, `layouts/topbar.blade.php`, `partials/command-palette.blade.php`

### Chunk B — Core components
- Cards / KPI cards (hero KPI with gradient border + glow)
- Buttons (primary = gradient magenta→violet like `fh-login`)
- Tables (dark rows, hover line glow)
- Forms (inputs match public search fields)
- Status chips (Draft / Pending / Published / Rejected / Open / …)
- Empty states, alerts, badges, pagination
- Files: `resources/css/components/admin-*.css` + small Blade partials if needed

### Chunk C — Dashboard
- KPI row with character accents
- Widgets: submissions, reviews, feedback, events, popular content
- Chart bars with cyan/magenta gradient
- Quick actions as “mission” chips
- Files: `admin/dashboard.blade.php` + CSS only if needed

### Chunk D — List + form modules (batch)
Apply shell + components to:
1. Contents + Submissions  
2. Categories + Tags + Media  
3. Characters + Merchandise + Events  
4. Reviews + Ratings + Feedback + Users + Analytics  

Keep HTML structure; replace Bootstrap-looking classes with `fh-adm-*` classes where needed.

### Chunk E — Polish
- Light theme parity (`data-theme="light"` like public)
- `prefers-reduced-motion`
- Mobile sidebar drawer + responsive tables
- Focus states, contrast pass
- Screenshots at 375 / 768 / 1440

---

## 6. Layout sketch (admin shell)

```
┌──────────────────────────────────────────────────────────┐
│ SIDEBAR (250px, gradient right edge)     TOPBAR glass    │
│  [Crown] FanHubPlus                      Ctrl+K  [Avatar]│
│  ── OVERVIEW ─────────                                  │
│  Dashboard                                           │
│  Profile                                             │
│  ── CONTENT ────                                        │
│  Content | Submissions | Characters | Merch…            │
│  Events | Reviews | Ratings | Feedback                  │
│  Categories | Tags | Media                              │
│  ── USERS ──────                                        │
│  Users | Roles                                         │
│  ── SYSTEM ─────                                        │
│  Activity | Sessions | Logs | Backup                    │
│  ──────────── [avatar footer] ───────────               │
├──────────────────────────────────────────────────────────┤
│  PAGE TITLE + actions                                    │
│  [KPI] [KPI] [KPI] [KPI]                                 │
│  ┌─ panel (gradient border) ─────────────────────────┐   │
│  │ table / form / widgets                            │   │
│  └───────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────┘
```

---

## 7. Signature visual moves (limited)

1. **Gradient frame** (already in public navbar/footer) on panels and KPI cards  
2. **Blade-edge active rail** (2px vertical gradient) on sidebar active item  
3. **Soft glow** only on primary CTA and focused input  
4. **Status chips** with icon + color (never color alone)  
5. **Micro motion:** hover lift on cards, input focus glow — nothing on page load  

---

## 8. Files likely changed (full program)

| File | Change |
|------|--------|
| `resources/css/admin.css` (new) | Admin tokens + shell + components |
| `resources/css/app.scss` | Import admin skin / fix body scope |
| `resources/css/public.css` | Optional shared token export (minimal) |
| `resources/views/layouts/app.blade.php` | `fh-admin` class, theme attr |
| `resources/views/layouts/sidebar.blade.php` | Structure + classes |
| `resources/views/layouts/topbar.blade.php` | Structure + classes |
| `resources/views/partials/command-palette.blade.php` | Panel styling |
| `resources/views/admin/dashboard.blade.php` | Dashboard skin |
| `resources/views/admin/**/index|create|edit|show` | Class swaps per chunk |
| `resources/views/components/*` | Button/input/empty-state if restyled |

**Not in scope:** PHP business logic, migrations, public home hero rebuild, chatbot.

---

## 9. Chunk A acceptance (first ship)

- [ ] Admin dark background matches `--fh-adm-bg`
- [ ] Sidebar has brand crown + group labels + active rail
- [ ] Topbar glass + Ctrl+K affordance
- [ ] Cards/buttons/inputs visibly FanHubPlus (not default Bootstrap)
- [ ] Dashboard loads without layout break
- [ ] No console errors; keyboard nav works
- [ ] Reduced-motion respected

---

## 10. Later optional (not required now)

- Per-user character theme picker (switch `--fh-accent` to Gojo blue / Nezuko pink)
- Animated “curse energy” subtle noise on login only
- Anime key visual strip on empty dashboard
