<?php
/**
 * Main fallback template — also serves as blog index.
 *
 * @package BrandsDiscovery
 */

get_header();

// If front page setting is "latest posts", show homepage layout.
if (is_front_page() && is_home()) {
    include get_template_directory() . '/templates/front-page.php';
    get_footer();
    return;
}
?>

<main id="main" class="site-main">
    <div class="container" style="padding-top:var(--space-2xl);padding-bottom:var(--space-3xl);">
        <?php if (have_posts()) : ?>
            <h1 class="section-title"><?php single_post_title(); ?></h1>
            <div class="brand-grid">
                <?php
                while (have_posts()) : the_post();
                    ?>
                    <article <?php post_class('card'); ?>>
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="bd-card-img">
                                <?php the_post_thumbnail('medium'); ?>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="bd-text-muted"><?php echo get_the_date(); ?></div>
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                    <?php
                endwhile;
                ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <div style="text-align:center;padding:64px 0;">
                <h2>Nothing Found</h2>
                <p style="color:var(--color-text-secondary);">No content matched your request. Try searching or browse our categories.</p>
                <a href="<?php echo esc_url(home_url('/brands')); ?>" class="btn btn-primary" style="margin-right:8px;">Browse Brands</a>
                <a href="<?php echo esc_url(home_url('/categories')); ?>" class="btn btn-outline">View Categories</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
