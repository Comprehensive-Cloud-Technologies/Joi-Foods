<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reviews_model extends CI_Model
{
    /**
     * Check if a review already exists for an order
     *
     * @param int $order_id Order ID
     * @return bool
     */
    public function review_exists($order_id)
    {
        return $this->db->where('order_id', $order_id)->count_all_results('order_reviews') > 0;
    }

    /**
     * Create a new review
     *
     * @param array $data Review data
     * @return int Insert ID
     */
    public function create_review($data)
    {
        $this->db->insert('order_reviews', $data);
        return $this->db->insert_id();
    }

    /**
     * Mark order as reviewed
     *
     * @param int $order_id Order ID
     * @return bool
     */
    public function mark_order_reviewed($order_id)
    {
        $this->db->where('id', $order_id);
        return $this->db->update('orders', ['is_reviewed' => 1]);
    }

    /**
     * Get review by order ID
     *
     * @param int $order_id Order ID
     * @return object|null
     */
    public function get_review_by_order($order_id)
    {
        return $this->db->get_where('order_reviews', ['order_id' => $order_id])->row();
    }
}
