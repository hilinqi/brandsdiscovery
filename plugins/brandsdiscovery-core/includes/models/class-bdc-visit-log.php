<?php
/**
 * Visit Log model.
 *
 * @package BrandsDiscovery_Core
 */

require_once BDC_CORE_PATH . 'includes/class-bdc-model.php';

class BDC_Visit_Log extends BDC_Model {

    public function __construct() {
        parent::__construct('visit_log');
    }

    /**
     * Record a Visit Store event.
     *
     * @param int $brand_id Brand ID.
     * @return array|WP_Error Result with redirect_url or error.
     */
    public function record_visit($brand_id) {
        $brands = new BDC_Brands();
        $brand = $brands->get($brand_id);

        if (!$brand) {
            return new WP_Error('not_found', 'Brand not found.');
        }

        if ($brand->publication_status !== BDC_Brands::STATUS_PUBLISHED) {
            return new WP_Error('not_published', 'Brand is not published.');
        }

        if (empty($brand->website)) {
            return new WP_Error('no_website', 'Brand has no website.');
        }

        // Validate URL.
        $website = esc_url_raw($brand->website);
        if (!filter_var($website, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'Invalid website URL.');
        }

        // Rate limit: max 5 clicks per IP per brand per hour.
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $hashed_ip = hash('sha256', $ip);

        global $wpdb;
        $recent_clicks = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . BDC_DB::table('visit_log') . "
            WHERE brand_id = %d AND visitor_ip = %s AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            $brand_id,
            $hashed_ip
        ));

        if ($recent_clicks >= 5) {
            // Still redirect but don't log.
            return array('redirect_url' => $website, 'logged' => false);
        }

        // Record visit.
        $this->create(array(
            'brand_id'     => $brand_id,
            'visitor_ip'   => $hashed_ip,
            'user_agent'   => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 512) : '',
            'referrer_url' => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '',
        ));

        do_action('bdc_visit_store_clicked', $brand_id, array(
            'ip'       => $hashed_ip,
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
        ));

        return array('redirect_url' => $website, 'logged' => true);
    }

    /**
     * Get visit count for a brand.
     *
     * @param int    $brand_id Brand ID.
     * @param string $since    MySQL date string (e.g., '2024-01-01').
     * @return int
     */
    public function get_count($brand_id, $since = '') {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM " . BDC_DB::table('visit_log') . " WHERE brand_id = %d",
            $brand_id
        );
        if (!empty($since)) {
            $sql .= $wpdb->prepare(" AND created_at >= %s", $since);
        }
        return (int) $wpdb->get_var($sql);
    }

    /**
     * Get recent visits for dashboard display.
     *
     * @param int $limit Max results.
     * @return array
     */
    public function get_recent($limit = 10) {
        global $wpdb;
        $sql = "SELECT vl.*, b.name as brand_name FROM " . BDC_DB::table('visit_log') . " vl
                INNER JOIN " . BDC_DB::table('brands') . " b ON b.id = vl.brand_id
                ORDER BY vl.created_at DESC LIMIT " . intval($limit);
        return $wpdb->get_results($sql);
    }
}
