<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Stock Helper
 *
 * Records stock movements into the stock_transactions ledger.
 * Used by every place that mutates store_products.available_stock so the
 * client portal can report on stock in/out history and current levels.
 *
 * @category  Helpers
 * @package   Joy_Foods
 * @developed_by ZooBit Infotech for Joy Foods.
 */

if (!function_exists('record_stock_transaction')) {
    /**
     * Record a single stock movement into stock_transactions.
     *
     * Only meaningful for finite (tracked) stock. Callers should skip
     * unlimited-stock products (available_stock = NULL) — but this function
     * also no-ops gracefully if both before and after are null.
     *
     * @param array $params {
     *     @type int    store_id          (required)
     *     @type int    product_id        (required)
     *     @type string transaction_type  IN | OUT | SET (required)
     *     @type string source            ORDER_PLACED | ORDER_REJECTED |
     *                                    ORDER_CANCELLED | MANUAL_UPDATE | INITIAL_STOCK
     *     @type int    quantity          Positive movement size
     *     @type int    stock_before      Stock before change (null = was unlimited)
     *     @type int    stock_after       Stock after change (null = now unlimited)
     *     @type string reference_type    ORDER | MANUAL (optional)
     *     @type int    reference_id      order_id when order-related (optional)
     *     @type string order_number      (optional)
     *     @type string performed_by_type STORE_STAFF | EMPLOYEE | GUEST | SYSTEM
     *     @type int    performed_by_id   (optional)
     *     @type string note              (optional)
     * }
     * @return int|false Inserted row ID, or false on failure
     */
    function record_stock_transaction($params)
    {
        $CI = &get_instance();

        $store_id = isset($params['store_id']) ? (int)$params['store_id'] : 0;
        $product_id = isset($params['product_id']) ? (int)$params['product_id'] : 0;

        if (empty($store_id) || empty($product_id) || empty($params['transaction_type'])) {
            log_message('error', 'record_stock_transaction: missing required params');
            return false;
        }

        // Resolve client_id + company_id from the store (denormalized for fast reports)
        $store = $CI->db
            ->select('client_id, company_id')
            ->where('id', $store_id)
            ->get('stores')
            ->row();

        if (empty($store)) {
            log_message('error', 'record_stock_transaction: store not found (' . $store_id . ')');
            return false;
        }

        $data = [
            'client_id'         => (int)$store->client_id,
            'company_id'        => $store->company_id ? (int)$store->company_id : null,
            'store_id'          => $store_id,
            'product_id'        => $product_id,
            'transaction_type'  => $params['transaction_type'],
            'source'            => isset($params['source']) ? $params['source'] : 'MANUAL_UPDATE',
            'quantity'          => isset($params['quantity']) ? (int)$params['quantity'] : 0,
            'stock_before'      => array_key_exists('stock_before', $params) && $params['stock_before'] !== null ? (int)$params['stock_before'] : null,
            'stock_after'       => array_key_exists('stock_after', $params) && $params['stock_after'] !== null ? (int)$params['stock_after'] : null,
            'reference_type'    => isset($params['reference_type']) ? $params['reference_type'] : null,
            'reference_id'      => isset($params['reference_id']) ? (int)$params['reference_id'] : null,
            'order_number'      => isset($params['order_number']) ? $params['order_number'] : null,
            'performed_by_type' => isset($params['performed_by_type']) ? $params['performed_by_type'] : 'SYSTEM',
            'performed_by_id'   => isset($params['performed_by_id']) ? (int)$params['performed_by_id'] : null,
            'note'              => isset($params['note']) ? $params['note'] : null,
            'created_at'        => date('Y-m-d H:i:s')
        ];

        if ($CI->db->insert('stock_transactions', $data)) {
            return $CI->db->insert_id();
        }

        log_message('error', 'record_stock_transaction: insert failed for product ' . $product_id);
        return false;
    }
}
