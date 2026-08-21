<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Policies_model extends CI_Model
{
    /**
     * Get all policies for a client
     */
    public function get_all_by_client($client_id)
    {
        $this->db->select('*');
        $this->db->from('policies');
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get single policy by ID and client
     */
    public function get_by_id($id, $client_id)
    {
        $this->db->select('*');
        $this->db->from('policies');
        $this->db->where('id', $id);
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Check if policy code exists for this client
     */
    public function check_policy_code_exists($client_id, $policy_code, $exclude_id = null)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('policy_code', $policy_code);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get('policies')->row();
    }

    /**
     * Get count of companies using this policy
     */
    public function get_company_usage_count($policy_id)
    {
        $this->db->where('policy_id', $policy_id);
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('company_policies');
    }

    /**
     * Get count of employees using this policy
     */
    public function get_employee_usage_count($policy_id)
    {
        $this->db->where('policy_id', $policy_id);
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('employee_policies');
    }

    /**
     * Get companies attached to this policy
     */
    public function get_attached_companies($policy_id)
    {
        $this->db->select('companies.id, companies.name, companies.company_code, company_policies.id as cp_id');
        $this->db->from('company_policies');
        $this->db->join('companies', 'companies.id = company_policies.company_id', 'left');
        $this->db->where('company_policies.policy_id', $policy_id);
        $this->db->where('company_policies.is_active', 1);
        $this->db->where('companies.deleted_at', NULL);
        return $this->db->get()->result();
    }

    /**
     * Get employees attached to this policy
     */
    public function get_attached_employees($policy_id)
    {
        $this->db->select('employees.id, employees.first_name, employees.last_name, employees.employee_code, employee_policies.id as ep_id');
        $this->db->from('employee_policies');
        $this->db->join('employees', 'employees.id = employee_policies.employee_id', 'left');
        $this->db->where('employee_policies.policy_id', $policy_id);
        $this->db->where('employee_policies.is_active', 1);
        $this->db->where('employees.deleted_at', NULL);
        return $this->db->get()->result();
    }

    /**
     * Detach policy from all companies
     */
    public function detach_from_companies($policy_id)
    {
        $this->db->where('policy_id', $policy_id);
        return $this->db->delete('company_policies');
    }

    /**
     * Detach policy from all employees
     */
    public function detach_from_employees($policy_id)
    {
        $this->db->where('policy_id', $policy_id);
        return $this->db->delete('employee_policies');
    }

    /**
     * Get policy usage summary
     */
    public function get_usage_summary($policy_id)
    {
        return array(
            'companies' => $this->get_company_usage_count($policy_id),
            'employees' => $this->get_employee_usage_count($policy_id)
        );
    }
}
