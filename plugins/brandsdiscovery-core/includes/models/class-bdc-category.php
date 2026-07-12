<?php
/**
 * Category model.
 *
 * @package BrandsDiscovery_Core
 */

require_once BDC_CORE_PATH . 'includes/class-bdc-model.php';

class BDC_Categories extends BDC_Model {

    public function __construct() {
        parent::__construct('categories');
    }

    /**
     * Get all active categories as a tree.
     *
     * @return array Hierarchical category tree.
     */
    public function get_tree() {
        $categories = $this->query(array(
            'where'   => array('status' => 'active'),
            'orderby' => 'display_order',
            'order'   => 'ASC',
            'limit'   => 100,
        ));

        return $this->build_tree($categories);
    }

    /**
     * Build a hierarchical tree from flat results.
     *
     * @param array $categories Flat array of category objects.
     * @param int   $parent_id  Current parent ID.
     * @return array
     */
    private function build_tree($categories, $parent_id = 0) {
        $tree = array();

        foreach ($categories as $cat) {
            $cat_parent = $cat->parent_id ? intval($cat->parent_id) : 0;

            if ($cat_parent === $parent_id) {
                $children = $this->build_tree($categories, $cat->id);
                if (!empty($children)) {
                    $cat->children = $children;
                }
                $tree[] = $cat;
            }
        }

        return $tree;
    }

    /**
     * Get full category with brand count.
     *
     * @param int $id Category ID.
     * @return object|null
     */
    public function get_full($id) {
        $category = $this->get($id);
        if (!$category) {
            return null;
        }

        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . BDC_DB::table('brand_category') . " bc
            INNER JOIN " . BDC_DB::table('brands') . " b ON b.id = bc.brand_id AND b.publication_status = 'published'
            WHERE bc.category_id = %d",
            $id
        ));
        $category->brand_count = (int) $count;

        return $category;
    }

    /**
     * Get by slug with full data.
     *
     * @param string $slug Category slug.
     * @return object|null
     */
    public function get_full_by_slug($slug) {
        $category = $this->get_by_slug($slug);
        if (!$category) {
            return null;
        }
        return $this->get_full($category->id);
    }

    /**
     * Get subcategories.
     *
     * @param int $parent_id Parent category ID.
     * @return array
     */
    public function get_children($parent_id) {
        return $this->query(array(
            'where'   => array(
                'parent_id' => $parent_id,
                'status'    => 'active',
            ),
            'orderby' => 'display_order',
            'order'   => 'ASC',
            'limit'   => 50,
        ));
    }

    /**
     * Get attributes assigned to a category.
     *
     * @param int $category_id Category ID.
     * @return array
     */
    public function get_attributes($category_id) {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT a.* FROM " . BDC_DB::table('attributes') . " a
            INNER JOIN " . BDC_DB::table('category_attributes') . " ca ON ca.attribute_id = a.id
            WHERE ca.category_id = %d
            ORDER BY a.display_order ASC",
            $category_id
        );
        $attributes = $wpdb->get_results($sql);

        foreach ($attributes as $attr) {
            $attr->options = json_decode($attr->options, true);
        }

        return $attributes;
    }

    /**
     * Get all ancestor categories for breadcrumb.
     *
     * @param int $category_id Category ID.
     * @return array Ancestors from root to current.
     */
    public function get_ancestors($category_id) {
        $ancestors = array();
        $current_id = $category_id;

        while ($current_id) {
            $category = $this->get($current_id);
            if (!$category) {
                break;
            }
            array_unshift($ancestors, $category);
            $current_id = $category->parent_id;
        }

        return $ancestors;
    }

    /**
     * Create a category.
     *
     * @param array $data Category data.
     * @return int|false
     */
    public function create_category($data) {
        if (empty($data['slug'])) {
            $data['slug'] = bdc_generate_slug($data['name'], 'categories');
        }

        return $this->create($data);
    }

    /**
     * Set attributes for a category.
     *
     * @param int   $category_id   Category ID.
     * @param array $attribute_ids Attribute IDs.
     */
    public function set_attributes($category_id, $attribute_ids) {
        BDC_DB::delete('category_attributes', array('category_id' => $category_id));

        foreach ($attribute_ids as $attr_id) {
            BDC_DB::insert('category_attributes', array(
                'category_id'  => $category_id,
                'attribute_id' => intval($attr_id),
            ));
        }
    }
}
