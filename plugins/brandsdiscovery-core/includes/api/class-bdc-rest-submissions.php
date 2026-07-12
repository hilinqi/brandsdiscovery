<?php
/**
 * REST API controller for Submissions.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_REST_Submissions {

    private $namespace = 'bdc/v1';
    private $submissions;

    public function __construct() {
        $this->submissions = new BDC_Submissions();
    }

    public function register_routes() {
        // Public: create submission.
        register_rest_route($this->namespace, '/submissions', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'create_submission'),
            'permission_callback' => '__return_true',
        ));

        // Admin: list submissions.
        register_rest_route($this->namespace, '/admin/submissions', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'list_submissions'),
            'permission_callback' => array($this, 'admin_permission'),
        ));

        // Admin: review submission.
        register_rest_route($this->namespace, '/admin/submissions/(?P<id>\d+)', array(
            'methods'             => 'PUT',
            'callback'            => array($this, 'review_submission'),
            'permission_callback' => array($this, 'admin_permission'),
            'args'                => array(
                'status' => array('required' => true, 'type' => 'string'),
                'notes'  => array('type' => 'string', 'default' => ''),
            ),
        ));

        // Admin: create brand from submission.
        register_rest_route($this->namespace, '/admin/submissions/(?P<id>\d+)/create-brand', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'create_brand'),
            'permission_callback' => array($this, 'admin_permission'),
        ));
    }

    public function create_submission($request) {
        $data = $request->get_params();

        $result = $this->submissions->create_submission($data);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response(array(
            'id'      => $result,
            'message' => 'Submission received successfully.',
        ), 201);
    }

    public function list_submissions($request) {
        $status = $request->get_param('status') ?: '';
        $per_page = (int) $request->get_param('per_page') ?: 20;
        $page = (int) $request->get_param('page') ?: 1;

        $results = $this->submissions->get_by_status($status, $per_page, ($page - 1) * $per_page);
        $total = $this->submissions->count($status ? array('status' => $status) : array());

        $data = array();
        foreach ($results as $submission) {
            $data[] = array(
                'id'               => (int) $submission->id,
                'type'             => $submission->type,
                'status'           => $submission->status,
                'data'             => json_decode($submission->data, true),
                'normalized_domain' => $submission->normalized_domain,
                'submitter_email'  => $submission->submitter_email,
                'reviewer_notes'   => $submission->reviewer_notes,
                'created_brand_id' => $submission->created_brand_id ? (int) $submission->created_brand_id : null,
                'created_at'       => $submission->created_at,
            );
        }

        return new WP_REST_Response(array(
            'submissions' => $data,
            'total'       => $total,
            'per_page'    => $per_page,
            'page'        => $page,
            'pages'       => ceil($total / $per_page),
        ), 200);
    }

    public function review_submission($request) {
        $id = (int) $request->get_param('id');
        $status = sanitize_text_field($request->get_param('status'));
        $notes = sanitize_textarea_field($request->get_param('notes'));
        $reviewer_id = get_current_user_id();

        $result = $this->submissions->change_status($id, $status, $reviewer_id, $notes);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response(array('status' => $status), 200);
    }

    public function create_brand($request) {
        $id = (int) $request->get_param('id');

        $brand_id = $this->submissions->create_brand_from_submission($id);

        if (!$brand_id) {
            return new WP_Error('create_failed', 'Failed to create brand from submission.', array('status' => 500));
        }

        return new WP_REST_Response(array('brand_id' => $brand_id), 201);
    }

    public function admin_permission() {
        return current_user_can('bdc_review_submissions');
    }
}
