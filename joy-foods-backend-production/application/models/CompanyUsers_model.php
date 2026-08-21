<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class CompanyUsers_model extends CI_Model
{
    /**
     * Get all users for a company
     */
    public function get_all_by_company($company_id)
    {
        $this->db->select('*');
        $this->db->from('company_users');
        $this->db->where('company_id', $company_id);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('first_name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get single user by ID
     */
    public function get_by_id($id, $company_id = null)
    {
        $this->db->select('*');
        $this->db->from('company_users');
        $this->db->where('id', $id);
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Check if email exists for this company
     */
    public function check_email_exists($company_id, $email, $exclude_id = null)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('email', $email);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get('company_users')->row();
    }

    /**
     * Get user count for a company
     */
    public function get_user_count($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('company_users');
    }

    /**
     * Get active users for dropdown
     */
    public function get_active_for_dropdown($company_id)
    {
        $this->db->select('id, first_name, last_name, email');
        $this->db->from('company_users');
        $this->db->where('company_id', $company_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('first_name', 'ASC');
        return $this->db->get()->result();
    }
}
