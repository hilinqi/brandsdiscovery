<?php
/**
 * Category page — show brands in a category.
 *
 * @package BrandsDiscovery
 */

get_header();

$slug = get_query_var('category_slug', '');
$data = bd_fetch_api('categories/slug/' . urlencode($slug));
$cat  = $data ?? null;

if (!$cat || isset($cat['code'])) {
    $cat = null;
}

$page  = max(1, intval($_GET['page'] ?? 1));
$brand_data = bd_fetch_api('brands', array(
    'category_id' => $cat['id'] ?? 0,
    'per_page'    => 20,
    'page'        => $page,
));
$brands = $brand_data['brands'] ?? array();
$total  = $brand_data['total'] ?? 0;
$pages  = $brand_data['pages'] ?? 0;
?>

<main class="site-main">
    <section class="bd-hero" style="padding:var(--space-xl) 0;">
        <div class="container">
            <h1 style="font-size:2rem;"><?php echo $cat ? esc_html($cat['name']) : 'Category'; ?></h1>
            <?php if ($cat && !empty($cat['description'])) : ?>
                <p class="bd-hero-sub"><?php echo esc_html($cat['description']); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <div class="container" style="padding-top:var(--space-xl);padding-bottom:var(--space-3xl);">
        <?php if (!empty($brands)) : ?>
            <p style="color:var(--color-text-secondary);margin-bottom:var(--space-lg);">
                <?php echo number_format($total); ?> brands
            </p>
            <div class="brand-grid">
                <?php foreach ($brands as $brand) : ?>
                    <?php echo bd_render_brand_card($brand); ?>
                <?php endforeach; ?>
            </div>
            <?php bd_pagination($page, $pages); ?>
        <?php else : ?>
            <div class="bd-empty-state">
                <h2>No Brands in This Category</h2>
                <p>Check back soon — we're adding new brands every day.</p>
                <a href="<?php echo esc_url(home_url('/categories')); ?>" class="btn btn-outline">All Categories</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
