<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Company_employees_model extends CI_Model
{
    /**
     * Get all employees for a company
     */
    public function get_all_by_company($company_id)
    {
        $this->db->select('employees.*, company_departments.name as department_name,
            COALESCE((SELECT SUM(t.amount) FROM transaction t WHERE t.user_id = employees.id AND t.transaction_type = 1), 0)
            - COALESCE((SELECT SUM(t.amount) FROM transaction t WHERE t.user_id = employees.id AND t.transaction_type = 2), 0)
            as wallet_balance', FALSE);
        $this->db->from('employees');
        $this->db->join('company_departments', 'company_departments.id = employees.department_id', 'left');
        $this->db->where('employees.company_id', $company_id);
        $this->db->where('employees.deleted_at', NULL);
        $this->db->order_by('employees.id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get single employee by ID
     */
    public function get_by_id($id, $company_id = null)
    {
        $this->db->select('employees.*, company_departments.name as department_name');
        $this->db->from('employees');
        $this->db->join('company_departments', 'company_departments.id = employees.department_id', 'left');
        $this->db->where('employees.id', $id);
        if ($company_id) {
            $this->db->where('employees.company_id', $company_id);
        }
        $this->db->where('employees.deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Check if employee code exists
     */
    public function check_code_exists($company_id, $code, $exclude_id = null)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('employee_code', $code);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get('employees')->row();
    }

    /**
     * Check if email exists
     */
    public function check_email_exists($company_id, $email, $exclude_id = null)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('email', $email);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get('employees')->row();
    }

    /**
     * Check if phone exists
     */
    public function check_phone_exists($company_id, $phone, $exclude_id = null)
    {
        if (empty($phone)) return null;

        $this->db->where('company_id', $company_id);
        $this->db->where('phone', $phone);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get('employees')->row();
    }

    /**
     * Get policies attached to company
     */
    public function get_company_policies($company_id)
    {
        $this->db->select('policies.id, policies.name, policies.policy_code, policies.policy_type, company_policies.is_default');
        $this->db->from('company_policies');
        $this->db->join('policies', 'policies.id = company_policies.policy_id', 'left');
        $this->db->where('company_policies.company_id', $company_id);
        $this->db->where('company_policies.is_active', 1);
        $this->db->where('policies.is_active', 1);
        $this->db->where('policies.deleted_at', NULL);
        $this->db->order_by('company_policies.is_default', 'DESC');
        $this->db->order_by('policies.name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get policies assigned to employee
     */
    public function get_employee_policies($employee_id)
    {
        $this->db->select('employee_policies.*, policies.name as policy_name, policies.policy_code, policies.policy_type');
        $this->db->from('employee_policies');
        $this->db->join('policies', 'policies.id = employee_policies.policy_id', 'left');
        $this->db->where('employee_policies.employee_id', $employee_id);
        $this->db->where('employee_policies.is_active', 1);
        return $this->db->get()->result();
    }

    /**
     * Get employee policy ID (single policy)
     */
    public function get_employee_policy_id($employee_id)
    {
        $this->db->select('policy_id');
        $this->db->from('employee_policies');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('is_active', 1);
        $this->db->limit(1);
        $result = $this->db->get()->row();
        return $result ? $result->policy_id : null;
    }

    /**
     * Save employee policy (single policy)
     */
    public function save_employee_policy($employee_id, $policy_id, $assigned_by = null)
    {
        // First, delete existing policy
        $this->db->where('employee_id', $employee_id);
        $this->db->delete('employee_policies');

        // Insert new policy if provided
        if (!empty($policy_id)) {
            $data = [
                'employee_id' => $employee_id,
                'policy_id' => $policy_id,
                'effective_from' => date('Y-m-d'),
                'is_active' => 1,
                'assigned_by' => $assigned_by,
                'created_at' => date('Y-m-d H:i:s')
            ];
            return $this->db->insert('employee_policies', $data);
        }
        return true;
    }

    /**
     * Get departments for dropdown
     */
    public function get_departments_dropdown($company_id)
    {
        $this->db->select('id, name, code');
        $this->db->from('company_departments');
        $this->db->where('company_id', $company_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get employee count by company
     */
    public function get_count($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('employees');
    }

    /**
     * Get active employee count
     */
    public function get_active_count($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('employees');
    }

    /**
     * Generate unique employee code
     */
    public function generate_employee_code($company_id, $company_code)
    {
        // Get the latest employee number
        $this->db->select('employee_code');
        $this->db->from('employees');
        $this->db->where('company_id', $company_id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get()->row();

        if ($last && preg_match('/(\d+)$/', $last->employee_code, $matches)) {
            $next_num = intval($matches[1]) + 1;
        } else {
            $next_num = 1;
        }

        return $company_code . '-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
    }


    /**
     * Get all employees count for a company
     */
    public function get_all_by_company_count($company_id)
    {
        $this->db->from('employees');
        $this->db->join(
            'company_departments',
            'company_departments.id = employees.department_id',
            'left'
        );
        $this->db->where('employees.company_id', $company_id);
        $this->db->where('employees.deleted_at IS NULL', null, false);

        return $this->db->count_all_results();
    }
}
