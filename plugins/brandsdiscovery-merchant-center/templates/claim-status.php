<?php
/**
 * Claim Status and Settings templates (shared).
 *
 * @package BrandsDiscovery_Merchant_Center
 */

$page = get_query_var('merchant_page', 'claim-status');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page === 'settings' ? 'Account Settings' : 'Claim Status'; ?> — <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('bdc-merchant-page'); ?>>

<div class="bdc-merchant-wrapper">
    <header class="bdc-merchant-header">
        <div class="bdc-merchant-header-inner">
            <div class="bdc-merchant-logo">
                <a href="<?php echo esc_url(home_url()); ?>"><?php bloginfo('name'); ?></a>
                <span class="bdc-merchant-label">Merchant Center</span>
            </div>
            <nav class="bdc-merchant-nav">
                <a href="<?php echo esc_url(home_url('/merchant/dashboard')); ?>">Dashboard</a>
                <a href="<?php echo esc_url(home_url('/merchant/claims')); ?>">Claims</a>
                <a href="<?php echo esc_url(home_url('/merchant/settings')); ?>">Settings</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a>
            </nav>
        </div>
    </header>

    <main class="bdc-merchant-main" style="max-width:640px;">
        <?php if ($page === 'settings') : ?>
            <h1>Account Settings</h1>
            <?php $user = wp_get_current_user(); ?>
            <div class="bdc-merchant-section">
                <h3>Profile Information</h3>
                <table class="bdc-merchant-table">
                    <tr><td><strong>Username</strong></td><td><?php echo esc_html($user->user_login); ?></td></tr>
                    <tr><td><strong>Email</strong></td><td><?php echo esc_html($user->user_email); ?></td></tr>
                    <tr><td><strong>Display Name</strong></td><td><?php echo esc_html($user->display_name); ?></td></tr>
                    <tr><td><strong>Role</strong></td><td>Merchant</td></tr>
                    <tr><td><strong>Joined</strong></td><td><?php echo esc_html(date('M j, Y', strtotime($user->user_registered))); ?></td></tr>
                </table>
                <p style="margin-top:12px;">
                    <a href="<?php echo esc_url(admin_url('profile.php')); ?>" class="btn btn-outline btn-sm">Edit Profile in WordPress</a>
                </p>
            </div>

            <div class="bdc-merchant-section">
                <h3>Notification Preferences</h3>
                <p class="bd-text-muted">Email notifications are sent to <strong><?php echo esc_html($user->user_email); ?></strong> for claim status updates, submission reviews, and important account notices.</p>
            </div>

        <?php else : ?>
            <h1>Claim Status</h1>
            <?php
            $claims_model = new BDC_Claims();
            $claims = $claims_model->get_by_user(get_current_user_id());
            ?>
            <?php if (empty($claims)) : ?>
                <p>No claims submitted. <a href="<?php echo esc_url(home_url('/brands')); ?>">Browse brands to claim</a>.</p>
            <?php else : ?>
                <?php foreach ($claims as $claim) : ?>
                    <?php $brands_obj = new BDC_Brands(); $brand = $brands_obj->get($claim->brand_id); ?>
                    <div class="bdc-merchant-section">
                        <h4><?php echo $brand ? esc_html($brand->name) : 'Brand #' . $claim->brand_id; ?></h4>
                        <p><strong>Status:</strong>
                            <span class="bd-badge bd-badge-<?php echo esc_attr($claim->status); ?>"><?php echo esc_html(ucfirst($claim->status)); ?></span>
                        </p>
                        <p><strong>Submitted:</strong> <?php echo esc_html(date('M j, Y', strtotime($claim->created_at))); ?></p>
                        <?php if (!empty($claim->reviewer_notes)) : ?>
                            <p><strong>Reviewer Notes:</strong> <?php echo esc_html($claim->reviewer_notes); ?></p>
                        <?php endif; ?>
                        <?php if ($claim->status === 'rejected') : ?>
                            <p style="color:#DC2626;">Your claim was rejected. You may re-submit after 7 days.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

<?php wp_footer(); ?>
</body>
</html>
