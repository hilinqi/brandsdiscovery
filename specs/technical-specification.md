# BrandsDiscovery Technical Specification v1.0.0

> Layer 3 of 4: Technical Specification
> Answers: How to build.

---

## 1. Architecture

```
Theme: brandsdiscovery-theme
  └── Consumes plugin public APIs only

Plugin: brandsdiscovery-core
  └── Brands, categories, products, attributes, search,
      submissions, Visit Store tracking, R2 media abstraction

Plugin: brandsdiscovery-merchant-center
  └── Depends on Core public interfaces
  └── Merchant registration, login, claims, dashboard, editing

Plugin: brandsdiscovery-seo-toolkit
  └── Depends on Core read interfaces
  └── SEO fields, metadata, schema, breadcrumb, sitemap, internal linking
```

**Dependency direction**: Theme → Plugins. Core must not depend on Theme, Merchant Center, or SEO Toolkit.

---

## 2. Plugin Communication

### 2.1 Rules

1. Plugins must not directly call each other's internal classes or functions.
2. All cross-plugin communication goes through:
   - **Public API** (documented classes/methods with `public` visibility)
   - **WordPress Hooks** (`apply_filters`, `do_action`)
   - **REST API** endpoints
3. Core Plugin exposes public interfaces; Merchant Center and SEO Toolkit consume them.
4. If a plugin is deactivated, dependent plugins must degrade gracefully (check with `function_exists()` / `class_exists()` / `did_action()`).

### 2.2 Core Public API (Sample)

```php
// Core exposes:
BDC_Brands::get_brand($id)
BDC_Brands::get_brands($args)
BDC_Categories::get_category($id)
BDC_Categories::get_categories($args)
BDC_Products::get_products($args)
BDC_Search::search($query, $args)
BDC_Submissions::create_submission($data)
BDC_Tracking::record_visit($brand_id)

// Hooks:
apply_filters('bdc_brand_data', $brand_data)
do_action('bdc_brand_published', $brand_id)
do_action('bdc_brand_claimed', $brand_id, $user_id)
do_action('bdc_visit_store_clicked', $brand_id, $data)
do_action('bdc_submission_created', $submission_id)
do_action('bdc_submission_status_changed', $submission_id, $old_status, $new_status)

// REST API:
GET  /wp-json/bdc/v1/brands
GET  /wp-json/bdc/v1/brands/{id}
GET  /wp-json/bdc/v1/categories
GET  /wp-json/bdc/v1/categories/{id}
GET  /wp-json/bdc/v1/search
POST /wp-json/bdc/v1/submissions
```

### 2.3 Merchant Center Hooks (Sample)

```php
do_action('bdc_merchant_registered', $user_id)
do_action('bdc_claim_submitted', $claim_id)
do_action('bdc_claim_status_changed', $claim_id, $old_status, $new_status)
```

### 2.4 SEO Toolkit Hooks (Sample)

```php
apply_filters('bdc_seo_title', $title, $context, $object_id)
apply_filters('bdc_seo_description', $description, $context, $object_id)
apply_filters('bdc_seo_schema', $schema, $context, $object_id)
apply_filters('bdc_breadcrumb_items', $items, $context)
apply_filters('bdc_sitemap_entries', $entries, $type)
```

---

## 3. Database Schema

### 3.1 Custom Tables

All custom tables use `$wpdb->prefix` prefix (default: `wp_`).

#### Table: `{prefix}bdc_brands`

| Column | Type | Length | Nullable | Default | Index | Description |
|--------|------|--------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | — | No | AUTO_INCREMENT | PRIMARY | Unique brand ID |
| name | VARCHAR | 255 | No | — | INDEX | Brand display name |
| slug | VARCHAR | 255 | No | — | UNIQUE | URL slug |
| logo_id | VARCHAR | 255 | Yes | NULL | — | R2 object key for logo |
| cover_id | VARCHAR | 255 | Yes | NULL | — | R2 object key for cover image |
| short_description | TEXT | — | Yes | NULL | — | Short summary (300 chars) |
| full_description | LONGTEXT | — | Yes | NULL | — | Full brand description |
| website | VARCHAR | 2048 | Yes | NULL | — | Official website URL |
| origin_country | VARCHAR | 100 | Yes | NULL | INDEX | ISO country code (2-letter) |
| markets | TEXT | — | Yes | NULL | — | JSON array of target market countries |
| shipping_regions | TEXT | — | Yes | NULL | — | JSON array of shipping region names |
| payment_methods | TEXT | — | Yes | NULL | — | JSON array of payment method names |
| return_policy | TEXT | — | Yes | NULL | — | Return policy text |
| support_contact | VARCHAR | 255 | Yes | NULL | — | Support email or phone |
| social_links | TEXT | — | Yes | NULL | — | JSON object: {platform: url} |
| is_verified | TINYINT(1) | — | No | 0 | INDEX | Verification status (0/1) |
| publication_status | VARCHAR | 30 | No | 'draft' | INDEX | draft/pending/published/paused/rejected |
| claim_status | VARCHAR | 30 | No | 'unclaimed' | INDEX | unclaimed/requested/claimed/suspended |
| claimed_by | BIGINT UNSIGNED | — | Yes | NULL | INDEX | WP user ID of claim owner |
| profile_completeness | TINYINT UNSIGNED | — | No | 0 | — | 0–100 percentage |
| display_order | INT | — | No | 0 | — | Manual sort order |
| created_at | DATETIME | — | No | CURRENT_TIMESTAMP | — | Creation timestamp |
| updated_at | DATETIME | — | No | CURRENT_TIMESTAMP | — | Last update timestamp |

#### Table: `{prefix}bdc_categories`

| Column | Type | Length | Nullable | Default | Index | Description |
|--------|------|--------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | — | No | AUTO_INCREMENT | PRIMARY | Unique category ID |
| name | VARCHAR | 255 | No | — | INDEX | Category display name |
| slug | VARCHAR | 255 | No | — | UNIQUE | URL slug |
| parent_id | BIGINT UNSIGNED | — | Yes | NULL | INDEX | Parent category ID (self-ref) |
| description | TEXT | — | Yes | NULL | — | Category description |
| hero_image_id | VARCHAR | 255 | Yes | NULL | — | R2 object key |
| seo_intro | TEXT | — | Yes | NULL | — | SEO-friendly intro text |
| display_order | INT | — | No | 0 | — | Manual sort order |
| status | VARCHAR | 20 | No | 'active' | INDEX | active/inactive |
| created_at | DATETIME | — | No | CURRENT_TIMESTAMP | — | Creation timestamp |
| updated_at | DATETIME | — | No | CURRENT_TIMESTAMP | — | Last update timestamp |

#### Table: `{prefix}bdc_brand_category`

| Column | Type | Length | Nullable | Default | Index | Description |
|--------|------|--------|----------|---------|-------|-------------|
| brand_id | BIGINT UNSIGNED | — | No | — | PRIMARY (composite) | FK to bdc_brands.id |
| category_id | BIGINT UNSIGNED | — | No | — | PRIMARY (composite) | FK to bdc_categories.id |
| is_primary | TINYINT(1) | — | No | 0 | — | 1 = primary category |

#### Table: `{prefix}bdc_products`

| Column | Type | Length | Nullable | Default | Index | Description |
|--------|------|--------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | — | No | AUTO_INCREMENT | PRIMARY | Unique product ID |
| brand_id | BIGINT UNSIGNED | — | No | — | INDEX | FK to bdc_brands.id |
| name | VARCHAR | 255 | No | — | — | Product name |
| slug | VARCHAR | 255 | No | — | UNIQUE | URL slug |
| description | TEXT | — | Yes | NULL | — | Product description |
| image_id | VARCHAR | 255 | Yes | NULL | — | R2 object key |
| price | VARCHAR | 100 | Yes | NULL | — | Display price (string for flexibility) |
| product_url | VARCHAR | 2048 | Yes | NULL | — | Link to product on official store |
| display_order | INT | — | No | 0 | — | Sort order within brand |
| status | VARCHAR | 20 | No | 'active' | INDEX | active/inactive |
| created_at | DATETIME | — | No | CURRENT_TIMESTAMP | — | — |
| updated_at | DATETIME | — | No | CURRENT_TIMESTAMP | — | — |

#### Table: `{prefix}bdc_attributes`

| Column | Type | Length | Nullable | Default | Index | Description |
|--------|------|--------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | — | No | AUTO_INCREMENT | PRIMARY | Unique attribute ID |
| name | VARCHAR | 255 | No | — | INDEX | Attribute display name |
| slug | VARCHAR | 255 | No | — | UNIQUE | Machine-readable key |
| field_type | VARCHAR | 30 | No | — | — | text/number/dropdown/radio/checkbox/multi-select/boolean/range |
| options | TEXT | — | Yes | NULL | — | JSON: predefined values for selectable types |
| display_order | INT | — | No | 0 | — | Sort order |
| created_at | DATETIME | — | No | CURRENT_TIMESTAMP | — | — |

#### Table: `{prefix}bdc_category_attributes`

| Column | Type | Length | Nullable | Default | Index | Description |
|--------|------|--------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | — | No | AUTO_INCREMENT | PRIMARY | — |
| category_id | BIGINT UNSIGNED | — | No | — | INDEX | FK to bdc_categories.id |
| attribute_id | BIGINT UNSIGNED | — | No | — | INDEX | FK to bdc_attributes.id |

#### Table: `{prefix}bdc_brand_attribute_values`

| Column | Type | Length | Nullable | Default | Index | Description |
|--------|------|--------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | — | No | AUTO_INCREMENT | PRIMARY | — |
| brand_id | BIGINT UNSIGNED | — | No | — | INDEX | FK to bdc_brands.id |
| attribute_id | BIGINT UNSIGNED | — | No | — | INDEX | FK to bdc_attributes.id |
| value | TEXT | — | Yes | NULL | — | Stored attribute value |

#### Table: `{prefix}bdc_submissions`

| Column | Type | Length | Nullable | Default | Index | Description |
|--------|------|--------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | — | No | AUTO_INCREMENT | PRIMARY | Unique submission ID |
| type | VARCHAR | 30 | No | — | INDEX | brand_submission / brand_request / report |
| data | LONGTEXT | — | No | — | — | JSON: full form data |
| normalized_domain | VARCHAR | 255 | Yes | NULL | INDEX | Normalized website domain for dedup |
| status | VARCHAR | 30 | No | 'new' | INDEX | new/reviewing/approved/rejected/needs_info |
| reviewer_id | BIGINT UNSIGNED | — | Yes | NULL | — | WP user ID of reviewer |
| reviewer_notes | TEXT | — | Yes | NULL | — | Internal admin notes |
| created_brand_id | BIGINT UNSIGNED | — | Yes | NULL | — | FK to bdc_brands (if submission created a brand) |
| submitter_email | VARCHAR | 255 | Yes | NULL | — | Submitter contact email |
| submitter_ip | VARCHAR | 45 | Yes | NULL | — | IP address for anti-spam |
| created_at | DATETIME | — | No | CURRENT_TIMESTAMP | INDEX | — |
| updated_at | DATETIME | — | No | CURRENT_TIMESTAMP | — | — |

#### Table: `{prefix}bdc_claims`

| Column | Type | Length | Nullable | Default | Index | Description |
|--------|------|--------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | — | No | AUTO_INCREMENT | PRIMARY | Unique claim ID |
| brand_id | BIGINT UNSIGNED | — | No | — | INDEX | FK to bdc_brands.id |
| user_id | BIGINT UNSIGNED | — | No | — | INDEX | FK to wp_users.ID |
| company_name | VARCHAR | 255 | No | — | — | Claimant company name |
| contact_name | VARCHAR | 255 | No | — | — | Contact person |
| contact_email | VARCHAR | 255 | No | — | — | Contact email |
| contact_phone | VARCHAR | 50 | Yes | NULL | — | Phone number |
| evidence | TEXT | — | Yes | NULL | — | JSON: evidence file references or text |
| status | VARCHAR | 30 | No | 'pending' | INDEX | pending/approved/rejected/needs_info/revoked |
| reviewer_id | BIGINT UNSIGNED | — | Yes | NULL | — | WP user ID of reviewer |
| reviewer_notes | TEXT | — | Yes | NULL | — | Internal admin notes |
| created_at | DATETIME | — | No | CURRENT_TIMESTAMP | — | — |
| updated_at | DATETIME | — | No | CURRENT_TIMESTAMP | — | — |

#### Table: `{prefix}bdc_visit_log`

| Column | Type | Length | Nullable | Default | Index | Description |
|--------|------|--------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | — | No | AUTO_INCREMENT | PRIMARY | — |
| brand_id | BIGINT UNSIGNED | — | No | — | INDEX | FK to bdc_brands.id |
| visitor_ip | VARCHAR | 45 | No | — | — | Anonymized IP (hashed) |
| user_agent | VARCHAR | 512 | Yes | NULL | — | Browser user agent |
| referrer_url | VARCHAR | 2048 | Yes | NULL | — | Referring page |
| created_at | DATETIME | — | No | CURRENT_TIMESTAMP | INDEX | — |

#### Table: `{prefix}bdc_activity_log`

| Column | Type | Length | Nullable | Default | Index | Description |
|--------|------|--------|----------|---------|-------|-------------|
| id | BIGINT UNSIGNED | — | No | AUTO_INCREMENT | PRIMARY | — |
| user_id | BIGINT UNSIGNED | — | Yes | NULL | INDEX | WP user who performed action |
| action | VARCHAR | 100 | No | — | INDEX | e.g., brand_published, claim_approved |
| object_type | VARCHAR | 50 | No | — | — | brand / category / product / claim / submission |
| object_id | BIGINT UNSIGNED | — | No | — | INDEX | ID of affected object |
| details | TEXT | — | Yes | NULL | — | JSON: contextual data |
| created_at | DATETIME | — | No | CURRENT_TIMESTAMP | — | — |

---

## 4. State Machines

### 4.1 Brand Publication State Machine

```
                    ┌──────────┐
        ┌──────────→│  Draft   │←──────────┐
        │           └────┬─────┘           │
        │                │ Submit for      │
        │                │ Review          │
        │           ┌────▼─────┐           │
        │           │ Pending  │           │
        │           │ Review   │───────┐   │
        │           └────┬─────┘       │   │
        │      Approve   │    Reject   │   │
        │                │             │   │
        │           ┌────▼─────┐  ┌────▼─────┐
        │           │Published │  │ Rejected  │
        │           └────┬─────┘  └───────────┘
        │      Pause     │
        │           ┌────▼─────┐
        │           │  Paused  │──────┐
        │           └──────────┘      │
        │                     Resume  │
        └─────────────────────────────┘
```

**Transition permissions**:
| From | To | Who Can Trigger |
|------|----|-----------------|
| Draft | Pending Review | Editor, Administrator |
| Pending Review | Published | Editor, Administrator |
| Pending Review | Rejected | Editor, Administrator, Moderator |
| Rejected | Draft | Editor, Administrator |
| Published | Paused | Editor, Administrator |
| Paused | Published | Editor, Administrator |
| Published | Draft | Editor, Administrator |
| Paused | Draft | Editor, Administrator |

**Constraints**:
- Merchants **cannot** change publication status directly.
- Merchant edits to published brands → changes enter Pending Review.
- Paused brands remain visible on site but show "temporarily unavailable" notice.
- Rejected brands are invisible on frontend.

### 4.2 Claim State Machine

```
                    ┌───────────┐
                    │ Unclaimed │←──────────────────┐
                    └─────┬─────┘                   │
                          │ Merchant submits claim   │
                    ┌─────▼──────┐                   │
                    │  Claim     │                   │
                    │ Requested  │──────┐            │
                    └─────┬──────┘      │            │
               Approve    │    Reject   │            │
                          │             │            │
                    ┌─────▼──────┐ ┌────▼───────┐    │
                    │  Claimed   │ │  Rejected   │    │
                    └─────┬──────┘ └────────────┘    │
                    Revoke│                          │
                    ┌─────▼──────┐                   │
                    │ Suspended  │──────┐            │
                    └────────────┘      │            │
                                Restore │            │
                                        └────────────┘
```

**Transition permissions**:
| From | To | Who Can Trigger |
|------|----|-----------------|
| Unclaimed | Claim Requested | Merchant |
| Claim Requested | Claimed | Administrator |
| Claim Requested | Rejected | Administrator |
| Claimed | Suspended | Administrator |
| Suspended | Claimed | Administrator |

**Constraints**:
- A brand can only have **one active claim** at a time.
- Rejected claims: merchant may re-submit after 7 days.
- Suspended claims: merchant cannot edit brand until restored.

### 4.3 Submission Review State Machine

```
                    ┌──────┐
                    │ New  │
                    └──┬───┘
                       │ Admin picks up
                    ┌──▼────────┐
                    │ Reviewing │
                    └──┬────────┘
           ┌───────────┼───────────┐
           │           │           │
    ┌──────▼──┐  ┌─────▼────┐ ┌───▼──────────┐
    │ Approved│  │ Rejected │ │ Needs Info    │
    └────┬────┘  └──────────┘ └───┬───────────┘
         │                        │
         │ Auto-create brand      │ Submitter
         │                        │ provides info
         │                   ┌────▼────┐
         │                   │Reviewing│ (back)
         │                   └─────────┘
    ┌────▼────┐
    │ Brand   │
    │ Created │
    └─────────┘
```

**Transition permissions**:
| From | To | Who Can Trigger |
|------|----|-----------------|
| New | Reviewing | Administrator, Editor, Moderator |
| Reviewing | Approved | Administrator, Editor |
| Reviewing | Rejected | Administrator, Editor, Moderator |
| Reviewing | Needs Info | Administrator, Editor, Moderator |
| Needs Info | Reviewing | System (after submitter responds) |

**On Approved**:
- If type = "brand_submission": system auto-creates a Draft brand.
- If type = "brand_request": logged for reference, no brand created.
- If type = "report": logged, affected brand flagged for review.

---

## 5. Permissions & Capabilities

### 5.1 WordPress Role Mapping

| Role | WP Role | Description |
|------|---------|-------------|
| Consumer | subscriber | Default WordPress subscriber |
| Merchant | bdc_merchant | Custom role: manage owned brands only |
| Moderator | bdc_moderator | Custom role: review submissions & claims |
| Editor | editor | WordPress editor + custom caps for admin menus |
| Administrator | administrator | Full access |

### 5.2 Custom Capabilities

| Capability | Granted To | Description |
|------------|------------|-------------|
| `bdc_manage_brands` | Editor, Admin | CRUD all brands |
| `bdc_manage_categories` | Editor, Admin | CRUD categories |
| `bdc_manage_attributes` | Editor, Admin | CRUD attributes |
| `bdc_manage_products` | Editor, Admin | CRUD all products |
| `bdc_review_submissions` | Moderator, Editor, Admin | Access submission queue |
| `bdc_review_claims` | Moderator, Editor, Admin | Access claim queue |
| `bdc_manage_own_brand` | Merchant | Edit owned brand profile |
| `bdc_manage_own_products` | Merchant | CRUD products for owned brand |
| `bdc_view_basic_traffic` | Merchant | View owned brand traffic |
| `bdc_manage_seo` | Editor, Admin | Access SEO settings |
| `bdc_manage_settings` | Admin | Access settings page |
| `bdc_manage_guides` | Editor, Admin | CRUD guide content |

### 5.3 Access Control

- All admin pages: `current_user_can()` check before rendering.
- All REST endpoints: `permission_callback` with capability check.
- All AJAX handlers: `check_ajax_referer()` + `current_user_can()`.
- Merchants redirected away from `/wp-admin/` (except their profile); use `admin_init` hook.

### 5.4 Data Access Rules

| Operation | Consumer | Merchant | Moderator | Editor | Admin |
|-----------|----------|----------|-----------|--------|-------|
| View published brands | ✅ | ✅ | ✅ | ✅ | ✅ |
| View own brand in Merchant Center | — | ✅ | — | — | — |
| Edit own brand (reviewable fields) | — | ✅ | — | — | — |
| Edit own brand (editorial fields) | — | ❌ | — | — | — |
| View/Create/Edit all brands | — | — | ✅ | ✅ | ✅ |
| Delete brands | — | — | ❌ | ❌ | ✅ |
| View/Manage submissions queue | — | — | ✅ | ✅ | ✅ |
| View/Manage claims queue | — | — | ✅ | ✅ | ✅ |
| Manage categories/attributes | — | — | ❌ | ✅ | ✅ |
| Manage settings/integrations | — | — | ❌ | ❌ | ✅ |
| Access WordPress Admin | ❌ | ❌ | ✅ | ✅ | ✅ |

---

## 6. REST API Endpoints

All endpoints under `/wp-json/bdc/v1/`.

### Public (no auth)

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/brands` | List published brands (paginated, filterable) |
| GET | `/brands/{id}` | Single brand detail |
| GET | `/brands/slug/{slug}` | Brand by slug |
| GET | `/categories` | Category tree |
| GET | `/categories/{id}` | Single category with attributes |
| GET | `/products` | List products |
| GET | `/search` | Search (query param: `q`) |
| GET | `/search/suggestions` | Search suggestions (query param: `q`) |
| POST | `/submissions` | Submit brand/request/report |
| POST | `/visit/{brand_id}` | Record Visit Store click |

### Authenticated (Merchant)

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/merchant/brands` | Merchant's owned brands |
| GET | `/merchant/brands/{id}` | Merchant's brand detail |
| PUT | `/merchant/brands/{id}` | Update brand (reviewable fields) |
| GET | `/merchant/brands/{id}/products` | Merchant's products |
| POST | `/merchant/brands/{id}/products` | Add product |
| PUT | `/merchant/brands/{id}/products/{pid}` | Update product |
| DELETE | `/merchant/brands/{id}/products/{pid}` | Delete product |
| POST | `/merchant/claims` | Submit claim |
| GET | `/merchant/claims` | Merchant's claims |
| GET | `/merchant/traffic/{brand_id}` | Basic traffic data |

### Authenticated (Admin/Editor/Moderator)

| Method | Route | Description |
|--------|-------|-------------|
| PUT | `/admin/brands/{id}` | Admin update brand (all fields) |
| DELETE | `/admin/brands/{id}` | Delete brand |
| POST | `/admin/brands/merge` | Merge two brands |
| PUT | `/admin/submissions/{id}` | Review submission (approve/reject/request-info) |
| PUT | `/admin/claims/{id}` | Review claim (approve/reject/request-info/revoke) |
| POST | `/admin/submissions/{id}/create-brand` | Create brand from approved submission |
| GET | `/admin/activity` | Activity log |

---

## 7. Visit Store Tracking

### 7.1 Redirect Flow

```
User clicks "Visit Store"
       ↓
POST /wp-json/bdc/v1/visit/{brand_id}
       ↓
Server validates:
  - brand_id exists and is published
  - destination URL is valid (not internal, has protocol)
  - rate limit: max 5 clicks per IP per brand per hour
       ↓
Record visit in bdc_visit_log
       ↓
Return JSON: { "redirect_url": "https://..." }
       ↓
Client-side: window.open(redirect_url, '_blank', 'noopener,noreferrer')
```

### 7.2 Data Collected

- brand_id
- visitor_ip (hashed with SHA-256 for privacy)
- user_agent
- referrer_url
- created_at

### 7.3 Rate Limiting

Max 5 tracked clicks per IP per brand per hour. Subsequent clicks still redirect but are not logged.

---

## 8. Cloudflare R2 Integration

### 8.1 Configuration

Credentials via `wp-config.php` constants (never committed):

```php
define('BDC_R2_ACCESS_KEY', 'xxx');
define('BDC_R2_SECRET_KEY', 'xxx');
define('BDC_R2_ENDPOINT', 'https://{account}.r2.cloudflarestorage.com');
define('BDC_R2_BUCKET', 'brandsdiscovery-prod');
define('BDC_R2_PUBLIC_URL', 'https://cdn.brandsdiscovery.com');
```

### 8.2 Image Upload Flow

```
1. File uploaded via WordPress media handler or custom REST endpoint
2. Validate: allowed types (jpg, jpeg, png, webp, svg), max size 5MB
3. Generate unique key: {type}/{id}/{timestamp}-{random}.{ext}
4. Upload original to R2
5. Generate presets (thumbnail, small, medium, large, card, hero)
6. Store object key(s) in database
7. Return public URL(s)
```

### 8.3 R2 Abstraction Layer

All R2 operations go through a single abstraction class (`BDC_Media`). Theme and plugins never call R2 SDK directly. This enables swapping to another storage provider without changing consuming code.

```php
BDC_Media::upload($file_path, $type, $id)
BDC_Media::get_url($object_key, $preset = 'original')
BDC_Media::delete($object_key)
BDC_Media::delete_all_for($type, $id)
```

---

## 9. Form Validation & Anti-Spam

### 9.1 Common Rules

| Field Type | Validation |
|------------|------------|
| Email | `filter_var($email, FILTER_VALIDATE_EMAIL)` |
| URL | `filter_var($url, FILTER_VALIDATE_URL)` + must have protocol |
| Required text | Non-empty, trimmed, max 500 chars |
| Description/textarea | Max 5000 chars |
| Country | Must be valid ISO 3166-1 alpha-2 |

### 9.2 Anti-Spam

- Honeypot field (hidden input, reject if filled)
- reCAPTCHA v3 on public forms (score threshold: 0.5)
- Rate limit: max 3 submissions per IP per hour
- Domain normalization for duplicate detection

### 9.3 Duplicate Detection (Submissions)

1. Extract domain from submitted URL:
   - Strip protocol (`http://`, `https://`)
   - Strip `www.` prefix
   - Strip path, query string, fragment
   - Lowercase
   - Result: `example.com`
2. Check against `bdc_brands.website` (normalized) and `bdc_submissions.normalized_domain` (pending submissions)
3. If match found: flag as potential duplicate, allow admin override

---

## 10. SEO Implementation

### 10.1 SEO Title Template Rules

Default templates (overridable per entity):

| Context | Template |
|---------|----------|
| Homepage | `{site_name} — Discover Independent Brands` |
| Brand | `{brand_name} — {primary_category} Brand | {site_name}` |
| Category | `{category_name} — Best Brands & Buying Guide | {site_name}` |
| Guide | `{guide_title} | {site_name}` |
| Legal Page | `{page_title} | {site_name}` |

### 10.2 Schema.org Mapping

| Page Type | Schema Type | Key Properties |
|-----------|-------------|----------------|
| Homepage | Organization (site) | name, url, logo, sameAs |
| Brand Detail | Brand | name, logo, url, description, sameAs |
| Brand Detail | Product (showcase) | name, image, description, brand |
| Category | CollectionPage | name, description, mainEntity (ItemList of brands) |
| Guide | Article | headline, author, datePublished, image |
| FAQ Section | FAQPage | mainEntity (Question/Answer list) |
| All Pages | BreadcrumbList | itemListElement |

### 10.3 Integration with Rank Math

- Rank Math handles: sitemap generation, Open Graph defaults, robots.txt, global SEO settings
- SEO Toolkit handles: brand-specific metadata, schema, internal linking relationships, dynamic meta templates
- SEO Toolkit hooks into Rank Math filters where available; otherwise outputs independently

---

## 11. Email System

### 11.1 Setup

Use a mature SMTP plugin (e.g., WP Mail SMTP, Post SMTP). Configured per SiteGround/environment.

### 11.2 Templates

All email content uses WordPress `wp_mail()`. Templates are plain HTML with inline styles.

Notification emails include:
- BrandsDiscovery logo
- Context-specific message
- Action link (where applicable)
- Footer: "This is an automated message from BrandsDiscovery."

---

## 12. Version Management

### 12.1 Version Numbers

All artifacts carry the same **Platform Version** (`1.0.0`).

| Artifact | Version Source | Example |
|----------|---------------|---------|
| Theme | `style.css` header: `Version: 1.0.0` | — |
| Core Plugin | `brandsdiscovery-core.php` header: `Version: 1.0.0` | — |
| Merchant Plugin | `brandsdiscovery-merchant-center.php` header | — |
| SEO Plugin | `brandsdiscovery-seo-toolkit.php` header | — |
| Database Schema | `BDC_DB_VERSION` constant in Core | — |
| REST API | URL path includes version: `/bdc/v1/` | — |
| Documentation | `v1.0.0` in all spec files | — |

### 12.2 Version Bump Rules

Semantic versioning (MAJOR.MINOR.PATCH):
- **MAJOR**: breaking changes to API, database schema, or public behavior
- **MINOR**: new backward-compatible features
- **PATCH**: bug fixes, performance improvements, wording changes

All version bumps require: changelog update, compatibility matrix check, test report.

---

## 13. Security

### 13.1 Data Protection

| Concern | Implementation |
|---------|---------------|
| All writes | `current_user_can()` + `wp_verify_nonce()` |
| REST API writes | `permission_callback` returns `current_user_can()` |
| SQL queries | `$wpdb->prepare()` for all dynamic queries |
| Output | `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses()` |
| File uploads | `wp_check_filetype()`, MIME validation |
| Redirects | `wp_safe_redirect()` or validated external URL |
| Secrets | `wp-config.php` constants, never in version control |
| Nonces | All forms and AJAX actions |
| XSS | Escape on output |
| CSRF | Nonce on all state-changing requests |

### 13.2 Password & Auth

- Use WordPress built-in password hashing
- Never store plaintext passwords
- Social login: rely on plugin's token exchange, store only linked user ID

---

## 14. Development Workflow

### 14.1 Environment

```
Local (LocalWP / MAMP / Docker)
  ↓
Staging (SiteGround staging site)
  ↓
Production (SiteGround live site)
```

### 14.2 Change Process

1. Read Global Rules + relevant module docs
2. Produce impact analysis: theme, Core, Merchant, SEO, database, API, docs
3. Implement changes across all affected components consistently
4. Update versions and changelog
5. Run automated checks + integration test
6. Test on staging
7. Package and release

### 14.3 Automated Checks

- PHP syntax (`php -l`)
- WordPress Coding Standards (`phpcs`)
- Plugin activation/deactivation cycle
- Database migration up/down
- REST endpoint permissions
- Form submission/validation
- Visit Store redirect flow
- R2 upload/display
- Responsive layout (mobile/tablet/desktop)
- Version consistency across all headers
