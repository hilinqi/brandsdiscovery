# BrandsDiscovery v1.0.0 — Rollback Steps

## Full Rollback

If the v1.0.0 deployment must be rolled back:

### Step 1: Deactivate Plugins
```bash
wp plugin deactivate brandsdiscovery-seo-toolkit
wp plugin deactivate brandsdiscovery-merchant-center
wp plugin deactivate brandsdiscovery-core
```

### Step 2: Switch Theme
```bash
wp theme activate twentytwentyfour
```

### Step 3: Remove Custom Database Tables
```sql
DROP TABLE IF EXISTS wp_bdc_activity_log;
DROP TABLE IF EXISTS wp_bdc_visit_log;
DROP TABLE IF EXISTS wp_bdc_claims;
DROP TABLE IF EXISTS wp_bdc_submissions;
DROP TABLE IF EXISTS wp_bdc_brand_attribute_values;
DROP TABLE IF EXISTS wp_bdc_category_attributes;
DROP TABLE IF EXISTS wp_bdc_attributes;
DROP TABLE IF EXISTS wp_bdc_brand_category;
DROP TABLE IF EXISTS wp_bdc_products;
DROP TABLE IF EXISTS wp_bdc_categories;
DROP TABLE IF EXISTS wp_bdc_brands;
```

### Step 4: Remove Custom Roles and Capabilities
```php
// Run via WP-CLI: wp eval-file rollback-roles.php
remove_role('bdc_merchant');
remove_role('bdc_moderator');

$editor = get_role('editor');
$caps = array('bdc_manage_brands', 'bdc_manage_categories', 'bdc_manage_attributes', 
              'bdc_manage_products', 'bdc_review_submissions', 'bdc_review_claims', 
              'bdc_manage_seo', 'bdc_manage_guides');
foreach ($caps as $cap) {
    $editor->remove_cap($cap);
    get_role('administrator')->remove_cap($cap);
}
get_role('administrator')->remove_cap('bdc_manage_settings');
```

### Step 5: Delete Option
```sql
DELETE FROM wp_options WHERE option_name = 'bdc_db_version';
```

### Step 6: Delete Plugin Files
```bash
rm -rf wp-content/plugins/brandsdiscovery-core
rm -rf wp-content/plugins/brandsdiscovery-merchant-center
rm -rf wp-content/plugins/brandsdiscovery-seo-toolkit
rm -rf wp-content/themes/brandsdiscovery-theme
```

### Step 7: Flush Rewrite Rules
```bash
wp rewrite flush
```

## Partial Rollback

To roll back only a specific plugin:
1. Deactivate the plugin
2. If it's Core: follow Full Rollback (other plugins depend on it)
3. If it's Merchant Center or SEO Toolkit: just deactivate and remove files

## Data Backup Before Rollback

Always take a full backup before rollback:
```bash
wp db export bdc-backup-$(date +%Y%m%d).sql
tar -czf bdc-files-backup-$(date +%Y%m%d).tar.gz wp-content/plugins/brandsdiscovery-*
```
