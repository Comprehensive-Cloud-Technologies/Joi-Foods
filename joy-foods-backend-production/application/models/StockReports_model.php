<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Stock Reports Model
 *
 * Powers the client portal inventory reports:
 *  - Current Stock Report  (live stock levels per store/product)
 *  - Stock Transactions    (IN/OUT/SET ledger history)
 *
 * @category  Models
 * @package   Joy_Foods
 * @developed_by ZooBit Infotech for Joy Foods.
 */
class StockReports_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ============================================================
     * Current Stock Report
     * ============================================================ */

    /**
     * Apply common filters for the current stock query.
     */
    private function _apply_current_stock_filters($client_id, $filters)
    {
        $this->db->where('s.client_id', $client_id);
        $this->db->where('sp.deleted_at IS NULL', NULL, FALSE);
        $this->db->where('p.deleted_at IS NULL', NULL, FALSE);

        if (!empty($filters['store_id'])) {
            $this->db->where('sp.store_id', $filters['store_id']);
        }
        if (!empty($filters['company_id'])) {
            $this->db->where('s.company_id', $filters['company_id']);
        }
        if (!empty($filters['category_id'])) {
            $this->db->where('p.category_id', $filters['category_id']);
        }
        if (!empty($filters['stock_status'])) {
            if ($filters['stock_status'] === 'in_stock') {
                $this->db->where('sp.available_stock >', 0);
            } elseif ($filters['stock_status'] === 'out_of_stock') {
                $this->db->where('sp.available_stock', 0);
            } elseif ($filters['stock_status'] === 'unlimited') {
                $this->db->where('sp.available_stock IS NULL', NULL, FALSE);
            } elseif ($filters['stock_status'] === 'tracked') {
                $this->db->where('sp.available_stock IS NOT NULL', NULL, FALSE);
            }
        }
    }

    /**
     * Get current stock levels for all store products.
     */
    public function get_current_stock($client_id, $filters = array())
    {
        $this->db
            ->select('sp.id, sp.available_stock, sp.is_active AS store_is_active, sp.price,
                      sp.updated_at AS stock_updated_at,
                      p.id AS product_id, p.name AS product_name, p.short_name, p.is_vegetarian,
                      c.name AS category_name,
                      s.id AS store_id, s.name AS store_name, s.store_code, s.store_type,
                      co.name AS company_name')
            ->from('store_products sp')
            ->join('stores s', 's.id = sp.store_id', 'inner')
            ->join('products p', 'p.id = sp.product_id', 'inner')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('companies co', 'co.id = s.company_id', 'left');

        $this->_apply_current_stock_filters($client_id, $filters);

        return $this->db
            ->order_by('s.name', 'ASC')
            ->order_by('p.name', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Get summary counts for the current stock report.
     */
    public function get_current_stock_summary($client_id, $filters = array())
    {
        $this->db
            ->select('COUNT(*) AS total_products,
                      SUM(CASE WHEN sp.available_stock IS NULL THEN 1 ELSE 0 END) AS unlimited_products,
                      SUM(CASE WHEN sp.available_stock = 0 THEN 1 ELSE 0 END) AS out_of_stock,
                      SUM(CASE WHEN sp.available_stock > 0 THEN 1 ELSE 0 END) AS in_stock,
                      COALESCE(SUM(CASE WHEN sp.available_stock IS NOT NULL THEN sp.available_stock ELSE 0 END), 0) AS total_units')
            ->from('store_products sp')
            ->join('stores s', 's.id = sp.store_id', 'inner')
            ->join('products p', 'p.id = sp.product_id', 'inner');

        $this->_apply_current_stock_filters($client_id, $filters);

        return $this->db->get()->row();
    }

    /* ============================================================
     * Stock Transactions Report
     * ============================================================ */

    /**
     * Apply common filters for the transactions query.
     */
    private function _apply_transaction_filters($client_id, $filters)
    {
        $this->db->where('st.client_id', $client_id);

        if (!empty($filters['store_id'])) {
            $this->db->where('st.store_id', $filters['store_id']);
        }
        if (!empty($filters['company_id'])) {
            $this->db->where('st.company_id', $filters['company_id']);
        }
        if (!empty($filters['product_id'])) {
            $this->db->where('st.product_id', $filters['product_id']);
        }
        if (!empty($filters['transaction_type'])) {
            $this->db->where('st.transaction_type', $filters['transaction_type']);
        }
        if (!empty($filters['source'])) {
            $this->db->where('st.source', $filters['source']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(st.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(st.created_at) <=', $filters['date_to']);
        }
    }

    /**
     * Get stock transactions (the IN/OUT/SET ledger).
     */
    public function get_transactions($client_id, $filters = array())
    {
        $this->db
            ->select('st.*,
                      p.name AS product_name, p.short_name,
                      s.name AS store_name, s.store_code, s.store_type,
                      co.name AS company_name')
            ->from('stock_transactions st')
            ->join('products p', 'p.id = st.product_id', 'left')
            ->join('stores s', 's.id = st.store_id', 'left')
            ->join('companies co', 'co.id = st.company_id', 'left');

        $this->_apply_transaction_filters($client_id, $filters);

        return $this->db
            ->order_by('st.created_at', 'DESC')
            ->order_by('st.id', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Get summary totals for the transactions report.
     */
    public function get_transactions_summary($client_id, $filters = array())
    {
        $this->db
            ->select("COUNT(*) AS total_transactions,
                      COALESCE(SUM(CASE WHEN st.transaction_type = 'IN' THEN st.quantity ELSE 0 END), 0) AS total_in,
                      COALESCE(SUM(CASE WHEN st.transaction_type = 'OUT' THEN st.quantity ELSE 0 END), 0) AS total_out,
                      COALESCE(SUM(CASE WHEN st.transaction_type = 'SET' THEN 1 ELSE 0 END), 0) AS total_adjustments")
            ->from('stock_transactions st');

        $this->_apply_transaction_filters($client_id, $filters);

        return $this->db->get()->row();
    }

    /* ============================================================
     * Filter dropdown helpers
     * ============================================================ */

    /**
     * Get categories that have products for this client (for filter dropdown).
     */
    public function get_categories_list($client_id)
    {
        return $this->db
            ->select('id, name')
            ->where('client_id', $client_id)
            ->where('is_active', 1)
            ->where('deleted_at', NULL)
            ->order_by('name', 'ASC')
            ->get('categories')
            ->result();
    }
}
