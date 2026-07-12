<?php
/**
 * Template helper functions.
 *
 * @package BrandsDiscovery
 */

/**
 * Fetch data from BrandsDiscovery REST API.
 *
 * @param string $endpoint API endpoint path.
 * @param array  $params   Query parameters.
 * @return array|WP_Error
 */
function bd_fetch_api($endpoint, $params = array()) {
    $url = rest_url('bdc/v1/' . ltrim($endpoint, '/'));

    if (!empty($params)) {
        $url = add_query_arg($params, $url);
    }

    $response = wp_remote_get($url, array('timeout' => 10));

    if (is_wp_error($response)) {
        return array();
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        return array();
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    return is_array($data) ? $data : array();
}

/**
 * Get brand card HTML.
 *
 * @param object|array $brand Brand data.
 * @return string HTML.
 */
function bd_render_brand_card($brand) {
    $brand = (array) $brand;
    $logo = !empty($brand['logo_url']) ? $brand['logo_url'] : '';
    $name = esc_html($brand['name'] ?? '');
    $desc = esc_html($brand['short_description'] ?? '');
    $country = esc_html($brand['origin_country'] ?? '');
    $verified = !empty($brand['is_verified']);
    $url = home_url('/brands/' . ($brand['slug'] ?? ''));
    $visit_url = home_url('/go/' . ($brand['id'] ?? 0));

    ob_start();
    ?>
    <div class="brand-card card">
        <div class="brand-card-img">
            <?php if ($logo) : ?>
                <img src="<?php echo esc_url($logo); ?>" alt="<?php echo $name; ?> logo" loading="lazy">
            <?php endif; ?>
        </div>
        <div class="brand-card-body">
            <h3 class="brand-card-name">
                <a href="<?php echo esc_url($url); ?>"><?php echo $name; ?></a>
            </h3>
            <?php if ($verified) : ?>
                <span class="bd-badge bd-badge-verified">✓ Verified</span>
            <?php endif; ?>
            <?php if ($desc) : ?>
                <p class="brand-card-desc"><?php echo $desc; ?></p>
            <?php endif; ?>
            <div class="brand-card-meta">
                <?php if ($country) : ?>
                    <span><?php echo $country; ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="brand-card-footer">
            <a href="<?php echo esc_url($visit_url); ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener noreferrer">
                Visit Store →
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render pagination.
 *
 * @param int $current Current page.
 * @param int $total   Total pages.
 * @param string $base_url Base URL for links.
 */
function bd_pagination($current, $total, $base_url = '') {
    if ($total <= 1) return;

    echo '<nav class="bd-pagination" aria-label="Page navigation">';
    echo '<div class="bd-pagination-inner">';

    // Previous.
    if ($current > 1) {
        echo '<a href="' . esc_url(add_query_arg('page', $current - 1, $base_url)) . '" class="btn btn-outline btn-sm">← Previous</a>';
    }

    // Pages.
    $start = max(1, $current - 3);
    $end = min($total, $current + 3);

    if ($start > 1) {
        echo '<a href="' . esc_url(add_query_arg('page', 1, $base_url)) . '" class="btn btn-outline btn-sm">1</a>';
        if ($start > 2) echo '<span>...</span>';
    }

    for ($i = $start; $i <= $end; $i++) {
        $class = $i === $current ? 'btn-primary' : 'btn-outline';
        echo '<a href="' . esc_url(add_query_arg('page', $i, $base_url)) . '" class="btn ' . $class . ' btn-sm">' . $i . '</a>';
    }

    if ($end < $total) {
        if ($end < $total - 1) echo '<span>...</span>';
        echo '<a href="' . esc_url(add_query_arg('page', $total, $base_url)) . '" class="btn btn-outline btn-sm">' . $total . '</a>';
    }

    // Next.
    if ($current < $total) {
        echo '<a href="' . esc_url(add_query_arg('page', $current + 1, $base_url)) . '" class="btn btn-outline btn-sm">Next →</a>';
    }

    echo '</div></nav>';
}
