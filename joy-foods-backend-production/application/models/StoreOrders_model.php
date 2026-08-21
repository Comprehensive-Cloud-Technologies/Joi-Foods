<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * StoreOrders Model
 *
 * Handles database operations for store-side order management.
 * Used by store staff to view and manage QSR/KOT orders.
 *
 * @category  Models
 * @package   Joy_Foods
 * @author    ZooBit Infotech
 * @version   1.0.0
 */
class StoreOrders_model extends CI_Model
{
    /**
     * Get orders by status for a store
     *
     * @param int         $store_id Store ID
     * @param array       $statuses Array of status values
     * @param string|null $module   Module filter (QSR, KOT) or null for all
     * @param int         $limit    Limit
     * @param int         $offset   Offset
     * @return array Orders
     */
    public function get_orders_by_status($store_id, $statuses, $module = null, $limit = 20, $offset = 0)
    {
        $this->db
            ->select('o.*, e.first_name as employee_first_name, e.last_name as employee_last_name')
            ->from('orders o')
            ->join('employees e', 'e.id = o.employee_id', 'left')
            ->where('o.store_id', $store_id)
            ->where_in('o.status', $statuses)
            ->where_in('o.module', ['QSR', 'KOT']); // Only QSR and KOT, not PREMEAL

        if (!empty($module)) {
            $this->db->where('o.module', $module);
        }

        return $this->db
            ->order_by('o.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    /**
     * Count orders by status for a store
     *
     * @param int         $store_id Store ID
     * @param array       $statuses Array of status values
     * @param string|null $module   Module filter (QSR, KOT) or null for all
     * @return int Count
     */
    public function count_orders_by_status($store_id, $statuses, $module = null)
    {
        $this->db
            ->from('orders')
            ->where('store_id', $store_id)
            ->where_in('status', $statuses)
            ->where_in('module', ['QSR', 'KOT']);

        if (!empty($module)) {
            $this->db->where('module', $module);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get order by ID and store
     *
     * @param int $order_id Order ID
     * @param int $store_id Store ID
     * @return object|null Order or null
     */
    public function get_order_by_id($order_id, $store_id)
    {
        return $this->db
            ->select('*')
            ->from('orders')
            ->where('id', $order_id)
            ->where('store_id', $store_id)
            ->get()
            ->row();
    }

    /**
     * Get orders eligible for RFID-driven pickup confirmation
     *
     * Returns orders in active statuses (CONFIRMED, PREPARING, READY) belonging
     * to a specific employee at a specific store, optionally filtered by module,
     * scheduled_date and meal_type (PREMEAL).
     *
     * @param int    $store_id       Store ID
     * @param int    $employee_id    Employee ID (resolved from RFID card)
     * @param string $module         Module: QSR, KOT or PREMEAL
     * @param string $scheduled_date YYYY-MM-DD (only used for PREMEAL)
     * @param string $meal_type      BREAKFAST|LUNCH|DINNER (only used for PREMEAL)
     * @return array Orders ready for pickup
     */
    public function get_orders_for_pickup($store_id, $employee_id, $module, $scheduled_date = null, $meal_type = null)
    {
        $this->db
            ->select('o.*, dl.name as delivery_location_name, dl.short_name as delivery_location_short_name, dl.floor as delivery_location_floor, dl.building as delivery_location_building')
            ->from('orders o')
            ->join('delivery_locations dl', 'dl.id = o.delivery_location_id', 'left')
            ->where('o.store_id', $store_id)
            ->where('o.employee_id', $employee_id)
            ->where('o.module', $module)
            ->where_in('o.status', ['CONFIRMED', 'PREPARING', 'READY'])
            ->where('o.is_guest_order', 0);

        if ($module === 'PREMEAL') {
            if (!empty($scheduled_date)) {
                $this->db->where('o.scheduled_date', $scheduled_date);
            }
            if (!empty($meal_type)) {
                $this->db->where('o.meal_type', $meal_type);
            }
        }

        return $this->db
            ->order_by('o.created_at', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get order by order number and store
     *
     * @param string $order_number Order Number
     * @param int    $store_id     Store ID
     * @return object|null Order or null
     */
    public function get_order_by_number($order_number, $store_id)
    {
        return $this->db
            ->select('*')
            ->from('orders')
            ->where('order_number', $order_number)
            ->where('store_id', $store_id)
            ->get()
            ->row();
    }

    /**
     * Get order items
     *
     * @param int $order_id Order ID
     * @return array Order items
     */
    public function get_order_items($order_id)
    {
        return $this->db
            ->select('*')
            ->from('order_items')
            ->where('order_id', $order_id)
            ->get()
            ->result();
    }

    /**
     * Update order status
     *
     * @param int    $order_id        Order ID
     * @param string $new_status      New status
     * @param array  $additional_data Additional data to update (e.g., ready_at, prep_time)
     * @return bool Success
     */
    public function update_order_status($order_id, $new_status, $additional_data = [])
    {
        $update_data = [
            'status' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Add confirmed_at timestamp if confirming
        if ($new_status === 'CONFIRMED') {
            $update_data['confirmed_at'] = date('Y-m-d H:i:s');
        }

        // Add completed_at timestamp if completing
        if (in_array($new_status, ['COMPLETED', 'CANCELLED', 'REJECTED'])) {
            $update_data['completed_at'] = date('Y-m-d H:i:s');
        }

        // Merge additional data (e.g., ready_at, prep_time)
        if (!empty($additional_data)) {
            $update_data = array_merge($update_data, $additional_data);
        }

        return $this->db->update('orders', $update_data, ['id' => $order_id]);
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
     * Add order payment record
     *
     * @param array $data Payment data
     * @return int|bool Insert ID or false
     */
    public function add_order_payment($data)
    {
        $this->db->insert('order_payments', $data);
        return $this->db->insert_id();
    }

    /**
     * Restore stock for rejected/cancelled order
     *
     * @param int $store_id   Store ID
     * @param int $product_id Product ID
     * @param int $quantity   Quantity to restore
     * @return bool Success
     */
    public function restore_stock($store_id, $product_id, $quantity, $context = [])
    {
        // Only restore if stock is not NULL (unlimited)
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
                    'source'           => 'ORDER_REJECTED',
                    'quantity'         => $quantity,
                    'stock_before'     => $old_stock,
                    'stock_after'      => $new_stock,
                    'reference_type'   => 'ORDER',
                    'note'             => 'Stock restored on order rejection'
                ], $context));
            }

            return $updated;
        }

        return true;
    }

    /**
     * Get order with full details for a store
     *
     * @param int $order_id Order ID
     * @param int $store_id Store ID
     * @return object|null Order with details or null
     */
    public function get_order_with_details($order_id, $store_id)
    {
        return $this->db
            ->select('o.*, e.first_name as employee_first_name, e.last_name as employee_last_name,
                      e.phone as employee_phone, e.email as employee_email,
                      s.name as store_name')
            ->from('orders o')
            ->join('employees e', 'e.id = o.employee_id', 'left')
            ->join('stores s', 's.id = o.store_id', 'left')
            ->where('o.id', $order_id)
            ->where('o.store_id', $store_id)
            ->get()
            ->row();
    }

    /**
     * Get order items with product details
     *
     * @param int $order_id Order ID
     * @return array Order items with product info
     */
    public function get_order_items_detailed($order_id)
    {
        return $this->db
            ->select('oi.*, p.name as product_name')
            ->from('order_items oi')
            ->join('products p', 'p.id = oi.product_id', 'left')
            ->where('oi.order_id', $order_id)
            ->get()
            ->result();
    }

    /**
     * Get order payments
     *
     * @param int $order_id Order ID
     * @return array Order payments
     */
    public function get_order_payments($order_id)
    {
        return $this->db
            ->select('*')
            ->from('order_payments')
            ->where('order_id', $order_id)
            ->order_by('created_at', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get orders count by status (for dashboard stats)
     *
     * @param int $store_id Store ID
     * @return array Counts by status
     */
    public function get_status_counts($store_id)
    {
        $result = $this->db
            ->select('status, COUNT(*) as count')
            ->from('orders')
            ->where('store_id', $store_id)
            ->where_in('module', ['QSR', 'KOT'])
            ->where('DATE(created_at)', date('Y-m-d'))
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
            'rejected' => 0
        ];

        foreach ($result as $row) {
            $counts[strtolower($row->status)] = (int)$row->count;
        }

        return $counts;
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
     * Get successful online payment for an order
     *
     * @param int $order_id Order ID
     * @return object|null Payment record or null
     */
    public function get_online_payment($order_id)
    {
        return $this->db->get_where('order_payments', [
            'order_id' => $order_id,
            'payment_type' => 'ONLINE_PAYMENT',
            'status' => 'SUCCESS'
        ])->row();
    }

    /**
     * Get total amount the employee actually paid for an order
     * (wallet + online portions; excludes COMPANY_SUBSIDY)
     *
     * @param int $order_id Order ID
     * @return float
     */
    public function get_employee_paid_amount($order_id)
    {
        $row = $this->db
            ->select_sum('amount')
            ->from('order_payments')
            ->where('order_id', $order_id)
            ->where_in('payment_type', ['WALLET_DEBIT', 'ONLINE_PAYMENT'])
            ->where('status', 'SUCCESS')
            ->get()
            ->row();

        return ($row && $row->amount !== null) ? (float)$row->amount : 0.0;
    }

    /**
     * Check whether a refund credit has already been recorded for an order
     *
     * @param int $order_id Order ID
     * @return bool
     */
    public function has_refund($order_id)
    {
        return $this->db
            ->from('order_payments')
            ->where('order_id', $order_id)
            ->where('payment_type', 'REFUND_CREDIT')
            ->count_all_results() > 0;
    }

    /**
     * Update order refund fields
     *
     * @param int   $order_id Order ID
     * @param array $data     Refund data to update
     * @return bool
     */
    public function update_order_refund($order_id, $data)
    {
        return $this->db->update('orders', $data, ['id' => $order_id]);
    }

    /**
     * Insert wallet refund transaction
     *
     * @param array $data Transaction data
     * @return int Insert ID (transaction_id)
     */
    public function insert_refund_transaction($data)
    {
        $this->db->insert('transaction', $data);
        return $this->db->insert_id();
    }
}
