<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Orders Model
 *
 * Handles database operations for orders including creation,
 * status updates, and order retrieval.
 *
 * @category  Models
 * @package   Joy_Foods
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-03
 */
class Orders_model extends CI_Model
{
    /**
     * Generate unique order number
     *
     * Format: {MODULE}-{YYYYMMDD}-{SEQUENCE}
     * Example: QSR-20260103-0001
     *
     * @param string $module Module type (QSR, KOT, PREMEAL)
     * @return string Generated order number
     */
    public function generate_order_number($module)
    {
        $date_part = date('Ymd');
        $prefix = strtoupper($module) . '-' . $date_part . '-';

        // Get the last order number for today with this module
        $this->db->select('order_number');
        $this->db->from('orders');
        $this->db->like('order_number', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get()->row();

        if ($last && preg_match('/(\d+)$/', $last->order_number, $matches)) {
            $next_num = intval($matches[1]) + 1;
        } else {
            $next_num = 1;
        }

        return $prefix . str_pad($next_num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create new order
     *
     * @param array $order_data Order data
     * @return int|bool Insert ID on success, false on failure
     */
    public function create_order($order_data)
    {
        $this->db->insert('orders', $order_data);
        return $this->db->insert_id();
    }

    /**
     * Create order item
     *
     * @param array $item_data Order item data
     * @return int|bool Insert ID on success, false on failure
     */
    public function create_order_item($item_data)
    {
        $this->db->insert('order_items', $item_data);
        return $this->db->insert_id();
    }

    /**
     * Create multiple order items
     *
     * @param array $items Array of order item data
     * @return bool True on success
     */
    public function create_order_items($items)
    {
        return $this->db->insert_batch('order_items', $items);
    }

    /**
     * Get order by ID
     *
     * @param int $order_id Order ID
     * @return object|null Order object or null
     */
    public function get_order_by_id($order_id)
    {
        return $this->db->get_where('orders', ['id' => $order_id])->row();
    }

    /**
     * Get order by order number
     *
     * @param string $order_number Order number
     * @return object|null Order object or null
     */
    public function get_order_by_number($order_number)
    {
        return $this->db->get_where('orders', ['order_number' => $order_number])->row();
    }

    /**
     * Get order with full details
     *
     * @param int $order_id Order ID
     * @param int $employee_id Employee ID (for validation)
     * @return object|null Order object with details or null
     */
    public function get_order_details($order_id, $employee_id)
    {
        $this->db->select('o.*, s.name as store_name, s.short_name as store_short_name,
                          s.address_line1, s.city, s.state, s.primary_phone as store_phone');
        $this->db->from('orders o');
        $this->db->join('stores s', 's.id = o.store_id');
        $this->db->where('o.id', $order_id);
        $this->db->where('o.employee_id', $employee_id);

        return $this->db->get()->row();
    }

    /**
     * Get order items by order ID
     *
     * @param int $order_id Order ID
     * @return array Order items
     */
    public function get_order_items($order_id)
    {
        $this->db->select('oi.*, p.thumbnail, p.is_vegetarian, p.is_vegan');
        $this->db->from('order_items oi');
        $this->db->join('products p', 'p.id = oi.product_id', 'left');
        $this->db->where('oi.order_id', $order_id);
        $this->db->order_by('oi.id', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Update order status
     *
     * @param int    $order_id   Order ID
     * @param string $status     New status
     * @param array  $extra_data Additional data to update
     * @return bool True on success
     */
    public function update_order_status($order_id, $status, $extra_data = [])
    {
        $update_data = array_merge(['status' => $status], $extra_data);
        $this->db->where('id', $order_id);
        return $this->db->update('orders', $update_data);
    }

    /**
     * Update order
     *
     * @param int   $order_id Order ID
     * @param array $data     Data to update
     * @return bool True on success
     */
    public function update_order($order_id, $data)
    {
        $this->db->where('id', $order_id);
        return $this->db->update('orders', $data);
    }

    /**
     * Add order status history
     *
     * @param array $history_data Status history data
     * @return int|bool Insert ID on success
     */
    public function add_status_history($history_data)
    {
        $this->db->insert('order_status_history', $history_data);
        return $this->db->insert_id();
    }

    /**
     * Add order payment record
     *
     * @param array $payment_data Payment data
     * @return int|bool Insert ID on success
     */
    public function add_order_payment($payment_data)
    {
        $this->db->insert('order_payments', $payment_data);
        return $this->db->insert_id();
    }

    /**
     * Get employee orders with pagination
     *
     * @param int    $employee_id Employee ID
     * @param string $module      Module type (QSR, KOT, PREMEAL)
     * @param int    $limit       Limit
     * @param int    $offset      Offset
     * @param string $status      Optional status filter
     * @return array Orders list
     */
    public function get_employee_orders($employee_id, $module, $limit = 20, $offset = 0, $status = null)
    {
        $this->db->select('o.*, s.name as store_name, s.short_name as store_short_name');
        $this->db->from('orders o');
        $this->db->join('stores s', 's.id = o.store_id');
        $this->db->where('o.employee_id', $employee_id);
        $this->db->where('o.module', $module);

        if ($status) {
            $this->db->where('o.status', $status);
        }

        $this->db->order_by('o.created_at', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    /**
     * Get employee orders count
     *
     * @param int    $employee_id Employee ID
     * @param string $module      Module type
     * @param string $status      Optional status filter
     * @return int Count
     */
    public function get_employee_orders_count($employee_id, $module, $status = null)
    {
        $this->db->from('orders');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('module', $module);

        if ($status) {
            $this->db->where('status', $status);
        }

        return $this->db->count_all_results();
    }

    /**
     * Check if order can be cancelled
     * Only PENDING or CONFIRMED orders can be cancelled
     *
     * @param int $order_id    Order ID
     * @param int $employee_id Employee ID
     * @return object|bool Order object if cancellable, false otherwise
     */
    public function get_cancellable_order($order_id, $employee_id)
    {
        $this->db->where('id', $order_id);
        $this->db->where('employee_id', $employee_id);
        $this->db->where_in('status', ['PENDING', 'CONFIRMED']);

        return $this->db->get('orders')->row();
    }

    /**
     * Store pending order for payment verification
     *
     * @param array $data Pending order data
     * @return int|bool Insert ID on success
     */
    public function store_pending_order($data)
    {
        $this->db->insert('pending_orders', $data);
        return $this->db->insert_id();
    }

    /**
     * Get pending order by Razorpay order ID
     *
     * @param string $razorpay_order_id Razorpay order ID
     * @return object|null Pending order or null
     */
    public function get_pending_order($razorpay_order_id)
    {
        return $this->db->get_where('pending_orders', [
            'razorpay_order_id' => $razorpay_order_id,
            'status' => 'PENDING'
        ])->row();
    }

    /**
     * Update pending order status
     *
     * @param int    $id     Pending order ID
     * @param string $status New status
     * @param array  $data   Additional data
     * @return bool True on success
     */
    public function update_pending_order($id, $status, $data = [])
    {
        $update_data = array_merge(['status' => $status], $data);
        $this->db->where('id', $id);
        return $this->db->update('pending_orders', $update_data);
    }

    /**
     * Atomically claim a pending order for completion
     *
     * Conditional update guarantees only one concurrent completion call
     * can win; must run inside the completion transaction so a rollback
     * releases the claim back to PENDING.
     *
     * @param int $id Pending order ID
     * @return bool True if this call won the claim
     */
    public function claim_pending_order($id)
    {
        $this->db->where('id', $id)
            ->where('status', 'PENDING')
            ->update('pending_orders', ['status' => 'COMPLETED']);

        return $this->db->affected_rows() === 1;
    }

    /**
     * Delete pending order
     *
     * @param int $id Pending order ID
     * @return bool True on success
     */
    public function delete_pending_order($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('pending_orders');
    }

    /**
     * Deduct stock from store_products after order placement
     *
     * Only deducts if available_stock is not NULL (NULL = unlimited stock)
     *
     * @param int $store_id   Store ID
     * @param int $product_id Product ID
     * @param int $quantity   Quantity to deduct
     * @return bool True on success
     */
    public function deduct_stock($store_id, $product_id, $quantity, $context = [])
    {
        // Get current stock
        $store_product = $this->db->get_where('store_products', [
            'store_id' => $store_id,
            'product_id' => $product_id
        ])->row();

        if (!$store_product) {
            return false;
        }

        // Only deduct if stock is tracked (not NULL)
        if ($store_product->available_stock !== null) {
            $old_stock = (int)$store_product->available_stock;
            $new_stock = max(0, $old_stock - $quantity);

            $this->db->where('store_id', $store_id);
            $this->db->where('product_id', $product_id);
            $updated = $this->db->update('store_products', [
                'available_stock' => $new_stock,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($updated) {
                record_stock_transaction(array_merge([
                    'store_id'         => $store_id,
                    'product_id'       => $product_id,
                    'transaction_type' => 'OUT',
                    'source'           => 'ORDER_PLACED',
                    'quantity'         => $quantity,
                    'stock_before'     => $old_stock,
                    'stock_after'      => $new_stock,
                    'reference_type'   => 'ORDER',
                    'note'             => 'Stock deducted on order placement'
                ], $context));
            }

            return $updated;
        }

        return true; // No deduction needed for unlimited stock
    }

    /**
     * Restore stock to store_products (for order cancellation/refund)
     *
     * Only restores if available_stock is not NULL (NULL = unlimited stock)
     *
     * @param int $store_id   Store ID
     * @param int $product_id Product ID
     * @param int $quantity   Quantity to restore
     * @return bool True on success
     */
    public function restore_stock($store_id, $product_id, $quantity, $context = [])
    {
        // Get current stock
        $store_product = $this->db->get_where('store_products', [
            'store_id' => $store_id,
            'product_id' => $product_id
        ])->row();

        if (!$store_product) {
            return false;
        }

        // Only restore if stock is tracked (not NULL)
        if ($store_product->available_stock !== null) {
            $old_stock = (int)$store_product->available_stock;
            $new_stock = $old_stock + $quantity;

            $this->db->where('store_id', $store_id);
            $this->db->where('product_id', $product_id);
            $updated = $this->db->update('store_products', [
                'available_stock' => $new_stock,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

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

        return true; // No restoration needed for unlimited stock
    }
}
