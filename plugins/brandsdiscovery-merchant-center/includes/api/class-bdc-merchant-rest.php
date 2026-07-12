<?php
/**
 * REST API controller for Merchant Center.
 *
 * @package BrandsDiscovery_Merchant_Center
 */

class BDC_Merchant_REST {

    private $namespace = 'bdc/v1/merchant';

    public function register_routes() {
        // List owned brands.
        register_rest_route($this->namespace, '/brands', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'list_brands'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Get brand detail.
        register_rest_route($this->namespace, '/brands/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_brand'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Update brand.
        register_rest_route($this->namespace, '/brands/(?P<id>\d+)', array(
            'methods'             => 'PUT',
            'callback'            => array($this, 'update_brand'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // List products for brand.
        register_rest_route($this->namespace, '/brands/(?P<id>\d+)/products', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'list_products'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Submit claim.
        register_rest_route($this->namespace, '/claims', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'submit_claim'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // List claims.
        register_rest_route($this->namespace, '/claims', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'list_claims'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Traffic data.
        register_rest_route($this->namespace, '/traffic/(?P<brand_id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_traffic'),
            'permission_callback' => array($this, 'check_permission'),
        ));
    }

    public function list_brands($request) {
        $dashboard = new BDC_Merchant_Dashboard();
        $data = $dashboard->get_data(get_current_user_id());
        return new WP_REST_Response(array('brands' => $data['brands']), 200);
    }

    public function get_brand($request) {
        $merchant_brands = new BDC_Merchant_Brands();
        $brand = $merchant_brands->get_editable_brand(
            (int) $request->get_param('id'),
            get_current_user_id()
        );

        if (is_wp_error($brand)) return $brand;
        return new WP_REST_Response($brand, 200);
    }

    public function update_brand($request) {
        $merchant_brands = new BDC_Merchant_Brands();
        $result = $merchant_brands->update_brand(
            (int) $request->get_param('id'),
            get_current_user_id(),
            $request->get_params()
        );

        if (is_wp_error($result)) return $result;
        return new WP_REST_Response(array('updated' => true), 200);
    }

    public function list_products($request) {
        $products = new BDC_Products();
        $results = $products->get_by_brand((int) $request->get_param('id'));
        return new WP_REST_Response(array('products' => $results), 200);
    }

    public function submit_claim($request) {
        $claims = new BDC_Claims();
        $data = $request->get_params();
        $data['user_id'] = get_current_user_id();

        $result = $claims->submit_claim($data);

        if (is_wp_error($result)) return $result;
        return new WP_REST_Response(array('claim_id' => $result), 201);
    }

    public function list_claims($request) {
        $claims = new BDC_Claims();
        $results = $claims->get_by_user(get_current_user_id());
        return new WP_REST_Response(array('claims' => $results), 200);
    }

    public function get_traffic($request) {
        $tracking = new BDC_Tracking();
        $brand_id = (int) $request->get_param('brand_id');

        // Verify ownership.
        $brands = new BDC_Brands();
        $brand = $brands->get($brand_id);

        if (!$brand || $brand->claimed_by != get_current_user_id()) {
            return new WP_Error('forbidden', 'Permission denied.', array('status' => 403));
        }

        return new WP_REST_Response(array(
            'today' => $tracking->get_visit_count($brand_id, 'today'),
            'week'  => $tracking->get_visit_count($brand_id, 'week'),
            'month' => $tracking->get_visit_count($brand_id, 'month'),
            'total' => $tracking->get_visit_count($brand_id, 'all'),
        ), 200);
    }

    public function check_permission() {
        return is_user_logged_in() && current_user_can('bdc_manage_own_brand');
    }
}
