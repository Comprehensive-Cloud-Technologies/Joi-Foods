<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Departments_model extends CI_Model
{
    /**
     * Get all departments for a company
     */
    public function get_all_by_company($company_id)
    {
        $this->db->select('*');
        $this->db->from('company_departments');
        $this->db->where('company_id', $company_id);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get single department by ID
     */
    public function get_by_id($id, $company_id = null)
    {
        $this->db->select('*');
        $this->db->from('company_departments');
        $this->db->where('id', $id);
        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Check if department code exists for this company
     */
    public function check_code_exists($company_id, $code, $exclude_id = null)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('code', $code);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get('company_departments')->row();
    }

    /**
     * Check if department name exists for this company
     */
    public function check_name_exists($company_id, $name, $exclude_id = null)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('name', $name);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get('company_departments')->row();
    }

    /**
     * Get employee count in department
     */
    public function get_employee_count($department_id)
    {
        $this->db->where('department_id', $department_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('employees');
    }

    /**
     * Get all departments with employee counts for a company
     */
    public function get_all_with_counts($company_id)
    {
        $this->db->select('company_departments.*, COUNT(employees.id) as employee_count');
        $this->db->from('company_departments');
        $this->db->join('employees', 'employees.department_id = company_departments.id AND employees.deleted_at IS NULL', 'left');
        $this->db->where('company_departments.company_id', $company_id);
        $this->db->where('company_departments.deleted_at', NULL);
        $this->db->group_by('company_departments.id');
        $this->db->order_by('company_departments.name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get active departments for dropdown
     */
    public function get_active_for_dropdown($company_id)
    {
        $this->db->select('id, name, code');
        $this->db->from('company_departments');
        $this->db->where('company_id', $company_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }
}
