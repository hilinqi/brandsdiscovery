<?php
/**
 * Template tag functions.
 *
 * @package BrandsDiscovery
 */

/**
 * Output breadcrumbs.
 */
function bd_breadcrumbs() {
    if (is_front_page()) return;

    echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
    echo '<a href="' . esc_url(home_url()) . '">Home</a>';

    if (is_post_type_archive('brand')) {
        echo ' > Brands';
    } elseif (is_singular() || is_page()) {
        global $post;
        if ($post && $post->post_parent) {
            $parent = get_post($post->post_parent);
            echo ' > <a href="' . get_permalink($parent) . '">' . esc_html($parent->post_title) . '</a>';
        }
        echo ' > ' . esc_html(get_the_title());
    } elseif (is_search()) {
        echo ' > Search results';
    } elseif (is_404()) {
        echo ' > Page not found';
    }

    echo '</nav>';
}
