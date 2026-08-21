<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Companies_model extends CI_Model
{
    /**
     * Get all companies for a client
     */
    public function get_all_by_client($client_id)
    {
        $this->db->select('*');
        $this->db->from('companies');
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get single company by ID and client
     */
    public function get_by_id($id, $client_id)
    {
        $this->db->select('*');
        $this->db->from('companies');
        $this->db->where('id', $id);
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Check if company code exists (globally unique)
     */
    public function check_company_code_exists($company_code, $exclude_id = null)
    {
        $this->db->where('company_code', $company_code);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get('companies')->row();
    }

    /**
     * Get count of departments in a company
     */
    public function get_department_count($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('company_departments');
    }

    /**
     * Get count of employees in a company
     */
    public function get_employee_count($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('employees');
    }

    /**
     * Get count of company users
     */
    public function get_user_count($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('company_users');
    }

    /**
     * Get count of policies attached to company
     */
    public function get_policy_count($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('company_policies');
    }

    /**
     * Get policies attached to this company
     */
    public function get_attached_policies($company_id)
    {
        $this->db->select('policies.id, policies.name, policies.policy_code, policies.policy_type, company_policies.is_default, company_policies.id as cp_id');
        $this->db->from('company_policies');
        $this->db->join('policies', 'policies.id = company_policies.policy_id', 'left');
        $this->db->where('company_policies.company_id', $company_id);
        $this->db->where('company_policies.is_active', 1);
        $this->db->where('policies.deleted_at', NULL);
        return $this->db->get()->result();
    }

    /**
     * Get departments of a company
     */
    public function get_departments($company_id)
    {
        $this->db->select('*');
        $this->db->from('company_departments');
        $this->db->where('company_id', $company_id);
        $this->db->where('is_active', 1);
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get company users
     */
    public function get_company_users($company_id)
    {
        $this->db->select('*');
        $this->db->from('company_users');
        $this->db->where('company_id', $company_id);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get count of stores linked to company
     */
    public function get_store_count($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('stores');
    }

    /**
     * Get count of orders for company
     */
    public function get_order_count($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where_not_in('status', array('CANCELLED', 'REJECTED'));
        return $this->db->count_all_results('orders');
    }

    /**
     * Get usage summary
     */
    public function get_usage_summary($company_id)
    {
        return array(
            'departments' => $this->get_department_count($company_id),
            'employees' => $this->get_employee_count($company_id),
            'users' => $this->get_user_count($company_id),
            'policies' => $this->get_policy_count($company_id),
            'stores' => $this->get_store_count($company_id),
            'orders' => $this->get_order_count($company_id)
        );
    }

    /**
     * Delete all departments of a company
     */
    public function delete_departments($company_id)
    {
        $this->db->where('company_id', $company_id);
        return $this->db->delete('company_departments');
    }

    /**
     * Detach all policies from company
     */
    public function detach_policies($company_id)
    {
        $this->db->where('company_id', $company_id);
        return $this->db->delete('company_policies');
    }

    /**
     * Soft delete all company users
     */
    public function delete_company_users($company_id)
    {
        $data = array('deleted_at' => date('Y-m-d H:i:s'));
        $this->db->where('company_id', $company_id);
        return $this->db->update('company_users', $data);
    }

    /**
     * Soft delete all employees of a company
     */
    public function delete_employees($company_id)
    {
        $data = array('deleted_at' => date('Y-m-d H:i:s'));
        $this->db->where('company_id', $company_id);
        return $this->db->update('employees', $data);
    }

    /**
     * Get active companies count for a client
     */
    public function get_active_count($client_id)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('companies');
    }
}
