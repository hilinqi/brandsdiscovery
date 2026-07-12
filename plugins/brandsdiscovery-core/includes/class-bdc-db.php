<?php
/**
 * Database abstraction layer.
 * All custom table queries go through this class.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_DB {

    /**
     * Get table name with prefix.
     *
     * @param string $table Table base name.
     * @return string Full table name.
     */
    public static function table($table) {
        global $wpdb;
        return $wpdb->prefix . 'bdc_' . $table;
    }

    /**
     * Insert a row.
     *
     * @param string $table Table base name.
     * @param array  $data  Column => value pairs.
     * @return int|false Insert ID or false on failure.
     */
    public static function insert($table, $data) {
        global $wpdb;
        $result = $wpdb->insert(self::table($table), $data);
        if ($result === false) {
            return false;
        }
        return $wpdb->insert_id;
    }

    /**
     * Update rows.
     *
     * @param string $table  Table base name.
     * @param array  $data   Column => value pairs.
     * @param array  $where  Column => value pairs for WHERE clause.
     * @return int|false Number of rows updated or false.
     */
    public static function update($table, $data, $where) {
        global $wpdb;
        return $wpdb->update(self::table($table), $data, $where);
    }

    /**
     * Delete rows.
     *
     * @param string $table Table base name.
     * @param array  $where Column => value pairs for WHERE clause.
     * @return int|false Number of rows deleted or false.
     */
    public static function delete($table, $where) {
        global $wpdb;
        return $wpdb->delete(self::table($table), $where);
    }

    /**
     * Get a single row.
     *
     * @param string $table Table base name.
     * @param array  $where Column => value pairs.
     * @return object|null
     */
    public static function get_row($table, $where) {
        global $wpdb;
        $conditions = array();
        foreach ($where as $col => $val) {
            $conditions[] = $wpdb->prepare("`$col` = %s", $val);
        }
        $sql = "SELECT * FROM " . self::table($table) . " WHERE " . implode(' AND ', $conditions) . " LIMIT 1";
        return $wpdb->get_row($sql);
    }

    /**
     * Get row by ID.
     *
     * @param string $table Table base name.
     * @param int    $id    Row ID.
     * @return object|null
     */
    public static function get_by_id($table, $id) {
        return self::get_row($table, array('id' => $id));
    }

    /**
     * Get multiple rows.
     *
     * @param string $table Table base name.
     * @param array  $args  Query arguments: where, orderby, order, limit, offset.
     * @return array
     */
    public static function get_results($table, $args = array()) {
        global $wpdb;
        $defaults = array(
            'where'   => array(),
            'where_raw' => '',
            'orderby' => 'id',
            'order'   => 'DESC',
            'limit'   => 20,
            'offset'  => 0,
        );
        $args = wp_parse_args($args, $defaults);

        $sql = "SELECT * FROM " . self::table($table);

        // Build WHERE clause.
        $where_parts = array();
        foreach ($args['where'] as $col => $val) {
            if (is_array($val)) {
                $placeholders = implode(',', array_fill(0, count($val), '%s'));
                $where_parts[] = $wpdb->prepare("`$col` IN ($placeholders)", $val);
            } else {
                $where_parts[] = $wpdb->prepare("`$col` = %s", $val);
            }
        }
        if (!empty($args['where_raw'])) {
            $where_parts[] = $args['where_raw'];
        }
        if (!empty($where_parts)) {
            $sql .= ' WHERE ' . implode(' AND ', $where_parts);
        }

        // Ordering.
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        $sql .= " ORDER BY $orderby";

        // Limit.
        if ($args['limit'] > 0) {
            $sql .= $wpdb->prepare(" LIMIT %d", $args['limit']);
        }
        if ($args['offset'] > 0) {
            $sql .= $wpdb->prepare(" OFFSET %d", $args['offset']);
        }

        return $wpdb->get_results($sql);
    }

    /**
     * Count rows.
     *
     * @param string $table Table base name.
     * @param array  $where Column => value pairs.
     * @return int
     */
    public static function count($table, $where = array()) {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM " . self::table($table);
        $where_parts = array();
        foreach ($where as $col => $val) {
            $where_parts[] = $wpdb->prepare("`$col` = %s", $val);
        }
        if (!empty($where_parts)) {
            $sql .= ' WHERE ' . implode(' AND ', $where_parts);
        }
        return (int) $wpdb->get_var($sql);
    }

    /**
     * Run a raw query and return results.
     *
     * @param string $query Prepared SQL query.
     * @return array
     */
    public static function query($query) {
        global $wpdb;
        return $wpdb->get_results($query);
    }

    /**
     * Escape a value using LIKE for search.
     *
     * @param string $value Value to escape.
     * @return string Escaped value.
     */
    public static function esc_like($value) {
        global $wpdb;
        return '%' . $wpdb->esc_like($value) . '%';
    }
}
