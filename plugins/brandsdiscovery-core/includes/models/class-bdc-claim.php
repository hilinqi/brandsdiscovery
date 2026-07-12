<?php
/**
 * Claim model.
 *
 * @package BrandsDiscovery_Core
 */

require_once BDC_CORE_PATH . 'includes/class-bdc-model.php';

class BDC_Claims extends BDC_Model {

    const STATUS_PENDING     = 'pending';
    const STATUS_APPROVED    = 'approved';
    const STATUS_REJECTED    = 'rejected';
    const STATUS_NEEDS_INFO  = 'needs_info';
    const STATUS_REVOKED     = 'revoked';

    public function __construct() {
        parent::__construct('claims');
    }

    /**
     * Submit a new claim.
     *
     * @param array $data Claim data: brand_id, user_id, company_name, contact_name,
     *                    contact_email, contact_phone, evidence.
     * @return int|WP_Error Claim ID or error.
     */
    public function submit_claim($data) {
        $brand_id = intval($data['brand_id'] ?? 0);
        $user_id  = intval($data['user_id'] ?? 0);

        if ($brand_id <= 0) {
            return new WP_Error('invalid_brand', 'Invalid brand ID.');
        }

        // Check brand exists and is unclaimed.
        $brands = new BDC_Brands();
        $brand = $brands->get($brand_id);

        if (!$brand) {
            return new WP_Error('brand_not_found', 'Brand not found.');
        }

        if ($brand->claim_status === BDC_Brands::CLAIM_CLAIMED) {
            return new WP_Error('already_claimed', 'This brand has already been claimed.');
        }

        // Check for existing pending claim from this user for this brand.
        $existing = $this->query(array(
            'where' => array(
                'brand_id' => $brand_id,
                'user_id'  => $user_id,
                'status'   => self::STATUS_PENDING,
            ),
            'limit' => 1,
        ));

        if (!empty($existing)) {
            return new WP_Error('duplicate_claim', 'You already have a pending claim for this brand.');
        }

        // Check if user was rejected within 7 days.
        $rejected = $this->query(array(
            'where' => array(
                'brand_id' => $brand_id,
                'user_id'  => $user_id,
                'status'   => self::STATUS_REJECTED,
            ),
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'limit'   => 1,
        ));

        if (!empty($rejected)) {
            $rejected_date = strtotime($rejected[0]->created_at);
            if ((time() - $rejected_date) < 7 * DAY_IN_SECONDS) {
                return new WP_Error('cooldown', 'Please wait 7 days before re-submitting a claim for this brand.');
            }
        }

        $insert_data = array(
            'brand_id'      => $brand_id,
            'user_id'       => $user_id,
            'company_name'  => sanitize_text_field($data['company_name'] ?? ''),
            'contact_name'  => sanitize_text_field($data['contact_name'] ?? ''),
            'contact_email' => sanitize_email($data['contact_email'] ?? ''),
            'contact_phone' => sanitize_text_field($data['contact_phone'] ?? ''),
            'evidence'      => !empty($data['evidence']) ? wp_json_encode($data['evidence']) : null,
            'status'        => self::STATUS_PENDING,
        );

        $id = $this->create($insert_data);

        if ($id) {
            // Update brand claim status.
            $brands->change_claim_status($brand_id, BDC_Brands::CLAIM_REQUESTED, $user_id);

            do_action('bdc_claim_submitted', $id);

            // Send confirmation email.
            $message = '<p>Your brand claim request has been submitted and is pending review.</p>';
            $message .= '<p>We will review your claim and get back to you within 3–5 business days.</p>';
            bdc_send_email($data['contact_email'], 'Claim Submitted', $message);
        }

        return $id;
    }

    /**
     * Review a claim (approve/reject/request-info).
     *
     * @param int    $id          Claim ID.
     * @param string $new_status  Target status.
     * @param int    $reviewer_id Reviewer user ID.
     * @param string $notes       Reviewer notes.
     * @return bool|WP_Error
     */
    public function review_claim($id, $new_status, $reviewer_id, $notes = '') {
        $claim = $this->get($id);
        if (!$claim) {
            return new WP_Error('not_found', 'Claim not found.');
        }

        if ($claim->status !== self::STATUS_PENDING && $claim->status !== self::STATUS_NEEDS_INFO) {
            return new WP_Error('invalid_status', 'This claim can no longer be reviewed.');
        }

        $update_data = array(
            'status'      => $new_status,
            'reviewer_id' => $reviewer_id,
        );

        if (!empty($notes)) {
            $update_data['reviewer_notes'] = $notes;
        }

        $this->update($id, $update_data);

        bdc_log_activity($reviewer_id, 'claim_' . $new_status, 'claim', $id);
        do_action('bdc_claim_status_changed', $id, $claim->status, $new_status);

        // Update brand claim status.
        $brands = new BDC_Brands();

        switch ($new_status) {
            case self::STATUS_APPROVED:
                $brands->change_claim_status($claim->brand_id, BDC_Brands::CLAIM_CLAIMED, $claim->user_id);
                break;
            case self::STATUS_REJECTED:
            case self::STATUS_REVOKED:
                $brands->change_claim_status($claim->brand_id, BDC_Brands::CLAIM_UNCLAIMED);
                break;
            case self::STATUS_NEEDS_INFO:
                // Brand claim status stays as 'requested'.
                break;
        }

        // Send notification email.
        if (!empty($claim->contact_email)) {
            $status_label = ucfirst(str_replace('_', ' ', $new_status));
            $message = '<p>Your brand claim for brand #' . $claim->brand_id . ' has been updated.</p>';
            $message .= '<p>New status: <strong>' . esc_html($status_label) . '</strong></p>';
            if (!empty($notes)) {
                $message .= '<p>Notes: ' . esc_html($notes) . '</p>';
            }
            bdc_send_email($claim->contact_email, 'Claim Status Update', $message);
        }

        return true;
    }

    /**
     * Get claims by brand.
     *
     * @param int $brand_id Brand ID.
     * @return array
     */
    public function get_by_brand($brand_id) {
        return $this->query(array(
            'where'   => array('brand_id' => $brand_id),
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'limit'   => 50,
        ));
    }

    /**
     * Get claims by user.
     *
     * @param int $user_id User ID.
     * @return array
     */
    public function get_by_user($user_id) {
        return $this->query(array(
            'where'   => array('user_id' => $user_id),
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'limit'   => 50,
        ));
    }
}
