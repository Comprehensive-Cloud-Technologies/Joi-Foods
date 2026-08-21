<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * StorePremeal Model
 *
 * Handles database operations for store-side PREMEAL order management.
 *
 * @category  Models
 * @package   Joy_Foods
 * @author    ZooBit Infotech
 * @version   1.0.0
 */
class StorePremeal_model extends CI_Model
{
    /**
     * Get pending PREMEAL primary orders for a store
     *
     * @param int         $store_id Store ID
     * @param int         $limit    Limit
     * @param int         $offset   Offset
     * @return array Orders
     */
    public function get_pending_orders($store_id, $limit = 20, $offset = 0)
    {
        return $this->db
            ->select('o.*, e.first_name as employee_first_name, e.last_name as employee_last_name,
                      e.phone as employee_phone, e.email as employee_email,
                      c.name as company_name')
            ->from('orders o')
            ->join('employees e', 'e.id = o.employee_id', 'left')
            ->join('companies c', 'c.id = o.company_id', 'left')
            ->where('o.store_id', $store_id)
            ->where('o.module', 'PREMEAL')
            ->where('o.is_primary_order', 1)
            ->where('o.status', 'PENDING')
            ->order_by('o.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    /**
     * Count pending PREMEAL primary orders for a store
     *
     * @param int $store_id Store ID
     * @return int Count
     */
    public function count_pending_orders($store_id)
    {
        return $this->db
            ->from('orders')
            ->where('store_id', $store_id)
            ->where('module', 'PREMEAL')
            ->where('is_primary_order', 1)
            ->where('status', 'PENDING')
            ->count_all_results();
    }

    /**
     * Get date range for a PREMEAL order (primary + children)
     *
     * @param int $primary_order_id Primary order ID
     * @return object|null Date range with min_date and max_date
     */
    public function get_order_date_range($primary_order_id)
    {
        return $this->db
            ->select('MIN(scheduled_date) as start_date, MAX(scheduled_date) as end_date, COUNT(*) as total_days')
            ->from('orders')
            ->group_start()
            ->where('id', $primary_order_id)
            ->or_where('parent_order_id', $primary_order_id)
            ->group_end()
            ->get()
            ->row();
    }

    /**
     * Get child orders count by status for a primary order
     *
     * @param int $primary_order_id Primary order ID
     * @return array Status counts
     */
    public function get_child_status_counts($primary_order_id)
    {
        $result = $this->db
            ->select('status, COUNT(*) as count')
            ->from('orders')
            ->group_start()
            ->where('id', $primary_order_id)
            ->or_where('parent_order_id', $primary_order_id)
            ->group_end()
            ->group_by('status')
            ->get()
            ->result();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row->status] = (int)$row->count;
        }
        return $counts;
    }

    /**
     * Get PREMEAL order by ID and store
     *
     * @param int $order_id Order ID
     * @param int $store_id Store ID
     * @return object|null Order or null
     */
    public function get_order_by_id($order_id, $store_id)
    {
        return $this->db
            ->select('o.*, e.first_name as employee_first_name, e.last_name as employee_last_name,
                      e.phone as employee_phone, e.email as employee_email,
                      c.name as company_name')
            ->from('orders o')
            ->join('employees e', 'e.id = o.employee_id', 'left')
            ->join('companies c', 'c.id = o.company_id', 'left')
            ->where('o.id', $order_id)
            ->where('o.store_id', $store_id)
            ->where('o.module', 'PREMEAL')
            ->get()
            ->row();
    }

    /**
     * Get all child orders for a primary order
     *
     * @param int $primary_order_id Primary order ID
     * @return array Child orders
     */
    public function get_child_orders($primary_order_id)
    {
        return $this->db
            ->select('*')
            ->from('orders')
            ->where('parent_order_id', $primary_order_id)
            ->order_by('scheduled_date', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get all orders (primary + children) for a PREMEAL booking
     *
     * @param int $primary_order_id Primary order ID
     * @return array All orders including primary
     */
    public function get_all_booking_orders($primary_order_id)
    {
        return $this->db
            ->select('*')
            ->from('orders')
            ->group_start()
            ->where('id', $primary_order_id)
            ->or_where('parent_order_id', $primary_order_id)
            ->group_end()
            ->order_by('scheduled_date', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get order items for an order
     *
     * @param int $order_id Order ID
     * @return array Order items
     */
    public function get_order_items($order_id)
    {
        return $this->db
            ->select('oi.*, p.thumbnail, p.is_vegetarian')
            ->from('order_items oi')
            ->join('products p', 'p.id = oi.product_id', 'left')
            ->where('oi.order_id', $order_id)
            ->get()
            ->result();
    }

    /**
     * Update status for primary order and all child orders.
     *
     * Only orders currently matching $from_status are updated. This prevents
     * approve/reject from resurrecting child orders the customer has already
     * cancelled (or that are otherwise no longer pending).
     *
     * @param int    $primary_order_id Primary order ID
     * @param string $new_status       New status
     * @param array  $additional_data  Additional fields to update
     * @param string $from_status      Only update orders currently in this status
     * @return bool Success
     */
    public function update_all_orders_status($primary_order_id, $new_status, $additional_data = [], $from_status = 'PENDING')
    {
        $update_data = [
            'status' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Add confirmed_at timestamp if confirming
        if ($new_status === 'CONFIRMED') {
            $update_data['confirmed_at'] = date('Y-m-d H:i:s');
        }

        // Merge additional data
        if (!empty($additional_data)) {
            $update_data = array_merge($update_data, $additional_data);
        }

        // Update primary order (only if it is still in the expected status)
        $this->db->where('id', $primary_order_id);
        if (!empty($from_status)) {
            $this->db->where('status', $from_status);
        }
        $this->db->update('orders', $update_data);

        // Update child orders (only those still in the expected status)
        $this->db->where('parent_order_id', $primary_order_id);
        if (!empty($from_status)) {
            $this->db->where('status', $from_status);
        }
        $this->db->update('orders', $update_data);

        return true;
    }

    /**
     * Get booking orders (primary + children) currently in a given status.
     *
     * @param int    $primary_order_id Primary order ID
     * @param string $status           Status to filter by
     * @return array Matching orders
     */
    public function get_booking_orders_by_status($primary_order_id, $status)
    {
        return $this->db
            ->select('*')
            ->from('orders')
            ->group_start()
            ->where('id', $primary_order_id)
            ->or_where('parent_order_id', $primary_order_id)
            ->group_end()
            ->where('status', $status)
            ->order_by('scheduled_date', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Add order status history
     *
     * @param array $data Status history data
     * @return int|bool Insert ID or false
     */
    public function add_status_history($data)
    {
        $this->db->insert('order_status_history', $data);
        return $this->db->insert_id();
    }

    /**
     * Get orders for a specific date and meal type
     *
     * @param int    $store_id  Store ID
     * @param string $date      Date (Y-m-d)
     * @param string $meal_type Meal type (BREAKFAST, LUNCH, DINNER)
     * @param array  $statuses  Array of status values to filter
     * @param int    $limit     Limit
     * @param int    $offset    Offset
     * @return array Orders
     */
    public function get_orders_by_date($store_id, $date, $meal_type, $statuses = [], $limit = 50, $offset = 0)
    {
        $this->db
            ->select('o.*, e.first_name as employee_first_name, e.last_name as employee_last_name,
                      e.phone as employee_phone, c.name as company_name')
            ->from('orders o')
            ->join('employees e', 'e.id = o.employee_id', 'left')
            ->join('companies c', 'c.id = o.company_id', 'left')
            ->where('o.store_id', $store_id)
            ->where('o.module', 'PREMEAL')
            ->where('o.scheduled_date', $date)
            ->where('o.meal_type', $meal_type);

        if (!empty($statuses)) {
            $this->db->where_in('o.status', $statuses);
        }

        return $this->db
            ->order_by('o.pickup_time', 'ASC')
            ->order_by('o.created_at', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    /**
     * Count orders for a specific date and meal type
     *
     * @param int    $store_id  Store ID
     * @param string $date      Date (Y-m-d)
     * @param string $meal_type Meal type
     * @param array  $statuses  Array of status values to filter
     * @return int Count
     */
    public function count_orders_by_date($store_id, $date, $meal_type, $statuses = [])
    {
        $this->db
            ->from('orders')
            ->where('store_id', $store_id)
            ->where('module', 'PREMEAL')
            ->where('scheduled_date', $date)
            ->where('meal_type', $meal_type);

        if (!empty($statuses)) {
            $this->db->where_in('status', $statuses);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get summary counts for a date (for dashboard)
     *
     * @param int    $store_id  Store ID
     * @param string $date      Date (Y-m-d)
     * @param string $meal_type Meal type
     * @return array Status counts
     */
    public function get_date_summary($store_id, $date, $meal_type)
    {
        $result = $this->db
            ->select('status, COUNT(*) as count')
            ->from('orders')
            ->where('store_id', $store_id)
            ->where('module', 'PREMEAL')
            ->where('scheduled_date', $date)
            ->where('meal_type', $meal_type)
            ->group_by('status')
            ->get()
            ->result();

        $counts = [
            'pending' => 0,
            'confirmed' => 0,
            'preparing' => 0,
            'ready' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'rejected' => 0,
            'total' => 0
        ];

        foreach ($result as $row) {
            $counts[strtolower($row->status)] = (int)$row->count;
            $counts['total'] += (int)$row->count;
        }

        return $counts;
    }

    /**
     * Update single order status
     *
     * @param int    $order_id        Order ID
     * @param string $new_status      New status
     * @param array  $additional_data Additional data
     * @return bool Success
     */
    public function update_order_status($order_id, $new_status, $additional_data = [])
    {
        $update_data = [
            'status' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($new_status === 'CONFIRMED') {
            $update_data['confirmed_at'] = date('Y-m-d H:i:s');
        }
        if ($new_status === 'PREPARING') {
            $update_data['preparing_started_at'] = date('Y-m-d H:i:s');
        }
        if ($new_status === 'READY') {
            $update_data['ready_at'] = date('Y-m-d H:i:s');
        }
        if ($new_status === 'COMPLETED') {
            $update_data['completed_at'] = date('Y-m-d H:i:s');
        }

        if (!empty($additional_data)) {
            $update_data = array_merge($update_data, $additional_data);
        }

        return $this->db->update('orders', $update_data, ['id' => $order_id]);
    }

    /**
     * Get total refundable employee contribution for a booking.
     *
     * Only sums orders still in the given status (default PENDING) — days the
     * customer already cancelled have been refunded separately and must not be
     * refunded again here.
     *
     * @param int    $primary_order_id Primary order ID
     * @param string $status           Only sum orders in this status
     * @return float Total employee contribution
     */
    public function get_total_employee_contribution($primary_order_id, $status = 'PENDING')
    {
        $this->db
            ->select('SUM(employee_contribution) as total')
            ->from('orders')
            ->group_start()
            ->where('id', $primary_order_id)
            ->or_where('parent_order_id', $primary_order_id)
            ->group_end();

        if (!empty($status)) {
            $this->db->where('status', $status);
        }

        $result = $this->db->get()->row();

        return $result ? (float)$result->total : 0;
    }
}
