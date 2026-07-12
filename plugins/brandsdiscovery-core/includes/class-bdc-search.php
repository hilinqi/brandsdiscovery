<?php
/**
 * Search engine.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Search {

    /**
     * Search across brands, categories, products, and guides.
     *
     * @param string $query Search query.
     * @param array  $args  Optional: type (brands/categories/products/guides), per_page, page.
     * @return array Results with counts per type.
     */
    public function search($query, $args = array()) {
        $defaults = array(
            'type'     => 'all',
            'per_page' => 20,
            'page'     => 1,
        );
        $args = wp_parse_args($args, $defaults);

        $query = trim(sanitize_text_field($query));
        if (empty($query)) {
            return $this->empty_result();
        }

        $results = array(
            'brands'      => array(),
            'categories'  => array(),
            'products'    => array(),
            'total_brands'     => 0,
            'total_categories' => 0,
            'total_products'   => 0,
            'query'      => $query,
        );

        if ($args['type'] === 'all' || $args['type'] === 'brands') {
            $results['brands'] = $this->search_brands($query, $args);
            $results['total_brands'] = $this->count_brands($query);
        }

        if ($args['type'] === 'all' || $args['type'] === 'categories') {
            $results['categories'] = $this->search_categories($query, 10);
            $results['total_categories'] = $this->count_categories($query);
        }

        if ($args['type'] === 'all' || $args['type'] === 'products') {
            $results['products'] = $this->search_products($query, $args);
            $results['total_products'] = $this->count_products($query);
        }

        return $results;
    }

    /**
     * Search brands with ranking.
     *
     * Ranking priority: exact match → text relevance → category relevance
     * → published/complete profile → verified → engagement.
     */
    private function search_brands($query, $args) {
        global $wpdb;
        $per_page = intval($args['per_page']);
        $offset = (intval($args['page']) - 1) * $per_page;

        $like = BDC_DB::esc_like($query);

        // Use a scoring approach: exact name match boosted, then LIKE, then verified.
        $sql = $wpdb->prepare(
            "SELECT t.*,
                CASE
                    WHEN t.name = %s THEN 100
                    WHEN t.name LIKE %s THEN 50
                    ELSE 10
                END
                + (t.is_verified * 20)
                + (t.profile_completeness / 10)
                AS relevance
            FROM " . BDC_DB::table('brands') . " t
            WHERE t.publication_status = 'published'
              AND (t.name LIKE %s OR t.short_description LIKE %s OR t.full_description LIKE %s)
            ORDER BY relevance DESC, t.display_order ASC
            LIMIT %d OFFSET %d",
            $query,
            $like,
            $like,
            $like,
            $like,
            $per_page,
            $offset
        );

        return $wpdb->get_results($sql);
    }

    private function count_brands($query) {
        global $wpdb;
        $like = BDC_DB::esc_like($query);
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . BDC_DB::table('brands') . "
            WHERE publication_status = 'published'
            AND (name LIKE %s OR short_description LIKE %s OR full_description LIKE %s)",
            $like, $like, $like
        ));
    }

    /**
     * Search categories.
     */
    private function search_categories($query, $limit = 10) {
        global $wpdb;
        $like = BDC_DB::esc_like($query);
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . BDC_DB::table('categories') . "
            WHERE status = 'active'
            AND (name LIKE %s OR description LIKE %s OR seo_intro LIKE %s)
            LIMIT %d",
            $like, $like, $like, $limit
        ));
    }

    private function count_categories($query) {
        global $wpdb;
        $like = BDC_DB::esc_like($query);
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . BDC_DB::table('categories') . "
            WHERE status = 'active' AND (name LIKE %s OR description LIKE %s)",
            $like, $like
        ));
    }

    /**
     * Search products.
     */
    private function search_products($query, $args) {
        global $wpdb;
        $per_page = intval($args['per_page']);
        $offset = (intval($args['page']) - 1) * $per_page;
        $like = BDC_DB::esc_like($query);

        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, b.name as brand_name FROM " . BDC_DB::table('products') . " p
            INNER JOIN " . BDC_DB::table('brands') . " b ON b.id = p.brand_id AND b.publication_status = 'published'
            WHERE p.status = 'active'
            AND (p.name LIKE %s OR p.description LIKE %s)
            LIMIT %d OFFSET %d",
            $like, $like, $per_page, $offset
        ));
    }

    private function count_products($query) {
        global $wpdb;
        $like = BDC_DB::esc_like($query);
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . BDC_DB::table('products') . " p
            INNER JOIN " . BDC_DB::table('brands') . " b ON b.id = p.brand_id AND b.publication_status = 'published'
            WHERE p.status = 'active' AND (p.name LIKE %s OR p.description LIKE %s)",
            $like, $like
        ));
    }

    /**
     * Get search suggestions.
     *
     * @param string $query Partial query.
     * @param int    $limit Max suggestions.
     * @return array
     */
    public function suggestions($query, $limit = 5) {
        global $wpdb;
        $like = BDC_DB::esc_like($query);

        $brands = $wpdb->get_results($wpdb->prepare(
            "SELECT name, 'brand' as type, slug FROM " . BDC_DB::table('brands') . "
            WHERE publication_status = 'published' AND name LIKE %s
            ORDER BY is_verified DESC LIMIT %d",
            $like, $limit
        ));

        $categories = $wpdb->get_results($wpdb->prepare(
            "SELECT name, 'category' as type, slug FROM " . BDC_DB::table('categories') . "
            WHERE status = 'active' AND name LIKE %s LIMIT %d",
            $like, $limit
        ));

        return array_merge($brands, $categories);
    }

    /**
     * Get popular brands for empty state.
     */
    public function get_popular_brands($limit = 4) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT b.* FROM " . BDC_DB::table('brands') . " b
            WHERE b.publication_status = 'published'
            AND b.is_verified = 1
            ORDER BY b.display_order ASC
            LIMIT %d",
            $limit
        ));
    }

    /**
     * Get empty result structure.
     */
    private function empty_result() {
        return array(
            'brands'           => array(),
            'categories'       => array(),
            'products'         => array(),
            'total_brands'     => 0,
            'total_categories' => 0,
            'total_products'   => 0,
            'query'            => '',
        );
    }
}
