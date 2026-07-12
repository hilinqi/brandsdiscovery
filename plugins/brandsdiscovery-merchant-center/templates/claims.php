<?php
/**
 * Claims list and Claim Status templates.
 *
 * @package BrandsDiscovery_Merchant_Center
 */

$claims_model = new BDC_Claims();
$claims = $claims_model->get_by_user(get_current_user_id());
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Claims — <?php bloginfo('name'); ?></title>
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
                <span class="current">Claims</span>
                <a href="<?php echo esc_url(home_url('/merchant/settings')); ?>">Settings</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a>
            </nav>
        </div>
    </header>

    <main class="bdc-merchant-main">
        <h1>My Brand Claims</h1>

        <?php if (empty($claims)) : ?>
            <div class="bdc-merchant-empty">
                <p>You haven't submitted any claims yet.</p>
                <p>Browse <a href="<?php echo esc_url(home_url('/brands')); ?>">brands</a> and claim your ownership to manage your brand profile.</p>
            </div>
        <?php else : ?>
            <table class="bdc-merchant-table">
                <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($claims as $claim) : ?>
                        <?php
                        $brands = new BDC_Brands();
                        $brand = $brands->get($claim->brand_id);
                        ?>
                        <tr>
                            <td><?php echo $brand ? esc_html($brand->name) : 'Brand #' . $claim->brand_id; ?></td>
                            <td><?php echo esc_html($claim->company_name); ?></td>
                            <td>
                                <span class="bd-badge bd-badge-<?php echo esc_attr($claim->status); ?>">
                                    <?php echo esc_html(ucfirst($claim->status)); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html(date('M j, Y', strtotime($claim->created_at))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</div>

<?php wp_footer(); ?>
</body>
</html>
