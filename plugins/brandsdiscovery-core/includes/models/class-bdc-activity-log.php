<?php
/**
 * Activity Log model.
 *
 * @package BrandsDiscovery_Core
 */

require_once BDC_CORE_PATH . 'includes/class-bdc-model.php';

class BDC_Activity_Log extends BDC_Model {

    public function __construct() {
        parent::__construct('activity_log');
    }

    /**
     * Get recent activity.
     *
     * @param int    $limit       Max entries.
     * @param string $object_type Filter by object type (optional).
     * @param int    $object_id   Filter by object ID (optional).
     * @return array
     */
    public function get_recent($limit = 50, $object_type = '', $object_id = 0) {
        $where = array();

        if (!empty($object_type)) {
            $where['object_type'] = $object_type;
        }

        if ($object_id > 0) {
            $where['object_id'] = $object_id;
        }

        return $this->query(array(
            'where'   => $where,
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'limit'   => $limit,
        ));
    }
}
