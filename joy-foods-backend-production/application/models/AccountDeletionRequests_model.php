<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class AccountDeletionRequests_model extends CI_Model
{
    /**
     * Resolve a company (and its client) by company_code
     *
     * @param string $company_code
     * @return object|null Row with id, client_id, name or null
     */
    public function get_company_by_code($company_code)
    {
        $this->db->select('id, client_id, name');
        $this->db->from('companies');
        $this->db->where('company_code', $company_code);
        $this->db->where('deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Find an employee by email within a company
     *
     * @param int    $company_id
     * @param string $email
     * @return object|null
     */
    public function get_employee_by_email($company_id, $email)
    {
        $this->db->select('id, first_name, last_name, employee_code');
        $this->db->from('employees');
        $this->db->where('company_id', $company_id);
        $this->db->where('email', $email);
        $this->db->where('deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Insert a deletion request
     *
     * @param array $data
     * @return int Insert ID
     */
    public function insert_request($data)
    {
        $this->db->insert('account_deletion_requests', $data);
        return $this->db->insert_id();
    }

    /**
     * Get all requests for a client with filters
     */
    public function get_all_by_client($client_id, $filters = [])
    {
        $this->db->select('adr.*, c.name as company_name, e.first_name, e.last_name, e.employee_code, e.phone as employee_phone');
        $this->db->from('account_deletion_requests adr');
        $this->db->join('companies c', 'c.id = adr.company_id', 'left');
        $this->db->join('employees e', 'e.id = adr.employee_id', 'left');
        $this->db->where('adr.client_id', $client_id);
        $this->db->where('adr.deleted_at', NULL);

        if (!empty($filters['company_id'])) {
            $this->db->where('adr.company_id', $filters['company_id']);
        }

        if (!empty($filters['status'])) {
            $this->db->where('adr.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(adr.created_at) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(adr.created_at) <=', $filters['date_to']);
        }

        $this->db->order_by('adr.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get single request by ID and client
     */
    public function get_by_id($id, $client_id)
    {
        $this->db->select('adr.*, c.name as company_name, e.first_name, e.last_name, e.employee_code, e.email as employee_email, e.phone as employee_phone');
        $this->db->from('account_deletion_requests adr');
        $this->db->join('companies c', 'c.id = adr.company_id', 'left');
        $this->db->join('employees e', 'e.id = adr.employee_id', 'left');
        $this->db->where('adr.id', $id);
        $this->db->where('adr.client_id', $client_id);
        $this->db->where('adr.deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Update request status (when a client processes a request)
     */
    public function update_status($id, $client_id, $data)
    {
        $this->db->where('id', $id);
        $this->db->where('client_id', $client_id);
        return $this->db->update('account_deletion_requests', $data);
    }
}
