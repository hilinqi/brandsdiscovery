<?php
/**
 * REST API controller for Brands.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_REST_Brands {

    private $namespace = 'bdc/v1';
    private $brands;

    public function __construct() {
        $this->brands = new BDC_Brands();
    }

    public function register_routes() {
        // Public: list brands.
        register_rest_route($this->namespace, '/brands', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'list_brands'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'category_id'   => array('type' => 'integer', 'default' => 0),
                'country'       => array('type' => 'string', 'default' => ''),
                'verified_only' => array('type' => 'boolean', 'default' => false),
                'search'        => array('type' => 'string', 'default' => ''),
                'orderby'       => array('type' => 'string', 'default' => 'display_order'),
                'order'         => array('type' => 'string', 'default' => 'ASC', 'enum' => array('ASC', 'DESC')),
                'per_page'      => array('type' => 'integer', 'default' => 20, 'minimum' => 1, 'maximum' => 100),
                'page'          => array('type' => 'integer', 'default' => 1, 'minimum' => 1),
            ),
        ));

        // Public: single brand by ID.
        register_rest_route($this->namespace, '/brands/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_brand'),
            'permission_callback' => '__return_true',
        ));

        // Public: single brand by slug.
        register_rest_route($this->namespace, '/brands/slug/(?P<slug>[a-z0-9-]+)', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_brand_by_slug'),
            'permission_callback' => '__return_true',
        ));

        // Admin: update brand.
        register_rest_route($this->namespace, '/admin/brands/(?P<id>\d+)', array(
            'methods'             => 'PUT',
            'callback'            => array($this, 'update_brand'),
            'permission_callback' => array($this, 'admin_permission'),
        ));

        // Admin: delete brand.
        register_rest_route($this->namespace, '/admin/brands/(?P<id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array($this, 'delete_brand'),
            'permission_callback' => array($this, 'admin_permission'),
        ));

        // Admin: merge brands.
        register_rest_route($this->namespace, '/admin/brands/merge', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'merge_brands'),
            'permission_callback' => array($this, 'admin_permission'),
            'args'                => array(
                'source_id' => array('required' => true, 'type' => 'integer'),
                'target_id' => array('required' => true, 'type' => 'integer'),
            ),
        ));

        // Admin: change status.
        register_rest_route($this->namespace, '/admin/brands/(?P<id>\d+)/status', array(
            'methods'             => 'PUT',
            'callback'            => array($this, 'change_status'),
            'permission_callback' => array($this, 'admin_permission'),
            'args'                => array(
                'status' => array('required' => true, 'type' => 'string'),
            ),
        ));
    }

    public function list_brands($request) {
        $args = array(
            'category_id'   => $request->get_param('category_id'),
            'country'       => $request->get_param('country'),
            'verified_only' => $request->get_param('verified_only'),
            'search'        => $request->get_param('search'),
            'orderby'       => $request->get_param('orderby'),
            'order'         => $request->get_param('order'),
            'per_page'      => $request->get_param('per_page'),
            'page'          => $request->get_param('page'),
        );

        $brands = $this->brands->get_published($args);
        $total  = $this->brands->count_published($args);

        $data = array();
        foreach ($brands as $brand) {
            $data[] = $this->format_brand($brand);
        }

        return new WP_REST_Response(array(
            'brands'    => $data,
            'total'     => $total,
            'per_page'  => $args['per_page'],
            'page'      => $args['page'],
            'pages'     => ceil($total / $args['per_page']),
        ), 200);
    }

    public function get_brand($request) {
        $id = (int) $request->get_param('id');
        $brand = $this->brands->get_full($id);

        if (!$brand || $brand->publication_status !== BDC_Brands::STATUS_PUBLISHED) {
            return new WP_Error('not_found', 'Brand not found.', array('status' => 404));
        }

        return new WP_REST_Response($this->format_brand_full($brand), 200);
    }

    public function get_brand_by_slug($request) {
        $slug = sanitize_title($request->get_param('slug'));
        $brand = $this->brands->get_full_by_slug($slug);

        if (!$brand || $brand->publication_status !== BDC_Brands::STATUS_PUBLISHED) {
            return new WP_Error('not_found', 'Brand not found.', array('status' => 404));
        }

        return new WP_REST_Response($this->format_brand_full($brand), 200);
    }

    public function update_brand($request) {
        $id = (int) $request->get_param('id');
        $params = $request->get_params();
        unset($params['id']);

        $result = $this->brands->update_full($id, $params);

        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update brand.', array('status' => 500));
        }

        $brand = $this->brands->get_full($id);
        return new WP_REST_Response($this->format_brand_full($brand), 200);
    }

    public function delete_brand($request) {
        $id = (int) $request->get_param('id');

        if (!current_user_can('administrator')) {
            return new WP_Error('forbidden', 'Only administrators can delete brands.', array('status' => 403));
        }

        $result = $this->brands->delete($id);

        if ($result === false) {
            return new WP_Error('delete_failed', 'Failed to delete brand.', array('status' => 500));
        }

        return new WP_REST_Response(array('deleted' => true), 200);
    }

    public function merge_brands($request) {
        $source_id = (int) $request->get_param('source_id');
        $target_id = (int) $request->get_param('target_id');
        $user_id   = get_current_user_id();

        $result = $this->brands->merge($source_id, $target_id, $user_id);

        if (!$result) {
            return new WP_Error('merge_failed', 'Failed to merge brands.', array('status' => 500));
        }

        return new WP_REST_Response(array('merged' => true, 'target_id' => $target_id), 200);
    }

    public function change_status($request) {
        $id     = (int) $request->get_param('id');
        $status = sanitize_text_field($request->get_param('status'));
        $user_id = get_current_user_id();

        $result = $this->brands->change_status($id, $status, $user_id);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response(array('status' => $status), 200);
    }

    /**
     * Format a brand for list response.
     */
    private function format_brand($brand) {
        return array(
            'id'                => (int) $brand->id,
            'name'              => $brand->name,
            'slug'              => $brand->slug,
            'logo_url'          => BDC_Media::get_url($brand->logo_id, 'small'),
            'cover_url'         => BDC_Media::get_url($brand->cover_id, 'medium'),
            'short_description' => $brand->short_description,
            'origin_country'    => $brand->origin_country,
            'is_verified'       => (bool) $brand->is_verified,
            'profile_completeness' => (int) $brand->profile_completeness,
            'created_at'        => $brand->created_at,
        );
    }

    /**
     * Format a brand for detail response.
     */
    private function format_brand_full($brand) {
        $data = $this->format_brand($brand);

        $data['full_description'] = $brand->full_description;
        $data['website']          = $brand->website;
        $data['markets']          = $brand->markets;
        $data['shipping_regions'] = $brand->shipping_regions;
        $data['payment_methods']  = $brand->payment_methods;
        $data['return_policy']    = $brand->return_policy;
        $data['support_contact']  = $brand->support_contact;
        $data['social_links']     = $brand->social_links;
        $data['claim_status']     = $brand->claim_status;
        $data['categories']       = array();
        $data['primary_category'] = null;
        $data['products']         = array();
        $data['attributes']       = $brand->attributes;

        if (isset($brand->categories)) {
            foreach ($brand->categories as $cat) {
                $data['categories'][] = array(
                    'id'         => (int) $cat->id,
                    'name'       => $cat->name,
                    'slug'       => $cat->slug,
                    'is_primary' => (bool) $cat->is_primary,
                );
            }
        }

        if (isset($brand->primary_category)) {
            $data['primary_category'] = array(
                'id'   => (int) $brand->primary_category->id,
                'name' => $brand->primary_category->name,
                'slug' => $brand->primary_category->slug,
            );
        }

        if (isset($brand->products)) {
            foreach ($brand->products as $product) {
                $data['products'][] = array(
                    'id'       => (int) $product->id,
                    'name'     => $product->name,
                    'slug'     => $product->slug,
                    'description' => $product->description,
                    'image_url'=> BDC_Media::get_url($product->image_id, 'card'),
                    'price'    => $product->price,
                );
            }
        }

        return $data;
    }

    /**
     * Permission check for admin endpoints.
     */
    public function admin_permission() {
        return current_user_can('bdc_manage_brands');
    }
}
