# BrandsDiscovery Gap Analysis v1.0.0

> Layer 4 of 4: Development Gap Analysis
> Answers: What is still undefined or needs refinement.
> 
> **This document identifies gaps. It is NOT a development specification.**
> Items here should eventually be resolved and merged into Layer 1–3 documents,
> or added to FUTURE-ROADMAP.md if they are post-MVP.

---

## Category A: Must Resolve Before Development

These gaps directly block implementation and must be addressed in formal specs.

### A1. Category-Level Attribute Defaults (→ mvp-specification.md)

**Gap**: The 10 top-level categories are defined by name only. Each category needs its initial attribute set specified.

**Resolution needed**:
- List at least 3–5 attributes per top-level category
- Provide sample selectable values for each dropdown/multi-select attribute
- Define which attributes are common across categories (e.g., "Origin Country") vs category-specific

### A2. Merchant Edit — Field-Level Permission Matrix (→ mvp-specification.md, technical-specification.md)

**Gap**: "Merchant changes enter review where required" and "Editorial fields remain platform-controlled" are stated but no field-level matrix exists.

**Resolution needed**:
| Brand Field | Merchant View | Merchant Edit | Requires Admin Review |
|-------------|---------------|---------------|----------------------|
| Name | Yes | Yes | Yes |
| Logo | Yes | Yes | Yes |
| Cover | Yes | Yes | Yes |
| Short Description | Yes | Yes | Yes |
| Full Description | Yes | Yes | Yes |
| Website | Yes | No | — |
| Origin Country | Yes | No | — |
| Categories | Yes | No | — |
| Markets | Yes | Yes | Yes |
| Shipping Regions | Yes | Yes | No |
| Payment Methods | Yes | Yes | No |
| Return Policy | Yes | Yes | No |
| Support Contact | Yes | Yes | No |
| Social Links | Yes | Yes | No |
| Products | Yes | Yes (CRUD) | No |

### A3. 12 Legal Page Contents (→ mvp-specification.md)

**Gap**: Names are listed but no content specifications exist.

**Resolution needed**: For each of the 12 legal/info pages, define:
- Who provides initial content (project owner, legal team, AI draft?)
- What specific sections each page must include
- Any required legal disclaimers

### A4. Claim Evidence Requirements (→ mvp-specification.md)

**Gap**: "Submit company/contact/evidence" — evidence types are undefined.

**Resolution needed**:
- Domain ownership verification (TXT record? email to admin@domain?)
- Business registration document upload
- Official email from company domain
- Which combinations qualify for auto-approval vs manual review?

### A5. "Complete Profile" Scoring Formula (→ technical-specification.md)

**Gap**: Search ranking uses "published/complete profile" but completeness scoring is undefined.

**Resolution needed**:
- Which fields count toward completeness?
- Weight per field or simple percentage?
- Minimum completeness threshold for search ranking boost?

### A6. Rank Math Integration Boundary (→ technical-specification.md)

**Gap**: "Rank Math + SEO Toolkit" split is mentioned but integration points are vague.

**Resolution needed**:
- Which Rank Math filters/hooks does SEO Toolkit use?
- What happens if Rank Math is deactivated? Fallback or error?
- Which settings are in Rank Math UI vs BrandsDiscovery admin?

---

## Category B: Should Resolve (Enhances Quality)

These do not block implementation but significantly affect user experience.

### B1. Footer Layout & Link Inventory (→ ui-ux-specification.md)

**Gap**: Footer content undefined beyond "navigation links + legal pages."

**Resolution**: Define footer columns (e.g., 4-column: Discover + For Merchants + Company + Legal), exact links per column.

### B2. 404 Page Content (→ ui-ux-specification.md)

**Gap**: 404 page content not specified.

**Resolution**: Skeleton: search bar + "Popular Categories" (4 cards) + "Popular Brands" (4 cards) + "Back to Homepage" button.

### B3. Brand Archive Page — Sort & Filter Options (→ ui-ux-specification.md)

### B4. Content Type Fields (Guides, Lists, Reviews, Comparisons) (→ mvp-specification.md)

**Gap**: Content types named but no field definitions exist.

### B5. Search Suggestions — Trigger & Display Rules (→ ui-ux-specification.md, technical-specification.md)

### B6. Account Shell Page — Consumer Features (→ mvp-specification.md)

**Gap**: What does the consumer see after login? Submission history? Favorites? Nothing?

### B7. Broken Link Detection — Method (→ technical-specification.md)

**Gap**: "Dashboard → broken links" mentioned but detection method unknown.

### B8. Admin Dashboard — Date Range Filtering (→ ui-ux-specification.md)

### B9. Merchant Notification Center (→ mvp-specification.md, ui-ux-specification.md)

**Gap**: Only email notifications mentioned. No in-app notification center.

### B10. Brand Slug Conflict Handling (→ technical-specification.md)

### B11. Cross-Category Brand — Attribute Merging (→ technical-specification.md)

### B12. Submitter History (→ mvp-specification.md)

**Gap**: Submitter cannot check status of previous submissions without login.

---

## Category C: Future (Do NOT Add to MVP)

These are explicitly excluded from MVP. Moved to FUTURE-ROADMAP.md.

| Item | Reason |
|------|--------|
| Dedicated search engine (Elasticsearch/Meilisearch) | MVP uses WP_Query; switch when traffic demands |
| Search result caching layer | Premature optimization |
| Search weight/ranking engine tuning UI | MVP uses fixed ranking rules |
| Advanced traffic analytics dashboard | Post-MVP feature |
| AI-powered recommendations | Excluded per Global Rules |
| Affiliate link/settlement system | Excluded per Global Rules |
| Multilingual frontend | Excluded per Global Rules |
| Custom SMTP server | Use mature plugin |
| Custom cookie consent system | Use mature plugin |
| Custom social login implementation | Use mature plugin |
| 301 redirect management UI | Use Rank Math or manual |
| A/B testing framework | Post-MVP |
| Ad placement management system | Post-MVP |
| Sponsored content bidding platform | Post-MVP |
| Lead/customer inquiry forms | Post-MVP |
| Advanced CRM integration | Excluded per Global Rules |
| Native mobile app | Excluded per Global Rules |
| Forum/community | Excluded per Global Rules |
