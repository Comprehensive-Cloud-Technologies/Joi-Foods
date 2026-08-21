<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class SupportInquiries_model extends CI_Model
{
    /**
     * Get all inquiries for a client with filters
     */
    public function get_all_by_client($client_id, $filters = [])
    {
        $this->db->select('si.*, c.name as company_name, e.first_name, e.last_name, e.email as employee_email, e.phone as employee_phone');
        $this->db->from('support_inquiries si');
        $this->db->join('companies c', 'c.id = si.company_id', 'left');
        $this->db->join('employees e', 'e.id = si.employee_id', 'left');
        $this->db->where('si.client_id', $client_id);
        $this->db->where('si.deleted_at', NULL);

        // Apply filters
        if (!empty($filters['company_id'])) {
            $this->db->where('si.company_id', $filters['company_id']);
        }

        if (!empty($filters['topic'])) {
            $this->db->where('si.topic', $filters['topic']);
        }

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(si.created_at) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(si.created_at) <=', $filters['date_to']);
        }

        $this->db->order_by('si.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get single inquiry by ID and client
     */
    public function get_by_id($id, $client_id)
    {
        $this->db->select('si.*, c.name as company_name, e.first_name, e.last_name, e.email as employee_email, e.phone as employee_phone, e.employee_code');
        $this->db->from('support_inquiries si');
        $this->db->join('companies c', 'c.id = si.company_id', 'left');
        $this->db->join('employees e', 'e.id = si.employee_id', 'left');
        $this->db->where('si.id', $id);
        $this->db->where('si.client_id', $client_id);
        $this->db->where('si.deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Get inquiry count by client
     */
    public function get_count_by_client($client_id, $filters = [])
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);

        if (!empty($filters['company_id'])) {
            $this->db->where('company_id', $filters['company_id']);
        }

        if (!empty($filters['topic'])) {
            $this->db->where('topic', $filters['topic']);
        }

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(created_at) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(created_at) <=', $filters['date_to']);
        }

        return $this->db->count_all_results('support_inquiries');
    }

    /**
     * Get distinct topics for filter dropdown
     */
    public function get_topics_by_client($client_id)
    {
        $this->db->distinct();
        $this->db->select('topic');
        $this->db->from('support_inquiries');
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('topic', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get count by topic
     */
    public function get_count_by_topic($client_id, $topic, $filters = [])
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('topic', $topic);
        $this->db->where('deleted_at', NULL);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(created_at) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(created_at) <=', $filters['date_to']);
        }

        return $this->db->count_all_results('support_inquiries');
    }
}
