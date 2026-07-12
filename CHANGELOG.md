# Changelog

## 1.0.0
Initial official MVP specification covering the public website, brand/category/product discovery, search, submissions, claims, merchant dashboard, SEO, admin operations, Cloudflare R2 and deployment.

### Added
- `specs/mvp-specification.md` — Layer 1: Product specification (feature boundary, pages, modules, workflows, permissions, forms, email, language rules)
- `specs/ui-ux-specification.md` — Layer 2: Design specification (color palette, typography, spacing, shadows, breakpoints, component library, state patterns, page layouts, responsive behavior, accessibility)
- `specs/technical-specification.md` — Layer 3: Technical specification (database dictionary, state machines, permissions matrix, REST API, plugin communication, R2 integration, form validation, SEO implementation, email system, version management, security)
- `specs/gap-analysis.md` — Layer 4: Gap analysis (categories A/B/C — must resolve, should resolve, future)
- `FUTURE-ROADMAP.md` — Post-MVP features excluded from current development scope
- Third-party plugin principle added to Global Rules
- 4-layer documentation architecture documented in README

### Changed
- Code comments language: Chinese → English (industry convention)
- Global Rules: added third-party plugin section, enhanced versioning rules
