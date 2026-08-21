<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DeliveryLocations_model extends CI_Model
{
    /**
     * Get all active locations for a store
     */
    public function get_all_by_store($store_id)
    {
        return $this->db
            ->select('*')
            ->from('delivery_locations')
            ->where('store_id', $store_id)
            ->where('deleted_at IS NULL', NULL, FALSE)
            ->order_by('display_order', 'ASC')
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get active locations for dropdown
     */
    public function get_active_by_store($store_id)
    {
        return $this->db
            ->select('id, name, short_name, floor, building')
            ->from('delivery_locations')
            ->where('store_id', $store_id)
            ->where('is_active', 1)
            ->where('deleted_at IS NULL', NULL, FALSE)
            ->order_by('display_order', 'ASC')
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get single location by ID with optional store verification
     */
    public function get_by_id($id, $store_id = null)
    {
        $this->db
            ->select('*')
            ->from('delivery_locations')
            ->where('id', $id)
            ->where('deleted_at IS NULL', NULL, FALSE);

        if ($store_id) {
            $this->db->where('store_id', $store_id);
        }

        return $this->db->get()->row();
    }

    /**
     * Get active location count for a store
     */
    public function get_active_count($store_id)
    {
        return $this->db
            ->where('store_id', $store_id)
            ->where('is_active', 1)
            ->where('deleted_at IS NULL', NULL, FALSE)
            ->count_all_results('delivery_locations');
    }

    /**
     * Check if location code exists for a store
     */
    public function check_code_exists($store_id, $location_code, $exclude_id = null)
    {
        $this->db->where('location_code', $location_code);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at IS NULL', NULL, FALSE);
        return $this->db->get('delivery_locations')->row();
    }

    /**
     * Generate a unique location code
     */
    public function generate_location_code()
    {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            $exists = $this->db->where('location_code', $code)->get('delivery_locations')->row();
        } while ($exists);

        return $code;
    }
}
