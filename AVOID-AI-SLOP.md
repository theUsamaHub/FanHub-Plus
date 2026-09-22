# Avoid AI Slop — Frontend Guidelines

> Rules to keep our UI clean, intentional, and distinctly human.

---

## 1. Never Use Emojis in UI

Emojis in interfaces look unprofessional and break visual consistency.

**Wrong:**
```
🚀 Welcome to our platform!
✅ Task completed successfully
❌ Something went wrong
🎉 Congratulations!
```

**Right:**
```
Welcome to our platform.
Task completed successfully.
Something went wrong.
Congratulations.
```

**Exception:** Emoji-based brands or products where it's part of the identity.

---

## 2. No Fade-Slide-Up Animations on Every Section

The "staggered fade-in from below" is the #1 AI slop tell. Every section, card, and element sliding up on scroll screams generated code.

**Rules:**
- ONE page-load animation maximum (hero entrance or a single reveal)
- Hover transitions on interactive elements only (buttons, links, cards)
- No infinite scrolling animations
- No parallax unless the product demands it
- Respect `prefers-reduced-motion`

**Wrong:**
```css
.fade-in {
  animation: fadeInUp 0.6s ease-out;
}
.card { animation: fadeInUp 0.5s ease-out; }
.hero { animation: fadeInUp 0.7s ease-out; }
```

**Right:**
```css
/* One entrance, timed, purposeful */
.hero {
  animation: heroEnter 0.8s ease-out;
}

/* Interaction feedback only */
.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

@media (prefers-reduced-motion: reduce) {
  * { animation: none !important; }
}
```

---

## 3. No Warm Cream Backgrounds + Terracotta Accents

The near-cream (#F4F1EA) with warm clay (#D97757) accent is Anthropic's signature palette. Using it makes your project look like a Claude clone.

**Avoid these combos:**
- Cream background + terracotta accent
- Near-black (#0B0B0B, #111) + acid green (#39FF14)
- Warm beige + burnt orange

**Use instead:** Pick a palette from your actual brand or subject matter.

---

## 4. No SaaS Card Kit

The generic SaaS look is instantly recognizable:

**Avoid:**
- Identical rounded cards for every content block
- Same `border-radius: 12px` on everything
- Same soft grey shadow: `box-shadow: 0 4px 6px rgba(0,0,0,0.1)`
- Gradient washes as decoration
- Cards with no visual hierarchy (everything looks the same)

**Do:**
- Vary card sizes and shapes based on content importance
- Use different border-radius for different element types
- Shadows should be subtle and purposeful
- Let one card type dominate, others support

---

## 5. No Template Chrome

These are the dead giveaways of AI-generated designs:

| Pattern | Why It's Bad | Fix |
|---------|-------------|-----|
| ALL-CAPS eyebrow labels above every heading | Decorative, not informative | Remove or use sparingly for ONE section type |
| Middle-dot meta strings: `A · B · C` | Filler, not design | Use proper separators or remove |
| Em-dash fragments: `WORD — description` | Overused template style | Write full sentences or use colon |
| Monospace font for data labels | Looks techy but lazy | Use the body font or a proper label style |
| Arrow on every link: `→ Learn more` | Visual noise | Plain link text is fine |
| Numbered markers: `01 / 02 / 03` | Only for actual sequences | Remove unless showing steps |

---

## 6. Typography Rules

**Typeface selection:**
- Pick 1-2 font families max
- Do NOT default to Inter/Roboto/Montserrat/Poppins for every project
- Check the subject matter — a toy company needs different type than a law firm

**Type scale:**
- Set a clear scale: `12 / 14 / 16 / 20 / 24 / 32 / 48 / 64`
- Use consistent weights (300, 400, 500, 600, 700)
- Line height: 1.5 for body, 1.2 for headings

**Avoid:**
- Accenting one word in a headline with italic/bold/color
- Using all caps for labels everywhere
- Adding typographic labels above content just to fill space
- Line lengths over 80 characters
- Inconsistent font weights across pages

---

## 7. Layout Rules

**Structure:**
- One clear visual hierarchy per page
- Left-aligned for text-heavy content
- Center-aligned for hero sections only
- Never center long paragraphs

**Spacing:**
- Use consistent spacing scale (4, 8, 12, 16, 24, 32, 48, 64)
- Section padding: minimum 48px vertical
- Element spacing should relate to content, not arbitrary numbers

**Avoid:**
- Newspaper-style dense columns with hairline rules
- Zero border-radius everywhere (unless intentional brutalism)
- Identical card grid for unrelated content types
- Content blocks that all look the same size

---

## 8. Color Rules

**Palette:**
- 1 primary, 1 secondary, 1-2 accent colors
- Neutral palette: 5-6 greys (not just black/white)
- Semantic colors: success (green), warning (amber), error (red), info (blue)

**Avoid:**
- Neon accents on dark backgrounds (acid green, electric blue)
- Warm cream (#F4F1EA) as default background
- High contrast between background and text without purpose
- Gradient backgrounds that add no meaning

**Check:** Does the color palette come from the subject matter? A healthcare app should not look like a gaming platform.

---

## 9. Motion Rules

| Type | When to Use | Example |
|------|-------------|---------|
| Page load | ONE entrance animation for hero/key element | Hero fades in, everything else is already there |
| Interaction feedback | Button hover, card hover, link hover | Subtle scale, color shift, underline |
| State change | Toast notifications, modals, dropdowns | Slide in from edge, fade overlay |
| Progress | Loading states, form submission | Spinner, progress bar |

**Never:**
- Stagger animations on every card in a grid
- Add floating particles or background motion
- Use bounce/elastic easing on everything
- Animate elements that appear only once (no repeat = no animation needed)

---

## 10. Content Rules

**Writing:**
- Active voice: "Save changes" not "Submit"
- Sentence case for headings, not Title Case
- No filler words: "Get started" not "Get started today!"
- Error messages explain what happened and how to fix it
- Empty states guide action, not apologize

**Avoid:**
- Generic placeholder text that sounds like marketing copy
- Overly enthusiastic language: "Amazing!", "Incredible!", "Revolutionary!"
- Vague CTAs: "Learn more", "Click here", "Get started"
- Long paragraphs in UI — keep it short

---

## 11. Responsive Design Checklist

- [ ] Mobile-first approach
- [ ] Touch targets minimum 44x44px
- [ ] No horizontal scroll on any screen
- [ ] Text readable without zoom (16px minimum body)
- [ ] Navigation works on mobile (hamburger or tabs)
- [ ] Forms usable on mobile (proper input types)
- [ ] Images scale correctly
- [ ] No fixed widths that break on small screens
- [ ] Keyboard navigation works
- [ ] Screen reader tested

---

## 12. Accessibility Checklist

- [ ] Color contrast ratio minimum 4.5:1 for text
- [ ] Focus visible on all interactive elements
- [ ] Alt text on all images
- [ ] Form labels associated with inputs
- [ ] Error messages linked to form fields
- [ ] Skip navigation link
- [ ] Proper heading hierarchy (h1 > h2 > h3)
- [ ] ARIA labels where needed
- [ ] Reduced motion respected
- [ ] Keyboard navigable

---

## 13. Design Token System

Create a single source of truth for all design values. No ad-hoc values anywhere.

**Create `resources/css/tokens.css`:**
```css
:root {
  /* Colors */
  --color-primary: #2563eb;
  --color-primary-hover: #1d4ed8;
  --color-secondary: #64748b;
  --color-success: #16a34a;
  --color-warning: #d97706;
  --color-error: #dc2626;
  --color-info: #0284c7;

  --color-bg: #ffffff;
  --color-bg-secondary: #f8fafc;
  --color-text: #0f172a;
  --color-text-secondary: #64748b;
  --color-border: #e2e8f0;

  --color-neutral-50: #f8fafc;
  --color-neutral-100: #f1f5f9;
  --color-neutral-200: #e2e8f0;
  --color-neutral-300: #cbd5e1;
  --color-neutral-400: #94a3b8;
  --color-neutral-500: #64748b;
  --color-neutral-600: #475569;
  --color-neutral-700: #334155;
  --color-neutral-800: #1e293b;
  --color-neutral-900: #0f172a;

  /* Spacing */
  --space-1: 4px;
  --space-2: 8px;
  --space-3: 12px;
  --space-4: 16px;
  --space-5: 24px;
  --space-6: 32px;
  --space-7: 48px;
  --space-8: 64px;
  --space-9: 96px;

  /* Border Radius */
  --radius-sm: 4px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --radius-xl: 16px;
  --radius-full: 9999px;

  /* Shadows */
  --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
  --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
  --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.04);

  /* Typography */
  --font-body: 'Inter', system-ui, sans-serif;
  --font-heading: 'Inter', system-ui, sans-serif;
  --font-mono: 'JetBrains Mono', monospace;

  --text-xs: 12px;
  --text-sm: 14px;
  --text-base: 16px;
  --text-lg: 20px;
  --text-xl: 24px;
  --text-2xl: 32px;
  --text-3xl: 48px;
  --text-4xl: 64px;

  --leading-tight: 1.2;
  --leading-normal: 1.5;
  --leading-relaxed: 1.7;
}
```

**Rule:** Never write `padding: 13px` or `color: #333` in components. Always use `var(--space-3)` or `var(--color-neutral-700)`.

---

## 14. Component Consistency Audit

Define core components ONCE. Never create a new style when an existing one works.

**Core components every project needs:**

| Component | Variants | Never Create |
|-----------|----------|--------------|
| Button | primary, secondary, ghost, danger | New button styles per page |
| Card | default, elevated, outlined | Random card layouts |
| Input | default, error, disabled | New input styles |
| Modal | sm, md, lg | Different modal designs |
| Toast | success, error, warning, info | Custom notification styles |
| Table | default, striped, compact | New table layouts |

**Checklist:**
- [ ] All buttons use the same component
- [ ] All inputs have same height, padding, border-radius
- [ ] All cards share same base style
- [ ] All modals follow same pattern
- [ ] No page-specific component styles

---

## 15. Icon System Rule

Pick ONE icon library. Never mix styles.

**Rule:** Use only one of these across the entire project:
- **Lucide** — `lucide-static` or `lucide-react`
- **Heroicons** — `heroicons` package
- **Phosphor** — `@phosphor-icons/react`

**Wrong:**
```html
<!-- Mixing Lucide, Heroicons, and custom SVGs -->
<i data-lucide="user"></i>
<svg><!-- Heroicons style --></svg>
<svg><!-- Custom random icon --></svg>
```

**Right:**
```html
<!-- Consistent Lucide icons only -->
<i data-lucide="user"></i>
<i data-lucide="settings"></i>
<i data-lucide="bell"></i>
```

**Check:** Search your codebase — if you find more than one icon library imported, fix it.

---

## 16. Form Pattern Standard

Every form follows the same structure.

**Structure:**
```html
<div class="form-group">
  <label for="email" class="form-label">Email address</label>
  <input
    type="email"
    id="email"
    class="form-input"
    placeholder="you@example.com"
  />
  <p class="form-error">Email is required.</p>
</div>
```

**Rules:**
- Label ABOVE input, always
- Error message BELOW input, linked with `aria-describedby`
- Consistent spacing: 8px between label and input, 16px between fields
- One primary button style per form (usually bottom-right or full-width)
- Placeholder is example, not label

**Check:**
- [ ] All inputs same height (40-44px)
- [ ] All labels same position
- [ ] Error states same style
- [ ] Buttons same placement

---

## 17. Loading State Standard

Two types only: **Skeleton for content**, **Spinner for actions**.

### Skeleton Loading Logic

Skeletons show the shape of content before it loads. No blank pages.

**CSS:**
```css
.skeleton {
  background: linear-gradient(
    90deg,
    var(--color-neutral-200) 25%,
    var(--color-neutral-100) 50%,
    var(--color-neutral-200) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s ease-in-out infinite;
  border-radius: var(--radius-md);
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Skeleton shapes */
.skeleton-text {
  height: 16px;
  margin-bottom: 8px;
}
.skeleton-text:last-child {
  width: 60%;
}
.skeleton-title {
  height: 24px;
  width: 40%;
  margin-bottom: 16px;
}
.skeleton-avatar {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-full);
}
.skeleton-card {
  height: 200px;
  border-radius: var(--radius-lg);
}
.skeleton-image {
  width: 100%;
  height: 200px;
  border-radius: var(--radius-md);
}

@media (prefers-reduced-motion: reduce) {
  .skeleton {
    animation: none;
    background: var(--color-neutral-200);
  }
}
```

**Blade Component (`resources/views/components/skeleton.blade.php`):**
```blade
@props(['type' => 'text', 'count' => 1])

@if($type === 'text')
  @for($i = 0; $i < $count; $i++)
    <div class="skeleton skeleton-text"></div>
  @endfor

@elseif($type === 'title')
  <div class="skeleton skeleton-title"></div>

@elseif($type === 'card')
  <div class="skeleton skeleton-card"></div>

@elseif($type === 'avatar')
  <div class="skeleton skeleton-avatar"></div>

@elseif($type === 'image')
  <div class="skeleton skeleton-image"></div>

@elseif($type === 'table')
  @for($i = 0; $i < $count; $i++)
    <div class="skeleton skeleton-text" style="height: 40px; margin-bottom: 4px;"></div>
  @endfor

@elseif($type === 'list')
  @for($i = 0; $i < $count; $i++)
    <div style="display: flex; gap: 12px; margin-bottom: 16px;">
      <x-skeleton type="avatar" />
      <div style="flex: 1;">
        <x-skeleton type="text" :count="2" />
      </div>
    </div>
  @endfor
@endif
```

**Usage in Blade:**
```blade
{{-- While loading --}}
<div wire:loading.class="hidden">
  <x-skeleton type="title" />
  <x-skeleton type="text" :count="3" />

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
    <x-skeleton type="card" />
    <x-skeleton type="card" />
    <x-skeleton type="card" />
  </div>
</div>

{{-- After load --}}
<div wire:loading.remove>
  <h1>Dashboard</h1>
  <p>Welcome back...</p>
  {{-- actual content --}}
</div>
```

**Alpine.js Loading:**
```html
<div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 1000)">
  <!-- Skeleton while loading -->
  <template x-if="loading">
    <div class="skeleton skeleton-card"></div>
  </template>

  <!-- Content after load -->
  <template x-if="!loading">
    <div class="card">Actual content here</div>
  </template>
</div>
```

### Spinner for Actions (buttons, form submit)

```css
.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid var(--color-neutral-300);
  border-top-color: var(--color-primary);
  border-radius: var(--radius-full);
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
```

**Usage:**
```html
<button class="btn btn-primary" onclick="submitForm(this)">
  <span class="btn-text">Save changes</span>
  <span class="spinner" style="display: none;"></span>
</button>

<script>
function submitForm(btn) {
  btn.querySelector('.btn-text').style.display = 'none';
  btn.querySelector('.spinner').style.display = 'block';
  btn.disabled = true;
  // ... submit
}
</script>
```

**Rule:** Never show a blank white page while loading. Always show skeleton shape of the content.

---

## 18. Empty State Pattern

Every list, table, and search result gets an empty state.

**Structure:**
- Icon (from your icon library, NOT emoji)
- Headline: what's missing
- Body: why it's empty
- Action: what to do next

**Blade Component (`resources/views/components/empty-state.blade.php`):**
```blade
@props(['icon' => 'inbox', 'title', 'description', 'actionText' => null, 'actionUrl' => null])

<div class="empty-state">
  <div class="empty-state-icon">
    <i data-lucide="{{ $icon }}"></i>
  </div>
  <h3 class="empty-state-title">{{ $title }}</h3>
  <p class="empty-state-description">{{ $description }}</p>
  @if($actionText && $actionUrl)
    <a href="{{ $actionUrl }}" class="btn btn-primary">
      {{ $actionText }}
    </a>
  @endif
</div>
```

**CSS:**
```css
.empty-state {
  text-align: center;
  padding: var(--space-8) var(--space-4);
}

.empty-state-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto var(--space-4);
  color: var(--color-neutral-400);
}

.empty-state-icon svg {
  width: 100%;
  height: 100%;
}

.empty-state-title {
  font-size: var(--text-lg);
  font-weight: 600;
  margin-bottom: var(--space-2);
  color: var(--color-text);
}

.empty-state-description {
  color: var(--color-text-secondary);
  margin-bottom: var(--space-5);
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}
```

**Usage:**
```blade
@if($users->isEmpty())
  <x-empty-state
    icon="users"
    title="No users yet"
    description="Get started by creating your first user account."
    actionText="Add user"
    actionUrl="/admin/users/create"
  />
@else
  <table>...</table>
@endif
```

**Common empty states:**
- No results: "No results for 'search term'" + Clear search button
- No data: "No items yet" + Create button
- Error: "Couldn't load data" + Retry button
- No permissions: "You don't have access" + Contact admin

---

## 19. Dark Mode Audit

If dark mode exists, it must be a proper theme — not just inverted colors.

**Rules:**
- [ ] Test all text for 4.5:1 contrast ratio
- [ ] Shadows become darker/subtler, not removed
- [ ] Borders are visible but not harsh
- [ ] Images/media don't need brightness adjustment
- [ ] Status colors adjusted for dark backgrounds
- [ ] Skeleton colors inverted
- [ ] No pure black (#000) — use #0f172a or #1a1a1a

**Dark mode tokens:**
```css
[data-theme="dark"] {
  --color-bg: #0f172a;
  --color-bg-secondary: #1e293b;
  --color-text: #f1f5f9;
  --color-text-secondary: #94a3b8;
  --color-border: #334155;
  --color-neutral-50: #1e293b;
  --color-neutral-100: #334155;
  --color-neutral-200: #475569;
  --shadow-sm: 0 1px 2px rgba(0,0,0,0.3);
  --shadow-md: 0 4px 6px rgba(0,0,0,0.4);
}
```

---

## 20. Responsive Breakpoint Standard

Use ONLY these breakpoints. No random custom ones.

| Prefix | Width | Use For |
|--------|-------|---------|
| `sm` | 640px | Large phones |
| `md` | 768px | Tablets |
| `lg` | 1024px | Small laptops |
| `xl` | 1280px | Desktops |
| `2xl` | 1536px | Large screens |

**Wrong:**
```css
@media (max-width: 700px) { }
@media (max-width: 900px) { }
@media (max-width: 1100px) { }
```

**Right:**
```css
@media (max-width: 640px) { }
@media (max-width: 768px) { }
@media (max-width: 1024px) { }
```

**Check:** Search for `@media` — if widths don't match the standard, fix them.

---

## 21. Spacing Scale Enforcement

Use ONLY these values. No `13px`, `27px`, `35px`.

**Allowed:**
```
4, 8, 12, 16, 24, 32, 48, 64, 96, 128
```

**Wrong:**
```css
padding: 13px;
margin-top: 27px;
gap: 35px;
```

**Right:**
```css
padding: var(--space-3);    /* 12px */
margin-top: var(--space-6); /* 32px */
gap: var(--space-6);        /* 32px */
```

**Check:** Grep for `px` values in your CSS/Blade. Anything not in the scale should be replaced with a token.

---

## 22. Before-Ship Screenshot Test

Take screenshots at 3 viewports BEFORE shipping.

**Viewport sizes:**
- **Mobile:** 375 × 812 (iPhone X)
- **Tablet:** 768 × 1024 (iPad)
- **Desktop:** 1440 × 900 (MacBook)

**Process:**
1. Open browser DevTools
2. Toggle device toolbar
3. Set custom viewport to each size
4. Screenshot the full page
5. Compare side-by-side

**Check for:**
- [ ] No horizontal scroll
- [ ] No overlapping elements
- [ ] Text readable at all sizes
- [ ] Buttons/links touchable (min 44px)
- [ ] Images not stretched/distorted
- [ ] Tables scroll horizontally or stack
- [ ] Forms usable on mobile
- [ ] Navigation accessible
- [ ] No content cut off

**Save screenshots** to `/screenshots/` folder for review.

---

## 23. Toast/Notification Pattern

Standard placement, timing, and stacking for toast notifications.

**Rules:**
- Position: **top-right** on desktop, **bottom-center** on mobile
- Auto-dismiss: **3-5 seconds** for success, **5-8 seconds** for errors
- Max **3 visible** at once (stack older ones)
- Never block content — always overlay
- Dismissible with close button AND auto-timeout

**CSS:**
```css
.toast-container {
  position: fixed;
  top: var(--space-4);
  right: var(--space-4);
  z-index: 400;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  max-width: 400px;
}

.toast {
  padding: var(--space-3) var(--space-4);
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  display: flex;
  align-items: center;
  gap: var(--space-3);
  animation: toastIn 0.3s ease-out;
}

.toast-success { border-left: 4px solid var(--color-success); }
.toast-error { border-left: 4px solid var(--color-error); }
.toast-warning { border-left: 4px solid var(--color-warning); }
.toast-info { border-left: 4px solid var(--color-info); }

@keyframes toastIn {
  from { opacity: 0; transform: translateX(100%); }
  to { opacity: 1; transform: translateX(0); }
}

@media (max-width: 640px) {
  .toast-container {
    top: auto;
    bottom: var(--space-4);
    left: var(--space-4);
    right: var(--space-4);
    max-width: none;
  }
}
```

**Blade Component (`resources/views/components/toast.blade.php`):**
```blade
@props(['type' => 'success', 'message'])

<div class="toast toast-{{ $type }}" role="alert">
  <span>{{ $message }}</span>
  <button onclick="this.closest('.toast').remove()" aria-label="Close">
    &times;
  </button>
</div>
```

**Check:**
- [ ] Same position across all pages
- [ ] Same timing for same type
- [ ] Max 3 visible
- [ ] Works on mobile

---

## 24. Modal Rules

Modals must be accessible and consistent.

**Rules:**
- Focus **trapped** inside modal when open
- **ESC key** closes modal
- **Click outside** closes modal
- Only **one modal** at a time (no stacking)
- **Body scroll locked** when modal open
- Return focus to trigger element on close

**CSS:**
```css
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 300;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-4);
}

.modal {
  background: var(--color-bg);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  max-width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  width: 100%;
}

.modal-sm { max-width: 400px; }
.modal-md { max-width: 560px; }
.modal-lg { max-width: 720px; }

.modal-header {
  padding: var(--space-5);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: var(--space-5);
}

.modal-footer {
  padding: var(--space-4) var(--space-5);
  border-top: 1px solid var(--color-border);
  display: flex;
  justify-content: flex-end;
  gap: var(--space-3);
}

body.modal-open {
  overflow: hidden;
}
```

**Alpine.js Implementation:**
```html
<div x-data="{ open: false }">
  <button @click="open = true" @keydown.escape.window="open = false">
    Open modal
  </button>

  <div
    x-show="open"
    x-cloak
    class="modal-overlay"
    @click.self="open = false"
    role="dialog"
    aria-modal="true"
  >
    <div class="modal modal-md">
      <div class="modal-header">
        <h2>Title</h2>
        <button @click="open = false" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        Content here
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="open = false">Cancel</button>
        <button class="btn btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</div>
```

**Check:**
- [ ] ESC closes
- [ ] Click outside closes
- [ ] Focus trapped inside
- [ ] Body scroll locked
- [ ] No modal stacking

---

## 25. Table Design Standard

Consistent table styling across all list views.

**Rules:**
- Header: bold, slightly darker background
- Rows: zebra striping OR borders (not both)
- Hover state on rows
- Horizontal scroll on mobile (never break layout)
- Max 10-25 rows per page with pagination

**CSS:**
```css
.table-container {
  overflow-x: auto;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
}

.table {
  width: 100%;
  border-collapse: collapse;
  font-size: var(--text-sm);
}

.table th {
  background: var(--color-bg-secondary);
  padding: var(--space-3) var(--space-4);
  text-align: left;
  font-weight: 600;
  color: var(--color-text-secondary);
  border-bottom: 1px solid var(--color-border);
  white-space: nowrap;
}

.table td {
  padding: var(--space-3) var(--space-4);
  border-bottom: 1px solid var(--color-border);
}

.table tbody tr:last-child td {
  border-bottom: none;
}

.table tbody tr:hover {
  background: var(--color-bg-secondary);
}

/* Zebra striping (optional - use ONE or the other) */
.table-striped tbody tr:nth-child(even) {
  background: var(--color-neutral-50);
}
```

**Check:**
- [ ] Header style consistent
- [ ] Mobile: horizontal scroll works
- [ ] Pagination below table
- [ ] Action buttons aligned right

---

## 26. Dropdown/Select Pattern

Consistent dropdown behavior across forms.

**Rules:**
- Same height as inputs (40-44px)
- Keyboard navigable: Arrow keys + Enter to select
- ESC closes dropdown
- Click outside closes
- Use native `<select>` unless custom features needed

**CSS:**
```css
.select {
  height: 44px;
  padding: 0 var(--space-4);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-bg);
  font-size: var(--text-base);
  color: var(--color-text);
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,..."); /* chevron icon */
  background-repeat: no-repeat;
  background-position: right var(--space-3) center;
}

.select:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.select:disabled {
  background-color: var(--color-neutral-100);
  cursor: not-allowed;
}
```

**Check:**
- [ ] Height matches inputs
- [ ] Keyboard navigable
- [ ] Closes on ESC / click outside
- [ ] Disabled state visible

---

## 27. Tooltip Rules

Tooltips provide supplementary info — never critical info.

**Rules:**
- **Delay:** 300ms before showing on hover
- **Mobile:** Disable on touch devices (use tap alternative)
- **Length:** Max 1 line, short text only
- **Never** hide required information in tooltips
- **Position:** Above element by default, flip if no space

**CSS:**
```css
.tooltip {
  position: relative;
}

.tooltip::after {
  content: attr(data-tooltip);
  position: absolute;
  bottom: calc(100% + 8px);
  left: 50%;
  transform: translateX(-50%);
  background: var(--color-neutral-900);
  color: white;
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-sm);
  font-size: var(--text-xs);
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.2s, visibility 0.2s;
  z-index: 100;
  pointer-events: none;
}

.tooltip:hover::after {
  opacity: 1;
  visibility: visible;
  transition-delay: 300ms;
}

/* Disable on touch devices */
@media (hover: none) {
  .tooltip::after { display: none; }
}
```

**Usage:**
```html
<button class="tooltip" data-tooltip="Delete item">
  <i data-lucide="trash-2"></i>
</button>
```

**Check:**
- [ ] Not used for critical info
- [ ] Short text only
- [ ] Disabled on mobile
- [ ] 300ms delay

---

## 28. Breadcrumb Standard

Navigation trail for nested pages.

**Rules:**
- Format: `Home > Level 1 > Level 2`
- Max **3 levels** visible
- Current page **not clickable** (last item)
- Hidden on mobile (use back button instead)
- Separator: chevron icon, not slash or pipe

**CSS:**
```css
.breadcrumb {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  margin-bottom: var(--space-5);
  list-style: none;
  padding: 0;
}

.breadcrumb a {
  color: var(--color-text-secondary);
  text-decoration: none;
}

.breadcrumb a:hover {
  color: var(--color-primary);
}

.breadcrumb li:not(:last-child)::after {
  content: '/';
  margin-left: var(--space-2);
  color: var(--color-neutral-400);
}

.breadcrumb li:last-child {
  color: var(--color-text);
  font-weight: 500;
}

@media (max-width: 640px) {
  .breadcrumb { display: none; }
}
```

**Blade Usage:**
```blade
<nav aria-label="Breadcrumb">
  <ol class="breadcrumb">
    <li><a href="/">Home</a></li>
    <li><a href="/admin/users">Users</a></li>
    <li>{{ $user->name }}</li>
  </ol>
</nav>
```

**Check:**
- [ ] Max 3 levels
- [ ] Current page not a link
- [ ] Hidden on mobile

---

## 29. Pagination Pattern

Consistent pagination for lists and tables.

**Rules:**
- Format: `First | Prev | 1 2 3 ... | Next | Last`
- Highlight current page
- Show "Showing X of Y" summary
- Disable prev/first on page 1, next/last on last page
- Max 5 page numbers visible

**CSS:**
```css
.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: var(--space-4);
  padding: var(--space-4) 0;
}

.pagination-info {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
}

.pagination-links {
  display: flex;
  gap: var(--space-1);
}

.pagination-link {
  min-width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 var(--space-3);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-bg);
  color: var(--color-text);
  text-decoration: none;
  font-size: var(--text-sm);
}

.pagination-link:hover:not(:disabled):not(.active) {
  background: var(--color-bg-secondary);
}

.pagination-link.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
}

.pagination-link:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
```

**Blade Usage:**
```blade
<div class="pagination">
  <div class="pagination-info">
    Showing {{ $items->firstItem() }} to {{ $items->lastItem() }}
    of {{ $items->total() }} results
  </div>
  <div class="pagination-links">
    {{ $items->links() }}
  </div>
</div>
```

**Check:**
- [ ] "Showing X of Y" text present
- [ ] Current page highlighted
- [ ] Disabled states work
- [ ] Mobile: wraps properly

---

## 30. Image Handling

Prevent layout shift and broken images.

**Rules:**
- Always set `width` and `height` attributes (prevent CLS)
- `object-fit: cover` for card/avatar images
- `loading="lazy"` for below-fold images
- `loading="eager"` only for hero/above-fold
- Fallback for broken images

**CSS:**
```css
.img-cover {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.img-responsive {
  max-width: 100%;
  height: auto;
}

.img-avatar {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-full);
  object-fit: cover;
}

.img-placeholder {
  background: var(--color-neutral-100);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-neutral-400);
}
```

**Usage:**
```html
<img
  src="photo.jpg"
  alt="Description"
  width="400"
  height="300"
  loading="lazy"
  class="img-cover"
  onerror="this.classList.add('img-placeholder'); this.src='/placeholder.svg';"
/>
```

**Check:**
- [ ] All images have width/height
- [ ] Below-fold images lazy loaded
- [ ] Alt text present
- [ ] Broken image fallback exists

---

## 31. Focus/Keyboard Navigation

Full keyboard support for all interactive elements.

**Rules:**
- Tab order follows visual order (left-to-right, top-to-bottom)
- Focus ring **visible** on all interactive elements
- Skip-to-content link as first tab stop
- No keyboard traps (can always escape modals/menus)
- Enter/Space activates buttons and links

**CSS:**
```css
/* Visible focus ring */
:focus-visible {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}

/* Remove default outline only when using :focus-visible */
:focus:not(:focus-visible) {
  outline: none;
}

/* Skip to content link */
.skip-to-content {
  position: absolute;
  top: -100%;
  left: var(--space-4);
  background: var(--color-primary);
  color: white;
  padding: var(--space-3) var(--space-4);
  border-radius: var(--radius-md);
  z-index: 1000;
  text-decoration: none;
}

.skip-to-content:focus {
  top: var(--space-4);
}
```

**HTML:**
```html
<body>
  <a href="#main-content" class="skip-to-content">Skip to content</a>
  <nav>...</nav>
  <main id="main-content">
    <!-- Page content -->
  </main>
</body>
```

**Check:**
- [ ] Tab through entire page works
- [ ] Focus always visible
- [ ] Skip link present and works
- [ ] No keyboard traps in modals/menus

---

## 32. Color Blindness Check

Never rely on color alone to convey meaning.

**Rules:**
- Red/green **alone** = never (8% of men are colorblind)
- Always pair color with **icon** or **text label**
- Test with Color Oracle or browser extension
- Status indicators: color + icon + text

**Wrong:**
```html
<span style="color: green;">Active</span>
<span style="color: red;">Inactive</span>
```

**Right:**
```html
<span class="status status-success">
  <i data-lucide="check-circle"></i> Active
</span>
<span class="status status-error">
  <i data-lucide="x-circle"></i> Inactive
</span>
```

**CSS:**
```css
.status {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-1) var(--space-3);
  border-radius: var(--radius-full);
  font-size: var(--text-sm);
  font-weight: 500;
}

.status-success {
  background: #dcfce7;
  color: #166534;
}

.status-error {
  background: #fee2e2;
  color: #991b1b;
}

.status-warning {
  background: #fef3c7;
  color: #92400e;
}

.status-info {
  background: #dbeafe;
  color: #1e40af;
}
```

**Check:**
- [ ] No red/green alone for meaning
- [ ] Status has icon + text
- [ ] Tested with Color Oracle

---

## 33. Print Styles

Clean output when users print pages.

**Rules:**
- Hide: nav, sidebar, buttons, modals, toasts
- Black text on white background
- Show URLs after links (optional)
- Remove background colors
- Expand collapsed content

**CSS:**
```css
@media print {
  /* Hide non-printable elements */
  nav, sidebar, .btn, .modal, .toast,
  .pagination, .no-print {
    display: none !important;
  }

  /* Reset colors */
  * {
    background: white !important;
    color: black !important;
    box-shadow: none !important;
  }

  /* Show link URLs */
  a[href]::after {
    content: " (" attr(href) ")";
    font-size: 0.8em;
    color: #666;
  }

  /* Remove internal link URLs */
  a[href^="#"]::after,
  a[href^="javascript"]::after {
    content: "";
  }

  /* Page setup */
  @page {
    margin: 1cm;
  }

  /* Expand content */
  .print-expand {
    max-height: none !important;
    overflow: visible !important;
  }

  /* Table borders */
  table {
    border-collapse: collapse;
  }
  th, td {
    border: 1px solid #ccc !important;
    padding: 8px;
  }
}
```

**Check:**
- [ ] Nav/sidebar hidden
- [ ] Text readable (black on white)
- [ ] Tables print with borders
- [ ] No page breaks in middle of content

---

## 34. Font Loading Strategy

Prevent invisible text (FOIT) and layout shift.

**Rules:**
- Use `font-display: swap` (show fallback first, swap when loaded)
- Preload critical fonts (body font only)
- Max **2 font families** total
- Subset fonts if possible (reduce file size)

**CSS:**
```css
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url('/fonts/inter-regular.woff2') format('woff2');
}

@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url('/fonts/inter-semibold.woff2') format('woff2');
}

/* Fallback stack */
body {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}
```

**HTML (preload critical font):**
```html
<head>
  <link rel="preload" href="/fonts/inter-regular.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/fonts/inter-semibold.woff2" as="font" type="font/woff2" crossorigin>
</head>
```

**Check:**
- [ ] `font-display: swap` on all fonts
- [ ] Body font preloaded
- [ ] Max 2 font families
- [ ] No invisible text on load

---

## 35. Text Overflow Handling

Long text must never break layout.

**Rules:**
- Truncate single-line with ellipsis
- Wrap long words with `word-break: break-word`
- No horizontal scroll from text content
- URLs in tables: truncate middle

**CSS:**
```css
/* Single-line truncation */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Multi-line truncation (2 lines) */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Multi-line truncation (3 lines) */
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Break long words/URLs */
.break-words {
  word-break: break-word;
  overflow-wrap: break-word;
}

/* Table cell truncation */
.table td {
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
```

**Check:**
- [ ] Long names/emails truncate
- [ ] URLs don't break layout
- [ ] Table cells have max-width

---

## 36. Z-Index Scale

Fixed z-index scale — never use random high numbers.

**Scale:**
| Layer | Z-Index | Usage |
|-------|---------|-------|
| Base | 0 | Default content |
| Dropdown | 100 | Select menus, dropdowns |
| Sticky | 200 | Sticky headers, sidebar |
| Modal | 300 | Modals, dialogs |
| Toast | 400 | Toast notifications |
| Tooltip | 500 | Tooltips (above modals) |

**Wrong:**
```css
.z-index: 9999;
.z-index: 99999;
.z-index: 100000;
```

**Right:**
```css
.dropdown { z-index: var(--z-dropdown); }   /* 100 */
.sticky { z-index: var(--z-sticky); }       /* 200 */
.modal { z-index: var(--z-modal); }         /* 300 */
.toast { z-index: var(--z-toast); }         /* 400 */
.tooltip { z-index: var(--z-tooltip); }     /* 500 */
```

**Tokens:**
```css
:root {
  --z-base: 0;
  --z-dropdown: 100;
  --z-sticky: 200;
  --z-modal: 300;
  --z-toast: 400;
  --z-tooltip: 500;
}
```

**Check:**
- [ ] No z-index above 500
- [ ] Uses tokens, not magic numbers

---

## 37. Hit Area Minimum

Touch targets must be large enough for mobile.

**Rules:**
- Buttons/links: **minimum 44×44px**
- Icon buttons: **minimum 40×40px** with padding
- Spacing between tap targets: **8px minimum**
- Links in text: line-height provides spacing

**CSS:**
```css
/* Minimum touch target */
.btn {
  min-height: 44px;
  min-width: 44px;
  padding: var(--space-3) var(--space-5);
}

/* Icon-only button */
.btn-icon {
  width: 40px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

/* Ensure spacing between targets */
.btn-group {
  display: flex;
  gap: var(--space-2); /* 8px minimum */
}

/* Inline links get spacing from line-height */
.content a {
  line-height: 2;
}
```

**Check:**
- [ ] All buttons ≥ 44px tall
- [ ] Icon buttons ≥ 40×40px
- [ ] 8px gap between targets

---

## 38. Scroll Behavior

Smooth, predictable scrolling.

**Rules:**
- Smooth scroll for anchor links only
- No scroll-jacking (never hijack scroll)
- Visible scrollbar (don't hide it)
- Back button preserves scroll position
- Sticky elements don't cause layout shift

**CSS:**
```css
/* Smooth scroll for anchor links */
html {
  scroll-behavior: smooth;
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  html {
    scroll-behavior: auto;
  }
}

/* Sticky header without layout shift */
.sticky-header {
  position: sticky;
  top: 0;
  z-index: var(--z-sticky);
}
```

**Check:**
- [ ] Anchor links scroll smoothly
- [ ] No scroll-jacking
- [ ] Back button preserves position

---

## 39. Content Width Standard

Consistent content widths for readability.

**Rules:**
- Body text: **60-80 characters** per line max
- Page container: **max 1200-1280px** centered
- Full-width sections: only for hero/CTA/banners
- Center align: heroes only, never long paragraphs

**CSS:**
```css
/* Page container */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--space-5);
}

/* Narrow content (text-heavy pages) */
.container-narrow {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 var(--space-5);
}

/* Full-width section */
.section-full {
  width: 100%;
  padding: var(--space-8) var(--space-5);
}

/* Body text line length */
.prose {
  max-width: 65ch; /* ~65 characters */
  line-height: var(--leading-relaxed);
}
```

**Check:**
- [ ] Text content max 65ch wide
- [ ] Container max 1200px
- [ ] Long paragraphs left-aligned

---

## 40. Error Page Design

Custom error pages — never show framework defaults.

**Rules:**
- Custom 404, 500, 403, 503 pages
- Clear message (what happened)
- Action button ("Go home" minimum)
- Match site design (not ugly default)
- Optional: search box, contact support link

**Blade (`resources/views/errors/404.blade.php`):**
```blade
@extends('layouts.app')

@section('content')
<div class="error-page">
  <div class="error-content">
    <h1 class="error-code">404</h1>
    <h2 class="error-title">Page not found</h2>
    <p class="error-description">
      The page you're looking for doesn't exist or has been moved.
    </p>
    <div class="error-actions">
      <a href="/" class="btn btn-primary">Go home</a>
      <button onclick="history.back()" class="btn btn-secondary">
        Go back
      </button>
    </div>
  </div>
</div>
@endsection
```

**CSS:**
```css
.error-page {
  min-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: var(--space-8) var(--space-4);
}

.error-code {
  font-size: var(--text-4xl);
  font-weight: 700;
  color: var(--color-neutral-300);
  margin-bottom: var(--space-4);
}

.error-title {
  font-size: var(--text-xl);
  margin-bottom: var(--space-3);
}

.error-description {
  color: var(--color-text-secondary);
  margin-bottom: var(--space-6);
  max-width: 400px;
}

.error-actions {
  display: flex;
  gap: var(--space-3);
  justify-content: center;
}
```

**Check:**
- [ ] Custom 404 page exists
- [ ] Custom 500 page exists
- [ ] "Go home" button present
- [ ] Matches site design

---

## Quick Self-Check

Before shipping, ask:

1. Could I swap this with another project and it would still look the same? (If yes, it's generic)
2. Does every visual choice come from the subject matter? (If not, it's decoration)
3. Am I using a pattern because it's right, or because it's easy? (If easy, reconsider)
4. Would a human designer approve this? (If uncertain, it's probably slop)
5. Does one element stand out as the hero? (If everything shouts, nothing does)

---

## References

- `Frontend_SKILL.md` — Full design process and critique guidelines
- `UNIVERSAL-FONTS.md` — Font selection reference
- Design with subject matter, not against it

---

## Quick Repeatable Checklist

Run this on EVERY project:

**Design Foundation:**
- [ ] Design tokens file exists (`tokens.css`)
- [ ] Only 1-2 font families used
- [ ] Only 1 icon library used
- [ ] Spacing uses scale (4/8/12/16/24/32/48/64)
- [ ] Z-index uses scale (100/200/300/400/500)
- [ ] Breakpoints use standard (640/768/1024/1280)

**Components:**
- [ ] Buttons use component, not custom styles
- [ ] Forms follow standard pattern
- [ ] Modals: ESC closes, focus trapped, scroll locked
- [ ] Dropdowns: keyboard navigable, same height as inputs
- [ ] Toasts: same position, max 3 visible, auto-dismiss

**Loading & States:**
- [ ] Loading shows skeleton, not blank page
- [ ] Empty states have icon + message + action
- [ ] Error pages custom (404/500), not framework default

**Visual:**
- [ ] No emojis in UI
- [ ] No fade-slide-up on every section
- [ ] Tables: consistent header, mobile scroll, pagination
- [ ] Images: width/height set, lazy loading below fold
- [ ] Text: truncation handled, no layout break

**Accessibility:**
- [ ] Focus ring visible on all interactive elements
- [ ] Skip-to-content link present
- [ ] Touch targets ≥ 44px
- [ ] No red/green alone for meaning
- [ ] Color contrast 4.5:1 minimum

**Content:**
- [ ] Body text max 65ch wide
- [ ] Breadcrumbs: max 3 levels, hidden on mobile
- [ ] Pagination: shows "Showing X of Y"
- [ ] Tooltips: not used for critical info

**Pre-Ship:**
- [ ] Screenshots taken at 375/768/1440
- [ ] Dark mode tested (if applicable)
- [ ] Print styles tested
- [ ] Keyboard navigation tested
- [ ] Accessibility checklist passed
