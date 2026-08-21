<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products_model extends CI_Model
{

    public function get_all_by_client($client_id)
    {
        $this->db->select('p.*, c.name as category_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'p.category_id = c.id', 'left');
        $this->db->where('p.client_id', $client_id);
        $this->db->where('p.deleted_at', NULL);
        $this->db->order_by('p.display_order', 'ASC');
        $this->db->order_by('p.name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id, $client_id)
    {
        $this->db->select('p.*, c.name as category_name');
        $this->db->from('products p');
        $this->db->join('categories c', 'p.category_id = c.id', 'left');
        $this->db->where('p.id', $id);
        $this->db->where('p.client_id', $client_id);
        $this->db->where('p.deleted_at', NULL);
        return $this->db->get()->row();
    }

    public function check_name_exists($name, $client_id, $exclude_id = null)
    {
        $this->db->where('name', $name);
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        $query = $this->db->get('products');
        return $query->num_rows() > 0;
    }

    public function get_active_categories($client_id)
    {
        $this->db->select('id, name, parent_id');
        $this->db->from('categories');
        $this->db->where('client_id', $client_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_products_count_by_category($category_id)
    {
        $this->db->where('category_id', $category_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('products');
    }

    /**
     * Get featured/popular products for a store (for API)
     * Filters by module type (QSR, KOT, PREMEAL)
     *
     * @param int    $store_id Store ID
     * @param string $module   Module type: QSR, KOT, or PREMEAL
     * @param int    $limit    Number of products to return
     * @return array List of featured products
     */
    public function get_featured_products_by_store($store_id, $module, $limit = 10)
    {
        // Build module-specific condition
        $module_column = '';
        if ($module == 'QSR') {
            $module_column = 'p.qsr_enabled = 1';
        } elseif ($module == 'KOT') {
            $module_column = 'p.kot_enabled = 1';
        } elseif ($module == 'PREMEAL') {
            $module_column = 'p.premeal_enabled = 1';
        }

        $this->db->select('p.id, p.name, p.short_name, p.description, p.thumbnail, p.images,
                          p.base_price, p.discount_price, p.tax_percentage,
                          p.is_vegetarian, p.is_vegan, p.calories,
                          p.is_featured, p.is_popular, p.display_order,
                          c.id as category_id, c.name as category_name,
                          sp.price as store_price, sp.available_stock');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.is_active = 1 AND sp.deleted_at IS NULL', 'inner');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.deleted_at', NULL);
        $this->db->where($module_column);
        $this->db->group_start();
        $this->db->where('p.is_featured', 1);
        $this->db->or_where('p.is_popular', 1);
        $this->db->group_end();
        $this->db->order_by('p.is_featured', 'DESC');
        $this->db->order_by('p.is_popular', 'DESC');
        $this->db->order_by('p.display_order', 'ASC');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    /**
     * Get products by category for a store (for API) with pagination
     * Filters by module type (QSR, KOT, PREMEAL)
     *
     * @param int      $store_id    Store ID
     * @param int|null $category_id Category ID (optional, null for all)
     * @param string   $module      Module type: QSR, KOT, or PREMEAL
     * @param int      $limit       Number of products per page
     * @param int      $offset      Offset for pagination
     * @return array List of products
     */
    public function get_products_by_store($store_id, $category_id, $module, $limit = 20, $offset = 0)
    {
        // Build module-specific condition
        $module_column = '';
        if ($module == 'QSR') {
            $module_column = 'p.qsr_enabled = 1';
        } elseif ($module == 'KOT') {
            $module_column = 'p.kot_enabled = 1';
        } elseif ($module == 'PREMEAL') {
            $module_column = 'p.premeal_enabled = 1';
        }

        $this->db->select('p.id, p.name, p.short_name, p.description, p.ingredients, p.thumbnail, p.images,
                          p.base_price, p.discount_price, p.tax_percentage,
                          p.is_vegetarian, p.is_vegan, p.calories,
                          p.breakfast, p.lunch, p.dinner,
                          p.is_featured, p.is_popular, p.display_order,
                          c.id as category_id, c.name as category_name,
                          sp.price as store_price, sp.available_stock');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.is_active = 1 AND sp.deleted_at IS NULL', 'inner');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.deleted_at', NULL);
        $this->db->where($module_column);

        if (!empty($category_id)) {
            $this->db->where('p.category_id', $category_id);
        }

        $this->db->order_by('p.is_featured', 'DESC');
        $this->db->order_by('p.display_order', 'ASC');
        $this->db->order_by('p.name', 'ASC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    /**
     * Get total count of products by category for a store (for pagination)
     * Filters by module type (QSR, KOT, PREMEAL)
     *
     * @param int      $store_id    Store ID
     * @param int|null $category_id Category ID (optional, null for all)
     * @param string   $module      Module type: QSR, KOT, or PREMEAL
     * @return int Total count of products
     */
    public function get_products_count_by_store($store_id, $category_id, $module)
    {
        // Build module-specific condition
        $module_column = '';
        if ($module == 'QSR') {
            $module_column = 'p.qsr_enabled = 1';
        } elseif ($module == 'KOT') {
            $module_column = 'p.kot_enabled = 1';
        } elseif ($module == 'PREMEAL') {
            $module_column = 'p.premeal_enabled = 1';
        }

        $this->db->from('products p');
        $this->db->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.is_active = 1 AND sp.deleted_at IS NULL', 'inner');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.deleted_at', NULL);
        $this->db->where($module_column);

        if (!empty($category_id)) {
            $this->db->where('p.category_id', $category_id);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get single product detail by ID for a store (for API)
     * Filters by module type (QSR, KOT, PREMEAL)
     *
     * @param int    $product_id Product ID
     * @param int    $store_id   Store ID
     * @param string $module     Module type: QSR, KOT, or PREMEAL
     * @return object|null Product object or null if not found
     */
    public function get_product_detail($product_id, $store_id, $module)
    {
        // Build module-specific condition
        $module_column = '';
        if ($module == 'QSR') {
            $module_column = 'p.qsr_enabled = 1';
        } elseif ($module == 'KOT') {
            $module_column = 'p.kot_enabled = 1';
        } elseif ($module == 'PREMEAL') {
            $module_column = 'p.premeal_enabled = 1';
        }

        $this->db->select('p.id, p.name, p.short_name, p.description, p.ingredients, p.thumbnail, p.images,
                          p.base_price, p.discount_price, p.tax_percentage,
                          p.is_vegetarian, p.is_vegan, p.calories,
                          p.breakfast, p.lunch, p.dinner,
                          p.is_featured, p.is_popular, p.display_order,
                          c.id as category_id, c.name as category_name,
                          sp.price as store_price, sp.available_stock');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.is_active = 1 AND sp.deleted_at IS NULL', 'inner');
        $this->db->where('p.id', $product_id);
        $this->db->where('p.is_active', 1);
        $this->db->where('p.deleted_at', NULL);
        $this->db->where($module_column);

        return $this->db->get()->row();
    }

    /**
     * Search products by keyword for a store (for API) with pagination
     * Searches in product name, short_name, and description
     * Filters by module type (QSR, KOT, PREMEAL)
     *
     * @param int    $store_id Store ID
     * @param string $keyword  Search keyword
     * @param string $module   Module type: QSR, KOT, or PREMEAL
     * @param int    $limit    Number of products per page
     * @param int    $offset   Offset for pagination
     * @return array List of matching products
     */
    public function search_products($store_id, $keyword, $module, $limit = 20, $offset = 0)
    {
        // Build module-specific condition
        $module_column = '';
        if ($module == 'QSR') {
            $module_column = 'p.qsr_enabled = 1';
        } elseif ($module == 'KOT') {
            $module_column = 'p.kot_enabled = 1';
        } elseif ($module == 'PREMEAL') {
            $module_column = 'p.premeal_enabled = 1';
        }

        $this->db->select('p.id, p.name, p.short_name, p.description, p.ingredients, p.thumbnail, p.images,
                          p.base_price, p.discount_price, p.tax_percentage,
                          p.is_vegetarian, p.is_vegan, p.calories,
                          p.breakfast, p.lunch, p.dinner,
                          p.is_featured, p.is_popular, p.display_order,
                          c.id as category_id, c.name as category_name,
                          sp.price as store_price, sp.available_stock');
        $this->db->from('products p');
        $this->db->join('categories c', 'c.id = p.category_id', 'left');
        $this->db->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.is_active = 1 AND sp.deleted_at IS NULL', 'inner');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.is_available', 1);
        $this->db->where('p.deleted_at', NULL);
        $this->db->where($module_column);

        // Search in name, short_name, and description
        $this->db->group_start();
        $this->db->like('p.name', $keyword);
        $this->db->or_like('p.short_name', $keyword);
        $this->db->or_like('p.description', $keyword);
        $this->db->group_end();

        $this->db->order_by('p.is_featured', 'DESC');
        $this->db->order_by('p.display_order', 'ASC');
        $this->db->order_by('p.name', 'ASC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    /**
     * Get total count of search results for a store (for pagination)
     * Filters by module type (QSR, KOT, PREMEAL)
     *
     * @param int    $store_id Store ID
     * @param string $keyword  Search keyword
     * @param string $module   Module type: QSR, KOT, or PREMEAL
     * @return int Total count of matching products
     */
    public function search_products_count($store_id, $keyword, $module)
    {
        // Build module-specific condition
        $module_column = '';
        if ($module == 'QSR') {
            $module_column = 'p.qsr_enabled = 1';
        } elseif ($module == 'KOT') {
            $module_column = 'p.kot_enabled = 1';
        } elseif ($module == 'PREMEAL') {
            $module_column = 'p.premeal_enabled = 1';
        }

        $this->db->from('products p');
        $this->db->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . $this->db->escape($store_id) . ' AND sp.is_active = 1 AND sp.deleted_at IS NULL', 'inner');
        $this->db->where('p.is_active', 1);
        $this->db->where('p.is_available', 1);
        $this->db->where('p.deleted_at', NULL);
        $this->db->where($module_column);

        // Search in name, short_name, and description
        $this->db->group_start();
        $this->db->like('p.name', $keyword);
        $this->db->or_like('p.short_name', $keyword);
        $this->db->or_like('p.description', $keyword);
        $this->db->group_end();

        return $this->db->count_all_results();
    }
}
