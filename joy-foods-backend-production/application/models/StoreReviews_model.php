<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * StoreReviews Model
 *
 * Handles database operations for store-side feedback/review reports.
 * Scoped to a single store_id for security.
 */
class StoreReviews_model extends CI_Model
{
    /**
     * Get reviews for a store with date filters
     */
    public function get_reviews($store_id, $filters = [])
    {
        $this->db->select('r.id, r.order_id, r.module, r.food_review, r.service_review, r.extra_comments, r.created_at,
                          o.order_number, o.total_amount, o.status as order_status,
                          o.is_guest_order, o.guest_name, o.guest_phone,
                          c.name as company_name,
                          e.first_name, e.last_name, e.employee_code');
        $this->db->from('order_reviews r');
        $this->db->join('orders o', 'o.id = r.order_id');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->join('employees e', 'e.id = r.employee_id', 'left');
        $this->db->where('r.store_id', $store_id);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(r.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(r.created_at) <=', $filters['date_to']);
        }

        $this->db->order_by('r.created_at', 'DESC');

        if (!empty($filters['limit'])) {
            $offset = !empty($filters['offset']) ? $filters['offset'] : 0;
            $this->db->limit($filters['limit'], $offset);
        }

        return $this->db->get()->result();
    }

    /**
     * Get total review count for pagination
     */
    public function get_reviews_count($store_id, $filters = [])
    {
        $this->db->from('order_reviews r');
        $this->db->where('r.store_id', $store_id);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(r.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(r.created_at) <=', $filters['date_to']);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get review summary stats for a store
     */
    public function get_reviews_summary($store_id, $filters = [])
    {
        $this->db->select('COUNT(*) as total_reviews');
        $this->db->from('order_reviews r');
        $this->db->where('r.store_id', $store_id);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(r.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(r.created_at) <=', $filters['date_to']);
        }

        return $this->db->get()->row();
    }

    /**
     * Get single review detail
     */
    public function get_review_detail($review_id, $store_id)
    {
        $this->db->select('r.*, o.order_number, o.total_amount, o.status as order_status, o.module as order_module,
                          o.created_at as order_date, o.is_guest_order, o.guest_name, o.guest_phone,
                          c.name as company_name, c.company_code,
                          e.first_name, e.last_name, e.employee_code, e.email as employee_email, e.phone as employee_phone');
        $this->db->from('order_reviews r');
        $this->db->join('orders o', 'o.id = r.order_id');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->join('employees e', 'e.id = r.employee_id', 'left');
        $this->db->where('r.id', $review_id);
        $this->db->where('r.store_id', $store_id);
        return $this->db->get()->row();
    }
}
