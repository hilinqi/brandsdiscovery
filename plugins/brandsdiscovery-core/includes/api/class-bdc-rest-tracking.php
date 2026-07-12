<?php
/**
 * REST API controller for Visit Store Tracking.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_REST_Tracking {

    private $namespace = 'bdc/v1';

    public function register_routes() {
        // Public: record visit.
        register_rest_route($this->namespace, '/visit/(?P<brand_id>\d+)', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'record_visit'),
            'permission_callback' => '__return_true',
        ));
    }

    public function record_visit($request) {
        $brand_id = (int) $request->get_param('brand_id');
        $tracking = new BDC_Tracking();

        $result = $tracking->record_visit($brand_id);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response($result, 200);
    }
}
