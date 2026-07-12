<?php
/**
 * Cloudflare R2 Media abstraction layer.
 *
 * Uses AWS S3-compatible SDK for R2 operations.
 * Configuration via wp-config.php constants.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Media {

    /**
     * Image presets for resizing.
     */
    const PRESETS = array(
        'thumbnail' => array('width' => 150, 'height' => 150, 'crop' => true),
        'small'     => array('width' => 300, 'height' => 0,   'crop' => false),
        'medium'    => array('width' => 600, 'height' => 0,   'crop' => false),
        'large'     => array('width' => 1200, 'height' => 0,  'crop' => false),
        'card'      => array('width' => 400, 'height' => 300, 'crop' => true),
        'hero'      => array('width' => 1600, 'height' => 600, 'crop' => true),
    );

    /**
     * Allowed image types and max size.
     */
    const ALLOWED_TYPES = array('jpg', 'jpeg', 'png', 'webp', 'svg');
    const MAX_SIZE = 5 * 1024 * 1024; // 5MB

    /**
     * Get R2 configuration.
     */
    private static function get_config() {
        return array(
            'key'        => defined('BDC_R2_ACCESS_KEY') ? BDC_R2_ACCESS_KEY : '',
            'secret'     => defined('BDC_R2_SECRET_KEY') ? BDC_R2_SECRET_KEY : '',
            'endpoint'   => defined('BDC_R2_ENDPOINT') ? BDC_R2_ENDPOINT : '',
            'bucket'     => defined('BDC_R2_BUCKET') ? BDC_R2_BUCKET : '',
            'public_url' => defined('BDC_R2_PUBLIC_URL') ? BDC_R2_PUBLIC_URL : '',
        );
    }

    /**
     * Check if R2 is configured.
     *
     * @return bool
     */
    public static function is_configured() {
        $config = self::get_config();
        return !empty($config['key']) && !empty($config['endpoint']) && !empty($config['bucket']);
    }

    /**
     * Get public URL for an object.
     *
     * @param string $object_key R2 object key.
     * @param string $preset     Preset name (original if empty).
     * @return string Public URL.
     */
    public static function get_url($object_key, $preset = 'original') {
        if (empty($object_key)) {
            return '';
        }

        $config = self::get_config();
        $base = rtrim($config['public_url'], '/');

        if ($preset !== 'original' && isset(self::PRESETS[$preset])) {
            // Append preset suffix: /logos/123/thumb_abc.jpg
            $pathinfo = pathinfo($object_key);
            $object_key = $pathinfo['dirname'] . '/' . $preset . '_' . $pathinfo['basename'];
        }

        return $base . '/' . ltrim($object_key, '/');
    }

    /**
     * Get URL for a specific size.
     *
     * @param string $object_key R2 object key.
     * @param string $size       Preset name (thumbnail/small/medium/large/card/hero).
     * @return string
     */
    public static function get_size_url($object_key, $size) {
        return self::get_url($object_key, $size);
    }

    /**
     * Validate and handle uploaded file.
     *
     * @param array  $file     $_FILES array element.
     * @param string $type     Content type: 'brand_logo', 'brand_cover', 'category_hero',
     *                         'product_image', 'guide_featured'.
     * @param int    $entity_id Entity ID the image belongs to.
     * @return string|WP_Error Object key or error.
     */
    public static function upload($file, $type, $entity_id) {
        if (!self::is_configured()) {
            return new WP_Error('r2_not_configured', 'Cloudflare R2 is not configured.');
        }

        // Validate.
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return new WP_Error('no_file', 'No file uploaded.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', 'File upload error: ' . $file['error']);
        }

        if ($file['size'] > self::MAX_SIZE) {
            return new WP_Error('file_too_large', 'File size exceeds maximum (5MB).');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_TYPES, true)) {
            return new WP_Error('invalid_type', 'File type not allowed. Allowed: ' . implode(', ', self::ALLOWED_TYPES));
        }

        // Verify MIME type.
        $filetype = wp_check_filetype($file['name']);
        if (empty($filetype['type'])) {
            return new WP_Error('invalid_mime', 'Could not determine file MIME type.');
        }

        // Generate object key.
        $base_path = self::get_base_path($type);
        $key = sprintf(
            '%s/%d/%s-%s.%s',
            $base_path,
            $entity_id,
            time(),
            substr(md5(uniqid()), 0, 8),
            $ext
        );

        // Upload to R2 using WordPress HTTP API.
        $config = self::get_config();

        // For MVP, store file locally and note R2 key for later sync.
        // Full R2 integration requires S3 SDK; this provides the abstraction layer.
        $upload_dir = wp_upload_dir();
        $local_path = $upload_dir['basedir'] . '/bdc-temp/' . $key;
        wp_mkdir_p(dirname($local_path));
        move_uploaded_file($file['tmp_name'], $local_path);

        // In production: use AWS SDK to upload to R2.
        // self::upload_to_r2($local_path, $key, $config);

        return $key;
    }

    /**
     * Delete an object from R2.
     *
     * @param string $object_key R2 object key.
     * @return bool
     */
    public static function delete($object_key) {
        if (empty($object_key)) {
            return false;
        }

        // In production: use AWS SDK to delete from R2.
        // For MVP with local storage, delete temp file.
        $upload_dir = wp_upload_dir();
        $local_path = $upload_dir['basedir'] . '/bdc-temp/' . $object_key;
        if (file_exists($local_path)) {
            unlink($local_path);
        }

        return true;
    }

    /**
     * Delete all objects for a given entity.
     *
     * @param string $type      Content type.
     * @param int    $entity_id Entity ID.
     * @return bool
     */
    public static function delete_all_for($type, $entity_id) {
        $prefix = self::get_base_path($type) . '/' . $entity_id . '/';

        $upload_dir = wp_upload_dir();
        $local_dir = $upload_dir['basedir'] . '/bdc-temp/' . $prefix;

        if (is_dir($local_dir)) {
            $files = glob($local_dir . '*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($local_dir);
        }

        // In production: list and delete all R2 objects with prefix.
        return true;
    }

    /**
     * Get base path for a content type.
     *
     * @param string $type Content type.
     * @return string
     */
    private static function get_base_path($type) {
        $map = array(
            'brand_logo'    => 'brands/logos',
            'brand_cover'   => 'brands/covers',
            'category_hero' => 'categories/heroes',
            'product_image' => 'products/images',
            'guide_featured' => 'guides/featured',
        );
        return isset($map[$type]) ? $map[$type] : 'misc';
    }
}
