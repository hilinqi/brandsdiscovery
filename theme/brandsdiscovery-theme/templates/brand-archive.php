<?php
/**
 * Brand archive — list all published brands with filters.
 *
 * @package BrandsDiscovery
 */

get_header();

$page  = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$data = bd_fetch_api('brands', array(
    'per_page' => $per_page,
    'page'     => $page,
    'orderby'  => $_GET['orderby'] ?? 'display_order',
    'order'    => $_GET['order'] ?? 'ASC',
));

$brands = $data['brands'] ?? array();
$total  = $data['total'] ?? 0;
$pages  = $data['pages'] ?? 0;
?>

<main class="site-main">
    <section class="bd-hero" style="padding:var(--space-xl) 0;">
        <div class="container">
            <h1 style="font-size:2rem;">All Brands</h1>
            <p class="bd-hero-sub">Discover independent brands from around the world.</p>
        </div>
    </section>

    <div class="container" style="padding-top:var(--space-xl);padding-bottom:var(--space-3xl);">
        <?php if (!empty($brands)) : ?>
            <p style="color:var(--color-text-secondary);margin-bottom:var(--space-lg);">
                <?php echo number_format($total); ?> brands found
            </p>
            <div class="brand-grid">
                <?php foreach ($brands as $brand) : ?>
                    <?php echo bd_render_brand_card($brand); ?>
                <?php endforeach; ?>
            </div>
            <?php bd_pagination($page, $pages); ?>
        <?php else : ?>
            <div class="bd-empty-state">
                <h2>No Brands Yet</h2>
                <p>We're curating the best independent brands. Be the first to submit one!</p>
                <a href="<?php echo esc_url(home_url('/submit-brand')); ?>" class="btn btn-primary">Submit a Brand</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
