<?php
/**
 * Merchant Dashboard template.
 *
 * @package BrandsDiscovery_Merchant_Center
 */

$dashboard = new BDC_Merchant_Dashboard();
$data = $dashboard->get_data(get_current_user_id());
$brands = $data['brands'] ?? array();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merchant Dashboard — <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('bdc-merchant-page'); ?>>

<div class="bdc-merchant-wrapper">
    <!-- Merchant Header -->
    <header class="bdc-merchant-header">
        <div class="bdc-merchant-header-inner">
            <div class="bdc-merchant-logo">
                <a href="<?php echo esc_url(home_url()); ?>"><?php bloginfo('name'); ?></a>
                <span class="bdc-merchant-label">Merchant Center</span>
            </div>
            <nav class="bdc-merchant-nav">
                <a href="<?php echo esc_url(home_url('/merchant/dashboard')); ?>" class="active">Dashboard</a>
                <?php if (!empty($brands)) : ?>
                    <?php foreach ($brands as $b) : ?>
                        <a href="<?php echo esc_url(home_url('/merchant/brand/' . $b->id)); ?>"><?php echo esc_html($b->name); ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="<?php echo esc_url(home_url('/merchant/claims')); ?>">Claims</a>
                <a href="<?php echo esc_url(home_url('/merchant/settings')); ?>">Settings</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a>
            </nav>
        </div>
    </header>

    <main class="bdc-merchant-main">
        <h1>Dashboard</h1>

        <!-- Stats Grid -->
        <div class="bdc-merchant-stats">
            <div class="bdc-merchant-stat-card">
                <h3><?php echo esc_html($data['brand_count']); ?></h3>
                <p>Owned Brands</p>
            </div>
            <div class="bdc-merchant-stat-card">
                <h3><?php echo number_format($data['total_views']); ?></h3>
                <p>Profile Views (est.)</p>
            </div>
            <div class="bdc-merchant-stat-card">
                <h3><?php echo number_format($data['total_clicks']); ?></h3>
                <p>Visit Store Clicks</p>
            </div>
            <div class="bdc-merchant-stat-card">
                <h3><?php echo esc_html($data['pending_claims']); ?></h3>
                <p>Pending Claims</p>
            </div>
        </div>

        <!-- My Brands -->
        <section class="bdc-merchant-section">
            <h2>My Brands</h2>
            <?php if (empty($brands)) : ?>
                <div class="bdc-merchant-empty">
                    <p>You don't have any claimed brands yet.</p>
                    <a href="<?php echo esc_url(home_url('/merchant/claims')); ?>" class="btn btn-primary">Claim a Brand</a>
                </div>
            <?php else : ?>
                <div class="bdc-merchant-brand-list">
                    <?php foreach ($brands as $brand) : ?>
                        <div class="bdc-merchant-brand-card">
                            <div class="bdc-merchant-brand-info">
                                <h3>
                                    <a href="<?php echo esc_url(home_url('/merchant/brand/' . $brand->id)); ?>">
                                        <?php echo esc_html($brand->name); ?>
                                    </a>
                                </h3>
                                <span class="bd-badge bd-badge-<?php echo esc_attr($brand->publication_status); ?>">
                                    <?php echo esc_html(ucfirst($brand->publication_status)); ?>
                                </span>
                                <span>Completeness: <?php echo intval($brand->profile_completeness); ?>%</span>
                                <span>Monthly clicks: <?php echo number_format($brand->visit_count); ?></span>
                            </div>
                            <div class="bdc-merchant-brand-actions">
                                <a href="<?php echo esc_url(home_url('/merchant/brand/' . $brand->id)); ?>" class="btn btn-outline btn-sm">Edit</a>
                                <a href="<?php echo esc_url(home_url('/merchant/brand/' . $brand->id . '/products')); ?>" class="btn btn-outline btn-sm">Products</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Recommended Actions -->
        <section class="bdc-merchant-section">
            <h2>Recommended Actions</h2>
            <?php
            $has_recommendations = false;
            foreach ($brands as $brand) :
                if ($brand->profile_completeness < 50) :
                    $has_recommendations = true;
            ?>
                <div class="bdc-merchant-action-item">
                    ⚠️ Complete your profile for <strong><?php echo esc_html($brand->name); ?></strong>
                    — Add a logo, cover image, and detailed description to improve visibility.
                    <a href="<?php echo esc_url(home_url('/merchant/brand/' . $brand->id)); ?>" class="btn btn-sm btn-primary">Fix Now</a>
                </div>
            <?php
                endif;
                if (empty($brand->categories)) :
                    $has_recommendations = true;
            ?>
                <div class="bdc-merchant-action-item">
                    ⚠️ Add categories for <strong><?php echo esc_html($brand->name); ?></strong>
                    — Categories help customers find your brand.
                </div>
            <?php
                endif;
            endforeach;
            if (!$has_recommendations) :
            ?>
                <p>🎉 Your brands are looking great! No actions needed right now.</p>
            <?php endif; ?>
        </section>
    </main>
</div>

<?php wp_footer(); ?>
</body>
</html>
