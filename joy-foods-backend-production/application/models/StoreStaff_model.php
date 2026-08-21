<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * StoreStaff Model
 *
 * Handles database operations for store staff authentication and management.
 *
 * @category  Models
 * @package   Joy_Foods
 * @author    ZooBit Infotech
 * @version   1.0.0
 */
class StoreStaff_model extends CI_Model
{
    /**
     * Get staff by email and store ID for login
     *
     * @param int    $store_id Store ID
     * @param string $email    Staff email
     * @return object|null Staff record or null
     */
    public function get_staff_for_login($store_id, $email)
    {
        return $this->db
            ->select('*')
            ->from('store_staff')
            ->where('store_id', $store_id)
            ->where('email', $email)
            ->where('deleted_at', NULL)
            ->get()
            ->row();
    }

    /**
     * Get staff by ID
     *
     * @param int $staff_id Staff ID
     * @return object|null Staff record or null
     */
    public function get_by_id($staff_id)
    {
        return $this->db
            ->select('*')
            ->from('store_staff')
            ->where('id', $staff_id)
            ->where('deleted_at', NULL)
            ->get()
            ->row();
    }

    /**
     * Get active staff by ID
     *
     * @param int $staff_id Staff ID
     * @return object|null Staff record or null
     */
    public function get_active_by_id($staff_id)
    {
        return $this->db
            ->select('*')
            ->from('store_staff')
            ->where('id', $staff_id)
            ->where('is_active', 1)
            ->where('deleted_at', NULL)
            ->get()
            ->row();
    }

    /**
     * Get staff password hash for verification
     *
     * @param int $staff_id Staff ID
     * @return object|null Staff record with id and password_hash
     */
    public function get_password_hash($staff_id)
    {
        return $this->db
            ->select('id, password_hash')
            ->from('store_staff')
            ->where('id', $staff_id)
            ->where('is_active', 1)
            ->where('deleted_at', NULL)
            ->get()
            ->row();
    }

    /**
     * Update last login timestamp and IP
     *
     * @param int    $staff_id  Staff ID
     * @param string $ip        IP address
     * @return bool
     */
    public function update_last_login($staff_id, $ip)
    {
        return $this->db->update('store_staff', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip
        ], ['id' => $staff_id]);
    }

    /**
     * Update staff password
     *
     * @param int    $staff_id      Staff ID
     * @param string $password_hash Hashed password
     * @return bool
     */
    public function update_password($staff_id, $password_hash)
    {
        return $this->db->update('store_staff', [
            'password_hash' => $password_hash,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $staff_id]);
    }

    /**
     * Get store by store code
     *
     * @param string $store_code Store code
     * @return object|null Store record or null
     */
    public function get_store_by_code($store_code)
    {
        return $this->db
            ->select('*')
            ->from('stores')
            ->where('store_code', $store_code)
            ->where('deleted_at', NULL)
            ->get()
            ->row();
    }

    /**
     * Get active store by ID
     *
     * @param int $store_id Store ID
     * @return object|null Store record or null
     */
    public function get_store_by_id($store_id)
    {
        return $this->db
            ->select('*')
            ->from('stores')
            ->where('id', $store_id)
            ->where('deleted_at', NULL)
            ->get()
            ->row();
    }
}
