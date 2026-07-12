# BrandsDiscovery Future Roadmap

> Post-MVP features. Not part of current development scope.
> Items here may be promoted to MVP specs in future versions if scope changes.

---

## Search & Discovery

| Feature | Priority | Rationale |
|---------|----------|-----------|
| Dedicated search engine (Elasticsearch/Meilisearch) | Medium | WP_Query works for MVP; switch when > 10k brands |
| Search result caching | Low | Premature without production traffic data |
| Search ranking weight tuning UI | Low | Fixed rules sufficient until SEO team requests |
| Search analytics (zero-result tracking, popular queries, conversion) | High | Critical for content gap discovery |
| Spell correction / fuzzy search | Medium | Expected by English-language users |
| Synonym mapping | Low | — |

## Merchant Experience

| Feature | Priority | Rationale |
|---------|----------|-----------|
| Advanced traffic analytics (date ranges, charts, source breakdown) | High | Merchants need data to value the platform |
| Notification center (in-app + email preferences) | High | Current email-only is insufficient |
| Bulk product import (CSV) | Medium | — |
| Multiple brand management per account | Medium | — |
| Merchant-to-merchant messaging | Low | — |
| Claim dispute resolution workflow | Medium | Two parties claiming same brand |

## Content & SEO

| Feature | Priority | Rationale |
|---------|----------|-----------|
| SEO field template engine (per content type) | High | Manual SEO entry doesn't scale |
| Image SEO (auto alt text, WebP conversion, srcset) | High | Core Web Vitals and SEO ranking |
| Content scheduling calendar | Low | — |
| Content A/B testing | Low | — |
| 301 redirect manager | Medium | Needed as brand slugs change |

## Admin & Operations

| Feature | Priority | Rationale |
|---------|----------|-----------|
| Bulk operations (approve/reject/delete/merge) | High | Admin bottleneck without it |
| Advanced activity audit log | Medium | — |
| Export (CSV) for brands, submissions, claims | Medium | — |
| Scheduled reports (weekly email digest to admin) | Low | — |
| Broken link auto-detection (cron-based crawler) | Medium | — |
| Database cleanup / optimization utilities | Low | — |

## Monetization (V2+)

| Feature | Priority | Rationale |
|---------|----------|-----------|
| Brand verification tier system (Verified / Premium / Featured) | High | Pricing differentiation |
| Ad placement management (CPM/CPC) | Medium | Requires traffic first |
| Sponsored listing system | Medium | — |
| Affiliate link settlement (tracking + payout) | Medium | — |
| Lead/customer inquiry forms (CTA on brand pages) | Medium | Revenue from brand leads |

## Platform

| Feature | Priority | Rationale |
|---------|----------|-----------|
| Multilingual frontend (Chinese + more) | Low | Global Rules MVP decision |
| Native mobile app | Low | Responsive web first |
| AI-powered brand recommendations | Low | Manual curation sufficient for launch |
| API for third-party integrations | Medium | — |
| Webhook notifications | Low | — |
