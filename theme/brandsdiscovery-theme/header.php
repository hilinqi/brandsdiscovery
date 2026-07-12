<?php
/**
 * Header template.
 *
 * @package BrandsDiscovery
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" role="banner">
    <div class="container">
        <div class="site-logo">
            <a href="<?php echo esc_url(home_url()); ?>" aria-label="BrandsDiscovery Home">
                BrandsDiscovery
            </a>
        </div>

        <nav class="main-nav" aria-label="Primary navigation">
            <a href="<?php echo esc_url(home_url('/brands')); ?>">Brands</a>
            <a href="<?php echo esc_url(home_url('/categories')); ?>">Categories</a>
            <a href="<?php echo esc_url(home_url('/guides')); ?>">Guides</a>
            <a href="<?php echo esc_url(home_url('/search')); ?>" aria-label="Search">🔍</a>
            <a href="<?php echo esc_url(home_url('/submit-brand')); ?>" class="btn btn-primary btn-sm">Submit Your Brand</a>
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(home_url('/account')); ?>">Account</a>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/login')); ?>">Login</a>
            <?php endif; ?>
        </nav>

        <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
            ☰
        </button>
    </div>
</header>
