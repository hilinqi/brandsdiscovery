# BrandsDiscovery Migration Report v1.0.0

## Schema Version: 1.0.0

### Initial Installation (Fresh)

No migration from previous versions — this is the initial release.

### Tables Created

| # | Table | Purpose |
|---|-------|---------|
| 1 | `{prefix}bdc_brands` | Brand records |
| 2 | `{prefix}bdc_categories` | Category hierarchy |
| 3 | `{prefix}bdc_brand_category` | Brand ↔ Category pivot |
| 4 | `{prefix}bdc_products` | Representative products |
| 5 | `{prefix}bdc_attributes` | Attribute definitions |
| 6 | `{prefix}bdc_category_attributes` | Category ↔ Attribute pivot |
| 7 | `{prefix}bdc_brand_attribute_values` | Brand attribute values |
| 8 | `{prefix}bdc_submissions` | User submissions |
| 9 | `{prefix}bdc_claims` | Brand claims |
| 10 | `{prefix}bdc_visit_log` | Visit Store tracking |
| 11 | `{prefix}bdc_activity_log` | Activity audit trail |

### Options Created

| Option | Value | Purpose |
|--------|-------|---------|
| `bdc_db_version` | `1.0.0` | Database version tracking |

### Roles Created

| Role | WP Role Name | Description |
|------|-------------|-------------|
| Merchant | `bdc_merchant` | Manage owned brands |
| Moderator | `bdc_moderator` | Review submissions and claims |

### Capabilities Added to Existing Roles

| Role | Capabilities Added |
|------|-------------------|
| Editor | `bdc_manage_brands`, `bdc_manage_categories`, `bdc_manage_attributes`, `bdc_manage_products`, `bdc_review_submissions`, `bdc_review_claims`, `bdc_manage_seo`, `bdc_manage_guides` |
| Administrator | Above + `bdc_manage_settings` |

### Migration for Future Versions

Future database changes must:
1. Increment `BDC_DB_VERSION` constant
2. Add migration logic in `BDC_Migrator::run()`
3. Update this report with version-specific changes
4. Provide rollback SQL for each change

### Rollback

For v1.0.0 rollback:
1. Deactivate `brandsdiscovery-core` plugin
2. Delete all `{prefix}bdc_*` tables
3. Remove `bdc_merchant` and `bdc_moderator` roles
4. Remove custom capabilities from Editor and Administrator roles
5. Delete `bdc_db_version` option
