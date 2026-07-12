<?php
/**
 * Plugin Name: BrandsDiscovery Core
 * Plugin URI: https://brandsdiscovery.com
 * Description: Core plugin for BrandsDiscovery — brands, categories, products, attributes, search, submissions, Visit Store tracking, and R2 media integration.
 * Version: 1.0.0
 * Author: BrandsDiscovery
 * License: GPL-2.0+
 * Text Domain: brandsdiscovery-core
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('BDC_CORE_VERSION', '1.0.0');
define('BDC_CORE_PATH', plugin_dir_path(__FILE__));
define('BDC_CORE_URL', plugin_dir_url(__FILE__));
define('BDC_CORE_FILE', __FILE__);

/**
 * Database version constant.
 * Increment when schema changes to trigger migration.
 */
define('BDC_DB_VERSION', '1.0.0');

/**
 * Load plugin textdomain.
 */
function bdc_load_textdomain() {
    load_plugin_textdomain('brandsdiscovery-core', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'bdc_load_textdomain');

/**
 * Plugin activation hook.
 */
register_activation_hook(__FILE__, 'bdc_activate_plugin');
function bdc_activate_plugin() {
    require_once BDC_CORE_PATH . 'includes/class-bdc-activator.php';
    BDC_Activator::activate();
}

/**
 * Plugin deactivation hook.
 */
register_deactivation_hook(__FILE__, 'bdc_deactivate_plugin');
function bdc_deactivate_plugin() {
    require_once BDC_CORE_PATH . 'includes/class-bdc-deactivator.php';
    BDC_Deactivator::deactivate();
}

/**
 * Load all dependencies and initialize the plugin.
 */
function bdc_init() {
    // Load helper functions.
    require_once BDC_CORE_PATH . 'includes/helpers.php';

    // Load database layer.
    require_once BDC_CORE_PATH . 'includes/class-bdc-db.php';

    // Load model classes.
    require_once BDC_CORE_PATH . 'includes/models/class-bdc-brand.php';
    require_once BDC_CORE_PATH . 'includes/models/class-bdc-category.php';
    require_once BDC_CORE_PATH . 'includes/models/class-bdc-product.php';
    require_once BDC_CORE_PATH . 'includes/models/class-bdc-attribute.php';
    require_once BDC_CORE_PATH . 'includes/models/class-bdc-submission.php';
    require_once BDC_CORE_PATH . 'includes/models/class-bdc-claim.php';
    require_once BDC_CORE_PATH . 'includes/models/class-bdc-visit-log.php';
    require_once BDC_CORE_PATH . 'includes/models/class-bdc-activity-log.php';

    // Load R2 media abstraction.
    require_once BDC_CORE_PATH . 'includes/class-bdc-media.php';

    // Load search engine.
    require_once BDC_CORE_PATH . 'includes/class-bdc-search.php';

    // Load tracking.
    require_once BDC_CORE_PATH . 'includes/class-bdc-tracking.php';

    // Load REST API controllers.
    require_once BDC_CORE_PATH . 'includes/api/class-bdc-rest-brands.php';
    require_once BDC_CORE_PATH . 'includes/api/class-bdc-rest-categories.php';
    require_once BDC_CORE_PATH . 'includes/api/class-bdc-rest-products.php';
    require_once BDC_CORE_PATH . 'includes/api/class-bdc-rest-search.php';
    require_once BDC_CORE_PATH . 'includes/api/class-bdc-rest-submissions.php';
    require_once BDC_CORE_PATH . 'includes/api/class-bdc-rest-tracking.php';

    // Load admin (always — hooks only fire in admin context).
    require_once BDC_CORE_PATH . 'includes/admin/class-bdc-admin.php';
    require_once BDC_CORE_PATH . 'includes/admin/class-bdc-admin-dashboard.php';
    require_once BDC_CORE_PATH . 'includes/admin/class-bdc-admin-brands.php';
    require_once BDC_CORE_PATH . 'includes/admin/class-bdc-admin-categories.php';
    require_once BDC_CORE_PATH . 'includes/admin/class-bdc-admin-products.php';
    require_once BDC_CORE_PATH . 'includes/admin/class-bdc-admin-submissions.php';
    require_once BDC_CORE_PATH . 'includes/admin/class-bdc-admin-claims.php';

    // Admin setup (menu registration etc).
    new BDC_Admin();

    // Initialize core class.
    $core = new BDC_Core();
    $core->run();
}
add_action('plugins_loaded', 'bdc_init');

// Check DB version and run migrations early.
add_action('plugins_loaded', 'bdc_check_db_version', 5);

/**
 * Check database version and run migrations.
 */
function bdc_check_db_version() {
    $installed_version = get_option('bdc_db_version', '0');
    if (version_compare($installed_version, BDC_DB_VERSION, '<')) {
        require_once BDC_CORE_PATH . 'includes/class-bdc-migrator.php';
        BDC_Migrator::run($installed_version, BDC_DB_VERSION);
    }
}

/**
 * Core plugin class.
 */
class BDC_Core {

    private $brands;
    private $categories;
    private $products;
    private $attributes;
    private $search;
    private $tracking;

    public function __construct() {
        $this->brands     = new BDC_Brands();
        $this->categories = new BDC_Categories();
        $this->products   = new BDC_Products();
        $this->attributes = new BDC_Attributes();
    }

    public function run() {
        // Register REST API routes.
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Register custom post types and taxonomies if needed.
        add_action('init', array($this, 'register_post_types'));

        // Register custom user roles.
        add_action('init', array($this, 'register_roles'));
    }

    public function register_rest_routes() {
        (new BDC_REST_Brands())->register_routes();
        (new BDC_REST_Categories())->register_routes();
        (new BDC_REST_Products())->register_routes();
        (new BDC_REST_Search())->register_routes();
        (new BDC_REST_Submissions())->register_routes();
        (new BDC_REST_Tracking())->register_routes();
    }

    public function register_post_types() {
        // Core uses custom tables, not CPTs.
        // Content types (guides, reviews, etc.) handled by theme.
    }

    public function register_roles() {
        // Register bdc_merchant role.
        if (!get_role('bdc_merchant')) {
            add_role('bdc_merchant', 'BrandsDiscovery Merchant', array(
                'read'                     => true,
                'bdc_manage_own_brand'     => true,
                'bdc_manage_own_products'  => true,
                'bdc_view_basic_traffic'   => true,
            ));
        }

        // Register bdc_moderator role.
        if (!get_role('bdc_moderator')) {
            add_role('bdc_moderator', 'BrandsDiscovery Moderator', array(
                'read'                    => true,
                'bdc_review_submissions'  => true,
                'bdc_review_claims'       => true,
                'bdc_manage_brands'       => true,
            ));
        }

        // Add capabilities to editor.
        $editor = get_role('editor');
        if ($editor) {
            $editor->add_cap('bdc_manage_brands');
            $editor->add_cap('bdc_manage_categories');
            $editor->add_cap('bdc_manage_attributes');
            $editor->add_cap('bdc_manage_products');
            $editor->add_cap('bdc_review_submissions');
            $editor->add_cap('bdc_review_claims');
            $editor->add_cap('bdc_manage_seo');
            $editor->add_cap('bdc_manage_guides');
        }

        // Add capabilities to administrator.
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('bdc_manage_brands');
            $admin->add_cap('bdc_manage_categories');
            $admin->add_cap('bdc_manage_attributes');
            $admin->add_cap('bdc_manage_products');
            $admin->add_cap('bdc_review_submissions');
            $admin->add_cap('bdc_review_claims');
            $admin->add_cap('bdc_manage_seo');
            $admin->add_cap('bdc_manage_settings');
            $admin->add_cap('bdc_manage_guides');
        }
    }
}
