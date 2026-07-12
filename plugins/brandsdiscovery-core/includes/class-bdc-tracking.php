<?php
/**
 * Visit Store tracking service.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Tracking {

    private $visit_log;

    public function __construct() {
        $this->visit_log = new BDC_Visit_Log();
    }

    /**
     * Record a Visit Store click and return redirect URL.
     *
     * @param int $brand_id Brand ID.
     * @return array|WP_Error
     */
    public function record_visit($brand_id) {
        return $this->visit_log->record_visit($brand_id);
    }

    /**
     * Get visit count for a brand.
     *
     * @param int    $brand_id Brand ID.
     * @param string $period   'today', 'week', 'month', 'all'.
     * @return int
     */
    public function get_visit_count($brand_id, $period = 'all') {
        $since = '';
        switch ($period) {
            case 'today':
                $since = date('Y-m-d');
                break;
            case 'week':
                $since = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'month':
                $since = date('Y-m-d', strtotime('-30 days'));
                break;
        }
        return $this->visit_log->get_count($brand_id, $since);
    }

    /**
     * Get recent visits for dashboard.
     *
     * @param int $limit Max entries.
     * @return array
     */
    public function get_recent($limit = 10) {
        return $this->visit_log->get_recent($limit);
    }
}
