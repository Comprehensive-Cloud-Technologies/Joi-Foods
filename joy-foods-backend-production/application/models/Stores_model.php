<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Stores_model extends CI_Model
{
    /**
     * Get all stores for a client
     */
    public function get_all_by_client($client_id)
    {
        $this->db->select('stores.*, companies.name as company_name, companies.company_code');
        $this->db->from('stores');
        $this->db->join('companies', 'companies.id = stores.company_id', 'left');
        $this->db->where('stores.client_id', $client_id);
        $this->db->where('stores.deleted_at', NULL);
        $this->db->order_by('stores.id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get single store by ID and client
     */
    public function get_by_id($id, $client_id)
    {
        $this->db->select('stores.*, companies.name as company_name, companies.company_code');
        $this->db->from('stores');
        $this->db->join('companies', 'companies.id = stores.company_id', 'left');
        $this->db->where('stores.id', $id);
        $this->db->where('stores.client_id', $client_id);
        $this->db->where('stores.deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Check if store code exists (globally unique)
     */
    public function check_store_code_exists($store_code, $exclude_id = null)
    {
        $this->db->where('store_code', $store_code);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get('stores')->row();
    }

    /**
     * Get count of staff in a store
     */
    public function get_staff_count($store_id)
    {
        $this->db->where('store_id', $store_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('store_staff');
    }

    /**
     * Get store staff members
     */
    public function get_store_staff($store_id)
    {
        $this->db->select('*');
        $this->db->from('store_staff');
        $this->db->where('store_id', $store_id);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get usage summary
     */
    public function get_usage_summary($store_id)
    {
        return array(
            'staff' => $this->get_staff_count($store_id)
        );
    }

    /**
     * Soft delete all store staff
     */
    public function delete_store_staff($store_id)
    {
        $data = array('deleted_at' => date('Y-m-d H:i:s'));
        $this->db->where('store_id', $store_id);
        return $this->db->update('store_staff', $data);
    }

    /**
     * Get active stores count for a client
     */
    public function get_active_count($client_id)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('stores');
    }

    /**
     * Get companies dropdown for client
     */
    public function get_companies_dropdown($client_id)
    {
        $this->db->select('id, name, company_code');
        $this->db->from('companies');
        $this->db->where('client_id', $client_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Generate unique staff code for store
     */
    public function generate_staff_code($store_id, $store_code)
    {
        // Get the latest staff number for this store (excluding deleted)
        $this->db->select('staff_code');
        $this->db->from('store_staff');
        $this->db->where('store_id', $store_id);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get()->row();

        if ($last && preg_match('/(\d+)$/', $last->staff_code, $matches)) {
            $next_num = intval($matches[1]) + 1;
        } else {
            $next_num = 1;
        }

        return $store_code . '-STF-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get stores by company and allowed store types (for API)
     *
     * @param int   $company_id          Company ID
     * @param array $allowed_store_types Array of allowed store types (QSR, KOT, PREMEAL)
     * @return array List of stores
     */
    public function get_stores_by_permissions($company_id, $allowed_store_types)
    {
        $this->db->select('id, store_code, name, short_name, store_type, thumbnail,
                          primary_email, primary_phone, address_line1, city, state, is_operational');
        $this->db->from('stores');
        $this->db->where('company_id', $company_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        $this->db->where_in('store_type', $allowed_store_types);
        $this->db->order_by('name', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get store by store code
     *
     * @param string $store_code Store code
     * @return object|null Store record
     */
    public function get_store_by_code($store_code)
    {
        $this->db->select('id, store_code, name, short_name, store_type, company_id, thumbnail,
                          primary_email, primary_phone, address_line1, city, state, is_operational, is_active');
        $this->db->from('stores');
        $this->db->where('store_code', $store_code);
        $this->db->where('deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Check if staff email exists across entire client
     *
     * @param int         $client_id  Client ID
     * @param string      $email      Email to check
     * @param int|null    $exclude_id Staff ID to exclude (for updates)
     * @return object|null Staff record if exists, null otherwise
     */
    public function check_staff_email_exists($client_id, $email, $exclude_id = null)
    {
        $this->db->select('store_staff.id');
        $this->db->from('store_staff');
        $this->db->join('stores', 'stores.id = store_staff.store_id');
        $this->db->where('stores.client_id', $client_id);
        $this->db->where('store_staff.email', $email);
        $this->db->where('store_staff.deleted_at', NULL);

        if ($exclude_id) {
            $this->db->where('store_staff.id !=', $exclude_id);
        }

        return $this->db->get()->row();
    }

    /**
     * Get staff by ID with store ownership verification
     *
     * @param int $staff_id  Staff ID
     * @param int $client_id Client ID for verification
     * @return object|null Staff record with store info
     */
    public function get_staff_by_id($staff_id, $client_id)
    {
        $this->db->select('store_staff.*, stores.id as store_id, stores.name as store_name, stores.client_id');
        $this->db->from('store_staff');
        $this->db->join('stores', 'stores.id = store_staff.store_id');
        $this->db->where('store_staff.id', $staff_id);
        $this->db->where('stores.client_id', $client_id);
        $this->db->where('store_staff.deleted_at', NULL);

        return $this->db->get()->row();
    }

    /**
     * Get staff by ID for update verification
     *
     * @param int $staff_id Staff ID
     * @return object|null Staff record with client_id
     */
    public function get_staff_for_update($staff_id)
    {
        $this->db->select('store_staff.*, stores.client_id');
        $this->db->from('store_staff');
        $this->db->join('stores', 'stores.id = store_staff.store_id');
        $this->db->where('store_staff.id', $staff_id);
        $this->db->where('store_staff.deleted_at', NULL);

        return $this->db->get()->row();
    }
}
