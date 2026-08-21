<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Guest_model extends CI_Model
{
    // ========================================
    // Guest Cart Operations
    // ========================================

    /**
     * Get existing cart item for same product in session+store
     */
    public function get_existing_cart($session_id, $store_id, $product_id)
    {
        return $this->db->get_where('guest_carts', [
            'session_id' => $session_id,
            'store_id' => $store_id,
            'product_id' => $product_id
        ])->row();
    }

    /**
     * Get cart item by ID and session
     */
    public function get_cart_item($cart_id, $session_id)
    {
        return $this->db->get_where('guest_carts', [
            'id' => $cart_id,
            'session_id' => $session_id
        ])->row();
    }

    /**
     * Add new item to guest cart
     */
    public function add_to_cart($data)
    {
        $this->db->insert('guest_carts', $data);
        return $this->db->insert_id();
    }

    /**
     * Update cart item quantity
     */
    public function update_quantity($cart_id, $quantity, $note = null)
    {
        $update_data = [
            'quantity' => $quantity,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($note !== null) {
            $update_data['note'] = $note;
        }
        $this->db->where('id', $cart_id);
        return $this->db->update('guest_carts', $update_data);
    }

    /**
     * Remove item from guest cart
     */
    public function remove_from_cart($cart_id)
    {
        $this->db->where('id', $cart_id);
        return $this->db->delete('guest_carts');
    }

    /**
     * Get cart items count
     */
    public function get_cart_count($session_id, $store_id)
    {
        return $this->db->where([
            'session_id' => $session_id,
            'store_id' => $store_id
        ])->count_all_results('guest_carts');
    }

    /**
     * Get all cart items with product details
     */
    public function get_cart_items($session_id, $store_id)
    {
        $this->db->select('gc.id as cart_id, gc.quantity, gc.note, gc.created_at, gc.updated_at,
                          p.id as product_id, p.name as product_name, p.short_name,
                          p.thumbnail, p.base_price, p.tax_percentage,
                          p.is_vegetarian, p.is_vegan,
                          sp.price as store_price, sp.available_stock');
        $this->db->from('guest_carts gc');
        $this->db->join('products p', 'p.id = gc.product_id AND p.is_active = 1 AND p.is_available = 1 AND p.deleted_at IS NULL', 'inner');
        $this->db->join('store_products sp', 'sp.product_id = gc.product_id AND sp.store_id = gc.store_id AND sp.is_active = 1 AND sp.deleted_at IS NULL', 'inner');
        $this->db->where('gc.session_id', $session_id);
        $this->db->where('gc.store_id', $store_id);
        $this->db->order_by('gc.created_at', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Clear all cart items for session+store
     */
    public function clear_cart($session_id, $store_id)
    {
        $this->db->where([
            'session_id' => $session_id,
            'store_id' => $store_id
        ]);
        return $this->db->delete('guest_carts');
    }

    // ========================================
    // Guest Pending Order Operations
    // ========================================

    /**
     * Store pending order for guest
     */
    public function store_pending_order($data)
    {
        $this->db->insert('pending_orders', $data);
        return $this->db->insert_id();
    }

    /**
     * Get pending order by razorpay_order_id and session
     */
    public function get_pending_by_razorpay($razorpay_order_id, $session_id)
    {
        return $this->db->get_where('pending_orders', [
            'razorpay_order_id' => $razorpay_order_id,
            'session_id' => $session_id,
            'status' => 'PENDING'
        ])->row();
    }

    /**
     * Get pending order by ID and session
     */
    public function get_pending_by_id($id, $session_id)
    {
        return $this->db->get_where('pending_orders', [
            'id' => $id,
            'session_id' => $session_id,
            'status' => 'PENDING'
        ])->row();
    }

    /**
     * Update pending order status
     */
    public function update_pending_order($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('pending_orders', $data);
    }

    // ========================================
    // Guest Order Detail Operations
    // ========================================

    /**
     * Get guest order with store details
     */
    public function get_guest_order_with_store($order_id)
    {
        return $this->db->select('o.*, s.name as store_name, s.short_name as store_short_name,
                                   s.address_line1, s.city, s.state, s.primary_phone as store_phone')
            ->from('orders o')
            ->join('stores s', 's.id = o.store_id', 'left')
            ->where('o.id', $order_id)
            ->where('o.is_guest_order', 1)
            ->get()->row();
    }

    /**
     * Get order items with product details for guest order
     */
    public function get_guest_order_items($order_id)
    {
        return $this->db->select('oi.id, oi.product_id, oi.product_name, oi.quantity,
                                   oi.unit_price, oi.base_price, oi.tax_amount, oi.total_amount, oi.note,
                                   p.short_name, p.thumbnail, p.is_vegetarian')
            ->from('order_items oi')
            ->join('products p', 'p.id = oi.product_id', 'left')
            ->where('oi.order_id', $order_id)
            ->get()->result();
    }
}
