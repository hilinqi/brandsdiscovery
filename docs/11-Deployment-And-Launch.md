# 11 Deployment and Launch

## Infrastructure
Cloudflare DNS; SiteGround; WordPress; Cloudflare R2.

## Flow
Local -> Staging -> Production. Never develop directly in production.

## Install Order
WordPress; third-party baseline plugins; Core; Merchant Center; SEO Toolkit; Theme; migrations; R2 config; permalink flush; verification.

## Launch Checklist
SSL, Cloudflare, R2, homepage, brand/category/search, submission, claim, merchant dashboard, bilingual admin helpers, SEO/sitemap, mobile, backups and rollback package.

## Acceptance
Staging passes; production succeeds; no critical PHP/JS errors; versions match; rollback package exists.
