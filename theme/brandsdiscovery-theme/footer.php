<?php
/**
 * Footer template.
 *
 * @package BrandsDiscovery
 */
?>

<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h4>Discover</h4>
                <a href="<?php echo esc_url(home_url('/brands')); ?>">All Brands</a>
                <a href="<?php echo esc_url(home_url('/categories')); ?>">Categories</a>
                <a href="<?php echo esc_url(home_url('/guides')); ?>">Buying Guides</a>
                <a href="<?php echo esc_url(home_url('/search')); ?>">Search</a>
            </div>
            <div class="footer-col">
                <h4>For Merchants</h4>
                <a href="<?php echo esc_url(home_url('/merchant/register')); ?>">Register</a>
                <a href="<?php echo esc_url(home_url('/merchant/login')); ?>">Merchant Login</a>
                <a href="<?php echo esc_url(home_url('/submit-brand')); ?>">Submit Your Brand</a>
                <a href="<?php echo esc_url(home_url('/partner')); ?>">Partner With Us</a>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <a href="<?php echo esc_url(home_url('/about')); ?>">About</a>
                <a href="<?php echo esc_url(home_url('/contact')); ?>">Contact</a>
                <a href="<?php echo esc_url(home_url('/faq')); ?>">FAQ</a>
                <a href="<?php echo esc_url(home_url('/advertise')); ?>">Advertise</a>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Privacy Policy</a>
                <a href="<?php echo esc_url(home_url('/terms')); ?>">Terms</a>
                <a href="<?php echo esc_url(home_url('/cookie-policy')); ?>">Cookie Policy</a>
                <a href="<?php echo esc_url(home_url('/affiliate-disclosure')); ?>">Affiliate Disclosure</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> BrandsDiscovery. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
