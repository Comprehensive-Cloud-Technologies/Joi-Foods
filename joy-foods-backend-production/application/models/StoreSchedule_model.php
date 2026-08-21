<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Store Schedule Model
 *
 * Handles premeal schedule data operations for store staff.
 *
 * @category  Models
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-12
 */
class StoreSchedule_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get premeal products for a store
     *
     * @param int      $store_id    Store ID
     * @param int|null $category_id Optional category filter
     * @param int      $limit       Limit
     * @param int      $offset      Offset
     * @return array Products
     */
    public function get_premeal_products($store_id, $category_id = null, $limit = 50, $offset = 0)
    {
        $this->db->select('
            p.id,
            p.name,
            p.short_name,
            p.thumbnail,
            p.is_vegetarian,
            p.is_vegan,
            p.category_id,
            c.name as category_name,
            sp.price,
            sp.is_active as store_is_active
        ');
        $this->db->from('store_products sp');
        $this->db->join('products p', 'p.id = sp.product_id');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('sp.store_id', $store_id);
        $this->db->where('sp.is_active', 1);
        $this->db->where('sp.deleted_at IS NULL');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.deleted_at IS NULL');
        $this->db->where('p.premeal_enabled', 1);

        if (!empty($category_id)) {
            $this->db->where('p.category_id', $category_id);
        }

        $this->db->order_by('c.display_order', 'ASC');
        $this->db->order_by('p.display_order', 'ASC');
        $this->db->order_by('p.name', 'ASC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    /**
     * Count premeal products for a store
     *
     * @param int      $store_id    Store ID
     * @param int|null $category_id Optional category filter
     * @return int Count
     */
    public function count_premeal_products($store_id, $category_id = null)
    {
        $this->db->from('store_products sp');
        $this->db->join('products p', 'p.id = sp.product_id');
        $this->db->where('sp.store_id', $store_id);
        $this->db->where('sp.is_active', 1);
        $this->db->where('sp.deleted_at IS NULL');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.deleted_at IS NULL');
        $this->db->where('p.premeal_enabled', 1);

        if (!empty($category_id)) {
            $this->db->where('p.category_id', $category_id);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get store details
     *
     * @param int $store_id Store ID
     * @return object|null Store
     */
    public function get_store($store_id)
    {
        return $this->db->get_where('stores', [
            'id' => $store_id,
            'is_active' => 1,
            'deleted_at' => null
        ])->row();
    }

    /**
     * Get product details
     *
     * @param int $product_id Product ID
     * @param int $store_id   Store ID
     * @return object|null Product
     */
    public function get_product($product_id, $store_id)
    {
        $this->db->select('
            p.id,
            p.name,
            p.short_name,
            p.thumbnail,
            p.is_vegetarian,
            p.category_id,
            c.name as category_name,
            sp.price,
            sp.is_active as store_is_active
        ');
        $this->db->from('store_products sp');
        $this->db->join('products p', 'p.id = sp.product_id');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->where('sp.store_id', $store_id);
        $this->db->where('sp.product_id', $product_id);
        $this->db->where('sp.deleted_at IS NULL');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.deleted_at IS NULL');
        $this->db->where('p.premeal_enabled', 1);

        return $this->db->get()->row();
    }

    /**
     * Check if product exists in store
     *
     * @param int $store_id   Store ID
     * @param int $product_id Product ID
     * @return bool
     */
    public function store_product_exists($store_id, $product_id)
    {
        $this->db->from('store_products sp');
        $this->db->join('products p', 'p.id = sp.product_id');
        $this->db->where('sp.store_id', $store_id);
        $this->db->where('sp.product_id', $product_id);
        $this->db->where('sp.deleted_at IS NULL');
        $this->db->where('p.premeal_enabled', 1);

        return $this->db->count_all_results() > 0;
    }

    /**
     * Get schedule for a product (all days)
     *
     * @param int $store_id   Store ID
     * @param int $product_id Product ID
     * @return array Schedule for all days
     */
    public function get_product_schedule($store_id, $product_id)
    {
        $this->db->select('*');
        $this->db->from('premeal_schedules');
        $this->db->where('store_id', $store_id);
        $this->db->where('product_id', $product_id);
        $this->db->where('deleted_at IS NULL');
        $this->db->order_by("FIELD(day_of_week, 'MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY')", '', false);

        return $this->db->get()->result();
    }

    /**
     * Get schedule for a specific day
     *
     * @param int    $store_id   Store ID
     * @param int    $product_id Product ID
     * @param string $day        Day of week
     * @return object|null Schedule
     */
    public function get_day_schedule($store_id, $product_id, $day)
    {
        return $this->db->get_where('premeal_schedules', [
            'store_id' => $store_id,
            'product_id' => $product_id,
            'day_of_week' => $day,
            'deleted_at' => null
        ])->row();
    }

    /**
     * Add schedule for a day
     *
     * @param array $data Schedule data
     * @return int|bool Insert ID or false
     */
    public function add_schedule($data)
    {
        $this->db->insert('premeal_schedules', $data);
        return $this->db->insert_id();
    }

    /**
     * Update schedule for a day
     *
     * @param int   $schedule_id Schedule ID
     * @param array $data        Update data
     * @return bool
     */
    public function update_schedule($schedule_id, $data)
    {
        $this->db->where('id', $schedule_id);
        return $this->db->update('premeal_schedules', $data);
    }

    /**
     * Delete schedule (soft delete)
     *
     * @param int $schedule_id Schedule ID
     * @return bool
     */
    public function delete_schedule($schedule_id)
    {
        $this->db->where('id', $schedule_id);
        return $this->db->update('premeal_schedules', [
            'deleted_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get premeal categories for a store
     *
     * @param int $store_id Store ID
     * @return array Categories
     */
    public function get_premeal_categories($store_id)
    {
        $this->db->select('
            c.id,
            c.name,
            c.description,
            c.icon,
            c.thumbnail,
            c.display_order,
            COUNT(DISTINCT sp.product_id) as product_count
        ');
        $this->db->from('categories c');
        $this->db->join('products p', 'p.category_id = c.id');
        $this->db->join('store_products sp', 'sp.product_id = p.id');
        $this->db->where('sp.store_id', $store_id);
        $this->db->where('sp.is_active', 1);
        $this->db->where('sp.deleted_at IS NULL');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.deleted_at IS NULL');
        $this->db->where('p.premeal_enabled', 1);
        $this->db->where('c.is_active', 1);
        $this->db->where('c.deleted_at IS NULL');
        $this->db->group_by('c.id');
        $this->db->order_by('c.display_order', 'ASC');
        $this->db->order_by('c.name', 'ASC');

        return $this->db->get()->result();
    }
}
