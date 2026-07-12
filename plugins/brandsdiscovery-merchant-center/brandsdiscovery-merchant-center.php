<?php
/**
 * Plugin Name: BrandsDiscovery Merchant Center
 * Plugin URI: https://brandsdiscovery.com
 * Description: Merchant dashboard, brand claiming, and profile editing for BrandsDiscovery.
 * Version: 1.0.0
 * Author: BrandsDiscovery
 * License: GPL-2.0+
 * Text Domain: brandsdiscovery-merchant-center
 */

if (!defined('WPINC')) {
    die;
}

define('BDC_MERCHANT_VERSION', '1.0.0');
define('BDC_MERCHANT_PATH', plugin_dir_path(__FILE__));
define('BDC_MERCHANT_URL', plugin_dir_url(__FILE__));

/**
 * Check if Core plugin is active.
 */
function bdc_merchant_check_core() {
    if (!class_exists('BDC_Brands')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo 'BrandsDiscovery Merchant Center requires the BrandsDiscovery Core plugin to be active.';
            echo '</p></div>';
        });
        return false;
    }
    return true;
}

/**
 * Initialize Merchant Center.
 */
function bdc_merchant_init() {
    if (!bdc_merchant_check_core()) {
        return;
    }

    // Load models.
    require_once BDC_MERCHANT_PATH . 'includes/class-bdc-merchant-dashboard.php';
    require_once BDC_MERCHANT_PATH . 'includes/class-bdc-merchant-brands.php';

    // Load REST API.
    require_once BDC_MERCHANT_PATH . 'includes/api/class-bdc-merchant-rest.php';

    // Register REST routes.
    add_action('rest_api_init', function() {
        (new BDC_Merchant_REST())->register_routes();
    });

    // Rewrite rules for merchant pages.
    add_action('init', 'bdc_merchant_rewrite_rules');
    add_filter('template_include', 'bdc_merchant_template_loader');
    add_filter('query_vars', 'bdc_merchant_query_vars');

    // Enqueue merchant assets.
    add_action('wp_enqueue_scripts', 'bdc_merchant_enqueue_assets');
}
add_action('plugins_loaded', 'bdc_merchant_init');

/**
 * Register query vars for merchant pages.
 */
function bdc_merchant_query_vars($vars) {
    $vars[] = 'merchant_page';
    $vars[] = 'brand_id';
    return $vars;
}

/**
 * Add rewrite rules for merchant Center.
 */
function bdc_merchant_rewrite_rules() {
    add_rewrite_rule(
        '^merchant/(dashboard|brand|products|claims|settings|claim-status)/?$',
        'index.php?merchant_page=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^merchant/claim/([0-9]+)/?$',
        'index.php?merchant_page=claim&brand_id=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^merchant/brand/([0-9]+)/products/?$',
        'index.php?merchant_page=products&brand_id=$matches[1]',
        'top'
    );
}

/**
 * Load merchant Center templates.
 */
function bdc_merchant_template_loader($template) {
    $page = get_query_var('merchant_page');
    if (empty($page)) return $template;

    // Redirect to login if not authenticated.
    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url($_SERVER['REQUEST_URI']));
        exit;
    }

    // Check merchant role.
    if (!current_user_can('bdc_manage_own_brand')) {
        wp_die('You do not have permission to access this page. / 您没有权限访问此页面。');
    }

    $template_map = array(
        'dashboard'     => BDC_MERCHANT_PATH . 'templates/dashboard.php',
        'brand'         => BDC_MERCHANT_PATH . 'templates/brand.php',
        'products'      => BDC_MERCHANT_PATH . 'templates/products.php',
        'claims'        => BDC_MERCHANT_PATH . 'templates/claims.php',
        'settings'      => BDC_MERCHANT_PATH . 'templates/settings.php',
        'claim'         => BDC_MERCHANT_PATH . 'templates/claim.php',
        'claim-status'  => BDC_MERCHANT_PATH . 'templates/claim-status.php',
    );

    if (isset($template_map[$page]) && file_exists($template_map[$page])) {
        return $template_map[$page];
    }

    return $template;
}

/**
 * Enqueue merchant Center assets.
 */
function bdc_merchant_enqueue_assets() {
    if (!get_query_var('merchant_page')) return;

    wp_enqueue_style(
        'bdc-merchant',
        BDC_MERCHANT_URL . 'assets/css/merchant.css',
        array(),
        BDC_MERCHANT_VERSION
    );

    wp_enqueue_script(
        'bdc-merchant',
        BDC_MERCHANT_URL . 'assets/js/merchant.js',
        array('jquery'),
        BDC_MERCHANT_VERSION,
        true
    );

    wp_localize_script('bdc-merchant', 'bdcMerchant', array(
        'apiUrl' => rest_url('bdc/v1/merchant/'),
        'nonce'  => wp_create_nonce('wp_rest'),
    ));
}
