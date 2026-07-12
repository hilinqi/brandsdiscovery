<?php
/**
 * Abstract model base class.
 *
 * @package BrandsDiscovery_Core
 */

abstract class BDC_Model {

    protected $table;

    public function __construct($table) {
        $this->table = $table;
    }

    /**
     * Get a single record by ID.
     *
     * @param int $id Record ID.
     * @return object|null
     */
    public function get($id) {
        return BDC_DB::get_by_id($this->table, $id);
    }

    /**
     * Query records.
     *
     * @param array $args Query arguments.
     * @return array
     */
    public function query($args = array()) {
        return BDC_DB::get_results($this->table, $args);
    }

    /**
     * Count records.
     *
     * @param array $where Conditions.
     * @return int
     */
    public function count($where = array()) {
        return BDC_DB::count($this->table, $where);
    }

    /**
     * Create a new record.
     *
     * @param array $data Data to insert.
     * @return int|false New ID or false.
     */
    public function create($data) {
        return BDC_DB::insert($this->table, $data);
    }

    /**
     * Update a record.
     *
     * @param int   $id   Record ID.
     * @param array $data Data to update.
     * @return int|false Rows affected or false.
     */
    public function update($id, $data) {
        return BDC_DB::update($this->table, $data, array('id' => $id));
    }

    /**
     * Delete a record.
     *
     * @param int $id Record ID.
     * @return int|false Rows affected or false.
     */
    public function delete($id) {
        return BDC_DB::delete($this->table, array('id' => $id));
    }

    /**
     * Find a record by slug.
     *
     * @param string $slug Record slug.
     * @return object|null
     */
    public function get_by_slug($slug) {
        return BDC_DB::get_row($this->table, array('slug' => $slug));
    }
}
