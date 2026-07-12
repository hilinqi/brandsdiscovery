<?php
/**
 * Plugin activation handler.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Activator {

    public static function activate() {
        self::create_tables();
        self::create_roles_and_caps();
        add_option('bdc_db_version', BDC_DB_VERSION);
        flush_rewrite_rules();
    }

    private static function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix;

        // Brands table.
        $sql = "CREATE TABLE {$prefix}bdc_brands (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            logo_id VARCHAR(255) DEFAULT NULL,
            cover_id VARCHAR(255) DEFAULT NULL,
            short_description TEXT DEFAULT NULL,
            full_description LONGTEXT DEFAULT NULL,
            website VARCHAR(2048) DEFAULT NULL,
            origin_country VARCHAR(100) DEFAULT NULL,
            markets TEXT DEFAULT NULL,
            shipping_regions TEXT DEFAULT NULL,
            payment_methods TEXT DEFAULT NULL,
            return_policy TEXT DEFAULT NULL,
            support_contact VARCHAR(255) DEFAULT NULL,
            social_links TEXT DEFAULT NULL,
            is_verified TINYINT(1) NOT NULL DEFAULT 0,
            publication_status VARCHAR(30) NOT NULL DEFAULT 'draft',
            claim_status VARCHAR(30) NOT NULL DEFAULT 'unclaimed',
            claimed_by BIGINT UNSIGNED DEFAULT NULL,
            profile_completeness TINYINT UNSIGNED NOT NULL DEFAULT 0,
            display_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY name (name),
            KEY origin_country (origin_country),
            KEY is_verified (is_verified),
            KEY publication_status (publication_status),
            KEY claim_status (claim_status),
            KEY claimed_by (claimed_by)
        ) $charset_collate;";
        dbDelta($sql);

        // Categories table.
        $sql = "CREATE TABLE {$prefix}bdc_categories (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            parent_id BIGINT UNSIGNED DEFAULT NULL,
            description TEXT DEFAULT NULL,
            hero_image_id VARCHAR(255) DEFAULT NULL,
            seo_intro TEXT DEFAULT NULL,
            display_order INT NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY name (name),
            KEY parent_id (parent_id),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);

        // Brand-Category pivot table.
        $sql = "CREATE TABLE {$prefix}bdc_brand_category (
            brand_id BIGINT UNSIGNED NOT NULL,
            category_id BIGINT UNSIGNED NOT NULL,
            is_primary TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (brand_id, category_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Products table.
        $sql = "CREATE TABLE {$prefix}bdc_products (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            brand_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            image_id VARCHAR(255) DEFAULT NULL,
            price VARCHAR(100) DEFAULT NULL,
            product_url VARCHAR(2048) DEFAULT NULL,
            display_order INT NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY brand_id (brand_id),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);

        // Attributes table.
        $sql = "CREATE TABLE {$prefix}bdc_attributes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            field_type VARCHAR(30) NOT NULL,
            options TEXT DEFAULT NULL,
            display_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY name (name)
        ) $charset_collate;";
        dbDelta($sql);

        // Category attributes pivot.
        $sql = "CREATE TABLE {$prefix}bdc_category_attributes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            category_id BIGINT UNSIGNED NOT NULL,
            attribute_id BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY attribute_id (attribute_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Brand attribute values.
        $sql = "CREATE TABLE {$prefix}bdc_brand_attribute_values (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            brand_id BIGINT UNSIGNED NOT NULL,
            attribute_id BIGINT UNSIGNED NOT NULL,
            value TEXT DEFAULT NULL,
            PRIMARY KEY (id),
            KEY brand_id (brand_id),
            KEY attribute_id (attribute_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Submissions table.
        $sql = "CREATE TABLE {$prefix}bdc_submissions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            type VARCHAR(30) NOT NULL,
            data LONGTEXT NOT NULL,
            normalized_domain VARCHAR(255) DEFAULT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'new',
            reviewer_id BIGINT UNSIGNED DEFAULT NULL,
            reviewer_notes TEXT DEFAULT NULL,
            created_brand_id BIGINT UNSIGNED DEFAULT NULL,
            submitter_email VARCHAR(255) DEFAULT NULL,
            submitter_ip VARCHAR(45) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY normalized_domain (normalized_domain),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql);

        // Claims table.
        $sql = "CREATE TABLE {$prefix}bdc_claims (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            brand_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            company_name VARCHAR(255) NOT NULL,
            contact_name VARCHAR(255) NOT NULL,
            contact_email VARCHAR(255) NOT NULL,
            contact_phone VARCHAR(50) DEFAULT NULL,
            evidence TEXT DEFAULT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            reviewer_id BIGINT UNSIGNED DEFAULT NULL,
            reviewer_notes TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY brand_id (brand_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Visit log table.
        $sql = "CREATE TABLE {$prefix}bdc_visit_log (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            brand_id BIGINT UNSIGNED NOT NULL,
            visitor_ip VARCHAR(45) NOT NULL,
            user_agent VARCHAR(512) DEFAULT NULL,
            referrer_url VARCHAR(2048) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY brand_id (brand_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql);

        // Activity log table.
        $sql = "CREATE TABLE {$prefix}bdc_activity_log (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            object_type VARCHAR(50) NOT NULL,
            object_id BIGINT UNSIGNED NOT NULL,
            details TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY object_type (object_type),
            KEY object_id (object_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    private static function create_roles_and_caps() {
        // Register custom roles on activation so they exist immediately.
        if (!get_role('bdc_merchant')) {
            add_role('bdc_merchant', 'BrandsDiscovery Merchant', array(
                'read' => true,
                'bdc_manage_own_brand' => true,
                'bdc_manage_own_products' => true,
                'bdc_view_basic_traffic' => true,
            ));
        }

        if (!get_role('bdc_moderator')) {
            add_role('bdc_moderator', 'BrandsDiscovery Moderator', array(
                'read' => true,
                'bdc_review_submissions' => true,
                'bdc_review_claims' => true,
                'bdc_manage_brands' => true,
            ));
        }

        // Add capabilities to editor.
        $editor = get_role('editor');
        if ($editor) {
            $caps = array('bdc_manage_brands', 'bdc_manage_categories', 'bdc_manage_attributes',
                          'bdc_manage_products', 'bdc_review_submissions', 'bdc_review_claims',
                          'bdc_manage_seo', 'bdc_manage_guides');
            foreach ($caps as $cap) {
                $editor->add_cap($cap);
            }
        }

        // Add capabilities to administrator.
        $admin = get_role('administrator');
        if ($admin) {
            $caps = array('bdc_manage_brands', 'bdc_manage_categories', 'bdc_manage_attributes',
                          'bdc_manage_products', 'bdc_review_submissions', 'bdc_review_claims',
                          'bdc_manage_seo', 'bdc_manage_guides', 'bdc_manage_settings');
            foreach ($caps as $cap) {
                $admin->add_cap($cap);
            }
        }
    }
}
