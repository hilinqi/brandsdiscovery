<?php
/**
 * Products management template.
 *
 * @package BrandsDiscovery_Merchant_Center
 */

$brand_id = get_query_var('brand_id', 0);
$merchant_brands = new BDC_Merchant_Brands();
$brand = $merchant_brands->get_editable_brand($brand_id, get_current_user_id());

if (is_wp_error($brand)) {
    wp_die(esc_html($brand->get_error_message()));
}

$products_model = new BDC_Products();
$products = $products_model->get_by_brand($brand_id, 20);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products — <?php echo esc_html($brand->name); ?> — <?php bloginfo('name'); ?></title>
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
                <a href="<?php echo esc_url(home_url('/merchant/brand/' . $brand_id)); ?>"><?php echo esc_html($brand->name); ?></a>
                <span class="current">Products</span>
                <a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a>
            </nav>
        </div>
    </header>

    <main class="bdc-merchant-main">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h1>Products — <?php echo esc_html($brand->name); ?></h1>
            <button id="bdc-add-product-btn" class="btn btn-primary">+ Add Product</button>
        </div>

        <!-- Add product form (hidden by default) -->
        <div id="bdc-add-product-form" class="bdc-merchant-section" style="display:none;">
            <h3>Add New Product</h3>
            <div id="bdc-product-message"></div>
            <form id="bdc-product-form" class="bdc-merchant-form">
                <?php wp_nonce_field('bdc_merchant_product', '_bdc_nonce'); ?>
                <div class="bdc-form-field">
                    <label for="product-name">Product Name *</label>
                    <input type="text" id="product-name" name="name" required>
                </div>
                <div class="bdc-form-field">
                    <label for="product-desc">Description</label>
                    <textarea id="product-desc" name="description" rows="3"></textarea>
                </div>
                <div class="bdc-form-field">
                    <label for="product-price">Price</label>
                    <input type="text" id="product-price" name="price" placeholder="$29.99">
                </div>
                <div class="bdc-form-field">
                    <label for="product-url">Product URL</label>
                    <input type="url" id="product-url" name="product_url" placeholder="https://...">
                </div>
                <button type="submit" class="btn btn-primary">Save Product</button>
                <button type="button" id="bdc-cancel-product" class="btn btn-outline">Cancel</button>
            </form>
        </div>

        <!-- Product list -->
        <?php if (empty($products)) : ?>
            <div class="bdc-merchant-empty">
                <p>No products added yet. Add your first product to showcase on your brand page.</p>
            </div>
        <?php else : ?>
            <table class="bdc-merchant-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product) : ?>
                        <tr>
                            <td><?php echo esc_html($product->name); ?></td>
                            <td><?php echo esc_html($product->price); ?></td>
                            <td><?php echo esc_html($product->status); ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline bdc-delete-product" data-id="<?php echo esc_attr($product->id); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</div>

<script>
(function() {
    var brandId = <?php echo intval($brand_id); ?>;
    var apiBase = bdcMerchant.apiUrl + 'brands/' + brandId + '/products';

    // Toggle add form.
    document.getElementById('bdc-add-product-btn').addEventListener('click', function() {
        document.getElementById('bdc-add-product-form').style.display = 'block';
        this.style.display = 'none';
    });

    document.getElementById('bdc-cancel-product').addEventListener('click', function() {
        document.getElementById('bdc-add-product-form').style.display = 'none';
        document.getElementById('bdc-add-product-btn').style.display = 'block';
    });

    // Save product.
    document.getElementById('bdc-product-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var data = {};
        for (var pair of formData.entries()) {
            if (pair[0] !== '_bdc_nonce') {
                data[pair[0]] = pair[1];
            }
        }

        fetch(apiBase, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': bdcMerchant.nonce
            },
            body: JSON.stringify(data)
        })
        .then(function(res) { return res.json(); })
        .then(function() {
            location.reload();
        });
    });

    // Delete product.
    document.querySelectorAll('.bdc-delete-product').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Delete this product?')) return;
            var id = this.dataset.id;

            fetch(apiBase + '/' + id, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': bdcMerchant.nonce }
            })
            .then(function() { location.reload(); });
        });
    });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
