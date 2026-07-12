# BrandsDiscovery UI/UX Specification v1.0.0

> Layer 2 of 4: Design Specification
> Answers: What pages look like.

---

## 1. Design System

### 1.1 Color Palette

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-primary` | `#1B2A4A` | Deep navy — primary brand color, header background, main headings |
| `--color-primary-light` | `#2D4070` | Hover states, secondary backgrounds |
| `--color-accent` | `#0D9488` | Teal/emerald — CTA buttons, links, active states |
| `--color-accent-hover` | `#0F766E` | CTA hover |
| `--color-accent-light` | `#CCFBF1` | Accent backgrounds, badges |
| `--color-warm` | `#F59E0B` | Limited warm accent — highlights, badges, star ratings |
| `--color-warm-light` | `#FEF3C7` | Warm accent backgrounds |
| `--color-bg` | `#F8FAFC` | Soft white — page background |
| `--color-surface` | `#FFFFFF` | Card backgrounds, modals |
| `--color-border` | `#E2E8F0` | Borders, dividers |
| `--color-text-primary` | `#0F172A` | Primary text |
| `--color-text-secondary` | `#475569` | Secondary text, descriptions, metadata |
| `--color-text-muted` | `#94A3B8` | Placeholder text, disabled text |
| `--color-success` | `#16A34A` | Success states, verified badges |
| `--color-warning` | `#F59E0B` | Warning states |
| `--color-error` | `#DC2626` | Error states, required indicators |
| `--color-info` | `#2563EB` | Info states |

### 1.2 Typography

| Token | Font Stack | Usage |
|-------|-----------|-------|
| `--font-heading` | `'Inter', -apple-system, BlinkMacSystemFont, sans-serif` | H1–H4 headings |
| `--font-body` | `'Inter', -apple-system, BlinkMacSystemFont, sans-serif` | Body text, UI elements |

| Level | Size (Desktop) | Size (Mobile) | Weight | Line Height |
|-------|---------------|---------------|--------|-------------|
| H1 | 36px / 2.25rem | 28px / 1.75rem | 700 | 1.2 |
| H2 | 28px / 1.75rem | 24px / 1.5rem | 700 | 1.3 |
| H3 | 22px / 1.375rem | 20px / 1.25rem | 600 | 1.4 |
| H4 | 18px / 1.125rem | 16px / 1rem | 600 | 1.4 |
| Body | 16px / 1rem | 15px / 0.9375rem | 400 | 1.6 |
| Body Small | 14px / 0.875rem | 13px / 0.8125rem | 400 | 1.5 |
| Caption | 12px / 0.75rem | 12px / 0.75rem | 400 | 1.4 |

### 1.3 Spacing Scale

| Token | Value |
|-------|-------|
| `--space-xs` | 4px / 0.25rem |
| `--space-sm` | 8px / 0.5rem |
| `--space-md` | 16px / 1rem |
| `--space-lg` | 24px / 1.5rem |
| `--space-xl` | 32px / 2rem |
| `--space-2xl` | 48px / 3rem |
| `--space-3xl` | 64px / 4rem |

### 1.4 Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| `--radius-sm` | 4px | Inputs, badges, small elements |
| `--radius-md` | 8px | Cards, buttons, modals |
| `--radius-lg` | 12px | Large cards, hero sections |
| `--radius-full` | 9999px | Pills, circular badges |

### 1.5 Shadows

| Token | Value | Usage |
|-------|-------|-------|
| `--shadow-sm` | `0 1px 2px rgba(0,0,0,0.05)` | Subtle elevation |
| `--shadow-md` | `0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05)` | Cards |
| `--shadow-lg` | `0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.05)` | Modals, dropdowns |
| `--shadow-xl` | `0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05)` | Full-screen modals |

### 1.6 Breakpoints (Mobile-First)

| Token | Min Width | Target |
|-------|-----------|--------|
| `--bp-sm` | 640px | Large phones |
| `--bp-md` | 768px | Tablets |
| `--bp-lg` | 1024px | Small desktops |
| `--bp-xl` | 1280px | Large desktops |

### 1.7 Layout

| Token | Value |
|-------|-------|
| Content max-width | 1200px |
| Narrow content max-width | 768px (articles, legal pages) |
| Page padding (mobile) | 16px |
| Page padding (desktop) | 24px |

### 1.8 Transitions

| Token | Value |
|-------|-------|
| Hover/active | `150ms ease-in-out` |
| Modal open/close | `200ms ease-out` |
| Page transitions | `300ms ease` |

---

## 2. Component Library

All pages must use these components. No duplicated implementations.

### 2.1 Brand Card

```
┌──────────────────────────────┐
│ [Logo 80x80]                 │
│ Brand Name                   │
│ ✓ Verified badge (if any)    │
│ Category label(s)            │
│ Short description (2 lines)  │
│ Origin country flag + name   │
│ ───────────────────────────  │
│ Visit Store →                │
└──────────────────────────────┘
```

**Variants**: grid item (default), list item (horizontal layout for archive), featured (larger, with cover image)
**States**: default, hover (shadow-md → shadow-lg, slight lift), loading (skeleton), empty (when no data)

### 2.2 Category Card

```
┌──────────────────────────────┐
│ [Hero Image / Icon]          │
│ Category Name                │
│ Brand count: "1,234 brands"  │
│ Arrow →                      │
└──────────────────────────────┘
```

**Variants**: with image (default), icon-only (compact grid), featured (larger)

### 2.3 Product Card

```
┌──────────────────────────────┐
│ [Product Image]              │
│ Product Name                 │
│ Brand Name (linked)          │
│ Price (if available)         │
└──────────────────────────────┘
```

**States**: default, hover, loading (skeleton)

### 2.4 Guide Card

```
┌──────────────────────────────┐
│ [Featured Image]             │
│ Guide Title                  │
│ Excerpt (2 lines)            │
│ Author name · Publish date   │
└──────────────────────────────┘
```

**Variants**: with image (default), text-only (compact sidebar)

### 2.5 Button

| Variant | BG | Text | Border | Usage |
|---------|-----|------|--------|-------|
| Primary | `--color-accent` | White | None | Main CTAs: Submit, Claim, Register |
| Secondary | `--color-primary` | White | None | Secondary actions |
| Outline | Transparent | `--color-primary` | `--color-primary` | Less important actions, dark backgrounds |
| Ghost | Transparent | `--color-accent` | None | Inline actions, icon buttons |
| Danger | `--color-error` | White | None | Destructive actions: Delete, Reject |

**Sizes**: sm (32px h), md (40px h, default), lg (48px h)
**States**: default, hover (10% darker), active, disabled (50% opacity), loading (spinner + disabled)

### 2.6 Input

```
┌──────────────────────────────┐
│ Label                    [*] │
│ ┌──────────────────────────┐ │
│ │ Placeholder...           │ │
│ └──────────────────────────┘ │
│ Helper text / Error message  │
└──────────────────────────────┘
```

**States**: default, focus (border primary), error (border error + error text), disabled (bg muted), readonly
**Variants**: text, email, url, number, textarea, password

### 2.7 Select / Dropdown

```
┌──────────────────────────────┐
│ Label                        │
│ ┌──────────────────────────┐ │
│ │ Selected option      ▼   │ │
│ └──────────────────────────┘ │
└──────────────────────────────┘
```

Dropdown panel: max-height 280px, scrollable, searchable when > 10 options

### 2.8 Checkbox / Radio

Standard WordPress-style, themed to `--color-accent`.

### 2.9 Toggle / Switch

Used for binary settings (index/noindex, enable/disable).

### 2.10 Badge

| Variant | Color | Usage |
|---------|-------|-------|
| Verified | `--color-success` bg, white text | "✓ Verified" |
| Claimed | `--color-info` bg, white text | "Claimed" |
| Pending | `--color-warning` bg, dark text | "Pending Review" |
| Paused | `--color-text-muted` bg, dark text | "Paused" |
| Sponsored | `--color-warm` bg, dark text | "Sponsored" |
| Category | `--color-accent-light` bg, accent text | Category label |
| Country | `--color-border` bg, text-secondary | Country tag |

### 2.11 Modal / Dialog

```
┌─────────────────────────────────┐
│                                 │
│  Title                          │
│  ───────────────────────────────│
│  Content...                     │
│                                 │
│  ───────────────────────────────│
│              [Cancel] [Confirm] │
└─────────────────────────────────┘
```

Overlay: rgba(0,0,0,0.5)
Max width: 560px (sm), 768px (md)

### 2.12 Breadcrumb

```
Home > Category > Subcategory > Brand Name
```

All segments linked except current page.
Separator: `>` or `/`
Truncate on mobile: `... > Subcategory > Current`

### 2.13 Pagination

```
← Previous   1  2  3  ...  8  Next →
```

Center-aligned. Current page highlighted with `--color-primary`.
Max visible pages: 7 (with ellipsis for large sets).

### 2.14 Search Bar (Header)

```
┌──────────────────────────────────────┐
│ 🔍  Search brands, categories...     │
└──────────────────────────────────────┘
```

On focus: expands, shows suggestions dropdown (max 5 items)

### 2.15 Search Suggestion Dropdown (see component specs)

### 2.16 Tabs

```
┌──────────┬──────────┬──────────┐
│ Brands   │ Products │ Guides   │
└──────────┴──────────┴──────────┘
```

Used on search results page to switch between result types.

---

## 3. State Patterns

### 3.1 Loading / Skeleton

All cards and lists must have skeleton loading states:

```
Card Skeleton:
┌──────────────────────────────┐
│ [█████████]                  │
│ ████████████████             │
│ ██████████                   │
│ ████  ████  ████             │
└──────────────────────────────┘
```

Pulse animation: `opacity 0.3 → 0.7 → 0.3`, 1.5s infinite.

**When to show**: initial page load, search in progress, filter changes.

### 3.2 Empty State

```
┌──────────────────────────────────────────────┐
│                                              │
│           [Illustration / Icon]              │
│                                              │
│           No results found                   │
│           Try adjusting your search          │
│           or browse popular brands.          │
│                                              │
│        [Browse Categories]  [Request Brand]  │
│                                              │
└──────────────────────────────────────────────┘
```

**When to show**: search with 0 results, empty category, empty list.

### 3.3 Error State

```
┌──────────────────────────────────────────────┐
│           Something went wrong               │
│           Please try again later.            │
│                                              │
│              [Try Again]                     │
└──────────────────────────────────────────────┘
```

**When to show**: API failure, network error, server error.

### 3.4 Success State

```
┌──────────────────────────────┐
│     ✓  Submission received!  │
│     We'll review and get     │
│     back to you shortly.     │
└──────────────────────────────┘
```

Green toast notification for form submissions, claim submissions, profile updates.

### 3.5 Toast Notifications

Position: top-right corner. Auto-dismiss after 5s (success, info) or persistent (error).
Types: success (green), error (red), warning (yellow), info (blue).

---

## 4. Page Layouts

### 4.1 Homepage

```
┌──────────────────────────────────────────────┐
│ HEADER                                       │
├──────────────────────────────────────────────┤
│                  HERO                        │
│        [Headline + Search Bar]               │
├──────────────────────────────────────────────┤
│          FEATURED CATEGORIES                 │
│   [Card] [Card] [Card] [Card] [Card]         │
├──────────────────────────────────────────────┤
│          FEATURED BRANDS                     │
│   [Card] [Card] [Card] [Card]                │
├──────────────────────────────────────────────┤
│          LATEST DISCOVERIES                  │
│   [Card] [Card] [Card] [Card]                │
├──────────────────────────────────────────────┤
│          BUYING GUIDES                       │
│   [Card] [Card] [Card]                       │
├──────────────────────────────────────────────┤
│          CTA SECTION                         │
│   Request a Brand  |  Submit Your Brand      │
├──────────────────────────────────────────────┤
│ FOOTER                                       │
└──────────────────────────────────────────────┘
```

### 4.2 Brand Detail Page

```
┌──────────────────────────────────────────────┐
│ HEADER + BREADCRUMB                          │
├──────────────────────────────────────────────┤
│ [Cover Image (full width)]                   │
├──────────────────────────────────────────────┤
│ [Logo 120x120]  Brand Name                   │
│                 Verified badge + Category     │
│                 Short summary (2-3 lines)    │
│                 [Visit Store] [Claim Brand]  │
├──────────────────────────────────────────────┤
│ ABOUT                          │ SIDEBAR     │
│ Full description...            │ ─────────── │
│                                │ Shipping    │
│ WHY WE LIKE THIS BRAND         │ Payment     │
│ ...                            │ Social Links│
│                                │             │
│ REPRESENTATIVE PRODUCTS        │             │
│ [P] [P] [P] [P]               │             │
├──────────────────────────────────────────────┤
│ RELATED BRANDS                               │
│ [Card] [Card] [Card] [Card]                  │
├──────────────────────────────────────────────┤
│ RELATED GUIDES                               │
│ [Card] [Card] [Card]                         │
├──────────────────────────────────────────────┤
│ Report link                                  │
├──────────────────────────────────────────────┤
│ FOOTER                                       │
└──────────────────────────────────────────────┘
```

### 4.3 Category Page

```
┌──────────────────────────────────────────────┐
│ HEADER + BREADCRUMB                          │
├──────────────────────────────────────────────┤
│ [Hero Image]                                 │
│ Category Name + Description (SEO intro)      │
├──────────────────────────────────────────────┤
│ SUBCATEGORIES (if any)                       │
│ [Card] [Card] [Card] [Card]                  │
├──────────────────────────────────────────────┤
│ FILTERS (sidebar left)     │ BRAND LIST     │
│ ┌─────────────────────┐    │ ┌────────────┐ │
│ │ Country        [▼]  │    │ │ Brand Card │ │
│ │ Attribute 1    [▼]  │    │ │ Brand Card │ │
│ │ Attribute 2    [▼]  │    │ │ Brand Card │ │
│ │ Price Range  [──○──]│    │ │ Brand Card │ │
│ └─────────────────────┘    │ └────────────┘ │
│                            │  PAGINATION    │
├──────────────────────────────────────────────┤
│ GUIDES (related)                             │
├──────────────────────────────────────────────┤
│ FAQ                                          │
└──────────────────────────────────────────────┘
```

### 4.4 Search Results Page

```
┌──────────────────────────────────────────────┐
│ HEADER + SEARCH BAR (filled)                 │
├──────────────────────────────────────────────┤
│ "123 results for 'search term'"              │
│ TABS: Brands (45) | Products (30) |          │
│       Categories (5) | Guides (8)            │
├──────────────────────────────────────────────┤
│ FILTERS (if Brand tab)    │ RESULTS LIST    │
│ ┌─────────────────────┐   │ [Card] [Card]   │
│ │ Category       [▼]  │   │ [Card] [Card]   │
│ │ Country        [▼]  │   │ [Card]          │
│ │ Verified only  [✓]  │   │                 │
│ └─────────────────────┘   │  PAGINATION     │
└──────────────────────────────────────────────┘
```

### 4.5 Merchant Dashboard

```
┌──────────────────────────────────────────────┐
│ MERCHANT HEADER: Logo | Dashboard | My Brand │
│ Products | Submissions | Settings | Logout   │
├──────────────────────────────────────────────┤
│ DASHBOARD                                    │
│ ┌────────────┬────────────┬──────────────┐   │
│ │ Published  │ Completeness│ Pending     │   │
│ │ Status     │    Score    │   Edits     │   │
│ ├────────────┼────────────┼──────────────┤   │
│ │ Profile    │ Visit Store │ Recommended │   │
│ │ Views      │   Clicks    │   Actions   │   │
│ └────────────┴────────────┴──────────────┘   │
│                                              │
│ RECENT ACTIVITY                              │
│ • Claim approved for Brand X — 2 days ago    │
│ • Product "ABC" added — 5 days ago           │
└──────────────────────────────────────────────┘
```

### 4.6 Admin Dashboard

Standard WordPress admin shell with custom menu. Above-the-fold widgets in 3-column grid showing counts: published brands, pending reviews, pending claims, pending submissions, broken links, recent Visit Store clicks.

---

## 5. Responsive Behavior

### 5.1 Navigation

- **Desktop** (`>= 1024px`): Horizontal nav bar, all items visible
- **Tablet** (`768px–1023px`): Horizontal nav, condensed labels
- **Mobile** (`< 768px`): Hamburger menu, slide-in drawer, search icon

### 5.2 Card Grids

- **Desktop**: 4 columns (brand/category), 3 columns (guides)
- **Tablet**: 3 columns
- **Mobile**: 1 column (brand/category), full-width cards

### 5.3 Two-Column Layouts (e.g., Category Page Filters + List)

- **Desktop**: sidebar 280px + content
- **Mobile**: filters collapse into top-bar toggle ("Filters ▼")

### 5.4 Typography Scaling

See 1.2 typography table for responsive sizes.

### 5.5 Tables

- **Desktop**: full table
- **Mobile**: cards instead of table rows, key-value layout

### 5.6 Images

- Cover images: `{width}w` responsive via srcset (sizes: 400w, 800w, 1200w, 1600w)
- Logo images: fixed container, object-fit contain
- All images: `loading="lazy"` except above-the-fold hero

---

## 6. Accessibility Requirements

- WCAG 2.1 Level AA compliance
- All interactive elements keyboard-navigable (Tab, Enter, Escape)
- Focus indicators visible on all interactive elements
- Color contrast ratio ≥ 4.5:1 for normal text, ≥ 3:1 for large text
- All images have meaningful `alt` text (logos: "BrandName logo"; decorative: empty alt)
- Forms: all inputs have associated `<label>`, required fields marked with asterisk + `aria-required`
- Modals: focus trapped inside, Escape to close, `aria-modal="true"`
- ARIA landmarks: `<header>`, `<nav>`, `<main>`, `<footer>`
- Screen reader: status messages use `aria-live` regions

---

## 7. Icons

Use a consistent SVG icon set. Recommended: Lucide Icons (MIT license) or Heroicons (MIT license).

Common icons needed:
- Search (magnifying glass)
- User / Account
- Menu (hamburger)
- Close (X)
- Arrow right / left / down / up
- External link (for Visit Store)
- Check / Verified
- Star
- Mail / Email
- Phone
- Globe (website/country)
- Map pin (location)
- Truck (shipping)
- Credit card (payment)
- Alert / Warning
- Info
- File upload (image upload)

---

## 8. Image Specifications

| Context | Min Size | Recommended | Aspect Ratio | Format |
|---------|----------|-------------|--------------|--------|
| Brand Logo | 200x200px | 400x400px | 1:1 | PNG/SVG |
| Brand Cover | 1200x400px | 1600x600px | 3:1 or 4:1 | WebP/JPG |
| Category Hero | 1200x400px | 1600x600px | 3:1 | WebP/JPG |
| Product Image | 400x400px | 800x800px | 1:1 | WebP/JPG |
| Guide Featured | 800x400px | 1200x600px | 2:1 | WebP/JPG |
| OG Image | 1200x630px | 1200x630px | 1.91:1 | JPG |

### R2 Image Presets

| Preset | Width | Height | Crop |
|--------|-------|--------|------|
| thumbnail | 150px | 150px | Center |
| small | 300px | — | Proportion |
| medium | 600px | — | Proportion |
| large | 1200px | — | Proportion |
| card | 400px | 300px | Center |
| hero | 1600px | 600px | Center |

---

## 9. Third-Party Component Overrides

When using third-party plugins (social login, cookie consent, forms), theme must provide CSS overrides to match:
- Button styles (primary/accent colors)
- Input field borders and focus states
- Typography
- Border radius
