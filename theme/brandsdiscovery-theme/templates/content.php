<?php
/**
 * Content pages (Guides, Lists, Reviews, Comparisons, Articles).
 *
 * @package BrandsDiscovery
 */

get_header();

$uri  = $_SERVER['REQUEST_URI'];
$path = trim(parse_url($uri, PHP_URL_PATH), '/');
?>

<main class="site-main">
    <section class="bd-hero" style="padding:var(--space-xl) 0;">
        <div class="container">
            <h1 style="font-size:2rem;"><?php echo esc_html(ucfirst(strtok($path, '/'))); ?></h1>
            <p class="bd-hero-sub">Expert guides, reviews, and comparisons to help you discover the best brands.</p>
        </div>
    </section>

    <div class="container" style="padding-bottom:var(--space-3xl);">
        <div class="bd-empty-state">
            <h2>Content Coming Soon</h2>
            <p>Our editorial team is working on in-depth guides, brand reviews, and comparisons. Stay tuned!</p>
            <a href="<?php echo esc_url(home_url('/brands')); ?>" class="btn btn-primary">Browse Brands</a>
        </div>
    </div>
</main>

<?php get_footer(); ?>
