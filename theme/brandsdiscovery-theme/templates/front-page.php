<?php
/**
 * Homepage template — hardcoded hero + dynamic content from Core API.
 * Falls back gracefully when database is empty.
 *
 * @package BrandsDiscovery
 */

get_header();
?>

<main id="main" class="site-main">

    <!-- Hero Section — always visible -->
    <section class="bd-hero">
        <div class="container">
            <h1>Discover Independent Brands</h1>
            <p class="bd-hero-sub">Explore unique brands from around the world. Find products you'll love and support independent creators.</p>
            <div class="search-bar" style="max-width:560px;margin:0 auto;">
                <span class="search-bar-icon" aria-hidden="true">🔍</span>
                <input type="search" id="home-search" 
                       placeholder="Search brands, categories..." 
                       aria-label="Search brands and categories">
            </div>
        </div>
    </section>

    <!-- 10 Top-Level Categories (hardcoded — always visible) -->
    <section class="bd-section">
        <div class="container">
            <h2 class="section-title">Browse Categories</h2>
            <div class="category-grid">
                <?php
                $top_categories = array(
                    array('name' => 'Electronics & Technology', 'slug' => 'electronics-technology',  'icon' => '💻'),
                    array('name' => 'Home & Kitchen',         'slug' => 'home-kitchen',          'icon' => '🏠'),
                    array('name' => 'Pet Products',           'slug' => 'pet-products',           'icon' => '🐾'),
                    array('name' => 'Beauty & Personal Care', 'slug' => 'beauty-personal-care',   'icon' => '💄'),
                    array('name' => 'Outdoor & Sports',       'slug' => 'outdoor-sports',         'icon' => '🏔️'),
                    array('name' => 'Fashion & Accessories',  'slug' => 'fashion-accessories',    'icon' => '👗'),
                    array('name' => 'Baby & Kids',            'slug' => 'baby-kids',              'icon' => '👶'),
                    array('name' => 'Automotive',             'slug' => 'automotive',             'icon' => '🚗'),
                    array('name' => 'Office & Productivity',  'slug' => 'office-productivity',    'icon' => '💼'),
                    array('name' => 'Lifestyle & Gifts',      'slug' => 'lifestyle-gifts',        'icon' => '🎁'),
                );
                foreach ($top_categories as $cat) :
                ?>
                    <a href="<?php echo esc_url(home_url('/categories/' . $cat['slug'])); ?>" class="category-card card">
                        <div class="category-card-img">
                            <span style="font-size:40px;"><?php echo $cat['icon']; ?></span>
                        </div>
                        <div class="category-card-body">
                            <h3 class="category-card-name"><?php echo esc_html($cat['name']); ?></h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Dynamic: Featured Brands from Core API -->
    <?php
    $featured = bd_fetch_api('brands', array('verified_only' => 1, 'per_page' => 8, 'orderby' => 'display_order'));
    $has_featured = is_array($featured) && !empty($featured['brands']);
    ?>
    <?php if ($has_featured) : ?>
    <section class="bd-section bd-section-alt">
        <div class="container">
            <h2 class="section-title">Featured Brands</h2>
            <div class="brand-grid">
                <?php foreach ($featured['brands'] as $brand) : ?>
                    <?php echo bd_render_brand_card($brand); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Dynamic: Latest Brands from Core API -->
    <?php
    $latest = bd_fetch_api('brands', array('per_page' => 8, 'orderby' => 'created_at', 'order' => 'DESC'));
    $has_latest = is_array($latest) && !empty($latest['brands']);
    ?>
    <?php if ($has_latest) : ?>
    <section class="bd-section">
        <div class="container">
            <h2 class="section-title">Latest Discoveries</h2>
            <div class="brand-grid">
                <?php foreach ($latest['brands'] as $brand) : ?>
                    <?php echo bd_render_brand_card($brand); ?>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:var(--space-lg)">
                <a href="<?php echo esc_url(home_url('/brands')); ?>" class="btn btn-outline">View All Brands →</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Empty state when no brands exist yet -->
    <?php if (!$has_featured && !$has_latest) : ?>
    <section class="bd-section">
        <div class="container" style="text-align:center;">
            <h2 class="section-title">Brands Are Coming Soon</h2>
            <p style="color:var(--color-text-secondary);max-width:500px;margin:0 auto 24px;">
                We're curating the best independent brands. In the meantime, browse our categories or submit a brand you love.
            </p>
            <a href="<?php echo esc_url(home_url('/submit-brand')); ?>" class="btn btn-primary btn-lg">Submit a Brand</a>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section — always visible -->
    <section class="bd-section bd-cta">
        <div class="container">
            <div class="bd-cta-grid">
                <div class="bd-cta-card">
                    <h3>Know a Brand We're Missing?</h3>
                    <p>Help us grow by submitting a brand or requesting one you'd like to see on BrandsDiscovery.</p>
                    <a href="<?php echo esc_url(home_url('/submit-brand')); ?>" class="btn btn-primary">Submit a Brand</a>
                </div>
                <div class="bd-cta-card">
                    <h3>Looking for Something Specific?</h3>
                    <p>Can't find what you're looking for? Request a brand or product and we'll try to add it.</p>
                    <a href="<?php echo esc_url(home_url('/request-brand')); ?>" class="btn btn-secondary">Request a Brand</a>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
(function() {
    var input = document.getElementById('home-search');
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                window.location.href = '<?php echo esc_url(home_url('/search')); ?>?q=' + encodeURIComponent(this.value.trim());
            }
        });
    }
})();
</script>

<?php get_footer(); ?>
