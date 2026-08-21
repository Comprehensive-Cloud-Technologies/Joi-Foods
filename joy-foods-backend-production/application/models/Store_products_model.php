<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Store_products_model extends CI_Model
{

    /**
     * Get store products by filter (category and/or store)
     */
    public function get_store_products_by_filter($client_id, $store_id = null, $category_id = null)
    {
        $this->db->select('sp.*, p.name as product_name, p.base_price, p.thumbnail, p.is_vegetarian,
                          c.name as category_name, s.name as store_name, s.store_code');
        $this->db->from('store_products sp');
        $this->db->join('products p', 'sp.product_id = p.id', 'left');
        $this->db->join('categories c', 'p.category_id = c.id', 'left');
        $this->db->join('stores s', 'sp.store_id = s.id', 'left');
        $this->db->where('sp.client_id', $client_id);
        $this->db->where('sp.deleted_at', NULL);
        $this->db->where('p.deleted_at', NULL);

        if (!empty($store_id)) {
            $this->db->where('sp.store_id', $store_id);
        }

        if (!empty($category_id) && $category_id != 'all') {
            $this->db->where('p.category_id', $category_id);
        }

        $this->db->order_by('p.name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get all store products for a client
     */
    public function get_all_by_client($client_id)
    {
        $this->db->select('sp.*, p.name as product_name, p.base_price, p.thumbnail, p.is_vegetarian,
                          c.name as category_name, s.name as store_name, s.store_code');
        $this->db->from('store_products sp');
        $this->db->join('products p', 'sp.product_id = p.id', 'left');
        $this->db->join('categories c', 'p.category_id = c.id', 'left');
        $this->db->join('stores s', 'sp.store_id = s.id', 'left');
        $this->db->where('sp.client_id', $client_id);
        $this->db->where('sp.deleted_at', NULL);
        $this->db->where('p.deleted_at', NULL);
        $this->db->order_by('s.name', 'ASC');
        $this->db->order_by('p.name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get store product by ID
     */
    public function get_by_id($id, $client_id)
    {
        $this->db->select('sp.*, p.name as product_name, p.base_price, p.thumbnail,
                          c.name as category_name, s.name as store_name');
        $this->db->from('store_products sp');
        $this->db->join('products p', 'sp.product_id = p.id', 'left');
        $this->db->join('categories c', 'p.category_id = c.id', 'left');
        $this->db->join('stores s', 'sp.store_id = s.id', 'left');
        $this->db->where('sp.id', $id);
        $this->db->where('sp.client_id', $client_id);
        $this->db->where('sp.deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Check if product exists in store
     */
    public function check_store_product_exists($store_id, $product_id, $client_id)
    {
        $this->db->where('store_id', $store_id);
        $this->db->where('product_id', $product_id);
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->get('store_products')->row();
    }

    /**
     * Get product details for adding to store
     * Returns product with store price if exists, else base price
     */
    public function get_product_for_store($product_id, $store_id, $client_id)
    {
        $this->db->select('p.id as product_id, p.name as product_name, p.base_price,
                          p.thumbnail, c.name as category_name,
                          sp.id as store_product_id, sp.price as store_price');
        $this->db->from('products p');
        $this->db->join('categories c', 'p.category_id = c.id', 'left');
        $this->db->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.deleted_at IS NULL', 'left');
        $this->db->where('p.id', $product_id);
        $this->db->where('p.client_id', $client_id);
        $this->db->where('p.deleted_at', NULL);
        $this->db->where('p.is_active', 1);
        return $this->db->get()->row();
    }

    /**
     * Get products for autocomplete search
     */
    public function get_products_autocomplete($client_id, $query)
    {
        $this->db->select('id, name as productname');
        $this->db->from('products');
        $this->db->where('client_id', $client_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        $this->db->like('name', $query, 'both');
        $this->db->order_by('name', 'ASC');
        $this->db->limit(20);
        return $this->db->get()->result();
    }

    /**
     * Get products by category filter for adding to store
     */
    public function get_products_by_category($client_id, $category_id, $store_id)
    {
        $this->db->select('p.id as product_id, p.name as product_name, p.base_price,
                          c.name as category_name,
                          sp.id as store_product_id, sp.price as store_price');
        $this->db->from('products p');
        $this->db->join('categories c', 'p.category_id = c.id', 'left');
        $this->db->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.deleted_at IS NULL', 'left');
        $this->db->where('p.client_id', $client_id);
        $this->db->where('p.is_active', 1);
        $this->db->where('p.deleted_at', NULL);

        if (!empty($category_id) && $category_id != 'all') {
            $this->db->where('p.category_id', $category_id);
        }

        $this->db->order_by('p.name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get all active stores for dropdown
     */
    public function get_stores_dropdown($client_id)
    {
        $this->db->select('id, name, store_code');
        $this->db->from('stores');
        $this->db->where('client_id', $client_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get all active categories for dropdown
     */
    public function get_categories_dropdown($client_id)
    {
        $this->db->select('id, name, parent_id');
        $this->db->from('categories');
        $this->db->where('client_id', $client_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get store products count
     */
    public function get_store_products_count($store_id, $client_id)
    {
        $this->db->where('store_id', $store_id);
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('store_products');
    }

    /**
     * Batch insert store products
     */
    public function batch_insert_store_products($data)
    {
        return $this->db->insert_batch('store_products', $data);
    }

    /**
     * Update store product price
     */
    public function update_store_product($id, $data, $client_id)
    {
        $this->db->where('id', $id);
        $this->db->where('client_id', $client_id);
        return $this->db->update('store_products', $data);
    }
}
