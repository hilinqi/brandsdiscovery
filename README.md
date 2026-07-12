# BrandsDiscovery MVP v1.0 Official Development Specification

This package is the only development authority for Pi Agent. Do not use previous BrandsDiscovery ZIP files as implementation specifications.

## Product
An English-language independent brand discovery website. Consumers discover brands and visit official stores. Brand owners claim and maintain profiles. MVP does not process transactions.

## Build Output
- Custom WordPress theme
- BrandsDiscovery Core plugin
- BrandsDiscovery Merchant Center plugin
- BrandsDiscovery SEO Toolkit plugin
- SiteGround deployment package
- Cloudflare R2 image integration
- Test and release reports

## Documentation Architecture (4 Layers)

| Layer | File | Purpose |
|-------|------|---------|
| 1 — MVP Specification | `specs/mvp-specification.md` | What to build, what NOT to build |
| 2 — UI/UX Specification | `specs/ui-ux-specification.md` | Design system, components, layouts |
| 3 — Technical Specification | `specs/technical-specification.md` | Database, API, permissions, workflow |
| 4 — Gap Analysis | `specs/gap-analysis.md` | What is still undefined |

Additional:
- `FUTURE-ROADMAP.md` — Post-MVP features (do NOT include in current development)
- `docs/01-12/` — Original module-level requirement documents (reference only)

## Reading Order
1. `README.md` (this file)
2. `00-Global-Rules.md`
3. `specs/mvp-specification.md`
4. `specs/ui-ux-specification.md`
5. `specs/technical-specification.md`
6. `specs/gap-analysis.md`
7. `FUTURE-ROADMAP.md`
8. Reference: `docs/01` through `docs/12`, `PI-EXECUTION-INSTRUCTION.md`, `PROJECT-ARCHITECTURE.md`, `MODULE-MAP.md`, `DEPENDENCY-MAP.md`, `MVP-ACCEPTANCE-CHECKLIST.md`
