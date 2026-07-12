<?php
/**
 * BrandsDiscovery Theme functions.
 *
 * @package BrandsDiscovery
 */

define('BDC_THEME_VERSION', '1.0.0');
define('BDC_THEME_PATH', get_template_directory());
define('BDC_THEME_URL', get_template_directory_uri());

/**
 * Theme setup.
 */
function brandsdiscovery_setup() {
    // Add theme support.
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('custom-logo');
    add_theme_support('responsive-embeds');

    // Register menus.
    register_nav_menus(array(
        'primary' => 'Primary Menu',
        'footer'  => 'Footer Menu',
    ));
}
add_action('after_setup_theme', 'brandsdiscovery_setup');

/**
 * Enqueue scripts and styles.
 */
function brandsdiscovery_enqueue_assets() {
    // Styles.
    wp_enqueue_style(
        'brandsdiscovery-style',
        BDC_THEME_URL . '/style.css',
        array(),
        BDC_THEME_VERSION
    );

    // Google Fonts (Inter).
    wp_enqueue_style(
        'brandsdiscovery-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        array(),
        null
    );

    // Main script.
    wp_enqueue_script(
        'brandsdiscovery-script',
        BDC_THEME_URL . '/assets/js/main.js',
        array(),
        BDC_THEME_VERSION,
        true
    );

    // Localize for API calls.
    wp_localize_script('brandsdiscovery-script', 'bdTheme', array(
        'apiUrl' => rest_url('bdc/v1/'),
        'nonce'  => wp_create_nonce('wp_rest'),
        'homeUrl' => home_url(),
    ));
}
add_action('wp_enqueue_scripts', 'brandsdiscovery_enqueue_assets');

/**
 * Register widget areas.
 */
function brandsdiscovery_widgets_init() {
    register_sidebar(array(
        'name'          => 'Sidebar',
        'id'            => 'sidebar',
        'before_widget' => '<div class="widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'brandsdiscovery_widgets_init');

/**
 * Include template helpers.
 */
require_once BDC_THEME_PATH . '/inc/template-functions.php';
require_once BDC_THEME_PATH . '/inc/template-tags.php';

/**
 * Route virtual pages to custom templates.
 *
 * Brands, categories, search etc. are not WordPress pages/posts —
 * data comes from BrandsDiscovery Core REST API / custom tables.
 * This filter intercepts 404s for known routes and loads the appropriate template.
 */
function brandsdiscovery_template_router($template) {
    $uri = $_SERVER['REQUEST_URI'];
    $path = trim(parse_url($uri, PHP_URL_PATH), '/');

    // Brand detail: /brands/{slug}
    if (preg_match('#^brands/([^/]+)$#', $path, $matches)) {
        set_query_var('brand_slug', $matches[1]);
        status_header(200);
        global $wp_query;
        $wp_query->is_404 = false;
        return BDC_THEME_PATH . '/templates/brand.php';
    }

    // Brand archive: /brands
    if ($path === 'brands') {
        status_header(200);
        global $wp_query;
        $wp_query->is_404 = false;
        return BDC_THEME_PATH . '/templates/brand-archive.php';
    }

    // Category page: /categories/{slug}
    if (preg_match('#^categories/([^/]+)$#', $path, $matches)) {
        set_query_var('category_slug', $matches[1]);
        status_header(200);
        global $wp_query;
        $wp_query->is_404 = false;
        return BDC_THEME_PATH . '/templates/category.php';
    }

    // Categories archive: /categories
    if ($path === 'categories') {
        status_header(200);
        global $wp_query;
        $wp_query->is_404 = false;
        return BDC_THEME_PATH . '/templates/category-archive.php';
    }

    // Search: /search
    if ($path === 'search' || strpos($path, 'search?') === 0) {
        status_header(200);
        global $wp_query;
        $wp_query->is_404 = false;
        return BDC_THEME_PATH . '/templates/search.php';
    }

    // Content pages: /guides, /lists, /reviews, /comparisons, /articles
    $content_routes = array('guides', 'lists', 'reviews', 'comparisons', 'articles');
    foreach ($content_routes as $route) {
        if ($path === $route || strpos($path, $route . '/') === 0) {
            status_header(200);
            global $wp_query;
            $wp_query->is_404 = false;
            return BDC_THEME_PATH . '/templates/content.php';
        }
    }

    // Legal/info pages
    $legal_slugs = array(
        'about', 'contact', 'faq',
        'editorial-policy', 'verification-policy', 'privacy-policy',
        'terms', 'cookie-policy', 'affiliate-disclosure',
        'advertise', 'partner',
        'submit-brand', 'request-brand', 'report',
    );
    foreach ($legal_slugs as $slug) {
        if ($path === $slug) {
            status_header(200);
            global $wp_query;
            $wp_query->is_404 = false;
            set_query_var('page_slug', $slug);
            return BDC_THEME_PATH . '/templates/static-page.php';
        }
    }

    return $template;
}
add_filter('template_include', 'brandsdiscovery_template_router', 99);
