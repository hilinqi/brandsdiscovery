<?php
/**
 * Brand detail page template.
 *
 * @package BrandsDiscovery
 */

get_header();

$slug = get_query_var('brand_slug', '');
if (empty($slug)) {
    // Try to extract slug from URL.
    $uri = $_SERVER['REQUEST_URI'];
    if (preg_match('#/brands/([^/]+)#', $uri, $matches)) {
        $slug = $matches[1];
    }
}

$data = bd_fetch_api('brands/slug/' . urlencode($slug));
$brand = $data ?? null;

if (!$brand || isset($brand['code'])) {
    get_template_part('404');
    get_footer();
    return;
}

$related_brands = bd_fetch_api('brands', array(
    'category_id' => $brand['primary_category']['id'] ?? 0,
    'per_page'    => 4,
));
?>

<main class="site-main">
    <!-- Hero -->
    <?php if (!empty($brand['cover_url'])) : ?>
    <div class="bd-brand-cover">
        <img src="<?php echo esc_url($brand['cover_url']); ?>" alt="" class="bd-cover-img" loading="eager">
    </div>
    <?php endif; ?>

    <div class="container">
        <!-- Header -->
        <div class="bd-brand-header">
            <?php if (!empty($brand['logo_url'])) : ?>
                <img src="<?php echo esc_url($brand['logo_url']); ?>" alt="<?php echo esc_attr($brand['name']); ?> logo" class="bd-brand-logo" width="120" height="120">
            <?php endif; ?>
            <div>
                <h1><?php echo esc_html($brand['name']); ?></h1>
                <div class="bd-brand-meta-header">
                    <?php if ($brand['is_verified']) : ?>
                        <span class="bd-badge bd-badge-verified">✓ Verified</span>
                    <?php endif; ?>
                    <?php if ($brand['primary_category']) : ?>
                        <a href="<?php echo esc_url(home_url('/categories/' . $brand['primary_category']['slug'])); ?>" class="bd-badge bd-badge-category">
                            <?php echo esc_html($brand['primary_category']['name']); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($brand['origin_country']) : ?>
                        <span class="bd-badge bd-badge-country"><?php echo esc_html($brand['origin_country']); ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($brand['short_description'])) : ?>
                    <p class="bd-brand-summary"><?php echo esc_html($brand['short_description']); ?></p>
                <?php endif; ?>
                <div class="bd-brand-actions">
                    <a href="<?php echo esc_url(home_url('/go/' . $brand['id'])); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener noreferrer" id="visit-store-btn" data-brand-id="<?php echo esc_attr($brand['id']); ?>">
                        Visit Store →
                    </a>
                    <?php if ($brand['claim_status'] === 'unclaimed') : ?>
                        <a href="<?php echo esc_url(home_url('/merchant/claim/' . $brand['id'])); ?>" class="btn btn-outline btn-lg">Claim This Brand</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="bd-brand-content">
            <div class="bd-brand-main">
                <?php if (!empty($brand['full_description'])) : ?>
                <section class="bd-brand-about">
                    <h2>About <?php echo esc_html($brand['name']); ?></h2>
                    <div class="bd-brand-description">
                        <?php echo wp_kses_post($brand['full_description']); ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Representative Products -->
                <?php if (!empty($brand['products'])) : ?>
                <section class="bd-brand-products">
                    <h2>Products</h2>
                    <div class="brand-grid">
                        <?php foreach ($brand['products'] as $product) : ?>
                            <div class="card">
                                <?php if (!empty($product['image_url'])) : ?>
                                    <div class="bd-product-img">
                                        <img src="<?php echo esc_url($product['image_url']); ?>" alt="<?php echo esc_attr($product['name']); ?>" loading="lazy">
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h4><?php echo esc_html($product['name']); ?></h4>
                                    <?php if (!empty($product['price'])) : ?>
                                        <span class="bd-price"><?php echo esc_html($product['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="bd-brand-sidebar">
                <?php if (!empty($brand['shipping_regions']) || !empty($brand['payment_methods'])) : ?>
                <div class="bd-sidebar-card">
                    <?php if (!empty($brand['shipping_regions'])) : ?>
                    <h4>Shipping</h4>
                    <p><?php echo esc_html(implode(', ', $brand['shipping_regions'])); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($brand['payment_methods'])) : ?>
                    <h4>Payment Methods</h4>
                    <p><?php echo esc_html(implode(', ', $brand['payment_methods'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($brand['social_links'])) : ?>
                <div class="bd-sidebar-card">
                    <h4>Follow</h4>
                    <div class="bd-social-links">
                        <?php foreach ($brand['social_links'] as $platform => $url) : ?>
                            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm">
                                <?php echo esc_html(ucfirst($platform)); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </aside>
        </div>

        <!-- Related Brands -->
        <?php if (!empty($related_brands['brands'])) : ?>
        <section class="bd-section">
            <h2 class="section-title">Related Brands</h2>
            <div class="brand-grid">
                <?php foreach ($related_brands['brands'] as $related) : ?>
                    <?php if ($related['id'] != $brand['id']) : ?>
                        <?php echo bd_render_brand_card($related); ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Report link -->
        <div style="margin-top:var(--space-2xl);text-align:center;">
            <a href="<?php echo esc_url(home_url('/report?brand=' . $brand['id'])); ?>" class="bd-text-muted">
                Report incorrect information
            </a>
        </div>
    </div>
</main>

<script>
// Track Visit Store click.
document.getElementById('visit-store-btn').addEventListener('click', function(e) {
    e.preventDefault();
    var brandId = this.dataset.brandId;
    var url = bdTheme.apiUrl + 'visit/' + brandId;

    fetch(url, {
        method: 'POST',
        headers: { 'X-WP-Nonce': bdTheme.nonce }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.redirect_url) {
            window.open(data.redirect_url, '_blank', 'noopener,noreferrer');
        }
    })
    .catch(function() {
        window.open(this.href, '_blank', 'noopener,noreferrer');
    });
});
</script>

<?php get_footer(); ?>
