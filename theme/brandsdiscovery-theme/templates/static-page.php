<?php
/**
 * Static pages (About, Contact, FAQ, legal pages, etc.).
 *
 * @package BrandsDiscovery
 */

get_header();

$slug = get_query_var('page_slug', '');
$title = ucwords(str_replace(array('-', '_'), ' ', $slug));

// Map slugs to display titles.
$titles = array(
    'about'                => 'About BrandsDiscovery',
    'contact'              => 'Contact Us',
    'faq'                  => 'Frequently Asked Questions',
    'editorial-policy'     => 'Editorial Policy',
    'verification-policy'  => 'Verification Policy',
    'privacy-policy'       => 'Privacy Policy',
    'terms'                => 'Terms of Service',
    'cookie-policy'        => 'Cookie Policy',
    'affiliate-disclosure' => 'Affiliate Disclosure',
    'advertise'            => 'Advertise With Us',
    'partner'              => 'Partner With Us',
    'submit-brand'         => 'Submit Your Brand',
    'request-brand'        => 'Request a Brand or Product',
    'report'               => 'Report Incorrect Information',
);
$title = $titles[$slug] ?? $title;
?>

<main class="site-main">
    <div class="container container-narrow" style="padding-top:var(--space-2xl);padding-bottom:var(--space-3xl);">
        <h1><?php echo esc_html($title); ?></h1>
        <div style="color:var(--color-text-secondary);line-height:1.7;margin-top:var(--space-lg);">
            <p>This page content is being prepared. Please check back soon.</p>
            <p>For immediate inquiries, please visit our <a href="<?php echo esc_url(home_url('/contact')); ?>">Contact page</a>.</p>
        </div>
    </div>
</main>

<?php get_footer(); ?>
