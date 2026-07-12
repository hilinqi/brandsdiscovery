<?php
/**
 * REST API controller for Categories.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_REST_Categories {

    private $namespace = 'bdc/v1';
    private $categories;

    public function __construct() {
        $this->categories = new BDC_Categories();
    }

    public function register_routes() {
        // Public: list categories (tree).
        register_rest_route($this->namespace, '/categories', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'list_categories'),
            'permission_callback' => '__return_true',
        ));

        // Public: single category.
        register_rest_route($this->namespace, '/categories/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_category'),
            'permission_callback' => '__return_true',
        ));

        // Public: category by slug.
        register_rest_route($this->namespace, '/categories/slug/(?P<slug>[a-z0-9-]+)', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_category_by_slug'),
            'permission_callback' => '__return_true',
        ));
    }

    public function list_categories() {
        $tree = $this->categories->get_tree();
        $data = $this->format_tree($tree);
        return new WP_REST_Response(array('categories' => $data), 200);
    }

    public function get_category($request) {
        $id = (int) $request->get_param('id');
        $category = $this->categories->get_full($id);

        if (!$category || $category->status !== 'active') {
            return new WP_Error('not_found', 'Category not found.', array('status' => 404));
        }

        $data = $this->format_category($category);

        // Get subcategories.
        $data['subcategories'] = array();
        $children = $this->categories->get_children($id);
        foreach ($children as $child) {
            $data['subcategories'][] = $this->format_category($child);
        }

        // Get attributes.
        $data['attributes'] = array();
        $attributes = $this->categories->get_attributes($id);
        foreach ($attributes as $attr) {
            $data['attributes'][] = array(
                'id'         => (int) $attr->id,
                'name'       => $attr->name,
                'slug'       => $attr->slug,
                'field_type' => $attr->field_type,
                'options'    => $attr->options,
            );
        }

        return new WP_REST_Response($data, 200);
    }

    public function get_category_by_slug($request) {
        $slug = sanitize_title($request->get_param('slug'));
        $category = $this->categories->get_full_by_slug($slug);

        if (!$category || $category->status !== 'active') {
            return new WP_Error('not_found', 'Category not found.', array('status' => 404));
        }

        $request->set_param('id', $category->id);
        return $this->get_category($request);
    }

    /**
     * Format category tree recursively.
     */
    private function format_tree($categories) {
        $data = array();
        foreach ($categories as $cat) {
            $item = $this->format_category($cat);
            if (isset($cat->children)) {
                $item['children'] = $this->format_tree($cat->children);
            }
            $data[] = $item;
        }
        return $data;
    }

    /**
     * Format a single category.
     */
    private function format_category($category) {
        return array(
            'id'          => (int) $category->id,
            'name'        => $category->name,
            'slug'        => $category->slug,
            'parent_id'   => $category->parent_id ? (int) $category->parent_id : null,
            'description' => $category->description,
            'hero_url'    => BDC_Media::get_url($category->hero_image_id, 'hero'),
            'seo_intro'   => $category->seo_intro,
            'brand_count' => isset($category->brand_count) ? (int) $category->brand_count : 0,
        );
    }
}
