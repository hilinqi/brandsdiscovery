<?php
/**
 * Database migration handler.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Migrator {

    /**
     * Run migrations from $old_version to current.
     *
     * @param string $old_version Previous DB version.
     * @param string $new_version Target DB version.
     */
    public static function run($old_version, $new_version) {
        // For MVP v1.0.0, tables are created by activator.
        // Future versions add incremental migrations here.
        update_option('bdc_db_version', $new_version);
    }
}
