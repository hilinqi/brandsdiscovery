# BrandsDiscovery MVP Specification v1.0.0

> Layer 1 of 4: Product Specification
> Answers: What to build, what NOT to build.

---

## 1. Product Overview

An English-language independent brand discovery website.
- Consumers discover brands and visit official stores.
- Brand owners claim and maintain profiles.
- MVP does **not** process transactions.

---

## 2. Product Boundary

### Included
- Brand profiles and discovery
- Category hierarchy with attributes
- Representative products (display only, no cart/checkout)
- Full-text search across brands, categories, products, and guides
- User-submitted brand suggestions and information reports
- Brand claim workflow with admin review
- Merchant dashboard (profile editing, basic traffic)
- SEO system (metadata, schema, sitemap, internal linking)
- Content types: Buying Guides, Best Brand Lists, Brand Reviews, Comparisons, General Articles
- Legal/compliance pages
- Admin operations with English + Chinese helper labels
- Cloudflare R2 image storage
- Outbound Visit Store tracking

### Excluded
- Shopping cart, checkout, orders, payments, inventory
- Native mobile app
- Community forum or user discussions
- AI-powered recommendations
- Advanced CRM or email marketing
- Multilingual frontend (English only for MVP)
- Affiliate settlement or commission tracking

---

## 3. User Roles

| Role | Scope | Access |
|------|-------|--------|
| **Consumer (Subscriber)** | Public website | Browse, search, submit brands, request brands/products, report incorrect info, create account, login |
| **Merchant** | Merchant Center | Register, login, claim brands, view dashboard, edit owned brand profiles, manage products, view basic traffic |
| **Moderator** | WordPress Admin | Review submissions, review claims, manage brands/categories/products (limited to review operations) |
| **Editor** | WordPress Admin | Full content management: brands, categories, products, guides, SEO |
| **Administrator** | WordPress Admin | Full access: all operations, settings, integrations, user management |

> Detailed permission matrix: see technical-specification.md

---

## 4. Page Inventory

### 4.1 Public Pages

| Page | Route | Description |
|------|-------|-------------|
| Homepage | `/` | Hero, search, featured categories, featured brands, latest discoveries, buying guides, request CTA, submit-brand CTA |
| Brand Archive | `/brands/` | Paginated brand list with filters (category, country, sort) |
| Brand Detail | `/brands/{slug}/` | Full brand profile (see Brand Page Layout) |
| Category Page | `/categories/{slug}/` | Category intro, subcategories, filters, brand list, guides, FAQ |
| Search | `/search/` | Search results with filters, suggestions, empty state |
| Login | `/login/` | WordPress login form + social login |
| Register | `/register/` | WordPress registration form + social login |
| Password Reset | `/reset-password/` | Standard WordPress password reset flow |
| Account | `/account/` | Consumer account shell (profile, submission history) |
| Submit Your Brand | `/submit-brand/` | Public submission form |
| Request a Brand/Product | `/request-brand/` | Public request form |
| Report Information | `/report/` | Public report form |
| 404 | N/A | Not-found page with search + popular links |

### 4.2 Content Pages

| Page | Route | Description |
|------|-------|-------------|
| Guide Archive | `/guides/` | List of all buying guides |
| Guide Detail | `/guides/{slug}/` | Individual guide article |
| Best Brand List | `/lists/{slug}/` | Ranked brand list |
| Brand Review | `/reviews/{slug}/` | Individual brand review |
| Comparison | `/comparisons/{slug}/` | Side-by-side brand comparison |
| General Article | `/articles/{slug}/` | General editorial content |

### 4.3 Legal / Info Pages

| Page | Route |
|------|-------|
| About | `/about/` |
| Contact | `/contact/` |
| FAQ | `/faq/` |
| Editorial Policy | `/editorial-policy/` |
| Verification Policy | `/verification-policy/` |
| Privacy Policy | `/privacy-policy/` |
| Terms of Service | `/terms/` |
| Cookie Policy | `/cookie-policy/` |
| Affiliate Disclosure | `/affiliate-disclosure/` |
| Advertise With Us | `/advertise/` |
| Partner With Us | `/partner/` |

### 4.4 Merchant Center Pages

| Page | Route | Auth Required |
|------|-------|---------------|
| Merchant Login | `/merchant/login/` | No |
| Merchant Register | `/merchant/register/` | No |
| Claim Brand | `/merchant/claim/{brand-id}/` | Yes (Merchant) |
| Claim Status | `/merchant/claim-status/` | Yes (Merchant) |
| Dashboard | `/merchant/dashboard/` | Yes (Merchant) |
| My Brand | `/merchant/brand/{id}/` | Yes (Merchant) |
| Products | `/merchant/brand/{id}/products/` | Yes (Merchant) |
| Submission Status | `/merchant/submissions/` | Yes (Merchant) |
| Account Settings | `/merchant/settings/` | Yes (Merchant) |

### 4.5 Admin Pages (WordPress Admin)

| Menu Item | English / Chinese |
|-----------|-------------------|
| Dashboard | Dashboard / 工作台 |
| Brands | Brands / 品牌 |
| Categories | Categories / 分类 |
| Products | Products / 产品 |
| Claim Requests | Claim Requests / 品牌认领申请 |
| Submissions | Submissions / 提交审核 |
| Guides | Guides / 指南内容 |
| SEO | SEO / SEO设置 |
| Settings | Settings / 设置 |

---

## 5. Module Descriptions

### 5.1 Platform Foundation
- Fast, responsive, mobile-first WordPress site
- Homepage with all sections defined in 4.1
- Authentication via WordPress users (social login optional via mature plugin)
- English-only public UI

### 5.2 Brand System
- Brand CRUD with fields: name, slug, logo, cover, short description, full description, official website, origin country, primary/secondary categories, markets, shipping regions, payment methods, return policy, support contact, social links, verification status, publication status, claim status
- **Publication statuses**: Draft, Pending Review, Published, Paused, Rejected
- **Claim statuses**: Unclaimed, Claim Requested, Claimed, Suspended
- Brand detail page layout: Hero → Logo/Name/Category → Verification badge → Summary → Visit Store button → About → Why We Like This Brand → Representative products → Shipping/Payment → Social links → Related brands → Related guides → Claim CTA → Report link
- Visit Store: tracked outbound redirect (record event → validate destination → redirect to official store)
- See State Machine for full transition rules: technical-specification.md

### 5.3 Category & Attribute System
- 10 top-level categories predefined:
  1. Electronics & Technology
  2. Home & Kitchen
  3. Pet Products
  4. Beauty & Personal Care
  5. Outdoor & Sports
  6. Fashion & Accessories
  7. Baby & Kids
  8. Automotive
  9. Office & Productivity
  10. Lifestyle & Gifts
- Hierarchical category tree (parent-child)
- Category data: name, slug, description, hero image, SEO intro, display order, status
- Attribute engine with field types: text, number, dropdown, radio, checkbox, multi-select, boolean, range
- Attributes bound per category, forms adapt dynamically
- Category page: Hero → Intro → Subcategories → Filters → Featured brands → Brand list → Guides → FAQ
- Standardized selectable values prioritized; free text only when necessary

### 5.4 Search & Discovery
- Scope: brands, categories, products, guides
- Interfaces: header search bar + suggestions + results page + empty state
- Ranking priority: exact brand match → text relevance → category relevance → published/complete profile → verified → basic engagement
- Sponsored content must be visually disclosed; cannot override exact relevance
- Empty state: related categories, popular brands, "Request a Brand/Product" form
- Unpublished records excluded from results

### 5.5 Merchant Center
- Registration, login, password reset
- Claim flow: submit company/contact/evidence → admin verifies → approve/reject → merchant gains ownership
- Dashboard overview: publication status, profile completeness, profile views, Visit Store clicks, pending edits, recommended actions
- Brand editing: merchant edits enter review where required; editorial fields remain platform-controlled
- Product management per owned brand
- Basic traffic data display
- Account settings
- Permissions: manage owned brands only, cannot alter verification, cannot access WordPress Admin

### 5.6 Submission & Review
- 3 public forms:
  - **Submit Your Brand**: name, URL, category, country, description, main products, contact email (required); logo, social links (optional)
  - **Request a Brand/Product**: type, category, description, country (required); budget, email (optional)
  - **Report Incorrect Information**: brand/page reference, incorrect field, correction details, reporter contact (optional), supporting screenshot (optional)
- Workflow: New → Reviewing → Approved / Rejected / Needs Information
- Duplicate prevention: normalize website domains, compare brand names against existing records and pending claims/submissions
- Anti-spam validation on all forms
- Brand auto-created from approved submission
- Status notification emails on every transition

### 5.7 SEO System
- Indexable: brand, category, guide pages
- Noindex: search, account, admin, thin filter pages
- Per-page SEO fields: title, meta description, canonical URL, index/noindex toggle, Open Graph image, social description
- SEO title/description template rules as defaults, manually overridable per page
- Schema.org types (only when matching visible content): Organization, Brand, Product (showcase), Article, FAQ, Breadcrumb
- Internal linking: brands → categories + related brands + guides; categories → subcategories + brands + guides; guides → referenced brands + categories
- XML sitemap: include published brands/categories/guides; exclude private/search/thin pages
- Integration: Rank Math for baseline SEO; SEO Toolkit for platform-specific relationships, metadata, and schema

### 5.8 Content & Legal Pages
- Content types: Buying Guide, Best Brand List, Brand Review, Comparison, General Article
- 12 required legal/info pages (see 4.3)
- Content rules: English only, useful to consumers, sponsored content disclosed, paid placement never presented as independent editorial ranking
- Third-party plugins allowed for: cookie consent, contact forms, SMTP, social login (integrations must remain replaceable)

### 5.9 Admin Operations
- 9 admin menu items with English + Chinese helper labels (see 4.5)
- Operations: brand/category/product CRUD; review/publish/pause/reject; brand merge; claim approve/reject/request-info/revoke; submission review
- Dashboard widgets: published brands count, pending reviews, pending claims, pending submissions, broken links, recent Visit Store clicks (today/this week)
- Roles: Administrator, Editor, Moderator
- Activity logging: record who did what and when for all state changes (status transitions, claims, submissions)
- Merchants blocked from WordPress Admin

### 5.10 Settings & Integrations
- General: site identity, defaults, search settings, submission settings, claim settings
- Cloudflare R2: endpoint, bucket, custom domain configuration; image presets; upload test; credentials stored outside committed code (wp-config.php constants)
- Email: SMTP plugin integration; notifications for registration, password reset, claim status, submission status
- Social login: third-party OAuth plugin (creates/links WordPress users); business logic depends only on WordPress roles/capabilities
- Cookie consent: mature consent plugin
- Security: capability checks, nonce verification on all writes; secrets never committed

### 5.11 Deployment & Launch
- Infrastructure: Cloudflare DNS → SiteGround hosting → WordPress → Cloudflare R2
- Flow: Local → Staging → Production (never develop in production)
- Install order: WordPress → third-party plugins (social login, cookie consent, SMTP, forms, Rank Math) → BrandsDiscovery Core → Merchant Center → SEO Toolkit → Theme → database migrations → R2 configuration → permalink flush → verification
- Launch checklist: SSL, Cloudflare, R2, homepage, brand/category/search, submission, claim, merchant dashboard, bilingual admin helpers, SEO/sitemap, mobile responsive, backups, rollback package

### 5.12 Developer & Release
- Repository structure: `/theme/brandsdiscovery-theme`, `/plugins/brandsdiscovery-core`, `/plugins/brandsdiscovery-merchant-center`, `/plugins/brandsdiscovery-seo-toolkit`, `/docs`, `/tests`, `/releases`
- Impact analysis required for every change (theme, Core, Merchant, SEO, database, API, documentation)
- Automated checks: PHP syntax, WordPress coding standards, plugin activation, database migrations, REST permissions, forms, redirects, R2 abstraction, responsive smoke tests, version consistency
- Integration test flow: create category + attributes → create brand + products → publish → search → open brand → click Visit Store → claim as merchant → admin approve → merchant edit → admin review → verify SEO → verify mobile
- Release report: versions, changed files, migrations, tests, known issues, installation steps, rollback steps

---

## 6. Forms Inventory

| Form | Required Fields | Optional Fields | Auth Required |
|------|----------------|-----------------|---------------|
| Submit Your Brand | Name, URL, Category, Country, Description, Main Products, Contact Email | Logo, Social Links | No |
| Request a Brand/Product | Type, Category, Description, Country | Budget, Email | No |
| Report Incorrect Information | Brand/Page Reference, Incorrect Field, Correction Details | Reporter Contact, Screenshot | No |
| Claim Brand | Company Name, Contact Person, Contact Email, Evidence | Phone, Notes | Yes (Merchant) |
| Merchant Edit Brand | Varies per field permission matrix | — | Yes (Merchant) |

---

## 7. Email Notifications

| Trigger | Recipient | Content |
|---------|-----------|---------|
| User registration | Consumer/Merchant | Welcome + verification link |
| Password reset request | Consumer/Merchant | Reset link |
| Submission received | Submitter | Confirmation + reference ID |
| Submission status change | Submitter | New status + admin notes |
| Claim submitted | Merchant | Confirmation + expected review time |
| Claim approved | Merchant | Congratulations + dashboard link |
| Claim rejected | Merchant | Reason + option to re-submit |
| Claim request info | Merchant | What additional info is needed |

---

## 8. Language Rules (from Global Rules)

| Context | Language |
|---------|----------|
| Public website | English only |
| Consumer account | English only |
| Merchant Center | English only |
| WordPress Admin menus, titles, fields, statuses, buttons, help panels, validation messages | English + Chinese helper text |
| Code variables, functions, classes, REST routes, slugs, database columns | English |
| Code comments | English (industry convention) |

---

## 9. Version

```
Platform:       1.0.0
Theme:          1.0.0
Core Plugin:    1.0.0
Merchant Plugin: 1.0.0
SEO Plugin:     1.0.0
Database Schema: 1.0.0
API Version:    1.0.0
Document:       1.0.0
```

All version numbers must remain consistent across headers, package filenames, changelogs, compatibility reports, and release reports.

---

## 10. Dependency Order

```
Platform Foundation (no dependency)
  ↓
Brand System (depends on Platform)
  ↓
Category & Attribute System (depends on Platform + Brand)
  ↓
Search & Discovery (depends on Brand + Category)
  ↓
Submission & Review (depends on Brand + Category + Admin)
Merchant Center (depends on Brand + WordPress auth)
SEO System (depends on Brand + Category + Content)
Content & Legal Pages (depends on Platform)
Admin Operations (consumes Core + Merchant interfaces)
Settings & Integrations (supports all modules)
  ↓
Deployment & Launch (requires all modules complete)
  ↓
Developer & Release (governs all modules)
```

---

## 11. Build Output

| Artifact | Type |
|----------|------|
| brandsdiscovery-theme | WordPress Theme (ZIP) |
| brandsdiscovery-core | WordPress Plugin (ZIP) |
| brandsdiscovery-merchant-center | WordPress Plugin (ZIP) |
| brandsdiscovery-seo-toolkit | WordPress Plugin (ZIP) |
| Compatibility matrix | Report |
| Migration report | Report |
| Test report | Report |
| Changed-file list | Report |
| Version report | Report |
| Rollback package | Archive |
