<?php
/**
 * Brand model.
 *
 * @package BrandsDiscovery_Core
 */

require_once BDC_CORE_PATH . 'includes/class-bdc-model.php';

class BDC_Brands extends BDC_Model {

    const STATUS_DRAFT           = 'draft';
    const STATUS_PENDING_REVIEW  = 'pending';
    const STATUS_PUBLISHED       = 'published';
    const STATUS_PAUSED          = 'paused';
    const STATUS_REJECTED        = 'rejected';

    const CLAIM_UNCLAIMED        = 'unclaimed';
    const CLAIM_REQUESTED        = 'requested';
    const CLAIM_CLAIMED          = 'claimed';
    const CLAIM_SUSPENDED        = 'suspended';

    /** @var BDC_Categories */
    private $categories;

    /** @var BDC_Products */
    private $products;

    /** @var BDC_Attributes */
    private $attributes;

    public function __construct() {
        parent::__construct('brands');
        $this->categories = new BDC_Categories();
        $this->products   = new BDC_Products();
        $this->attributes = new BDC_Attributes();
    }

    /**
     * Get published brands with optional filters.
     *
     * @param array $args Query args: category_id, country, verified_only, search,
     *                    orderby (name/created_at/display_order), order, per_page, page.
     * @return array
     */
    public function get_published($args = array()) {
        global $wpdb;
        $defaults = array(
            'category_id'   => 0,
            'country'       => '',
            'verified_only' => false,
            'search'        => '',
            'orderby'       => 'display_order',
            'order'         => 'ASC',
            'per_page'      => 20,
            'page'          => 1,
        );
        $args = wp_parse_args($args, $defaults);

        $where = array('publication_status' => self::STATUS_PUBLISHED);
        $joins = array();

        // Category filter.
        if ($args['category_id'] > 0) {
            $cat_table = BDC_DB::table('brand_category');
            $joins[] = $wpdb->prepare(
                "INNER JOIN $cat_table bc ON bc.brand_id = t.id AND bc.category_id = %d",
                $args['category_id']
            );
        }

        // Country filter.
        if (!empty($args['country']) && bdc_is_valid_country_code($args['country'])) {
            $where['origin_country'] = strtoupper($args['country']);
        }

        // Verified only.
        if ($args['verified_only']) {
            $where['is_verified'] = 1;
        }

        // Search.
        $search_where = '';
        if (!empty($args['search'])) {
            $search_where = $wpdb->prepare(
                "AND (t.name LIKE %s OR t.short_description LIKE %s)",
                BDC_DB::esc_like($args['search']),
                BDC_DB::esc_like($args['search'])
            );
        }

        $table = BDC_DB::table('brands');
        $join_clause = implode(' ', $joins);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        $limit = intval($args['per_page']);
        $offset = (intval($args['page']) - 1) * $limit;

        $sql = "SELECT DISTINCT t.* FROM $table t $join_clause";
        $where_parts = array();
        foreach ($where as $col => $val) {
            $where_parts[] = $wpdb->prepare("t.`$col` = %s", $val);
        }
        if (!empty($where_parts)) {
            $sql .= ' WHERE ' . implode(' AND ', $where_parts);
        }
        if (!empty($search_where)) {
            $sql .= ' ' . $search_where;
        }
        $sql .= " ORDER BY t.$orderby LIMIT $limit OFFSET $offset";

        return $wpdb->get_results($sql);
    }

    /**
     * Count published brands.
     *
     * @param array $args Same filter args as get_published().
     * @return int
     */
    public function count_published($args = array()) {
        global $wpdb;
        $defaults = array(
            'category_id'   => 0,
            'country'       => '',
            'verified_only' => false,
            'search'        => '',
        );
        $args = wp_parse_args($args, $defaults);

        $where = array('publication_status' => self::STATUS_PUBLISHED);
        $joins = array();

        if ($args['category_id'] > 0) {
            $cat_table = BDC_DB::table('brand_category');
            $joins[] = $wpdb->prepare(
                "INNER JOIN $cat_table bc ON bc.brand_id = t.id AND bc.category_id = %d",
                $args['category_id']
            );
        }
        if (!empty($args['country']) && bdc_is_valid_country_code($args['country'])) {
            $where['origin_country'] = strtoupper($args['country']);
        }
        if ($args['verified_only']) {
            $where['is_verified'] = 1;
        }

        $table = BDC_DB::table('brands');
        $join_clause = implode(' ', $joins);

        $sql = "SELECT COUNT(DISTINCT t.id) FROM $table t $join_clause";
        $where_parts = array();
        foreach ($where as $col => $val) {
            $where_parts[] = $wpdb->prepare("t.`$col` = %s", $val);
        }
        if (!empty($where_parts)) {
            $sql .= ' WHERE ' . implode(' AND ', $where_parts);
        }
        if (!empty($args['search'])) {
            $sql .= $wpdb->prepare(
                " AND (t.name LIKE %s OR t.short_description LIKE %s)",
                BDC_DB::esc_like($args['search']),
                BDC_DB::esc_like($args['search'])
            );
        }

        return (int) $wpdb->get_var($sql);
    }

    /**
     * Get full brand data with categories, products, and attributes.
     *
     * @param int $id Brand ID.
     * @return object|null
     */
    public function get_full($id) {
        $brand = $this->get($id);
        if (!$brand) {
            return null;
        }

        $brand->categories = $this->get_categories($brand->id);
        $brand->primary_category = null;

        foreach ($brand->categories as $cat) {
            if ($cat->is_primary) {
                $brand->primary_category = $cat;
                break;
            }
        }

        $brand->products   = $this->products->get_by_brand($brand->id, 4);
        $brand->attributes = $this->attributes->get_values_for_brand($brand->id);

        // Parse JSON fields.
        $brand->markets          = json_decode($brand->markets, true) ?: array();
        $brand->shipping_regions = json_decode($brand->shipping_regions, true) ?: array();
        $brand->payment_methods  = json_decode($brand->payment_methods, true) ?: array();
        $brand->social_links     = json_decode($brand->social_links, true) ?: array();

        return $brand;
    }

    /**
     * Get brand by slug with full data.
     *
     * @param string $slug Brand slug.
     * @return object|null
     */
    public function get_full_by_slug($slug) {
        $brand = $this->get_by_slug($slug);
        if (!$brand) {
            return null;
        }
        return $this->get_full($brand->id);
    }

    /**
     * Get categories associated with a brand.
     *
     * @param int $brand_id Brand ID.
     * @return array
     */
    public function get_categories($brand_id) {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT c.*, bc.is_primary FROM " . BDC_DB::table('categories') . " c
            INNER JOIN " . BDC_DB::table('brand_category') . " bc ON bc.category_id = c.id
            WHERE bc.brand_id = %d AND c.status = 'active'
            ORDER BY bc.is_primary DESC, c.display_order ASC",
            $brand_id
        );
        return $wpdb->get_results($sql);
    }

    /**
     * Set brand categories.
     *
     * @param int   $brand_id    Brand ID.
     * @param array $category_ids Array of category IDs.
     * @param int   $primary_id  Primary category ID.
     */
    public function set_categories($brand_id, $category_ids, $primary_id = 0) {
        // Remove existing.
        BDC_DB::delete('brand_category', array('brand_id' => $brand_id));

        // Insert new.
        foreach ($category_ids as $cat_id) {
            BDC_DB::insert('brand_category', array(
                'brand_id'    => $brand_id,
                'category_id' => intval($cat_id),
                'is_primary'  => ($cat_id == $primary_id) ? 1 : 0,
            ));
        }
    }

    /**
     * Create a brand with full data.
     *
     * @param array $data    Brand data.
     * @param array $category_ids Category IDs.
     * @param int   $primary_id   Primary category ID.
     * @return int|false Brand ID or false.
     */
    public function create_full($data, $category_ids = array(), $primary_id = 0) {
        // Generate slug.
        if (empty($data['slug'])) {
            $data['slug'] = bdc_generate_slug($data['name'], 'brands');
        }

        // JSON encode array fields.
        foreach (array('markets', 'shipping_regions', 'payment_methods', 'social_links') as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = wp_json_encode($data[$field]);
            }
        }

        $brand_id = $this->create($data);

        if ($brand_id && !empty($category_ids)) {
            $this->set_categories($brand_id, $category_ids, $primary_id);
        }

        if ($brand_id) {
            bdc_log_activity(get_current_user_id(), 'brand_created', 'brand', $brand_id);
        }

        return $brand_id;
    }

    /**
     * Update a brand with category sync.
     *
     * @param int   $id           Brand ID.
     * @param array $data         Brand data.
     * @param array $category_ids Category IDs (null = don't update).
     * @param int   $primary_id   Primary category ID.
     * @return int|false Rows affected.
     */
    public function update_full($id, $data, $category_ids = null, $primary_id = 0) {
        // JSON encode array fields.
        foreach (array('markets', 'shipping_regions', 'payment_methods', 'social_links') as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = wp_json_encode($data[$field]);
            }
        }

        $result = $this->update($id, $data);

        if ($category_ids !== null) {
            $this->set_categories($id, $category_ids, $primary_id);
        }

        if ($result !== false) {
            bdc_log_activity(get_current_user_id(), 'brand_updated', 'brand', $id);
        }

        return $result;
    }

    /**
     * Change publication status with state machine validation.
     *
     * @param int    $id         Brand ID.
     * @param string $new_status Target status.
     * @param int    $user_id    User making the change.
     * @return bool|WP_Error True on success, WP_Error on invalid transition.
     */
    public function change_status($id, $new_status, $user_id) {
        $brand = $this->get($id);
        if (!$brand) {
            return new WP_Error('not_found', 'Brand not found.');
        }

        $valid_transitions = array(
            self::STATUS_DRAFT          => array(self::STATUS_PENDING_REVIEW),
            self::STATUS_PENDING_REVIEW => array(self::STATUS_PUBLISHED, self::STATUS_REJECTED),
            self::STATUS_PUBLISHED      => array(self::STATUS_PAUSED, self::STATUS_DRAFT),
            self::STATUS_PAUSED         => array(self::STATUS_PUBLISHED, self::STATUS_DRAFT),
            self::STATUS_REJECTED       => array(self::STATUS_DRAFT),
        );

        $current = $brand->publication_status;

        if ($current === $new_status) {
            return true; // No change.
        }

        if (!isset($valid_transitions[$current]) || !in_array($new_status, $valid_transitions[$current], true)) {
            return new WP_Error(
                'invalid_transition',
                sprintf('Cannot transition from %s to %s.', $current, $new_status)
            );
        }

        $this->update($id, array('publication_status' => $new_status));

        $action = 'brand_' . $new_status;
        bdc_log_activity($user_id, $action, 'brand', $id, array(
            'from' => $current,
            'to'   => $new_status,
        ));

        do_action('bdc_brand_status_changed', $id, $current, $new_status);

        if ($new_status === self::STATUS_PUBLISHED) {
            do_action('bdc_brand_published', $id);
        }

        return true;
    }

    /**
     * Change claim status.
     *
     * @param int    $id         Brand ID.
     * @param string $new_status Target claim status.
     * @param int    $user_id    Claiming user ID (for 'claimed' status).
     * @return bool|WP_Error
     */
    public function change_claim_status($id, $new_status, $user_id = 0) {
        $brand = $this->get($id);
        if (!$brand) {
            return new WP_Error('not_found', 'Brand not found.');
        }

        $update = array('claim_status' => $new_status);

        if ($new_status === self::CLAIM_CLAIMED && $user_id > 0) {
            $update['claimed_by'] = $user_id;
        }

        if ($new_status === self::CLAIM_UNCLAIMED) {
            $update['claimed_by'] = null;
        }

        $this->update($id, $update);

        bdc_log_activity(get_current_user_id(), 'claim_' . $new_status, 'brand', $id, array(
            'user_id' => $user_id,
        ));

        if ($new_status === self::CLAIM_CLAIMED && $user_id > 0) {
            do_action('bdc_brand_claimed', $id, $user_id);
        }

        return true;
    }

    /**
     * Get related brands (same category, published).
     *
     * @param int $brand_id Brand ID.
     * @param int $limit    Max number.
     * @return array
     */
    public function get_related($brand_id, $limit = 4) {
        global $wpdb;

        $brand = $this->get($brand_id);
        if (!$brand) {
            return array();
        }

        $cat_table = BDC_DB::table('brand_category');
        $brand_table = BDC_DB::table('brands');

        $sql = $wpdb->prepare(
            "SELECT DISTINCT b.* FROM $brand_table b
            INNER JOIN $cat_table bc ON bc.brand_id = b.id
            INNER JOIN $cat_table bc2 ON bc2.category_id = bc.category_id AND bc2.brand_id = %d
            WHERE b.id != %d
              AND b.publication_status = %s
            ORDER BY b.display_order ASC
            LIMIT %d",
            $brand_id,
            $brand_id,
            self::STATUS_PUBLISHED,
            $limit
        );

        return $wpdb->get_results($sql);
    }

    /**
     * Get featured brands (published, verified, ordered).
     *
     * @param int $limit Max number.
     * @return array
     */
    public function get_featured($limit = 8) {
        return $this->query(array(
            'where'   => array(
                'publication_status' => self::STATUS_PUBLISHED,
                'is_verified'        => 1,
            ),
            'orderby' => 'display_order',
            'order'   => 'ASC',
            'limit'   => $limit,
        ));
    }

    /**
     * Get latest brands (published, by created_at DESC).
     *
     * @param int $limit Max number.
     * @return array
     */
    public function get_latest($limit = 8) {
        return $this->query(array(
            'where'   => array('publication_status' => self::STATUS_PUBLISHED),
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'limit'   => $limit,
        ));
    }

    /**
     * Merge two brands. Source is deleted, data merged into target.
     *
     * @param int $source_id Source brand ID (will be deleted).
     * @param int $target_id Target brand ID (data merged into).
     * @param int $user_id   Admin user ID.
     * @return bool
     */
    public function merge($source_id, $target_id, $user_id) {
        $source = $this->get($source_id);
        $target = $this->get($target_id);

        if (!$source || !$target) {
            return false;
        }

        global $wpdb;

        // Move products.
        $wpdb->update(
            BDC_DB::table('products'),
            array('brand_id' => $target_id),
            array('brand_id' => $source_id)
        );

        // Move categories (only if not already on target).
        $source_cats = $wpdb->get_results($wpdb->prepare(
            "SELECT category_id FROM " . BDC_DB::table('brand_category') . " WHERE brand_id = %d",
            $source_id
        ));
        $target_cat_ids = wp_list_pluck(
            $wpdb->get_results($wpdb->prepare(
                "SELECT category_id FROM " . BDC_DB::table('brand_category') . " WHERE brand_id = %d",
                $target_id
            )),
            'category_id'
        );

        foreach ($source_cats as $cat) {
            if (!in_array($cat->category_id, $target_cat_ids)) {
                BDC_DB::insert('brand_category', array(
                    'brand_id'    => $target_id,
                    'category_id' => $cat->category_id,
                    'is_primary'  => 0,
                ));
            }
        }

        // Move attribute values.
        $wpdb->update(
            BDC_DB::table('brand_attribute_values'),
            array('brand_id' => $target_id),
            array('brand_id' => $source_id)
        );

        // Move visit log.
        $wpdb->update(
            BDC_DB::table('visit_log'),
            array('brand_id' => $target_id),
            array('brand_id' => $source_id)
        );

        // Move claims.
        $wpdb->update(
            BDC_DB::table('claims'),
            array('brand_id' => $target_id),
            array('brand_id' => $source_id)
        );

        // Update submissions that referenced source.
        $wpdb->update(
            BDC_DB::table('submissions'),
            array('created_brand_id' => $target_id),
            array('created_brand_id' => $source_id)
        );

        // Delete source brand.
        $this->delete($source_id);

        bdc_log_activity($user_id, 'brand_merged', 'brand', $target_id, array(
            'source_id' => $source_id,
        ));

        return true;
    }

    /**
     * Recalculate profile completeness.
     *
     * @param int $id Brand ID.
     */
    public function recalculate_completeness($id) {
        $brand = $this->get($id);
        if (!$brand) {
            return;
        }

        $categories = $this->get_categories($id);
        $data = (array) $brand;
        $data['categories'] = !empty($categories);

        $score = bdc_calculate_completeness($data);
        $this->update($id, array('profile_completeness' => $score));
    }
}
