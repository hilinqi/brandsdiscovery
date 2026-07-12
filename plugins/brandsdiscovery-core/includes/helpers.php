<?php
/**
 * Helper functions.
 *
 * @package BrandsDiscovery_Core
 */

/**
 * Normalize a URL to extract its domain for duplicate detection.
 *
 * @param string $url URL to normalize.
 * @return string Normalized domain (e.g., "example.com").
 */
function bdc_normalize_domain($url) {
    $url = trim($url);
    if (empty($url)) {
        return '';
    }

    // Add protocol if missing.
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    $parsed = wp_parse_url($url);
    $host = isset($parsed['host']) ? $parsed['host'] : '';

    // Strip www. prefix.
    $host = preg_replace('#^www\.#i', '', $host);

    return strtolower($host);
}

/**
 * Generate a unique slug.
 *
 * @param string $text  Source text.
 * @param string $table Table base name.
 * @param int    $exclude_id ID to exclude (for updates).
 * @return string Unique slug.
 */
function bdc_generate_slug($text, $table, $exclude_id = 0) {
    $slug = sanitize_title($text);

    if (empty($slug)) {
        $slug = 'untitled-' . uniqid();
    }

    $original_slug = $slug;
    $counter = 1;

    while (bdc_slug_exists($slug, $table, $exclude_id)) {
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }

    return $slug;
}

/**
 * Check if a slug already exists in a table.
 *
 * @param string $slug       Slug to check.
 * @param string $table      Table base name.
 * @param int    $exclude_id ID to exclude from check.
 * @return bool
 */
function bdc_slug_exists($slug, $table, $exclude_id = 0) {
    global $wpdb;
    $sql = $wpdb->prepare(
        "SELECT COUNT(*) FROM " . BDC_DB::table($table) . " WHERE slug = %s AND id != %d",
        $slug,
        $exclude_id
    );
    return (int) $wpdb->get_var($sql) > 0;
}

/**
 * Calculate profile completeness score (0–100).
 *
 * @param array $brand_data Brand data array.
 * @return int Completeness percentage.
 */
function bdc_calculate_completeness($brand_data) {
    $fields = array(
        'name'              => 15,
        'short_description' => 10,
        'full_description'  => 15,
        'website'           => 10,
        'origin_country'    => 10,
        'logo_id'           => 10,
        'cover_id'          => 5,
        'categories'        => 10,
        'social_links'      => 5,
        'support_contact'   => 5,
        'return_policy'     => 5,
    );

    $score = 0;
    foreach ($fields as $field => $weight) {
        if (!empty($brand_data[$field])) {
            $score += $weight;
        }
    }

    return min(100, $score);
}

/**
 * Validate ISO 3166-1 alpha-2 country code.
 *
 * @param string $code Two-letter country code.
 * @return bool
 */
function bdc_is_valid_country_code($code) {
    $valid_codes = array(
        'AF', 'AX', 'AL', 'DZ', 'AS', 'AD', 'AO', 'AI', 'AQ', 'AG', 'AR', 'AM', 'AW', 'AU', 'AT', 'AZ',
        'BS', 'BH', 'BD', 'BB', 'BY', 'BE', 'BZ', 'BJ', 'BM', 'BT', 'BO', 'BQ', 'BA', 'BW', 'BV', 'BR',
        'IO', 'BN', 'BG', 'BF', 'BI', 'CV', 'KH', 'CM', 'CA', 'KY', 'CF', 'TD', 'CL', 'CN', 'CX', 'CC',
        'CO', 'KM', 'CG', 'CD', 'CK', 'CR', 'CI', 'HR', 'CU', 'CW', 'CY', 'CZ', 'DK', 'DJ', 'DM', 'DO',
        'EC', 'EG', 'SV', 'GQ', 'ER', 'EE', 'SZ', 'ET', 'FK', 'FO', 'FJ', 'FI', 'FR', 'GF', 'PF', 'TF',
        'GA', 'GM', 'GE', 'DE', 'GH', 'GI', 'GR', 'GL', 'GD', 'GP', 'GU', 'GT', 'GG', 'GN', 'GW', 'GY',
        'HT', 'HM', 'VA', 'HN', 'HK', 'HU', 'IS', 'IN', 'ID', 'IR', 'IQ', 'IE', 'IM', 'IL', 'IT', 'JM',
        'JP', 'JE', 'JO', 'KZ', 'KE', 'KI', 'KP', 'KR', 'KW', 'KG', 'LA', 'LV', 'LB', 'LS', 'LR', 'LY',
        'LI', 'LT', 'LU', 'MO', 'MG', 'MW', 'MY', 'MV', 'ML', 'MT', 'MH', 'MQ', 'MR', 'MU', 'YT', 'MX',
        'FM', 'MD', 'MC', 'MN', 'ME', 'MS', 'MA', 'MZ', 'MM', 'NA', 'NR', 'NP', 'NL', 'NC', 'NZ', 'NI',
        'NE', 'NG', 'NU', 'NF', 'MK', 'MP', 'NO', 'OM', 'PK', 'PW', 'PS', 'PA', 'PG', 'PY', 'PE', 'PH',
        'PN', 'PL', 'PT', 'PR', 'QA', 'RE', 'RO', 'RU', 'RW', 'BL', 'SH', 'KN', 'LC', 'MF', 'PM', 'VC',
        'WS', 'SM', 'ST', 'SA', 'SN', 'RS', 'SC', 'SL', 'SG', 'SX', 'SK', 'SI', 'SB', 'SO', 'ZA', 'GS',
        'SS', 'ES', 'LK', 'SD', 'SR', 'SJ', 'SE', 'CH', 'SY', 'TW', 'TJ', 'TZ', 'TH', 'TL', 'TG', 'TK',
        'TO', 'TT', 'TN', 'TR', 'TM', 'TC', 'TV', 'UG', 'UA', 'AE', 'GB', 'US', 'UM', 'UY', 'UZ', 'VU',
        'VE', 'VN', 'VG', 'VI', 'WF', 'EH', 'YE', 'ZM', 'ZW',
    );
    return in_array(strtoupper($code), $valid_codes, true);
}

/**
 * Log activity to activity_log table.
 *
 * @param int    $user_id     WP user ID.
 * @param string $action      Action name (e.g., 'brand_published').
 * @param string $object_type Object type (brand/category/product/claim/submission).
 * @param int    $object_id   Object ID.
 * @param array  $details     Additional contextual data.
 * @return int|false Insert ID.
 */
function bdc_log_activity($user_id, $action, $object_type, $object_id, $details = array()) {
    return BDC_DB::insert('activity_log', array(
        'user_id'     => $user_id,
        'action'      => $action,
        'object_type' => $object_type,
        'object_id'   => $object_id,
        'details'     => wp_json_encode($details),
    ));
}

/**
 * Send notification email.
 *
 * @param string $to      Recipient email.
 * @param string $subject Email subject.
 * @param string $message HTML message body.
 * @return bool
 */
function bdc_send_email($to, $subject, $message) {
    $site_name = get_bloginfo('name');
    $header = '<div style="max-width:600px;margin:0 auto;font-family:Inter,Arial,sans-serif;">';
    $header .= '<h2 style="color:#1B2A4A;">' . esc_html($site_name) . '</h2>';
    $footer = '<hr style="border-color:#E2E8F0;"><p style="color:#94A3B8;font-size:12px;">';
    $footer .= 'This is an automated message from ' . esc_html($site_name) . '.</p>';
    $footer .= '</div>';

    $full_message = $header . $message . $footer;
    $headers = array('Content-Type: text/html; charset=UTF-8');

    return wp_mail($to, $subject, $full_message, $headers);
}
