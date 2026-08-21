<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * MyOrders Model
 *
 * Handles database operations for employee order history.
 *
 * @category  Models
 * @package   Joy_Foods
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-08
 */
class MyOrders_model extends CI_Model
{
    /**
     * Get orders list with pagination
     *
     * @param int    $employee_id Employee ID
     * @param int    $limit       Number of orders per page
     * @param int    $offset      Offset for pagination
     * @return array List of orders with items
     */
    public function get_orders_list($employee_id, $limit = 20, $offset = 0)
    {
        $this->db->select('o.id, o.order_number, o.module, o.status, o.total_amount,
                          o.scheduled_date, o.meal_type, o.pickup_time, o.pickup_code,
                          o.is_scheduled, o.company_contribution, o.employee_contribution,
                          o.delivery_location_id,
                          o.is_primary_order, o.parent_order_id, o.created_at,
                          s.name as store_name, s.short_name as store_short_name,
                          s.address_line1, s.city,
                          dl.name as delivery_location_name');
        $this->db->from('orders o');
        $this->db->join('stores s', 's.id = o.store_id', 'left');
        $this->db->join('delivery_locations dl', 'dl.id = o.delivery_location_id', 'left');
        $this->db->where('o.employee_id', $employee_id);

        // Exclude PREMEAL child orders (only show primary/parent orders)
        $this->db->where("(o.module != 'PREMEAL' OR o.is_primary_order = 1)", NULL, FALSE);

        $this->db->order_by('o.created_at', 'DESC');
        $this->db->limit($limit, $offset);

        $orders = $this->db->get()->result();

        // Get items for each order
        foreach ($orders as &$order) {
            $order->items = $this->get_order_items($order->id);
        }

        return $orders;
    }

    /**
     * Get total count of orders
     *
     * @param int    $employee_id Employee ID
     * @return int Count of orders
     */
    public function get_orders_count($employee_id)
    {
        $this->db->where('employee_id', $employee_id);
        // Exclude PREMEAL child orders
        $this->db->where("(module != 'PREMEAL' OR is_primary_order = 1)", NULL, FALSE);

        return $this->db->count_all_results('orders');
    }

    /**
     * Get order items by order ID
     *
     * @param int $order_id Order ID
     * @return array List of order items
     */
    public function get_order_items($order_id)
    {
        $this->db->select('oi.id, oi.product_id, oi.quantity, oi.unit_price, oi.total_amount,p.is_vegetarian,
                          oi.product_name, p.short_name, p.thumbnail');
        $this->db->from('order_items oi');
        $this->db->join('products p', 'p.id = oi.product_id', 'left');
        $this->db->where('oi.order_id', $order_id);

        return $this->db->get()->result();
    }

    /**
     * Get order details by ID
     *
     * @param int $order_id    Order ID
     * @param int $employee_id Employee ID (for validation)
     * @return object|null Order object or null
     */
    public function get_order_details($order_id, $employee_id)
    {
        $this->db->select('o.*, s.name as store_name, s.short_name as store_short_name,
                          s.address_line1, s.city, s.primary_phone as store_phone,
                          s.breakfast_time, s.lunch_time, s.dinner_time,
                          dl.name as delivery_location_name, dl.short_name as delivery_location_short_name,
                          dl.floor as delivery_location_floor, dl.building as delivery_location_building,
                          cd.name as department_name, cd.code as department_code,
                          pol.name as policy_name, pol.policy_type');
        $this->db->from('orders o');
        $this->db->join('stores s', 's.id = o.store_id', 'left');
        $this->db->join('delivery_locations dl', 'dl.id = o.delivery_location_id', 'left');
        $this->db->join('company_departments cd', 'cd.id = o.department_id', 'left');
        $this->db->join('policies pol', 'pol.id = o.policy_id', 'left');
        $this->db->where('o.id', $order_id);
        $this->db->where('o.employee_id', $employee_id);

        $order = $this->db->get()->row();

        if ($order) {
            // Get order items
            $order->items = $this->get_order_items_detailed($order_id);

            // For PREMEAL primary orders, get child orders (scheduled dates)
            if ($order->module == 'PREMEAL' && $order->is_primary_order == 1) {
                $order->scheduled_orders = $this->get_child_orders($order_id);
            }
        }

        return $order;
    }

    /**
     * Get detailed order items with product info
     *
     * @param int $order_id Order ID
     * @return array List of order items with details
     */
    public function get_order_items_detailed($order_id)
    {
        $this->db->select('oi.id, oi.product_id, oi.quantity, oi.unit_price, oi.base_price,
                          oi.tax_amount, oi.total_amount, oi.note,
                          oi.product_name, p.short_name, p.thumbnail, p.is_vegetarian');
        $this->db->from('order_items oi');
        $this->db->join('products p', 'p.id = oi.product_id', 'left');
        $this->db->where('oi.order_id', $order_id);

        return $this->db->get()->result();
    }

    /**
     * Get child orders for a PREMEAL primary order
     *
     * @param int $parent_order_id Parent order ID
     * @return array List of child orders
     */
    public function get_child_orders($parent_order_id)
    {
        $this->db->select('id, order_number, status, scheduled_date, meal_type,
                          pickup_time, pickup_code, total_amount,
                          company_contribution, employee_contribution,
                          wallet_deducted, discount_amount, created_at');
        $this->db->from('orders');
        $this->db->where('parent_order_id', $parent_order_id);
        $this->db->order_by('scheduled_date', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Get single order by ID (for cancellation)
     *
     * @param int $order_id    Order ID
     * @param int $employee_id Employee ID
     * @return object|null Order object or null
     */
    public function get_order_by_id($order_id, $employee_id)
    {
        $this->db->select('o.*, s.breakfast_time, s.lunch_time, s.dinner_time');
        $this->db->from('orders o');
        $this->db->join('stores s', 's.id = o.store_id', 'left');
        $this->db->where('o.id', $order_id);
        $this->db->where('o.employee_id', $employee_id);

        return $this->db->get()->row();
    }

    /**
     * Get employee's policy for cancellation rules
     *
     * @param int $employee_id Employee ID
     * @return object|null Policy object or null
     */
    public function get_employee_policy($employee_id)
    {
        $this->db->select('p.*');
        $this->db->from('employee_policies ep');
        $this->db->join('policies p', 'p.id = ep.policy_id', 'left');
        $this->db->where('ep.employee_id', $employee_id);
        $this->db->where('ep.is_active', 1);
        $this->db->where('ep.effective_from <=', date('Y-m-d'));
        $this->db->group_start();
        $this->db->where('ep.effective_until IS NULL', NULL, FALSE);
        $this->db->or_where('ep.effective_until >=', date('Y-m-d'));
        $this->db->group_end();
        $this->db->order_by('ep.priority', 'DESC');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    /**
     * Cancel order
     *
     * @param int    $order_id Order ID
     * @param string $reason   Cancellation reason
     * @return bool True on success
     */
    public function cancel_order($order_id, $reason = null)
    {
        $data = [
            'status' => 'CANCELLED',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'cancellation_reason' => $reason,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $order_id);
        return $this->db->update('orders', $data);
    }

    /**
     * Add order payment record (refund credits for employee cancellations)
     *
     * @param array $data Payment data
     * @return int Insert ID
     */
    public function add_order_payment($data)
    {
        $this->db->insert('order_payments', $data);
        return $this->db->insert_id();
    }

    /**
     * Log refund to refunds table
     *
     * @param array $data Refund data
     * @return int Insert ID
     */
    public function log_refund($data)
    {
        $this->db->insert('refunds', $data);
        return $this->db->insert_id();
    }

    /**
     * Promote a new primary order for a PREMEAL booking.
     *
     * Called when the current primary order of a multi-day PREMEAL booking is
     * cancelled. Without this, the remaining child days would be orphaned —
     * the store's pending list keys off `is_primary_order = 1`, so the booking
     * would vanish from their queue.
     *
     * Picks the earliest still-active day (not CANCELLED/REJECTED) as the new
     * primary, re-points every other day in the booking to it, and demotes the
     * old (now cancelled) primary into a normal child of the new primary so it
     * still shows in the booking history.
     *
     * @param int $old_primary_id The cancelled primary order ID
     * @return int|false New primary order ID, or false if no day left to promote
     */
    public function promote_premeal_primary($old_primary_id)
    {
        // All orders in this booking: the old primary + its children
        $booking_orders = $this->db
            ->select('id, status, scheduled_date')
            ->from('orders')
            ->group_start()
            ->where('id', $old_primary_id)
            ->or_where('parent_order_id', $old_primary_id)
            ->group_end()
            ->order_by('scheduled_date', 'ASC')
            ->order_by('id', 'ASC')
            ->get()
            ->result();

        // Find the earliest non-terminal day (excluding the old primary itself)
        $terminal = ['CANCELLED', 'REJECTED'];
        $new_primary = null;
        foreach ($booking_orders as $o) {
            if ((int)$o->id === (int)$old_primary_id) {
                continue;
            }
            if (!in_array($o->status, $terminal)) {
                $new_primary = $o;
                break;
            }
        }

        // No active day left — nothing to promote, booking is effectively over
        if (!$new_primary) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        // Promote the chosen day to primary
        $this->db->update('orders', [
            'is_primary_order' => 1,
            'parent_order_id'  => null,
            'updated_at'       => $now
        ], ['id' => $new_primary->id]);

        // Re-point every other day in the booking (incl. the old cancelled
        // primary) to the new primary so the whole booking stays linked.
        foreach ($booking_orders as $o) {
            if ((int)$o->id === (int)$new_primary->id) {
                continue;
            }
            $this->db->update('orders', [
                'is_primary_order' => 0,
                'parent_order_id'  => $new_primary->id,
                'updated_at'       => $now
            ], ['id' => $o->id]);
        }

        return (int)$new_primary->id;
    }

    /**
     * Add order status history entry
     *
     * @param array $history_data Status history data
     * @return int Insert ID
     */
    public function add_status_history($history_data)
    {
        $this->db->insert('order_status_history', $history_data);
        return $this->db->insert_id();
    }

    /**
     * Restore stock for a product in a store
     *
     * Only restores if stock is tracked (not NULL / unlimited).
     *
     * @param int $store_id   Store ID
     * @param int $product_id Product ID
     * @param int $quantity   Quantity to restore
     * @return bool True on success
     */
    public function restore_stock($store_id, $product_id, $quantity, $context = [])
    {
        $store_product = $this->db
            ->select('available_stock')
            ->from('store_products')
            ->where('store_id', $store_id)
            ->where('product_id', $product_id)
            ->get()
            ->row();

        if ($store_product && $store_product->available_stock !== null) {
            $old_stock = (int)$store_product->available_stock;
            $new_stock = $old_stock + (int)$quantity;

            $updated = $this->db
                ->set('available_stock', 'available_stock + ' . (int)$quantity, FALSE)
                ->where('store_id', $store_id)
                ->where('product_id', $product_id)
                ->update('store_products');

            if ($updated) {
                record_stock_transaction(array_merge([
                    'store_id'         => $store_id,
                    'product_id'       => $product_id,
                    'transaction_type' => 'IN',
                    'source'           => 'ORDER_CANCELLED',
                    'quantity'         => $quantity,
                    'stock_before'     => $old_stock,
                    'stock_after'      => $new_stock,
                    'reference_type'   => 'ORDER',
                    'note'             => 'Stock restored on order cancellation'
                ], $context));
            }

            return $updated;
        }

        return true;
    }
}
