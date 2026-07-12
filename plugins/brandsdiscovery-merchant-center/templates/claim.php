<?php
/**
 * Claim brand template.
 *
 * @package BrandsDiscovery_Merchant_Center
 */

$brand_id = get_query_var('brand_id', 0);
$brand_name = '';

if ($brand_id) {
    $brands = new BDC_Brands();
    $brand = $brands->get($brand_id);
    if ($brand) {
        $brand_name = $brand->name;
    }
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Brand — <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('bdc-merchant-page'); ?>>

<div class="bdc-merchant-wrapper">
    <header class="bdc-merchant-header">
        <div class="bdc-merchant-header-inner">
            <div class="bdc-merchant-logo">
                <a href="<?php echo esc_url(home_url()); ?>"><?php bloginfo('name'); ?></a>
                <span class="bdc-merchant-label">Merchant Center</span>
            </div>
            <nav class="bdc-merchant-nav">
                <a href="<?php echo esc_url(home_url('/merchant/dashboard')); ?>">Dashboard</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a>
            </nav>
        </div>
    </header>

    <main class="bdc-merchant-main" style="max-width:640px;">
        <h1>Claim This Brand</h1>
        <?php if ($brand_name) : ?>
            <p>You are claiming: <strong><?php echo esc_html($brand_name); ?></strong></p>
        <?php endif; ?>

        <div id="bdc-claim-message"></div>

        <form id="bdc-claim-form" class="bdc-merchant-form">
            <?php wp_nonce_field('bdc_merchant_claim', '_bdc_nonce'); ?>
            <input type="hidden" name="brand_id" value="<?php echo esc_attr($brand_id); ?>">

            <div class="bdc-form-field">
                <label for="company-name">Company Name *</label>
                <input type="text" id="company-name" name="company_name" required>
            </div>

            <div class="bdc-form-field">
                <label for="contact-name">Contact Person *</label>
                <input type="text" id="contact-name" name="contact_name" required>
            </div>

            <div class="bdc-form-field">
                <label for="contact-email">Contact Email *</label>
                <input type="email" id="contact-email" name="contact_email" required>
            </div>

            <div class="bdc-form-field">
                <label for="contact-phone">Phone Number</label>
                <input type="tel" id="contact-phone" name="contact_phone">
            </div>

            <div class="bdc-form-field">
                <label for="evidence">Evidence of Ownership</label>
                <textarea id="evidence" name="evidence" rows="4" placeholder="Describe your relationship to this brand. Include any relevant links or documentation."></textarea>
                <small>Provide information that helps us verify you are the rightful owner or authorized representative of this brand.</small>
            </div>

            <div class="bdc-merchant-form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Submit Claim</button>
            </div>
        </form>
    </main>
</div>

<script>
document.getElementById('bdc-claim-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var message = document.getElementById('bdc-claim-message');
    var formData = new FormData(form);
    var data = {};

    for (var pair of formData.entries()) {
        if (pair[0] !== '_bdc_nonce') {
            data[pair[0]] = pair[1];
        }
    }

    fetch(bdcMerchant.apiUrl + 'claims', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': bdcMerchant.nonce
        },
        body: JSON.stringify(data)
    })
    .then(function(res) { return res.json(); })
    .then(function(result) {
        if (result.code) {
            message.innerHTML = '<div class="bd-notice bd-notice-error">' + result.message + '</div>';
        } else {
            message.innerHTML = '<div class="bd-notice bd-notice-success">✓ Claim submitted! We\'ll review it and get back to you within 3-5 business days.</div>';
            form.reset();
        }
    })
    .catch(function() {
        message.innerHTML = '<div class="bd-notice bd-notice-error">Network error. Please try again.</div>';
    });
});
</script>

<?php wp_footer(); ?>
</body>
</html>
