<?php
/**
 * Admin dashboard for BrandsDiscovery Core.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'register_menus'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_init', array($this, 'block_merchant_access'));
    }

    /**
     * Register admin menu items with English + Chinese helper text.
     */
    public function register_menus() {
        // Main menu: Dashboard / 工作台.
        add_menu_page(
            'BrandsDiscovery / 品牌发现',
            'BrandsDiscovery',
            'bdc_manage_brands',
            'brandsdiscovery',
            array('BDC_Admin_Dashboard', 'render'),
            'dashicons-store',
            30
        );

        // Dashboard / 工作台.
        add_submenu_page(
            'brandsdiscovery',
            'Dashboard / 工作台',
            'Dashboard / 工作台',
            'bdc_manage_brands',
            'brandsdiscovery',
            array('BDC_Admin_Dashboard', 'render')
        );

        // Brands / 品牌.
        add_submenu_page(
            'brandsdiscovery',
            'Brands / 品牌',
            'Brands / 品牌',
            'bdc_manage_brands',
            'brandsdiscovery-brands',
            array('BDC_Admin_Brands', 'render')
        );

        // Categories / 分类.
        add_submenu_page(
            'brandsdiscovery',
            'Categories / 分类',
            'Categories / 分类',
            'bdc_manage_categories',
            'brandsdiscovery-categories',
            array('BDC_Admin_Categories', 'render')
        );

        // Products / 产品.
        add_submenu_page(
            'brandsdiscovery',
            'Products / 产品',
            'Products / 产品',
            'bdc_manage_products',
            'brandsdiscovery-products',
            array('BDC_Admin_Products', 'render')
        );

        // Claim Requests / 品牌认领申请.
        add_submenu_page(
            'brandsdiscovery',
            'Claim Requests / 品牌认领申请',
            'Claims / 认领申请',
            'bdc_review_claims',
            'brandsdiscovery-claims',
            array('BDC_Admin_Claims', 'render')
        );

        // Submissions / 提交审核.
        add_submenu_page(
            'brandsdiscovery',
            'Submissions / 提交审核',
            'Submissions / 提交审核',
            'bdc_review_submissions',
            'brandsdiscovery-submissions',
            array('BDC_Admin_Submissions', 'render')
        );
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'brandsdiscovery') === false) {
            return;
        }

        wp_enqueue_style(
            'bdc-admin',
            BDC_CORE_URL . 'assets/css/admin.css',
            array(),
            BDC_CORE_VERSION
        );

        wp_enqueue_script(
            'bdc-admin',
            BDC_CORE_URL . 'assets/js/admin.js',
            array('jquery', 'wp-api'),
            BDC_CORE_VERSION,
            true
        );

        wp_localize_script('bdc-admin', 'bdcAdmin', array(
            'apiUrl'   => rest_url('bdc/v1/'),
            'nonce'    => wp_create_nonce('wp_rest'),
            'messages' => array(
                'confirmDelete' => 'Are you sure? This action cannot be undone. / 确定要删除吗？此操作不可撤销。',
                'saved'         => 'Saved successfully. / 保存成功。',
                'error'         => 'An error occurred. / 发生错误。',
            ),
        ));
    }

    /**
     * Block merchants from accessing WordPress admin.
     */
    public function block_merchant_access() {
        if (current_user_can('bdc_manage_own_brand') && !current_user_can('bdc_manage_brands')) {
            $current_page = $_SERVER['REQUEST_URI'] ?? '';
            $allowed = array('profile.php', 'admin-ajax.php', 'admin-post.php');

            $is_allowed = false;
            foreach ($allowed as $page) {
                if (strpos($current_page, $page) !== false) {
                    $is_allowed = true;
                    break;
                }
            }

            if (!$is_allowed && is_admin() && !wp_doing_ajax()) {
                wp_redirect(home_url('/merchant/dashboard/'));
                exit;
            }
        }
    }
}
