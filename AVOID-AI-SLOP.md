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
