<?php
/**
 * Product model.
 *
 * @package BrandsDiscovery_Core
 */

require_once BDC_CORE_PATH . 'includes/class-bdc-model.php';

class BDC_Products extends BDC_Model {

    public function __construct() {
        parent::__construct('products');
    }

    /**
     * Get products for a brand.
     *
     * @param int $brand_id Brand ID.
     * @param int $limit    Max products.
     * @return array
     */
    public function get_by_brand($brand_id, $limit = 10) {
        return $this->query(array(
            'where'   => array(
                'brand_id' => $brand_id,
                'status'   => 'active',
            ),
            'orderby' => 'display_order',
            'order'   => 'ASC',
            'limit'   => $limit,
        ));
    }

    /**
     * Create a product.
     *
     * @param array $data Product data.
     * @return int|false
     */
    public function create_product($data) {
        if (empty($data['slug'])) {
            $data['slug'] = bdc_generate_slug($data['name'], 'products');
        }
        return $this->create($data);
    }
}
