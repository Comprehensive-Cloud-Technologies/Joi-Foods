<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Banners_model extends CI_Model
{
    /**
     * Get all banners for a client with company name
     */
    public function get_all_by_client($client_id)
    {
        $this->db->select('banners.*, companies.name as company_name');
        $this->db->from('banners');
        $this->db->join('companies', 'companies.id = banners.company_id', 'left');
        $this->db->where('banners.client_id', $client_id);
        $this->db->where('banners.deleted_at', NULL);
        $this->db->order_by('banners.display_order', 'ASC');
        $this->db->order_by('banners.id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get single banner by ID and client
     */
    public function get_by_id($id, $client_id)
    {
        $this->db->select('*');
        $this->db->from('banners');
        $this->db->where('id', $id);
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Get active banners for a company (for API/frontend)
     */
    public function get_active_banners_by_company($company_id)
    {
        $this->db->select('*');
        $this->db->from('banners');
        $this->db->where('company_id', $company_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('display_order', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get banner count by client
     */
    public function get_count_by_client($client_id)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('banners');
    }

    /**
     * Get active banner count by client
     */
    public function get_active_count($client_id)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('banners');
    }

    /**
     * Get banner count by company
     */
    public function get_count_by_company($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('banners');
    }
}
