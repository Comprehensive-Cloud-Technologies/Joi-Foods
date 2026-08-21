<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Store Inventory Model
 *
 * Handles inventory operations for store staff.
 *
 * @category  Models
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-04
 */
class StoreInventory_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get categories that have products assigned to this store
     *
     * @param int $store_id Store ID
     * @return array List of categories
     */
    public function get_store_categories($store_id)
    {
        return $this->db
            ->distinct()
            ->select('c.id, c.name, c.description, c.icon, c.thumbnail, c.display_order,
                      COUNT(DISTINCT sp.product_id) as product_count')
            ->from('categories c')
            ->join('products p', 'p.category_id = c.id AND p.is_active = 1 AND p.deleted_at IS NULL', 'inner')
            ->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.deleted_at IS NULL', 'inner')
            ->where('c.is_active', 1)
            ->where('c.deleted_at', NULL)
            ->group_by('c.id')
            ->order_by('c.display_order', 'ASC')
            ->order_by('c.name', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get products by category for a store (with store-specific pricing)
     *
     * @param int $store_id Store ID
     * @param int|null $category_id Category ID (null for all)
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array List of products
     */
    public function get_store_products($store_id, $category_id = null, $limit = 50, $offset = 0)
    {
        $this->db
            ->select('p.id, p.name, p.short_name, p.thumbnail,
                      p.is_vegetarian, p.is_vegan,
                      c.id as category_id, c.name as category_name,
                      sp.id as store_product_id, sp.price, sp.available_stock, sp.is_active as store_is_active')
            ->from('products p')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.deleted_at IS NULL', 'inner')
            ->where('p.is_active', 1)
            ->where('p.deleted_at', NULL);

        if (!empty($category_id)) {
            $this->db->where('p.category_id', $category_id);
        }

        return $this->db
            ->order_by('p.display_order', 'ASC')
            ->order_by('p.name', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    /**
     * Count products for a store by category
     *
     * @param int $store_id Store ID
     * @param int|null $category_id Category ID (null for all)
     * @return int Count
     */
    public function count_store_products($store_id, $category_id = null)
    {
        $this->db
            ->from('products p')
            ->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.deleted_at IS NULL', 'inner')
            ->where('p.is_active', 1)
            ->where('p.deleted_at', NULL);

        if (!empty($category_id)) {
            $this->db->where('p.category_id', $category_id);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get single store product by product ID
     *
     * @param int $product_id Product ID
     * @param int $store_id Store ID
     * @return object|null Store product or null
     */
    public function get_store_product($product_id, $store_id)
    {
        return $this->db
            ->select('p.id, p.name, p.short_name, p.thumbnail,
                      p.is_vegetarian, p.is_vegan,
                      c.id as category_id, c.name as category_name,
                      sp.id as store_product_id, sp.price, sp.available_stock, sp.is_active as store_is_active')
            ->from('products p')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.deleted_at IS NULL', 'inner')
            ->where('p.id', $product_id)
            ->where('p.is_active', 1)
            ->where('p.deleted_at', NULL)
            ->get()
            ->row();
    }

    /**
     * Update product stock for a store
     *
     * @param int $store_id Store ID
     * @param int $product_id Product ID
     * @param int|null $stock New stock value (null for unlimited)
     * @return bool Success
     */
    public function update_stock($store_id, $product_id, $stock, $context = [])
    {
        // Capture stock before the change for the ledger
        $before = $this->db
            ->select('available_stock')
            ->where('store_id', $store_id)
            ->where('product_id', $product_id)
            ->where('deleted_at', NULL)
            ->get('store_products')
            ->row();
        $stock_before = ($before && $before->available_stock !== null) ? (int)$before->available_stock : null;

        $updated = $this->db
            ->where('store_id', $store_id)
            ->where('product_id', $product_id)
            ->where('deleted_at', NULL)
            ->update('store_products', [
                'available_stock' => $stock,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // Record the manual change in the ledger (only when stock is tracked)
        if ($updated && $stock !== null) {
            $stock_after = (int)$stock;
            $delta = $stock_after - ($stock_before ?? 0);
            $type = $delta >= 0 ? 'IN' : 'OUT';

            record_stock_transaction(array_merge([
                'store_id'         => $store_id,
                'product_id'       => $product_id,
                'transaction_type' => $stock_before === null ? 'SET' : $type,
                'source'           => 'MANUAL_UPDATE',
                'quantity'         => abs($delta),
                'stock_before'     => $stock_before,
                'stock_after'      => $stock_after,
                'reference_type'   => 'MANUAL',
                'note'             => 'Stock updated by store staff'
            ], $context));
        }

        return $updated;
    }

    /**
     * Update product status (active/inactive) for a store
     *
     * @param int $store_id Store ID
     * @param int $product_id Product ID
     * @param bool $is_active Active status
     * @return bool Success
     */
    public function update_status($store_id, $product_id, $is_active)
    {
        return $this->db
            ->where('store_id', $store_id)
            ->where('product_id', $product_id)
            ->where('deleted_at', NULL)
            ->update('store_products', [
                'is_active' => $is_active ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Check if store product exists
     *
     * @param int $store_id Store ID
     * @param int $product_id Product ID
     * @return bool Exists
     */
    public function store_product_exists($store_id, $product_id)
    {
        $result = $this->db
            ->where('store_id', $store_id)
            ->where('product_id', $product_id)
            ->where('deleted_at', NULL)
            ->get('store_products')
            ->row();

        return !empty($result);
    }

}
