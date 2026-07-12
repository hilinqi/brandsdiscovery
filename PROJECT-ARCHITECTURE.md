# Project Architecture

## Theme: brandsdiscovery-theme
Public and merchant templates, reusable UI components, accessibility, responsive layouts and frontend interactions.

## Plugin 1: brandsdiscovery-core
Brands, categories, products, attributes, search, submissions, Visit Store redirect tracking and R2 media abstraction.

## Plugin 2: brandsdiscovery-merchant-center
Merchant registration, login flow, claims, verification, dashboard, ownership permissions and profile editing workflow.

## Plugin 3: brandsdiscovery-seo-toolkit
SEO fields, metadata, schema, breadcrumb data, internal linking, sitemap integration and index controls.

## Dependency Direction
Theme -> plugin public interfaces  
Merchant Center -> Core public interfaces  
SEO Toolkit -> Core read interfaces  

Core must not depend on the theme, Merchant Center or SEO Toolkit.
