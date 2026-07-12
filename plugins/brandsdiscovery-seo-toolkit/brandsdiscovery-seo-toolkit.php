<?php
/**
 * Plugin Name: BrandsDiscovery SEO Toolkit
 * Plugin URI: https://brandsdiscovery.com
 * Description: SEO metadata, schema, sitemap integration, and internal linking for BrandsDiscovery.
 * Version: 1.0.0
 * Author: BrandsDiscovery
 * License: GPL-2.0+
 * Text Domain: brandsdiscovery-seo-toolkit
 */

if (!defined('WPINC')) {
    die;
}

define('BDC_SEO_VERSION', '1.0.0');
define('BDC_SEO_PATH', plugin_dir_path(__FILE__));
define('BDC_SEO_URL', plugin_dir_url(__FILE__));

/**
 * Initialize SEO Toolkit.
 */
function bdc_seo_init() {
    if (!class_exists('BDC_Brands')) {
        return;
    }

    // All SEO classes are defined in this file.
    // Initialize modules.
    new BDC_SEO_Meta();
    new BDC_SEO_Schema();
    new BDC_SEO_Sitemap();
    new BDC_SEO_Internal_Links();
}
add_action('plugins_loaded', 'bdc_seo_init', 20);

/**
 * SEO meta output (title, description, canonical, OG, robots).
 */
class BDC_SEO_Meta {

    public function __construct() {
        add_action('wp_head', array($this, 'output_meta'), 1);
        add_filter('pre_get_document_title', array($this, 'filter_title'), 20);
    }

    public function filter_title($title) {
        if (is_front_page()) {
            return apply_filters('bdc_seo_title', get_bloginfo('name') . ' — Discover Independent Brands', 'homepage', 0);
        }
        return $title;
    }

    public function output_meta() {
        echo '<meta name="description" content="' . esc_attr(apply_filters('bdc_seo_description', get_bloginfo('description'), 'global', 0)) . '">' . "\n";

        // Open Graph defaults.
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";

        // Robots: noindex search/account pages.
        if (is_search()) {
            echo '<meta name="robots" content="noindex, follow">' . "\n";
        }
    }
}

/**
 * Schema.org structured data output.
 */
class BDC_SEO_Schema {

    public function __construct() {
        add_action('wp_head', array($this, 'output_breadcrumb_schema'), 5);
        add_action('wp_footer', array($this, 'output_site_schema'), 5);
    }

    public function output_breadcrumb_schema() {
        if (is_front_page()) return;

        $items = apply_filters('bdc_breadcrumb_items', array(
            array('name' => 'Home', 'url' => home_url()),
        ), 'page');

        if (count($items) <= 1) return;

        $schema = array(
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => array(),
        );

        $pos = 1;
        foreach ($items as $item) {
            $schema['itemListElement'][] = array(
                '@type'    => 'ListItem',
                'position' => $pos,
                'name'     => $item['name'],
                'item'     => $item['url'],
            );
            $pos++;
        }

        echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>' . "\n";
    }

    public function output_site_schema() {
        $schema = apply_filters('bdc_seo_schema', array(
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => get_bloginfo('name'),
            'url'      => home_url(),
        ), 'site', 0);

        echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>' . "\n";
    }
}

/**
 * Sitemap integration.
 */
class BDC_SEO_Sitemap {

    public function __construct() {
        add_filter('rank_math/sitemap/entry', array($this, 'add_brand_entries'), 10, 3);
    }

    public function add_brand_entries($url, $type, $object) {
        // Add brand/category URLs to sitemap via Rank Math filter.
        // For MVP, relies on Rank Math; custom sitemap generation added post-MVP.
        return $url;
    }
}

/**
 * Internal link management.
 */
class BDC_SEO_Internal_Links {

    public function __construct() {
        add_filter('bdc_brand_data', array($this, 'add_related_links'));
    }

    public function add_related_links($data) {
        // Add related categories and guides to brand data.
        return $data;
    }
}
