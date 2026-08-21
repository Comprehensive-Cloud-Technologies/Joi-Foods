<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Categories_model extends CI_Model
{
    /**
     * Get all categories for a client
     */
    public function get_all_by_client($client_id)
    {
        $this->db->select('c.*, pc.name as parent_category_name');
        $this->db->from('categories c');
        $this->db->join('categories pc', 'pc.id = c.parent_id', 'left');
        $this->db->where('c.client_id', $client_id);
        $this->db->where('c.deleted_at', NULL);
        $this->db->order_by('c.display_order', 'ASC');
        $this->db->order_by('c.id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get single category by ID and client
     */
    public function get_by_id($id, $client_id)
    {
        $this->db->select('c.*, pc.name as parent_category_name');
        $this->db->from('categories c');
        $this->db->join('categories pc', 'pc.id = c.parent_id', 'left');
        $this->db->where('c.id', $id);
        $this->db->where('c.client_id', $client_id);
        $this->db->where('c.deleted_at', NULL);
        return $this->db->get()->row();
    }

    /**
     * Get only top-level categories (parent_id IS NULL)
     */
    public function get_top_level_categories($client_id, $exclude_id = null)
    {
        $this->db->select('id, name');
        $this->db->from('categories');
        $this->db->where('client_id', $client_id);
        $this->db->where('parent_id', NULL);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get active categories count for a client
     */
    public function get_active_count($client_id)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('categories');
    }

    /**
     * Get primary categories count
     */
    public function get_primary_count($client_id)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('is_primary', 1);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('categories');
    }

    /**
     * Get count of products in a category
     */
    public function get_products_count($category_id)
    {
        $this->db->where('category_id', $category_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('products');
    }

    /**
     * Get count of subcategories
     */
    public function get_subcategories_count($category_id)
    {
        $this->db->where('parent_id', $category_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('categories');
    }

    /**
     * Get usage summary
     */
    public function get_usage_summary($category_id)
    {
        return array(
            'products' => $this->get_products_count($category_id),
            'subcategories' => $this->get_subcategories_count($category_id)
        );
    }

    /**
     * Check if category name already exists for client
     */
    public function check_name_exists($name, $client_id, $exclude_id = null)
    {
        $this->db->where('name', $name);
        $this->db->where('client_id', $client_id);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get('categories')->row();
    }

    /**
     * Soft delete all subcategories
     */
    public function delete_subcategories($category_id)
    {
        $data = array('deleted_at' => date('Y-m-d H:i:s'));
        $this->db->where('parent_id', $category_id);
        return $this->db->update('categories', $data);
    }

    /**
     * Get categories that have products in a specific store (for API)
     * Filters by module type (QSR, KOT, PREMEAL)
     *
     * @param int    $store_id Store ID
     * @param string $module   Module type: QSR, KOT, or PREMEAL
     * @return array List of categories
     */
    public function get_categories_by_store($store_id, $module)
    {
        // Build module-specific conditions
        $module_column_category = '';
        $module_column_product = '';

        if ($module == 'QSR') {
            $module_column_category = 'c.qsr_enabled = 1';
            $module_column_product = 'p.qsr_enabled = 1';
        } elseif ($module == 'KOT') {
            $module_column_category = 'c.kot_enabled = 1';
            $module_column_product = 'p.kot_enabled = 1';
        } elseif ($module == 'PREMEAL') {
            $module_column_category = 'c.premeal_enabled = 1';
            $module_column_product = 'p.premeal_enabled = 1';
        }

        $this->db->distinct();
        $this->db->select('c.id, c.name, c.description, c.icon, c.thumbnail, c.is_primary, c.display_order');
        $this->db->from('categories c');
        $this->db->join('products p', 'p.category_id = c.id AND p.is_active = 1 AND p.deleted_at IS NULL AND ' . $module_column_product, 'inner');
        $this->db->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.is_active = 1 AND sp.deleted_at IS NULL', 'inner');
        $this->db->where('c.is_active', 1);
        $this->db->where('c.deleted_at', NULL);
        $this->db->where($module_column_category);
        $this->db->order_by('c.is_primary', 'DESC');
        $this->db->order_by('c.display_order', 'ASC');
        $this->db->order_by('c.name', 'ASC');

        return $this->db->get()->result();
    }
}
