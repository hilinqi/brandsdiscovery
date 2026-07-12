<?php
/**
 * REST API controller for Search.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_REST_Search {

    private $namespace = 'bdc/v1';

    public function register_routes() {
        // Public: search.
        register_rest_route($this->namespace, '/search', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'search'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'q'        => array('required' => true, 'type' => 'string'),
                'type'     => array('type' => 'string', 'default' => 'all'),
                'per_page' => array('type' => 'integer', 'default' => 20),
                'page'     => array('type' => 'integer', 'default' => 1),
            ),
        ));

        // Public: search suggestions.
        register_rest_route($this->namespace, '/search/suggestions', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'suggestions'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'q'     => array('required' => true, 'type' => 'string'),
                'limit' => array('type' => 'integer', 'default' => 5),
            ),
        ));
    }

    public function search($request) {
        $search = new BDC_Search();
        $results = $search->search(
            $request->get_param('q'),
            array(
                'type'     => $request->get_param('type'),
                'per_page' => $request->get_param('per_page'),
                'page'     => $request->get_param('page'),
            )
        );

        // Format brand results.
        $results['brands'] = array_map(function($brand) {
            return array(
                'id'                => (int) $brand->id,
                'name'              => $brand->name,
                'slug'              => $brand->slug,
                'logo_url'          => BDC_Media::get_url($brand->logo_id, 'small'),
                'short_description' => $brand->short_description,
                'origin_country'    => $brand->origin_country,
                'is_verified'       => (bool) $brand->is_verified,
            );
        }, $results['brands']);

        // Format category results.
        $results['categories'] = array_map(function($cat) {
            return array(
                'id'   => (int) $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            );
        }, $results['categories']);

        // Format product results.
        $results['products'] = array_map(function($product) {
            return array(
                'id'         => (int) $product->id,
                'name'       => $product->name,
                'slug'       => $product->slug,
                'brand_name' => $product->brand_name ?? '',
                'image_url'  => BDC_Media::get_url($product->image_id, 'thumbnail'),
            );
        }, $results['products']);

        return new WP_REST_Response($results, 200);
    }

    public function suggestions($request) {
        $search = new BDC_Search();
        $suggestions = $search->suggestions(
            $request->get_param('q'),
            $request->get_param('limit')
        );

        return new WP_REST_Response(array('suggestions' => $suggestions), 200);
    }
}
