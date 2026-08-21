<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Coupons_model extends CI_Model
{
    /**
     * Get all coupons for a client
     *
     * @param int $client_id Client ID
     * @return array List of coupons
     */
    public function get_all_by_client($client_id)
    {
        $this->db->select('coupons.*, companies.name as company_name');
        $this->db->from('coupons');
        $this->db->join('companies', 'companies.id = coupons.company_id', 'left');
        $this->db->where('coupons.client_id', $client_id);
        $this->db->where('coupons.deleted_at', NULL);
        $this->db->order_by('coupons.id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get single coupon by ID and client
     *
     * @param int $id        Coupon ID
     * @param int $client_id Client ID
     * @return object|null Coupon record
     */
    public function get_by_id($id, $client_id)
    {
        $this->db->select('coupons.*, companies.name as company_name');
        $this->db->from('coupons');
        $this->db->join('companies', 'companies.id = coupons.company_id', 'left');
        $this->db->where('coupons.id', $id);
        $this->db->where('coupons.client_id', $client_id);
        $this->db->where('coupons.deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Check if coupon code exists for a client
     *
     * @param int         $client_id  Client ID
     * @param string      $code       Coupon code
     * @param int|null    $exclude_id Coupon ID to exclude (for updates)
     * @return object|null Coupon record if exists
     */
    public function check_code_exists($client_id, $code, $exclude_id = null)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('code', $code);
        $this->db->where('deleted_at', NULL);

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        return $this->db->get('coupons')->row();
    }

    /**
     * Create a new coupon
     *
     * @param array $data Coupon data
     * @return int|bool Insert ID or false
     */
    public function create($data)
    {
        $this->db->insert('coupons', $data);
        return $this->db->insert_id();
    }

    /**
     * Update coupon
     *
     * @param int   $id   Coupon ID
     * @param array $data Coupon data
     * @return bool Success status
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('coupons', $data);
    }

    /**
     * Soft delete coupon
     *
     * @param int $id Coupon ID
     * @return bool Success status
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->update('coupons', ['deleted_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Get companies dropdown for client
     *
     * @param int $client_id Client ID
     * @return array List of companies
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
     * Get coupon usage count
     *
     * @param int $coupon_id Coupon ID
     * @return int Usage count
     */
    public function get_usage_count($coupon_id)
    {
        $this->db->where('coupon_id', $coupon_id);
        return $this->db->count_all_results('coupon_usage');
    }

    /**
     * Get coupon stats for a client
     *
     * @param int $client_id Client ID
     * @return array Stats
     */
    public function get_stats($client_id)
    {
        $coupons = $this->get_all_by_client($client_id);

        $stats = [
            'total' => count($coupons),
            'active' => 0,
            'expired' => 0,
            'percentage' => 0,
            'fixed' => 0
        ];

        $now = date('Y-m-d H:i:s');

        foreach ($coupons as $coupon) {
            if ($coupon->is_active && ($coupon->valid_until === null || $coupon->valid_until >= $now)) {
                $stats['active']++;
            } else {
                $stats['expired']++;
            }

            if ($coupon->discount_type == 'PERCENTAGE') {
                $stats['percentage']++;
            } else {
                $stats['fixed']++;
            }
        }

        return $stats;
    }
}
