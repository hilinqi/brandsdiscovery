<?php
/**
 * Integration Tests for BrandsDiscovery MVP.
 *
 * Tests are designed to run manually inside a WordPress environment
 * with the plugins and theme activated.
 *
 * Usage: Place in WordPress root and run: php bdc-integration-tests.php
 * Or run individual test classes via WP-CLI.
 *
 * @package BrandsDiscovery_Tests
 */

if (!defined('ABSPATH')) {
    // Allow running from CLI with WordPress loaded.
    if (php_sapi_name() === 'cli') {
        // Load WordPress.
        $_SERVER['HTTP_HOST'] = 'localhost';
        require_once dirname(__FILE__) . '/wp-load.php';
    } else {
        die('This file must be run within WordPress.');
    }
}

class BDC_Integration_Tests {

    private $passed = 0;
    private $failed = 0;
    private $errors = array();

    public function __construct() {
        echo "========================================\n";
        echo "BrandsDiscovery Integration Tests v1.0.0\n";
        echo "========================================\n\n";
    }

    /**
     * Assert a condition.
     */
    private function assert($condition, $test_name, $details = '') {
        if ($condition) {
            $this->passed++;
            echo "  ✓ PASS: $test_name\n";
        } else {
            $this->failed++;
            $this->errors[] = "FAIL: $test_name" . ($details ? " — $details" : '');
            echo "  ✗ FAIL: $test_name\n";
            if ($details) echo "        $details\n";
        }
    }

    /**
     * Run all tests.
     */
    public function run_all() {
        $this->test_plugin_activation();
        $this->test_database_schema();
        $this->test_user_roles();
        $this->test_brand_crud();
        $this->test_category_tree();
        $this->test_product_crud();
        $this->test_attribute_engine();
        $this->test_brand_state_machine();
        $this->test_submission_workflow();
        $this->test_claim_workflow();
        $this->test_search();
        $this->test_visit_tracking();
        $this->test_permissions();
        $this->test_version_consistency();
        $this->test_api_endpoints();
        $this->summary();
    }

    /**
     * 1. Plugin Activation
     */
    public function test_plugin_activation() {
        echo "1. Plugin Activation\n";
        echo "   ─────────────────\n";

        $this->assert(class_exists('BDC_Core'), 'Core plugin class exists');
        $this->assert(class_exists('BDC_Brands'), 'Brands model class exists');
        $this->assert(class_exists('BDC_Categories'), 'Categories model class exists');
        $this->assert(class_exists('BDC_Products'), 'Products model class exists');
        $this->assert(class_exists('BDC_Attributes'), 'Attributes model class exists');
        $this->assert(class_exists('BDC_Submissions'), 'Submissions model class exists');
        $this->assert(class_exists('BDC_Claims'), 'Claims model class exists');
        $this->assert(class_exists('BDC_Visit_Log'), 'Visit Log model class exists');
        $this->assert(class_exists('BDC_Activity_Log'), 'Activity Log model class exists');
        $this->assert(class_exists('BDC_Search'), 'Search engine class exists');
        $this->assert(class_exists('BDC_Tracking'), 'Tracking service class exists');
        $this->assert(class_exists('BDC_Media'), 'Media abstraction class exists');
        $this->assert(class_exists('BDC_DB'), 'Database layer class exists');
        $this->assert(class_exists('BDC_Activator'), 'Activator class exists');

        // Check if Merchant Center is active.
        $merchant_active = class_exists('BDC_Merchant_REST');
        echo $merchant_active ? "  ℹ  Merchant Center plugin is active.\n" : "  ℹ  Merchant Center plugin is NOT active.\n";

        // Check if SEO Toolkit is active.
        $seo_active = class_exists('BDC_SEO_Meta');
        echo $seo_active ? "  ℹ  SEO Toolkit plugin is active.\n" : "  ℹ  SEO Toolkit plugin is NOT active.\n";

        // Check if theme is active.
        $theme = wp_get_theme();
        echo "  ℹ  Active theme: " . $theme->get('Name') . " v" . $theme->get('Version') . "\n\n";
    }

    /**
     * 2. Database Schema
     */
    public function test_database_schema() {
        echo "2. Database Schema\n";
        echo "   ───────────────\n";
        global $wpdb;

        $tables = array(
            'bdc_brands',
            'bdc_categories',
            'bdc_brand_category',
            'bdc_products',
            'bdc_attributes',
            'bdc_category_attributes',
            'bdc_brand_attribute_values',
            'bdc_submissions',
            'bdc_claims',
            'bdc_visit_log',
            'bdc_activity_log',
        );

        foreach ($tables as $table) {
            $full_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_name'") === $full_name;
            $this->assert($exists, "Table '$table' exists");
        }

        $this->assert(
            defined('BDC_DB_VERSION') && BDC_DB_VERSION === '1.0.0',
            'Database version constant is 1.0.0'
        );

        echo "\n";
    }

    /**
     * 3. User Roles
     */
    public function test_user_roles() {
        echo "3. User Roles & Capabilities\n";
        echo "   ─────────────────────────\n";

        $this->assert(
            get_role('bdc_merchant') !== null,
            'Merchant role (bdc_merchant) exists'
        );
        $this->assert(
            get_role('bdc_moderator') !== null,
            'Moderator role (bdc_moderator) exists'
        );

        $merchant = get_role('bdc_merchant');
        if ($merchant) {
            $this->assert($merchant->has_cap('bdc_manage_own_brand'), 'Merchant has bdc_manage_own_brand capability');
            $this->assert($merchant->has_cap('bdc_manage_own_products'), 'Merchant has bdc_manage_own_products capability');
            $this->assert($merchant->has_cap('bdc_view_basic_traffic'), 'Merchant has bdc_view_basic_traffic capability');
            $this->assert(!$merchant->has_cap('manage_options'), 'Merchant does NOT have manage_options');
        }

        $admin = get_role('administrator');
        if ($admin) {
            $this->assert($admin->has_cap('bdc_manage_brands'), 'Admin has bdc_manage_brands capability');
            $this->assert($admin->has_cap('bdc_manage_settings'), 'Admin has bdc_manage_settings capability');
        }

        echo "\n";
    }

    /**
     * 4. Brand CRUD
     */
    public function test_brand_crud() {
        echo "4. Brand CRUD Operations\n";
        echo "   ─────────────────────\n";

        $brands = new BDC_Brands();

        // Create.
        $brand_id = $brands->create_full(array(
            'name'              => 'Test Brand ' . uniqid(),
            'short_description' => 'A test brand for integration testing.',
            'website'           => 'https://testbrand.example.com',
            'origin_country'    => 'US',
            'publication_status' => 'draft',
            'claim_status'      => 'unclaimed',
        ), array());

        $this->assert(is_int($brand_id) && $brand_id > 0, 'Create brand returns valid ID', "Brand ID: $brand_id");

        // Read.
        $brand = $brands->get($brand_id);
        $this->assert($brand !== null, 'Read brand by ID returns data');
        $this->assert($brand->origin_country === 'US', 'Origin country stored correctly');

        // Update.
        $updated = $brands->update($brand_id, array('short_description' => 'Updated description.'));
        $this->assert($updated !== false, 'Update brand succeeds');
        $brand = $brands->get($brand_id);
        $this->assert($brand->short_description === 'Updated description.', 'Updated value returned correctly');

        // Full read.
        $full = $brands->get_full($brand_id);
        $this->assert($full !== null, 'get_full() returns extended data');
        $this->assert(isset($full->categories), 'Full data includes categories');
        $this->assert(isset($full->products), 'Full data includes products');

        // Slug generation.
        $this->assert(!empty($brand->slug), 'Slug auto-generated: ' . $brand->slug);

        // Clean up.
        $brands->delete($brand_id);
        $this->assert($brands->get($brand_id) === null, 'Delete brand succeeds (brand no longer exists)');

        echo "\n";
    }

    /**
     * 5. Category Tree
     */
    public function test_category_tree() {
        echo "5. Category Tree\n";
        echo "   ─────────────\n";

        $categories = new BDC_Categories();

        // Create parent.
        $parent_id = $categories->create_category(array(
            'name' => 'Test Parent Category',
            'status' => 'active',
        ));
        $this->assert($parent_id > 0, 'Create parent category succeeds');

        // Create child.
        $child_id = $categories->create_category(array(
            'name'      => 'Test Child Category',
            'parent_id' => $parent_id,
            'status'    => 'active',
        ));
        $this->assert($child_id > 0, 'Create child category succeeds');

        // Tree.
        $tree = $categories->get_tree();
        $this->assert(is_array($tree) && count($tree) > 0, 'get_tree() returns data');

        // Children.
        $children = $categories->get_children($parent_id);
        $this->assert(count($children) > 0, 'get_children() returns child categories');
        $this->assert(intval($children[0]->id) === $child_id, 'Child category has correct ID');

        // get_full.
        $full = $categories->get_full($parent_id);
        $this->assert(isset($full->brand_count), 'get_full() includes brand_count');

        // Clean up.
        $categories->delete($child_id);
        $categories->delete($parent_id);

        echo "\n";
    }

    /**
     * 6. Product CRUD
     */
    public function test_product_crud() {
        echo "6. Product CRUD\n";
        echo "   ────────────\n";

        $brands = new BDC_Brands();
        $brand_id = $brands->create_full(array(
            'name'              => 'Product Test Brand',
            'publication_status' => 'draft',
            'claim_status'      => 'unclaimed',
        ), array());

        $products = new BDC_Products();
        $product_id = $products->create_product(array(
            'brand_id' => $brand_id,
            'name'     => 'Test Product',
            'price'    => '$29.99',
            'status'   => 'active',
        ));
        $this->assert($product_id > 0, 'Create product returns valid ID');

        $by_brand = $products->get_by_brand($brand_id);
        $this->assert(count($by_brand) > 0, 'get_by_brand() returns products');
        $this->assert($by_brand[0]->name === 'Test Product', 'Product name matches');

        // Clean up.
        $products->delete($product_id);
        $brands->delete($brand_id);

        echo "\n";
    }

    /**
     * 7. Attribute Engine
     */
    public function test_attribute_engine() {
        echo "7. Attribute Engine\n";
        echo "   ────────────────\n";

        $attributes = new BDC_Attributes();
        $categories = new BDC_Categories();

        // Create attribute.
        $attr_id = $attributes->create_attribute(array(
            'name'       => 'Battery Life',
            'field_type' => 'text',
        ));
        $this->assert($attr_id > 0, 'Create attribute succeeds');

        // Create category and bind.
        $cat_id = $categories->create_category(array(
            'name'   => 'Attr Test Category',
            'status' => 'active',
        ));
        $categories->set_attributes($cat_id, array($attr_id));

        // Verify.
        $cat_attrs = $categories->get_attributes($cat_id);
        $this->assert(count($cat_attrs) > 0, 'Category returns bound attributes');
        $this->assert($cat_attrs[0]->name === 'Battery Life', 'Attribute name matches');

        // Set brand values.
        $brands = new BDC_Brands();
        $brand_id = $brands->create_full(array(
            'name' => 'Attribute Brand Test',
            'publication_status' => 'draft',
            'claim_status' => 'unclaimed',
        ), array());

        $attributes->set_values_for_brand($brand_id, array($attr_id => 'Up to 12 hours'));
        $values = $attributes->get_values_for_brand($brand_id);
        $this->assert(isset($values['battery-life']), 'Brand has attribute value');
        $this->assert($values['battery-life']['value'] === 'Up to 12 hours', 'Attribute value matches');

        // Clean up.
        $brands->delete($brand_id);
        $categories->delete($cat_id);
        $attributes->delete($attr_id);

        echo "\n";
    }

    /**
     * 8. Brand State Machine
     */
    public function test_brand_state_machine() {
        echo "8. Brand Publication State Machine\n";
        echo "   ───────────────────────────────\n";

        $brands = new BDC_Brands();
        $admin_id = 1; // Assume admin exists.

        $brand_id = $brands->create_full(array(
            'name'              => 'State Machine Test',
            'publication_status' => 'draft',
            'claim_status'      => 'unclaimed',
        ), array());

        // Draft → Pending Review.
        $result = $brands->change_status($brand_id, BDC_Brands::STATUS_PENDING_REVIEW, $admin_id);
        $this->assert($result === true, 'Draft → Pending Review succeeds');

        // Pending Review → Published.
        $result = $brands->change_status($brand_id, BDC_Brands::STATUS_PUBLISHED, $admin_id);
        $this->assert($result === true, 'Pending Review → Published succeeds');

        // Published → Paused.
        $result = $brands->change_status($brand_id, BDC_Brands::STATUS_PAUSED, $admin_id);
        $this->assert($result === true, 'Published → Paused succeeds');

        // Paused → Published.
        $result = $brands->change_status($brand_id, BDC_Brands::STATUS_PUBLISHED, $admin_id);
        $this->assert($result === true, 'Paused → Published succeeds');

        // Invalid transition.
        $result = $brands->change_status($brand_id, BDC_Brands::STATUS_REJECTED, $admin_id);
        $this->assert(is_wp_error($result), 'Published → Rejected should fail (invalid)');
        if (is_wp_error($result)) {
            $this->assert($result->get_error_code() === 'invalid_transition', 'Error code is invalid_transition');
        }

        // Clean up.
        $brands->delete($brand_id);

        echo "\n";
    }

    /**
     * 9. Submission Workflow
     */
    public function test_submission_workflow() {
        echo "9. Submission Workflow\n";
        echo "   ───────────────────\n";

        $submissions = new BDC_Submissions();

        // Create submission.
        $sub_id = $submissions->create_submission(array(
            'type'         => 'brand_submission',
            'name'         => 'New Brand Name',
            'website'      => 'https://newbrand.example.com',
            'category'     => '1',
            'country'      => 'US',
            'description'  => 'Description of the brand.',
            'main_products' => 'Products list',
            'contact_email' => 'submitter@example.com',
        ));

        if (is_wp_error($sub_id)) {
            $this->assert(false, 'Create submission should not error', $sub_id->get_error_message());
        } else {
            $this->assert($sub_id > 0, 'Create submission returns valid ID');

            // Read.
            $sub = $submissions->get($sub_id);
            $this->assert($sub->status === 'new', 'New submission has status "new"');
            $this->assert($sub->normalized_domain === 'newbrand.example.com', 'Domain normalized correctly');

            // New → Reviewing.
            $result = $submissions->change_status($sub_id, BDC_Submissions::STATUS_REVIEWING, 1);
            $this->assert($result === true, 'New → Reviewing succeeds');

            // Reviewing → Approved (create brand).
            $result = $submissions->change_status($sub_id, BDC_Submissions::STATUS_APPROVED, 1);
            $this->assert($result === true, 'Reviewing → Approved succeeds');

            // Check brand created.
            $sub = $submissions->get($sub_id);
            $this->assert($sub->created_brand_id > 0, 'Approved submission creates a brand (ID: ' . $sub->created_brand_id . ')');

            // Clean up.
            if ($sub->created_brand_id) {
                $brands = new BDC_Brands();
                $brands->delete($sub->created_brand_id);
            }
            $submissions->delete($sub_id);
        }

        echo "\n";
    }

    /**
     * 10. Claim Workflow
     */
    public function test_claim_workflow() {
        echo "10. Claim Workflow\n";
        echo "    ──────────────\n";

        $brands = new BDC_Brands();
        $claims = new BDC_Claims();

        // Create a brand.
        $brand_id = $brands->create_full(array(
            'name'              => 'Claimable Brand',
            'publication_status' => 'published',
            'claim_status'      => 'unclaimed',
        ), array());

        // Create a test user for claiming.
        $user_id = wp_create_user('test_merchant_' . time(), 'testpass123', 'merchant@test.example.com');
        if (is_wp_error($user_id)) {
            $this->assert(false, 'Create test user should not error', $user_id->get_error_message());
            $brands->delete($brand_id);
            echo "\n";
            return;
        }

        $user = new WP_User($user_id);
        $user->set_role('bdc_merchant');

        // Submit claim.
        $claim_id = $claims->submit_claim(array(
            'brand_id'      => $brand_id,
            'user_id'       => $user_id,
            'company_name'  => 'Test Company',
            'contact_name'  => 'John Doe',
            'contact_email' => 'john@testcompany.example.com',
        ));

        if (is_wp_error($claim_id)) {
            $this->assert(false, 'Submit claim should not error', $claim_id->get_error_message());
        } else {
            $this->assert($claim_id > 0, 'Submit claim returns valid ID');

            // Check brand claim status updated.
            $brand = $brands->get($brand_id);
            $this->assert($brand->claim_status === 'requested', 'Brand claim_status changed to requested');

            // Duplicate check.
            $dup = $claims->submit_claim(array(
                'brand_id'      => $brand_id,
                'user_id'       => $user_id,
                'company_name'  => 'Test Company',
                'contact_name'  => 'John Doe',
                'contact_email' => 'john@testcompany.example.com',
            ));
            $this->assert(is_wp_error($dup), 'Duplicate claim should be rejected');
            $this->assert($dup->get_error_code() === 'duplicate_claim', 'Error code is duplicate_claim');

            // Approve claim.
            $claims->review_claim($claim_id, BDC_Claims::STATUS_APPROVED, 1);
            $brand = $brands->get($brand_id);
            $this->assert($brand->claim_status === 'claimed', 'Brand claim_status changed to claimed after approval');

            // Clean up.
            $claims->delete($claim_id);
        }

        $brands->delete($brand_id);
        wp_delete_user($user_id);

        echo "\n";
    }

    /**
     * 11. Search
     */
    public function test_search() {
        echo "11. Search & Discovery\n";
        echo "    ──────────────────\n";

        $search = new BDC_Search();

        // Create a searchable brand.
        $brands = new BDC_Brands();
        $brand_id = $brands->create_full(array(
            'name'              => 'UniqueSearchBrand',
            'short_description' => 'A unique test brand for search testing.',
            'full_description'  => 'Long description with test keyword.',
            'publication_status' => 'published',
            'is_verified'       => 1,
        ), array());

        // Search.
        $results = $search->search('UniqueSearchBrand');
        $this->assert(isset($results['brands']), 'Search returns brands array');
        $this->assert($results['total_brands'] > 0, 'Search found the test brand');

        // Search unpublished.
        $brands->update($brand_id, array('publication_status' => 'draft'));
        $results = $search->search('UniqueSearchBrand');
        $this->assert($results['total_brands'] === 0, 'Unpublished brand excluded from search');

        // Suggestions.
        $brands->update($brand_id, array('publication_status' => 'published'));
        $suggestions = $search->suggestions('Unique');
        $this->assert(count($suggestions) > 0, 'Search suggestions return results');

        // Clean up.
        $brands->delete($brand_id);

        echo "\n";
    }

    /**
     * 12. Visit Store Tracking
     */
    public function test_visit_tracking() {
        echo "12. Visit Store Tracking\n";
        echo "    ────────────────────\n";

        $brands = new BDC_Brands();
        $tracking = new BDC_Tracking();

        // Create a published brand with website.
        $brand_id = $brands->create_full(array(
            'name'               => 'Tracking Test Brand',
            'website'            => 'https://trackingtest.example.com',
            'publication_status' => 'published',
        ), array());

        // Record visit.
        $result = $tracking->record_visit($brand_id);

        if (is_wp_error($result)) {
            $this->assert(false, 'Record visit should not error', $result->get_error_message());
        } else {
            $this->assert(isset($result['redirect_url']), 'Visit returns redirect_url');
            $this->assert($result['redirect_url'] === 'https://trackingtest.example.com', 'Redirect URL matches brand website');

            // Check count.
            $count = $tracking->get_visit_count($brand_id, 'all');
            $this->assert($count >= 1, 'Visit count is at least 1');
        }

        // Rate limit.
        for ($i = 0; $i < 6; $i++) {
            $tracking->record_visit($brand_id);
        }
        $count = $tracking->get_visit_count($brand_id, 'all');
        // Should be max ~6 (1 real + max 5 logged due to rate limit).
        $this->assert($count <= 6, 'Rate limiting prevents excessive logging');

        // Clean up.
        $brands->delete($brand_id);

        echo "\n";
    }

    /**
     * 13. Permissions
     */
    public function test_permissions() {
        echo "13. Permission Checks\n";
        echo "    ─────────────────\n";

        // Consumer cannot access admin.
        $this->assert(!current_user_can('subscriber', 'bdc_manage_brands'), 'Subscriber does not have bdc_manage_brands');

        // Admin can access admin.
        $admin = get_role('administrator');
        $this->assert($admin && $admin->has_cap('bdc_manage_brands'), 'Administrator has bdc_manage_brands');

        // Moderator exists.
        $mod = get_role('bdc_moderator');
        $this->assert($mod !== null, 'Moderator role exists');

        echo "\n";
    }

    /**
     * 14. Version Consistency
     */
    public function test_version_consistency() {
        echo "14. Version Consistency\n";
        echo "    ────────────────────\n";

        $versions = array(
            'BDC_CORE_VERSION'     => defined('BDC_CORE_VERSION') ? BDC_CORE_VERSION : null,
            'BDC_MERCHANT_VERSION' => defined('BDC_MERCHANT_VERSION') ? BDC_MERCHANT_VERSION : null,
            'BDC_SEO_VERSION'      => defined('BDC_SEO_VERSION') ? BDC_SEO_VERSION : null,
            'BDC_THEME_VERSION'    => defined('BDC_THEME_VERSION') ? BDC_THEME_VERSION : null,
            'BDC_DB_VERSION'       => defined('BDC_DB_VERSION') ? BDC_DB_VERSION : null,
        );

        foreach ($versions as $name => $ver) {
            $this->assert($ver === '1.0.0', "$name is 1.0.0", "Got: $ver");
        }

        echo "\n";
    }

    /**
     * 15. REST API Endpoints
     */
    public function test_api_endpoints() {
        echo "15. REST API Endpoint Registration\n";
        echo "    ──────────────────────────────\n";

        $rest_server = rest_get_server();
        $routes = $rest_server->get_routes();

        $endpoints = array(
            '/bdc/v1/brands',
            '/bdc/v1/categories',
            '/bdc/v1/products',
            '/bdc/v1/search',
            '/bdc/v1/search/suggestions',
            '/bdc/v1/submissions',
            '/bdc/v1/visit/(?P<brand_id>\d+)',
        );

        foreach ($endpoints as $endpoint) {
            $this->assert(
                isset($routes[$endpoint]),
                "Route '$endpoint' is registered"
            );
        }

        echo "\n";
    }

    /**
     * Print test summary.
     */
    public function summary() {
        $total = $this->passed + $this->failed;

        echo "========================================\n";
        echo "TEST RESULTS\n";
        echo "========================================\n";
        echo "Total:  $total\n";
        echo "Passed: $this->passed ✓\n";
        echo "Failed: $this->failed ✗\n";

        if ($this->failed > 0) {
            echo "\nFAILURES:\n";
            foreach ($this->errors as $error) {
                echo "  $error\n";
            }
        }

        echo "\n";

        // Exit with appropriate code.
        exit($this->failed > 0 ? 1 : 0);
    }
}

// Auto-run when executed from CLI.
if (php_sapi_name() === 'cli' && isset($argv) && basename($argv[0]) === 'bdc-integration-tests.php') {
    $tests = new BDC_Integration_Tests();
    $tests->run_all();
}
