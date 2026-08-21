<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class CompanyPolicies_model extends CI_Model
{
    /**
     * Get all policies attached to a company
     */
    public function get_all_by_company($company_id)
    {
        $this->db->select('company_policies.*, policies.name as policy_name, policies.policy_code, policies.policy_type, policies.description as policy_description');
        $this->db->from('company_policies');
        $this->db->join('policies', 'policies.id = company_policies.policy_id', 'left');
        $this->db->where('company_policies.company_id', $company_id);
        $this->db->where('policies.deleted_at', NULL);
        $this->db->order_by('company_policies.priority', 'ASC');
        $this->db->order_by('company_policies.id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get single company policy by ID
     */
    public function get_by_id($id, $company_id = null)
    {
        $this->db->select('company_policies.*, policies.name as policy_name, policies.policy_code, policies.policy_type');
        $this->db->from('company_policies');
        $this->db->join('policies', 'policies.id = company_policies.policy_id', 'left');
        $this->db->where('company_policies.id', $id);
        if ($company_id) {
            $this->db->where('company_policies.company_id', $company_id);
        }
        return $this->db->get()->row();
    }

    /**
     * Check if policy is already attached to company
     */
    public function check_policy_attached($company_id, $policy_id, $exclude_id = null)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('policy_id', $policy_id);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get('company_policies')->row();
    }

    /**
     * Get available policies for a company (not yet attached)
     */
    public function get_available_policies($client_id, $company_id)
    {
        $this->db->select('policies.*');
        $this->db->from('policies');
        $this->db->where('policies.client_id', $client_id);
        $this->db->where('policies.is_active', 1);
        $this->db->where('policies.deleted_at', NULL);
        $this->db->where("policies.id NOT IN (SELECT policy_id FROM company_policies WHERE company_id = $company_id)", NULL, FALSE);
        $this->db->order_by('policies.name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get all policies for client (for dropdown)
     */
    public function get_all_client_policies($client_id)
    {
        $this->db->select('*');
        $this->db->from('policies');
        $this->db->where('client_id', $client_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get employee count using this company policy
     */
    public function get_employee_count($company_policy_id)
    {
        $cp = $this->get_by_id($company_policy_id);
        if (!$cp) return 0;

        $this->db->where('policy_id', $cp->policy_id);
        $this->db->where('employee_id IN (SELECT id FROM employees WHERE company_id = ' . $cp->company_id . ' AND deleted_at IS NULL)', NULL, FALSE);
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('employee_policies');
    }

    /**
     * Delete a company policy attachment
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('company_policies');
    }

    /**
     * Count employees assigned to a specific policy within a company
     */
    public function get_policy_employee_count($company_id, $policy_id)
    {
        return $this->db->from('employee_policies ep')
            ->join('employees e', 'e.id = ep.employee_id')
            ->where('ep.policy_id', $policy_id)
            ->where('ep.is_active', 1)
            ->where('e.company_id', $company_id)
            ->where('e.deleted_at', NULL)
            ->count_all_results();
    }
}
