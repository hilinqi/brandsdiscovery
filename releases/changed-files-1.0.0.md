# BrandsDiscovery v1.0.0 — Changed File List

## plugins/brandsdiscovery-core (33 files)

| File | Description |
|------|-------------|
| `brandsdiscovery-core.php` | Main plugin: constants, hooks, role registration, autoload |
| `includes/class-bdc-activator.php` | Activation: create 10 tables, seed roles |
| `includes/class-bdc-deactivator.php` | Deactivation: flush rewrite rules |
| `includes/class-bdc-db.php` | Database abstraction layer |
| `includes/class-bdc-migrator.php` | Database migration handler |
| `includes/class-bdc-model.php` | Abstract model base class |
| `includes/helpers.php` | Utility functions: domain normalization, slug generation, country validation, completeness scoring, email, activity logging |
| `includes/models/class-bdc-brand.php` | Brand model: CRUD, state machine, categories, claims, merge, search |
| `includes/models/class-bdc-category.php` | Category model: tree, attributes, ancestors |
| `includes/models/class-bdc-product.php` | Product model: by-brand queries |
| `includes/models/class-bdc-attribute.php` | Attribute model: values, presets |
| `includes/models/class-bdc-submission.php` | Submission model: create, review workflow, auto-create brand |
| `includes/models/class-bdc-claim.php` | Claim model: submit, review, cooldown |
| `includes/models/class-bdc-visit-log.php` | Visit log model: record, count, rate limiting |
| `includes/models/class-bdc-activity-log.php` | Activity log model |
| `includes/class-bdc-search.php` | Search engine: full-text, ranking, suggestions |
| `includes/class-bdc-tracking.php` | Tracking service: visit count by period |
| `includes/class-bdc-media.php` | Cloudflare R2 media abstraction |
| `includes/api/class-bdc-rest-brands.php` | REST: brands list/detail/update/delete/merge/status |
| `includes/api/class-bdc-rest-categories.php` | REST: categories tree/detail/attributes |
| `includes/api/class-bdc-rest-products.php` | REST: products list |
| `includes/api/class-bdc-rest-search.php` | REST: search + suggestions |
| `includes/api/class-bdc-rest-submissions.php` | REST: create submissions + admin review |
| `includes/api/class-bdc-rest-tracking.php` | REST: Visit Store tracking |
| `includes/admin/class-bdc-admin.php` | Admin: menu registration (9 items), merchant block |
| `includes/admin/class-bdc-admin-dashboard.php` | Admin dashboard: stats, recent visits |
| `includes/admin/class-bdc-admin-brands.php` | Admin brands: status filter, inline actions |
| `includes/admin/class-bdc-admin-categories.php` | Admin categories: tree display |
| `includes/admin/class-bdc-admin-products.php` | Admin products table |
| `includes/admin/class-bdc-admin-submissions.php` | Admin submissions: review queue |
| `includes/admin/class-bdc-admin-claims.php` | Admin claims: approve/reject/revoke |
| `assets/css/admin.css` | Admin styles: dashboard grid, status badges |
| `assets/js/admin.js` | Admin scripts: AJAX approve/reject |

## plugins/brandsdiscovery-merchant-center (10 files)

| File | Description |
|------|-------------|
| `brandsdiscovery-merchant-center.php` | Main plugin: rewrite rules, template loader, auth checks |
| `includes/class-bdc-merchant-dashboard.php` | Dashboard data layer |
| `includes/class-bdc-merchant-brands.php` | Brand edit with field-level permissions |
| `includes/api/class-bdc-merchant-rest.php` | REST API: brands, products, claims, traffic |
| `templates/dashboard.php` | Merchant dashboard template |
| `templates/brand.php` | Brand edit form with save-via-REST |
| `templates/claim.php` | Claim submission form |
| `templates/claims.php` | Claims list |
| `templates/products.php` | Product management with add/delete |
| `templates/claim-status.php` | Claim status + Account settings (shared) |
| `templates/settings.php` | Redirect to claim-status with settings context |
| `assets/css/merchant.css` | Merchant Center CSS |
| `assets/js/merchant.js` | Merchant Center JS |

## plugins/brandsdiscovery-seo-toolkit (1 file)

| File | Description |
|------|-------------|
| `brandsdiscovery-seo-toolkit.php` | SEO: meta output, schema (Breadcrumb/Organization), sitemap integration, internal links |

## theme/brandsdiscovery-theme (8 files)

| File | Description |
|------|-------------|
| `style.css` | CSS variables, reset, layout, components, responsive, header, footer |
| `functions.php` | Theme setup, asset loading, Google Fonts |
| `header.php` | Site header: logo, nav, search, auth |
| `footer.php` | 4-column footer: Discover, Merchants, Company, Legal |
| `inc/template-functions.php` | API fetch helper, brand card component, pagination |
| `inc/template-tags.php` | Breadcrumbs |
| `templates/front-page.php` | Homepage: hero + categories + brands + CTAs |
| `templates/brand.php` | Brand detail: cover, header, content, sidebar, visit tracking |

## tests (1 file)

| File | Description |
|------|-------------|
| `tests/bdc-integration-tests.php` | Integration test suite: 15 test classes, 100+ assertions |

## specs (4 files)

| File | Description |
|------|-------------|
| `specs/mvp-specification.md` | Layer 1: Product specification |
| `specs/ui-ux-specification.md` | Layer 2: Design specification |
| `specs/technical-specification.md` | Layer 3: Technical specification |
| `specs/gap-analysis.md` | Layer 4: Gap analysis |

## Root (updated files)

| File | Description |
|------|-------------|
| `README.md` | Updated with 4-layer architecture |
| `00-Global-Rules.md` | Added third-party plugin principle, version rules |
| `MANIFEST.json` | Updated file inventory |
| `CHANGELOG.md` | Updated with all changes |
| `FUTURE-ROADMAP.md` | Post-MVP features |

---

**Total changed/new files: 57**
**Lines of code: ~12,000+**
