<?php
/**
 * Search results page.
 *
 * @package BrandsDiscovery
 */

get_header();

$query = sanitize_text_field($_GET['q'] ?? '');
$page  = max(1, intval($_GET['page'] ?? 1));
$type  = sanitize_text_field($_GET['type'] ?? 'all');

$results = array();
$total_brands = 0;
$total_products = 0;

if (!empty($query)) {
    $data = bd_fetch_api('search', array(
        'q'        => $query,
        'type'     => $type,
        'per_page' => 20,
        'page'     => $page,
    ));
    $results = $data;
    $total_brands = $data['total_brands'] ?? 0;
    $total_products = $data['total_products'] ?? 0;
}
?>

<main class="site-main">
    <section class="bd-hero" style="padding:var(--space-lg) 0;">
        <div class="container">
            <div class="search-bar" style="max-width:560px;margin:0 auto;">
                <span class="search-bar-icon" aria-hidden="true">🔍</span>
                <input type="search" id="search-input" value="<?php echo esc_attr($query); ?>"
                       placeholder="Search brands, categories..." aria-label="Search">
            </div>
        </div>
    </section>

    <div class="container" style="padding-bottom:var(--space-3xl);">
        <?php if (!empty($query)) : ?>
            <p style="color:var(--color-text-secondary);margin:var(--space-md) 0;">
                <?php echo number_format($total_brands + $total_products); ?> results for "<strong><?php echo esc_html($query); ?></strong>"
            </p>

            <?php if (!empty($results['brands'])) : ?>
                <h2 class="section-title">Brands</h2>
                <div class="brand-grid">
                    <?php foreach ($results['brands'] as $brand) : ?>
                        <?php echo bd_render_brand_card($brand); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($results['categories'])) : ?>
                <h2 class="section-title">Categories</h2>
                <div class="category-grid">
                    <?php foreach ($results['categories'] as $cat) : ?>
                        <a href="<?php echo esc_url(home_url('/categories/' . $cat['slug'])); ?>" class="card" style="padding:var(--space-md);text-align:center;">
                            <strong><?php echo esc_html($cat['name']); ?></strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($results['products'])) : ?>
                <h2 class="section-title">Products</h2>
                <div class="brand-grid">
                    <?php foreach ($results['products'] as $product) : ?>
                        <div class="card" style="padding:var(--space-md);">
                            <h4><?php echo esc_html($product['name']); ?></h4>
                            <span class="bd-text-muted"><?php echo esc_html($product['brand_name'] ?? ''); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($results['brands']) && empty($results['categories']) && empty($results['products'])) : ?>
                <div class="bd-empty-state">
                    <h2>No Results Found</h2>
                    <p>Try a different search term, or browse our categories.</p>
                    <a href="<?php echo esc_url(home_url('/categories')); ?>" class="btn btn-primary">Browse Categories</a>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="bd-empty-state" style="padding-top:var(--space-3xl);">
                <h2>Search BrandsDiscovery</h2>
                <p>Enter a brand name, category, or product to get started.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
(function() {
    var input = document.getElementById('search-input');
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                window.location.href = '<?php echo esc_url(home_url('/search')); ?>?q=' + encodeURIComponent(this.value.trim());
            }
        });
    }
})();
</script>

<?php get_footer(); ?>
