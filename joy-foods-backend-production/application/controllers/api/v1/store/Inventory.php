<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Store Inventory Controller
 *
 * Handles inventory management for store staff.
 * Lists categories, products with store-specific pricing, and allows
 * stock and status updates.
 *
 * @category  Controllers
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-04
 */
class Inventory extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('StoreInventory_model', 'inventory_model');
        $this->tokenHandler = new TokenHandler();

        // Load Monolog library for logging
        $this->load->library('monolog');
        $this->logger = new Monolog();
    }

    /**
     * Output JSON response
     *
     * @param array $data Response data
     * @return void
     */
    private function output($data)
    {
        header("Content-Type: application/json; charset=UTF-8");
        if (isset($data['status'])) {
            http_response_code($data['status']);
        }
        echo json_encode($data);
    }

    /**
     * Check API authorization header
     *
     * @return bool True if authorized
     */
    private function check_auth()
    {
        $headers_of_page = $this->input->request_headers();
        if (isset($headers_of_page['Auth']) && $headers_of_page['Auth'] == config_item('api_authorization')) {
            return true;
        }
        $this->output([
            'status' => 401,
            'success' => false,
            'message' => 'Unauthorized. Invalid API key.'
        ]);
        return false;
    }

    /**
     * Get Bearer token from Authorization header
     *
     * @return string|null Token or null
     */
    private function check_bearer_token()
    {
        $headers_of_page = $this->input->request_headers();
        if (isset($headers_of_page['Authorization']) && strpos($headers_of_page['Authorization'], 'Bearer ') === 0) {
            $authHeader = $headers_of_page['Authorization'];
            $token = substr($authHeader, 7);
            return $token;
        }
        return null;
    }

    /**
     * Decode JWT token
     *
     * @param string $token JWT token
     * @return object|null Decoded token or null
     */
    private function decode_token($token)
    {
        try {
            $decoded = $this->tokenHandler->DecodeToken($token);
            return $decoded;
        } catch (Exception $e) {
            log_message('error', 'Token decoding failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Authenticate store staff and return decoded token
     *
     * @return object|bool Decoded token or false
     */
    private function authenticate()
    {
        $token = $this->check_bearer_token();

        if (empty($token)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Authorization token is required',
                'data' => null
            ]);
            return false;
        }

        $decoded = $this->decode_token($token);

        if (empty($decoded)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid or expired token',
                'data' => null
            ]);
            return false;
        }

        // Check if token has expired
        if (isset($decoded->exp) && $decoded->exp < time()) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Token has expired',
                'data' => null
            ]);
            return false;
        }

        // Validate role - must be store_staff
        if (!isset($decoded->role) || $decoded->role !== 'store_staff') {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Access denied. Invalid role',
                'data' => null
            ]);
            return false;
        }

        // Validate required fields
        if (!isset($decoded->staff_id) || !isset($decoded->store_id)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid token data',
                'data' => null
            ]);
            return false;
        }

        return $decoded;
    }

    /**
     * Get Categories
     *
     * Returns list of categories that have products assigned to this store.
     *
     * @api POST /api/v1/store/inventory/categories
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @return void Outputs JSON response
     *         - 200: List of categories with product counts
     *         - 401: Unauthorized
     */
    public function categories()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        // Authenticate store staff
        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $store_id = $auth->store_id;
        $staff_id = $auth->staff_id;

        $this->logger->info('Store Inventory Categories API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_inventory');

        // Get categories
        $categories = $this->inventory_model->get_store_categories($store_id);

        // Format categories
        $categories_data = [];
        foreach ($categories as $category) {
            $categories_data[] = [
                'id' => (int)$category->id,
                'name' => $category->name,
                'description' => $category->description,
                'icon' => $category->icon ? base_url($category->icon) : null,
                'thumbnail' => $category->thumbnail ? base_url($category->thumbnail) : null,
                'display_order' => (int)$category->display_order,
                'product_count' => (int)$category->product_count
            ];
        }

        $this->logger->info('Categories fetched', [
            'store_id' => $store_id,
            'count' => count($categories_data)
        ], 'store_inventory');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($categories_data) ? 'No categories found' : 'Categories fetched successfully',
            'data' => [
                'categories' => $categories_data,
                'total_count' => count($categories_data)
            ]
        ]);
    }

    /**
     * Get Products
     *
     * Returns list of products assigned to this store with store-specific pricing.
     * Can filter by category.
     *
     * @api POST /api/v1/store/inventory/products
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param int category_id (optional) Filter by category ID
     * @param int page (optional) Page number (default: 1)
     * @param int per_page (optional) Items per page (default: 50, max: 100)
     *
     * @return void Outputs JSON response
     *         - 200: List of products with store pricing
     *         - 401: Unauthorized
     */
    public function products()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        // Authenticate store staff
        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $store_id = $auth->store_id;
        $staff_id = $auth->staff_id;

        $this->logger->info('Store Inventory Products API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_inventory');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $category_id = isset($post_data['category_id']) ? (int)$post_data['category_id'] : null;
        $page = isset($post_data['page']) ? (int)$post_data['page'] : 1;
        $per_page = isset($post_data['per_page']) ? (int)$post_data['per_page'] : 50;

        // Validate pagination
        if ($page < 1) $page = 1;
        if ($per_page < 1) $per_page = 50;
        if ($per_page > 100) $per_page = 100;

        $offset = ($page - 1) * $per_page;

        // Get products
        $products = $this->inventory_model->get_store_products($store_id, $category_id, $per_page, $offset);
        $total_count = $this->inventory_model->count_store_products($store_id, $category_id);

        // Format products
        $products_data = [];
        foreach ($products as $product) {
            $products_data[] = [
                'id' => (int)$product->id,
                'name' => $product->name,
                'short_name' => $product->short_name,
                'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                'is_vegetarian' => (bool)$product->is_vegetarian,
                'is_vegan' => (bool)$product->is_vegan,
                'category' => [
                    'id' => $product->category_id ? (int)$product->category_id : null,
                    'name' => $product->category_name
                ],
                'price' => (float)$product->price,
                'available_stock' => $product->available_stock !== null ? (int)$product->available_stock : null,
                'is_active' => (bool)$product->store_is_active
            ];
        }

        // Pagination info
        $total_pages = ceil($total_count / $per_page);

        $this->logger->info('Products fetched', [
            'store_id' => $store_id,
            'category_id' => $category_id,
            'count' => count($products_data)
        ], 'store_inventory');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($products_data) ? 'No products found' : 'Products fetched successfully',
            'data' => [
                'products' => $products_data,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total_count' => $total_count,
                    'total_pages' => $total_pages,
                    'has_next' => $page < $total_pages,
                    'has_previous' => $page > 1
                ]
            ]
        ]);
    }

    /**
     * Update Stock
     *
     * Updates the available stock for a product at this store.
     *
     * @api POST /api/v1/store/inventory/update_stock
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param int product_id (required) Product ID
     * @param int|null stock (required) New stock value (null or -1 for unlimited)
     *
     * @return void Outputs JSON response
     *         - 200: Stock updated successfully
     *         - 400: Invalid product or parameters
     *         - 401: Unauthorized
     */
    public function update_stock()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        // Authenticate store staff
        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $store_id = $auth->store_id;
        $staff_id = $auth->staff_id;

        $this->logger->info('Update Stock API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_inventory');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $product_id = isset($post_data['product_id']) ? (int)$post_data['product_id'] : null;
        $stock = isset($post_data['stock']) ? $post_data['stock'] : null;

        // Validate product_id
        if (empty($product_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'product_id is required',
                'data' => null
            ]);
            return;
        }

        // Validate stock - must be provided
        if ($stock === null || $stock === '') {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'stock is required (use -1 for unlimited)',
                'data' => null
            ]);
            return;
        }

        // Parse stock value
        $stock = (int)$stock;
        if ($stock < 0) {
            $stock = null; // Unlimited
        }

        // Check if product exists for this store
        if (!$this->inventory_model->store_product_exists($store_id, $product_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product not found for this store',
                'data' => null
            ]);
            return;
        }

        // Get current product for logging
        $product = $this->inventory_model->get_store_product($product_id, $store_id);
        if (empty($product)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product is not available for this store',
                'data' => null
            ]);
            return;
        }
        $old_stock = $product->available_stock;

        // Update stock
        $updated = $this->inventory_model->update_stock($store_id, $product_id, $stock, [
            'performed_by_type' => 'STORE_STAFF',
            'performed_by_id'   => $staff_id,
            'note'              => 'Stock updated by store staff'
        ]);

        if (!$updated) {
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update stock. Please try again.',
                'data' => null
            ]);
            return;
        }

        $this->logger->info('Stock updated', [
            'product_id' => $product_id,
            'store_id' => $store_id,
            'staff_id' => $staff_id,
            'old_stock' => $old_stock,
            'new_stock' => $stock
        ], 'store_inventory');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Stock updated successfully',
            'data' => [
                'product_id' => $product_id,
                'product_name' => $product->name,
                'old_stock' => $old_stock !== null ? (int)$old_stock : null,
                'new_stock' => $stock
            ]
        ]);
    }

    /**
     * Update Status
     *
     * Updates the active/inactive status for a product at this store.
     *
     * @api POST /api/v1/store/inventory/update_status
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param int product_id (required) Product ID
     * @param bool is_active (required) Active status (1 or 0)
     *
     * @return void Outputs JSON response
     *         - 200: Status updated successfully
     *         - 400: Invalid product or parameters
     *         - 401: Unauthorized
     */
    public function update_status()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        // Authenticate store staff
        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $store_id = $auth->store_id;
        $staff_id = $auth->staff_id;

        $this->logger->info('Update Status API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_inventory');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $product_id = isset($post_data['product_id']) ? (int)$post_data['product_id'] : null;
        $is_active = isset($post_data['is_active']) ? $post_data['is_active'] : null;

        // Validate product_id
        if (empty($product_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'product_id is required',
                'data' => null
            ]);
            return;
        }

        // Validate is_active
        if ($is_active === null || $is_active === '') {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'is_active is required (1 for active, 0 for inactive)',
                'data' => null
            ]);
            return;
        }

        $is_active = (bool)$is_active;

        // Check if product exists for this store
        if (!$this->inventory_model->store_product_exists($store_id, $product_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product not found for this store',
                'data' => null
            ]);
            return;
        }

        // Get current product for logging
        $product = $this->inventory_model->get_store_product($product_id, $store_id);
        if (empty($product)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product is not available for this store',
                'data' => null
            ]);
            return;
        }
        $old_status = (bool)$product->store_is_active;

        // Update status
        $updated = $this->inventory_model->update_status($store_id, $product_id, $is_active);

        if (!$updated) {
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update status. Please try again.',
                'data' => null
            ]);
            return;
        }

        $status_text = $is_active ? 'activated' : 'deactivated';

        $this->logger->info('Product status updated', [
            'product_id' => $product_id,
            'store_id' => $store_id,
            'staff_id' => $staff_id,
            'old_status' => $old_status,
            'new_status' => $is_active
        ], 'store_inventory');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Product ' . $status_text . ' successfully',
            'data' => [
                'product_id' => $product_id,
                'product_name' => $product->name,
                'is_active' => $is_active
            ]
        ]);
    }
}
