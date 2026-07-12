# 00 Global Rules

## Language
Public website: English only.  
Consumer account: English only.  
Merchant Center: English only.  
WordPress Admin: English plus Chinese helper text.

Examples:
- Brands / 品牌
- Categories / 分类
- Claim Requests / 品牌认领申请
- Approve / 通过
- Reject / 拒绝

Variables, functions, classes, REST routes, slugs and database columns: English.  
Code comments: English, following industry conventions.  
Custom admin operator explanations: Chinese helper text.

## Product Boundary
Included: discovery, brand profiles, representative products, search, submission, claim, merchant management, SEO and outbound traffic.  
Excluded: cart, checkout, orders, inventory, native app, forum, AI recommendation, advanced CRM, multilingual frontend and affiliate settlement.

## Architecture
Theme owns presentation only. Plugins own data, workflows, permissions, APIs and migrations.

## Infrastructure
DNS: Cloudflare.  
Hosting: SiteGround.  
Images: Cloudflare R2.  
No production URL, bucket URL or secret may be hardcoded.

## WordPress
Follow WordPress Coding Standards. Use roles/capabilities, nonce checks, validation, sanitization and escaping. Templates never query custom tables directly.

## Third-Party Plugins
MVP may use mature WordPress plugins for: social login, cookie consent, contact forms, SMTP.
Principle: use mature plugins, do not reinvent the wheel.
All business logic must remain independent of third-party plugins. Replacing a plugin must not break BrandsDiscovery Core.

## Versioning
Semantic versioning (MAJOR.MINOR.PATCH). Every functional change requires a version update, changelog, compatibility check, and test report.
All artifacts (theme, plugins, database, API, documentation) must carry the same version number per release.

## Security and Testing
Protect all writes, prevent duplicate claims/submissions, validate redirects, and test activation, migrations, permissions, REST, responsive UI, cross-plugin compatibility and version consistency.
