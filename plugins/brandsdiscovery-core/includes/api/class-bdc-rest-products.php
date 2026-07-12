<?php
/**
 * REST API controller for Products.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_REST_Products {

    private $namespace = 'bdc/v1';

    public function register_routes() {
        // Public: list products.
        register_rest_route($this->namespace, '/products', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'list_products'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'brand_id'  => array('type' => 'integer', 'default' => 0),
                'per_page'  => array('type' => 'integer', 'default' => 20),
                'page'      => array('type' => 'integer', 'default' => 1),
            ),
        ));
    }

    public function list_products($request) {
        $products = new BDC_Products();
        $brand_id = (int) $request->get_param('brand_id');

        if ($brand_id > 0) {
            $results = $products->get_by_brand($brand_id, $request->get_param('per_page'));
        } else {
            $results = $products->query(array(
                'where'   => array('status' => 'active'),
                'orderby' => 'created_at',
                'order'   => 'DESC',
                'limit'   => $request->get_param('per_page'),
                'offset'  => ((int) $request->get_param('page') - 1) * $request->get_param('per_page'),
            ));
        }

        $data = array();
        foreach ($results as $product) {
            $data[] = array(
                'id'        => (int) $product->id,
                'brand_id'  => (int) $product->brand_id,
                'name'      => $product->name,
                'slug'      => $product->slug,
                'description' => $product->description,
                'image_url' => BDC_Media::get_url($product->image_id, 'card'),
                'price'     => $product->price,
            );
        }

        return new WP_REST_Response(array('products' => $data), 200);
    }
}
