<?php
/**
 * Plugin deactivation handler.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Deactivator {

    public static function deactivate() {
        flush_rewrite_rules();
    }
}
