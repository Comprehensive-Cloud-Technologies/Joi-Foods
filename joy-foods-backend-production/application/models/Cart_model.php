<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cart_model extends CI_Model
{
    /**
     * Get cart item by ID and employee ID
     *
     * @param int $cart_id     Cart ID
     * @param int $employee_id Employee ID
     * @return object|null Cart object or null
     */
    public function get_cart_item($cart_id, $employee_id)
    {
        return $this->db->get_where('carts', [
            'id' => $cart_id,
            'employee_id' => $employee_id
        ])->row();
    }

    /**
     * Get existing cart item for same product in same store/module
     *
     * @param int    $employee_id Employee ID
     * @param int    $store_id    Store ID
     * @param int    $product_id  Product ID
     * @param string $module      Module type (QSR, KOT, PREMEAL)
     * @return object|null Cart object or null
     */
    public function get_existing_cart($employee_id, $store_id, $product_id, $module)
    {
        return $this->db->get_where('carts', [
            'employee_id' => $employee_id,
            'store_id' => $store_id,
            'product_id' => $product_id,
            'module' => $module
        ])->row();
    }

    /**
     * Add new item to cart
     *
     * @param array $data Cart data
     * @return int Insert ID
     */
    public function add_to_cart($data)
    {
        $this->db->insert('carts', $data);
        return $this->db->insert_id();
    }

    /**
     * Update cart item quantity
     *
     * @param int $cart_id  Cart ID
     * @param int $quantity New quantity
     * @param string|null $note Optional note
     * @return bool Update success
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
        return $this->db->update('carts', $update_data);
    }

    /**
     * Remove item from cart
     *
     * @param int $cart_id Cart ID
     * @return bool Delete success
     */
    public function remove_from_cart($cart_id)
    {
        $this->db->where('id', $cart_id);
        return $this->db->delete('carts');
    }

    /**
     * Get cart items count for employee in store/module
     *
     * @param int    $employee_id Employee ID
     * @param int    $store_id    Store ID
     * @param string $module      Module type (QSR, KOT, PREMEAL)
     * @return int Cart items count
     */
    public function get_cart_count($employee_id, $store_id, $module)
    {
        return $this->db->where([
            'employee_id' => $employee_id,
            'store_id' => $store_id,
            'module' => $module
        ])->count_all_results('carts');
    }

    /**
     * Get all cart items for employee in store/module with full product details
     * Only returns valid products (active product and active store_product)
     *
     * @param int    $employee_id Employee ID
     * @param int    $store_id    Store ID
     * @param string $module      Module type (QSR, KOT, PREMEAL)
     * @return array Cart items with product details
     */
    public function get_cart_items($employee_id, $store_id, $module)
    {
        $this->db->select('c.id as cart_id, c.quantity, c.note, c.scheduled_date, c.meal_type,
                          c.created_at, c.updated_at,
                          p.id as product_id, p.name as product_name, p.short_name,
                          p.thumbnail, p.base_price, p.tax_percentage,
                          p.is_vegetarian, p.is_vegan,
                          sp.price as store_price, sp.available_stock');
        $this->db->from('carts c');
        $this->db->join('products p', 'p.id = c.product_id AND p.is_active = 1 AND p.is_available = 1 AND p.deleted_at IS NULL', 'inner');
        $this->db->join('store_products sp', 'sp.product_id = c.product_id AND sp.store_id = c.store_id AND sp.is_active = 1 AND sp.deleted_at IS NULL', 'inner');
        $this->db->where('c.employee_id', $employee_id);
        $this->db->where('c.store_id', $store_id);
        $this->db->where('c.module', $module);
        $this->db->order_by('c.created_at', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Clear all cart items for employee in store/module
     *
     * @param int    $employee_id Employee ID
     * @param int    $store_id    Store ID
     * @param string $module      Module type (QSR, KOT, PREMEAL)
     * @return bool Delete success
     */
    public function clear_cart($employee_id, $store_id, $module)
    {
        $this->db->where([
            'employee_id' => $employee_id,
            'store_id' => $store_id,
            'module' => $module
        ]);
        return $this->db->delete('carts');
    }
}
