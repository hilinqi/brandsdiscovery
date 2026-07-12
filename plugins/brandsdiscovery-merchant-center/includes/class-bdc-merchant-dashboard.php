<?php
/**
 * Merchant Dashboard data layer.
 *
 * @package BrandsDiscovery_Merchant_Center
 */

class BDC_Merchant_Dashboard {

    private $brands;
    private $claims;
    private $tracking;

    public function __construct() {
        $this->brands   = new BDC_Brands();
        $this->claims   = new BDC_Claims();
        $this->tracking = new BDC_Tracking();
    }

    /**
     * Get dashboard data for a merchant.
     *
     * @param int $user_id Merchant user ID.
     * @return array Dashboard data.
     */
    public function get_data($user_id) {
        $owned_brands = $this->get_owned_brands($user_id);

        $total_views = 0;
        $total_clicks = 0;

        foreach ($owned_brands as $brand) {
            $total_clicks += $this->tracking->get_visit_count($brand->id, 'month');
            // Profile views tracking (simplified for MVP).
            $total_views += $this->tracking->get_visit_count($brand->id, 'month') * 3;
        }

        return array(
            'brands'              => $owned_brands,
            'brand_count'         => count($owned_brands),
            'total_views'         => $total_views,
            'total_clicks'        => $total_clicks,
            'pending_claims'      => $this->get_pending_claims_count($user_id),
            'recent_activity'     => $this->get_recent_activity($user_id),
        );
    }

    /**
     * Get brands owned by a merchant.
     */
    private function get_owned_brands($user_id) {
        $results = $this->brands->query(array(
            'where' => array(
                'claimed_by' => $user_id,
                'claim_status' => BDC_Brands::CLAIM_CLAIMED,
            ),
            'limit' => 20,
        ));

        foreach ($results as $brand) {
            $brand->categories = $this->brands->get_categories($brand->id);
            $brand->visit_count = $this->tracking->get_visit_count($brand->id, 'month');
        }

        return $results;
    }

    /**
     * Get pending claims count.
     */
    private function get_pending_claims_count($user_id) {
        return $this->claims->count(array(
            'user_id' => $user_id,
            'status'  => BDC_Claims::STATUS_PENDING,
        ));
    }

    /**
     * Get recent activity.
     */
    private function get_recent_activity($user_id) {
        $activity = new BDC_Activity_Log();
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT * FROM " . BDC_DB::table('activity_log') . "
            WHERE user_id = %d OR object_type = 'claim'
            ORDER BY created_at DESC LIMIT 10",
            $user_id
        );
        return $wpdb->get_results($sql);
    }
}
