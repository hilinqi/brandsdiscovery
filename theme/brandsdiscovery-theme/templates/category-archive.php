<?php
/**
 * Categories archive — show all top-level categories.
 *
 * @package BrandsDiscovery
 */

get_header();

$data = bd_fetch_api('categories');
$categories = $data['categories'] ?? array();
?>

<main class="site-main">
    <section class="bd-hero" style="padding:var(--space-xl) 0;">
        <div class="container">
            <h1 style="font-size:2rem;">Browse Categories</h1>
            <p class="bd-hero-sub">Find brands by category. Explore products and discover new favorites.</p>
        </div>
    </section>

    <div class="container" style="padding-top:var(--space-xl);padding-bottom:var(--space-3xl);">
        <?php if (!empty($categories)) : ?>
            <div class="category-grid">
                <?php foreach ($categories as $cat) : ?>
                    <a href="<?php echo esc_url(home_url('/categories/' . $cat['slug'])); ?>" class="category-card card">
                        <div class="category-card-img">
                            <?php if (!empty($cat['hero_url'])) : ?>
                                <img src="<?php echo esc_url($cat['hero_url']); ?>" alt="" loading="lazy">
                            <?php else : ?>
                                <span style="font-size:36px;">📂</span>
                            <?php endif; ?>
                        </div>
                        <div class="category-card-body">
                            <h3 class="category-card-name"><?php echo esc_html($cat['name']); ?></h3>
                            <?php if (($cat['brand_count'] ?? 0) > 0) : ?>
                                <span class="bd-text-muted"><?php echo number_format($cat['brand_count']); ?> brands</span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="bd-empty-state">
                <h2>Categories Coming Soon</h2>
                <p>We're organizing our brand directory. Check back shortly!</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
