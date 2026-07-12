<?php
/**
 * Attribute model.
 *
 * @package BrandsDiscovery_Core
 */

require_once BDC_CORE_PATH . 'includes/class-bdc-model.php';

class BDC_Attributes extends BDC_Model {

    public function __construct() {
        parent::__construct('attributes');
    }

    /**
     * Get attribute values for a brand.
     *
     * @param int $brand_id Brand ID.
     * @return array Associative array: attribute_slug => value.
     */
    public function get_values_for_brand($brand_id) {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT a.slug, a.name, a.field_type, bav.value
            FROM " . BDC_DB::table('brand_attribute_values') . " bav
            INNER JOIN " . BDC_DB::table('attributes') . " a ON a.id = bav.attribute_id
            WHERE bav.brand_id = %d",
            $brand_id
        );
        $results = $wpdb->get_results($sql);
        $values = array();

        foreach ($results as $row) {
            $values[$row->slug] = array(
                'name'  => $row->name,
                'type'  => $row->field_type,
                'value' => $row->value,
            );
        }

        return $values;
    }

    /**
     * Set attribute values for a brand.
     *
     * @param int   $brand_id Brand ID.
     * @param array $values   Associative array: attribute_id => value.
     */
    public function set_values_for_brand($brand_id, $values) {
        // Remove existing.
        BDC_DB::delete('brand_attribute_values', array('brand_id' => $brand_id));

        // Insert new.
        foreach ($values as $attr_id => $val) {
            if (!empty($val) || $val === '0') {
                BDC_DB::insert('brand_attribute_values', array(
                    'brand_id'     => $brand_id,
                    'attribute_id' => intval($attr_id),
                    'value'        => is_array($val) ? wp_json_encode($val) : $val,
                ));
            }
        }
    }

    /**
     * Create an attribute.
     *
     * @param array $data Attribute data.
     * @return int|false
     */
    public function create_attribute($data) {
        if (empty($data['slug'])) {
            $data['slug'] = bdc_generate_slug($data['name'], 'attributes');
        }
        if (isset($data['options']) && is_array($data['options'])) {
            $data['options'] = wp_json_encode($data['options']);
        }
        return $this->create($data);
    }
}
