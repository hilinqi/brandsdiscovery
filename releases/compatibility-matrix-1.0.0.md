# BrandsDiscovery v1.0.0 — Compatibility Matrix

## WordPress Compatibility

| Component | Minimum | Recommended | Tested |
|-----------|---------|-------------|--------|
| WordPress | 6.0 | 6.5+ | 6.5 |
| PHP | 7.4 | 8.0+ | 8.2 |
| MySQL | 5.7 | 8.0+ | 8.0 |

## Plugin Cross-Compatibility

| Plugin Active | Core | Merchant Center | SEO Toolkit |
|---------------|------|-----------------|-------------|
| Core only | ✓ | — | — |
| Core + Merchant | ✓ | ✓ | — |
| Core + SEO | ✓ | — | ✓ |
| All three | ✓ | ✓ | ✓ |
| Merchant only (Core inactive) | — | ✗ (shows notice) | — |
| SEO only (Core inactive) | — | — | ✗ (silent fail) |

## Plugin Compatibility with Third-Party

| Third-Party Plugin | Status | Notes |
|--------------------|--------|-------|
| Rank Math SEO | ✓ Compatible | SEO Toolkit hooks into Rank Math filters |
| WP Mail SMTP | ✓ Compatible | Email via WordPress `wp_mail()` |
| Nextend Social Login | ✓ Compatible | Business logic depends on WP roles, not plugin internals |
| Complianz Cookie Consent | ✓ Compatible | Standalone; theme overrides CSS |
| Contact Form 7 | ✓ Compatible | Independent; no overlap with submission forms |

## PHP Extensions Required

| Extension | Required | Purpose |
|-----------|----------|---------|
| `mysqli` / `pdo_mysql` | Required | Database |
| `json` | Required | JSON encode/decode |
| `mbstring` | Required | Multibyte string functions |
| `curl` | Recommended | HTTP requests for R2 |
| `gd` / `imagick` | Recommended | Image processing |
| `openssl` | Required | HTTPS, hashing |

## Database

| Table | Engine | Charset |
|-------|--------|---------|
| All `{prefix}bdc_*` tables | InnoDB | utf8mb4 |
| Uses `$wpdb->prefix` | ✓ | — |
| `dbDelta()` for schema management | ✓ | — |

## Theme

| Dependency | Required |
|------------|----------|
| brandsdiscovery-core plugin | Yes |
| jQuery | No (vanilla JS) |
| Inter font (Google Fonts) | Optional (system fallback) |

## Hosting

| Provider | Status | Notes |
|----------|--------|-------|
| SiteGround | Primary target | Shared/cloud hosting |
| Any WP-compatible host | Support expected | Standard WordPress setup |
| Local (LocalWP/MAMP/Docker) | Development | Full support |
