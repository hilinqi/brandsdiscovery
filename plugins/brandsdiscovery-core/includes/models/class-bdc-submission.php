<?php
/**
 * Submission model.
 *
 * @package BrandsDiscovery_Core
 */

require_once BDC_CORE_PATH . 'includes/class-bdc-model.php';

class BDC_Submissions extends BDC_Model {

    const STATUS_NEW         = 'new';
    const STATUS_REVIEWING   = 'reviewing';
    const STATUS_APPROVED    = 'approved';
    const STATUS_REJECTED    = 'rejected';
    const STATUS_NEEDS_INFO  = 'needs_info';

    const TYPE_BRAND_SUBMISSION = 'brand_submission';
    const TYPE_BRAND_REQUEST    = 'brand_request';
    const TYPE_REPORT           = 'report';

    public function __construct() {
        parent::__construct('submissions');
    }

    /**
     * Create a new submission with duplicate detection.
     *
     * @param array $data Submission data including 'type' and form fields.
     * @return int|WP_Error Submission ID or error.
     */
    public function create_submission($data) {
        $type = isset($data['type']) ? $data['type'] : '';

        if (!in_array($type, array(self::TYPE_BRAND_SUBMISSION, self::TYPE_BRAND_REQUEST, self::TYPE_REPORT), true)) {
            return new WP_Error('invalid_type', 'Invalid submission type.');
        }

        // Rate limit check.
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $recent_count = BDC_DB::count('submissions', array(
            'submitter_ip' => $ip,
        ));

        // Check submissions in the last hour (simplified).
        global $wpdb;
        $recent = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . BDC_DB::table('submissions') . "
            WHERE submitter_ip = %s AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            $ip
        ));
        if ($recent >= 3) {
            return new WP_Error('rate_limit', 'Too many submissions. Please try again later.');
        }

        // Duplicate detection for brand submissions.
        $normalized_domain = '';
        if ($type === self::TYPE_BRAND_SUBMISSION && !empty($data['website'])) {
            $normalized_domain = bdc_normalize_domain($data['website']);

            // Check existing brands.
            if (!empty($normalized_domain)) {
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM " . BDC_DB::table('brands') . "
                    WHERE website LIKE %s",
                    '%' . $wpdb->esc_like($normalized_domain) . '%'
                ));
                if ($existing > 0) {
                    // Still allow submission but flag.
                    $data['_duplicate_flag'] = true;
                }
            }

            // Check pending submissions.
            $pending = BDC_DB::count('submissions', array('normalized_domain' => $normalized_domain));
            if ($pending > 0) {
                $data['_duplicate_flag'] = true;
            }
        }

        $insert_data = array(
            'type'              => $type,
            'data'              => wp_json_encode($data),
            'normalized_domain' => $normalized_domain,
            'status'            => self::STATUS_NEW,
            'submitter_email'   => isset($data['contact_email']) ? sanitize_email($data['contact_email']) : '',
            'submitter_ip'      => $ip,
        );

        $id = $this->create($insert_data);

        if ($id) {
            do_action('bdc_submission_created', $id);

            // Send confirmation email.
            if (!empty($data['contact_email'])) {
                $message = '<p>Thank you for your submission. We have received it and will review it shortly.</p>';
                $message .= '<p>Reference ID: <strong>#' . $id . '</strong></p>';
                bdc_send_email($data['contact_email'], 'Submission Received', $message);
            }
        }

        return $id;
    }

    /**
     * Change submission status with state machine validation.
     *
     * @param int    $id         Submission ID.
     * @param string $new_status Target status.
     * @param int    $reviewer_id WP user ID of reviewer.
     * @param string $notes      Reviewer notes.
     * @return bool|WP_Error
     */
    public function change_status($id, $new_status, $reviewer_id, $notes = '') {
        $submission = $this->get($id);
        if (!$submission) {
            return new WP_Error('not_found', 'Submission not found.');
        }

        $valid_transitions = array(
            self::STATUS_NEW        => array(self::STATUS_REVIEWING),
            self::STATUS_REVIEWING  => array(self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_NEEDS_INFO),
            self::STATUS_NEEDS_INFO => array(self::STATUS_REVIEWING),
        );

        $current = $submission->status;

        if (!isset($valid_transitions[$current]) || !in_array($new_status, $valid_transitions[$current], true)) {
            return new WP_Error('invalid_transition', 'Invalid status transition.');
        }

        $update_data = array(
            'status'      => $new_status,
            'reviewer_id' => $reviewer_id,
        );

        if (!empty($notes)) {
            $update_data['reviewer_notes'] = $notes;
        }

        $this->update($id, $update_data);

        do_action('bdc_submission_status_changed', $id, $current, $new_status);
        bdc_log_activity($reviewer_id, 'submission_' . $new_status, 'submission', $id);

        // Auto-create brand on approval.
        if ($new_status === self::STATUS_APPROVED && $submission->type === self::TYPE_BRAND_SUBMISSION) {
            $this->create_brand_from_submission($id);
        }

        // Send notification email.
        if (!empty($submission->submitter_email)) {
            $status_label = ucfirst(str_replace('_', ' ', $new_status));
            $message = '<p>Your submission (#' . $id . ') has been updated.</p>';
            $message .= '<p>New status: <strong>' . esc_html($status_label) . '</strong></p>';
            if (!empty($notes)) {
                $message .= '<p>Notes: ' . esc_html($notes) . '</p>';
            }
            bdc_send_email($submission->submitter_email, 'Submission Status Update', $message);
        }

        return true;
    }

    /**
     * Create a Draft brand from an approved submission.
     *
     * @param int $submission_id Submission ID.
     * @return int|false Brand ID.
     */
    public function create_brand_from_submission($submission_id) {
        $submission = $this->get($submission_id);
        if (!$submission || $submission->type !== self::TYPE_BRAND_SUBMISSION) {
            return false;
        }

        $data = json_decode($submission->data, true);
        if (!$data) {
            return false;
        }

        $brands = new BDC_Brands();
        $brand_data = array(
            'name'             => sanitize_text_field($data['name'] ?? ''),
            'website'          => esc_url_raw($data['url'] ?? $data['website'] ?? ''),
            'origin_country'   => strtoupper(sanitize_text_field($data['country'] ?? '')),
            'short_description' => sanitize_textarea_field($data['description'] ?? ''),
            'publication_status' => BDC_Brands::STATUS_DRAFT,
            'claim_status'       => BDC_Brands::CLAIM_UNCLAIMED,
        );

        $category_id = intval($data['category'] ?? 0);
        $category_ids = $category_id > 0 ? array($category_id) : array();

        $brand_id = $brands->create_full($brand_data, $category_ids, $category_id);

        if ($brand_id) {
            $this->update($submission_id, array('created_brand_id' => $brand_id));
        }

        return $brand_id;
    }

    /**
     * Get submissions by status.
     *
     * @param string $status Status filter (empty = all).
     * @param int    $limit  Max results.
     * @param int    $offset Offset.
     * @return array
     */
    public function get_by_status($status = '', $limit = 20, $offset = 0) {
        $where = array();
        if (!empty($status)) {
            $where['status'] = $status;
        }

        return $this->query(array(
            'where'   => $where,
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'limit'   => $limit,
            'offset'  => $offset,
        ));
    }
}
