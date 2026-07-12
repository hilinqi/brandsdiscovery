<?php
/**
 * Merchant Brand management.
 *
 * @package BrandsDiscovery_Merchant_Center
 */

class BDC_Merchant_Brands {

    /**
     * Get brand data for merchant editing, filtered by field permissions.
     *
     * @param int $brand_id Brand ID.
     * @param int $user_id  Merchant user ID.
     * @return array|WP_Error Brand data or error.
     */
    public function get_editable_brand($brand_id, $user_id) {
        $brands = new BDC_Brands();
        $brand = $brands->get($brand_id);

        if (!$brand) {
            return new WP_Error('not_found', 'Brand not found.');
        }

        if ($brand->claimed_by != $user_id) {
            return new WP_Error('forbidden', 'You do not own this brand.');
        }

        if ($brand->claim_status !== BDC_Brands::CLAIM_CLAIMED) {
            return new WP_Error('not_claimed', 'Brand is not claimed.');
        }

        return $brands->get_full($brand_id);
    }

    /**
     * Update brand as merchant (only reviewable fields).
     *
     * @param int   $brand_id Brand ID.
     * @param int   $user_id  Merchant user ID.
     * @param array $data     Updated fields.
     * @return bool|WP_Error
     */
    public function update_brand($brand_id, $user_id, $data) {
        $brands = new BDC_Brands();
        $brand = $brands->get($brand_id);

        if (!$brand || $brand->claimed_by != $user_id) {
            return new WP_Error('forbidden', 'Permission denied.');
        }

        // Fields merchants can edit (may require review).
        $editable_fields = array(
            'short_description',
            'full_description',
            'shipping_regions',
            'payment_methods',
            'return_policy',
            'support_contact',
            'social_links',
        );

        $update_data = array();
        foreach ($editable_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = $data[$field];
            }
        }

        // Fields that require admin review.
        $review_fields = array(
            'short_description',
            'full_description',
        );
        $needs_review = false;
        foreach ($review_fields as $field) {
            if (isset($update_data[$field])) {
                $needs_review = true;
                break;
            }
        }

        if ($needs_review && $brand->publication_status === BDC_Brands::STATUS_PUBLISHED) {
            // Mark for pending review.
            $update_data['publication_status'] = BDC_Brands::STATUS_PENDING_REVIEW;
        }

        return $brands->update($brand_id, $update_data);
    }
}
