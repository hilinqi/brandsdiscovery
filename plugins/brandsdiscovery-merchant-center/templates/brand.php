<?php
/**
 * Brand edit page for Merchant Center.
 *
 * @package BrandsDiscovery_Merchant_Center
 */

$brand_id = get_query_var('brand_id', 0);
if (!$brand_id && isset($_GET['id'])) {
    $brand_id = intval($_GET['id']);
}

if (!$brand_id) {
    wp_die('Brand not specified.');
}

$merchant_brands = new BDC_Merchant_Brands();
$brand = $merchant_brands->get_editable_brand($brand_id, get_current_user_id());

if (is_wp_error($brand)) {
    wp_die(esc_html($brand->get_error_message()));
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Brand — <?php bloginfo('name'); ?></title>
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
                <span class="current"><?php echo esc_html($brand->name); ?></span>
                <a href="<?php echo esc_url(home_url('/merchant/settings')); ?>">Settings</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a>
            </nav>
        </div>
    </header>

    <main class="bdc-merchant-main">
        <h1>Edit: <?php echo esc_html($brand->name); ?></h1>

        <form id="bdc-edit-brand-form" class="bdc-merchant-form">
            <?php wp_nonce_field('bdc_merchant_edit_brand', '_bdc_nonce'); ?>

            <div class="bdc-merchant-form-section">
                <h3>Basic Information</h3>

                <div class="bdc-form-field">
                    <label for="brand-name">Brand Name</label>
                    <input type="text" id="brand-name" value="<?php echo esc_attr($brand->name); ?>" disabled>
                    <small>Brand name is managed by the platform. Contact support to request changes.</small>
                </div>

                <div class="bdc-form-field">
                    <label for="short-description">Short Description</label>
                    <textarea id="short-description" name="short_description" rows="3" maxlength="300"><?php echo esc_textarea($brand->short_description ?? ''); ?></textarea>
                    <small>Brief summary visible in brand cards and search results. Max 300 characters.</small>
                </div>

                <div class="bdc-form-field">
                    <label for="full-description">Full Description</label>
                    <textarea id="full-description" name="full_description" rows="8"><?php echo esc_textarea($brand->full_description ?? ''); ?></textarea>
                    <small>Detailed brand description shown on your brand page.</small>
                </div>
            </div>

            <div class="bdc-merchant-form-section">
                <h3>Shipping & Policies</h3>

                <div class="bdc-form-field">
                    <label for="shipping-regions">Shipping Regions</label>
                    <input type="text" id="shipping-regions" name="shipping_regions" value="<?php echo esc_attr(implode(', ', (array) $brand->shipping_regions)); ?>" placeholder="e.g., Worldwide, North America, Europe">
                    <small>Comma-separated list of regions you ship to.</small>
                </div>

                <div class="bdc-form-field">
                    <label for="payment-methods">Payment Methods</label>
                    <input type="text" id="payment-methods" name="payment_methods" value="<?php echo esc_attr(implode(', ', (array) $brand->payment_methods)); ?>" placeholder="e.g., Visa, Mastercard, PayPal, Apple Pay">
                    <small>Comma-separated list of accepted payment methods.</small>
                </div>

                <div class="bdc-form-field">
                    <label for="return-policy">Return Policy</label>
                    <textarea id="return-policy" name="return_policy" rows="3"><?php echo esc_textarea($brand->return_policy ?? ''); ?></textarea>
                </div>
            </div>

            <div class="bdc-merchant-form-section">
                <h3>Contact & Social</h3>

                <div class="bdc-form-field">
                    <label for="support-contact">Support Contact</label>
                    <input type="text" id="support-contact" name="support_contact" value="<?php echo esc_attr($brand->support_contact ?? ''); ?>" placeholder="support@example.com">
                </div>

                <div class="bdc-form-field">
                    <label>Social Links</label>
                    <?php
                    $platforms = array('website', 'facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'tiktok', 'pinterest');
                    $social = (array) $brand->social_links;
                    foreach ($platforms as $platform) :
                        $value = $social[$platform] ?? '';
                    ?>
                        <input type="url" name="social_links[<?php echo esc_attr($platform); ?>]" 
                               value="<?php echo esc_attr($value); ?>" 
                               placeholder="<?php echo esc_attr(ucfirst($platform)); ?> URL"
                               style="margin-bottom:8px;">
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bdc-merchant-form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                <span id="bdc-save-message" style="margin-left:12px;"></span>
            </div>

            <p class="bd-text-muted" style="margin-top:16px;">
                <strong>Note:</strong> Changes to descriptions may require admin review before being published.
                Shipping regions, payment methods, policies, and contact information are updated immediately.
            </p>
        </form>
    </main>
</div>

<script>
(function() {
    var form = document.getElementById('bdc-edit-brand-form');
    var message = document.getElementById('bdc-save-message');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(form);
        var data = {};
        var socialLinks = {};

        for (var pair of formData.entries()) {
            if (pair[0].startsWith('social_links[')) {
                var platform = pair[0].match(/\[(.*?)\]/)[1];
                if (pair[1]) socialLinks[platform] = pair[1];
            } else if (pair[0] !== '_bdc_nonce') {
                data[pair[0]] = pair[1];
            }
        }

        if (Object.keys(socialLinks).length > 0) {
            data.social_links = JSON.stringify(socialLinks);
        }

        // Parse comma-separated fields.
        ['shipping_regions', 'payment_methods'].forEach(function(field) {
            if (data[field]) {
                data[field] = JSON.stringify(
                    data[field].split(',').map(function(s) { return s.trim(); }).filter(Boolean)
                );
            }
        });

        fetch(bdcMerchant.apiUrl + 'brands/<?php echo intval($brand_id); ?>', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': bdcMerchant.nonce
            },
            body: JSON.stringify(data)
        })
        .then(function(res) { return res.json(); })
        .then(function(result) {
            if (result.code) {
                message.innerHTML = '<span style="color:#DC2626;">Error: ' + result.message + '</span>';
            } else {
                message.innerHTML = '<span style="color:#16A34A;">✓ Saved successfully!</span>';
                setTimeout(function() { message.innerHTML = ''; }, 3000);
            }
        })
        .catch(function() {
            message.innerHTML = '<span style="color:#DC2626;">Network error. Please try again.</span>';
        });
    });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
