<?php
//Jai Sree Ram
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Home API Controller
 *
 * Handles home screen related APIs including banners, featured content,
 * and other home screen elements for the mobile app.
 *
 * @category  Controllers
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2025-12-31
 */
class Home extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Banners_model', 'banners');
        $this->load->model('Categories_model', 'categories');
        $this->load->model('Products_model', 'products');
        $this->load->model('Cart_model', 'cart_model');
        $this->tokenHandler = new TokenHandler();

        // Load Monolog library for logging
        $this->load->library('monolog');
        $this->logger = new Monolog();
    }

    private function output($data)
    {
        header("Content-Type: application/json; charset=UTF-8");
        if(isset($data['status'])){
            http_response_code($data['status']);
        }
        echo json_encode($data);
    }

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

    private function check_bearer_token()
    {
        $headers_of_page = $this->input->request_headers();
        if (isset($headers_of_page['Authorization']) && strpos($headers_of_page['Authorization'], 'Bearer ') === 0) {
            $authHeader = $headers_of_page['Authorization'];
            $token = substr($authHeader, 7); // Remove 'Bearer ' from the beginning
            return $token;
        }
        return null;
    }

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
     * Authenticate request and validate employee token
     *
     * @return object|bool Returns decoded token data if authenticated, false otherwise
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

        // Validate role
        if (!isset($decoded->role) || $decoded->role !== 'employee') {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Access denied. Invalid role',
                'data' => null
            ]);
            return false;
        }

        // Validate required fields
        if (!isset($decoded->employee_id) || !isset($decoded->company_id)) {
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
     * Get Banners
     *
     * Returns list of active banners for a company.
     * If company_id is provided in GET parameter, use that.
     * Otherwise, get company_id from authenticated user's session/token.
     *
     * @api GET /api/v1/user/home/banners
     * @api GET /api/v1/user/home/banners?company_id=123
     *
     * @header Authorization Bearer {token} - JWT token from login
     * @query  company_id (optional) - Company ID to fetch banners for
     *
     * @return void Outputs JSON response
     *         - 200: Success with banners list
     *         - 401: Unauthorized (missing or invalid token)
     *         - 400: Company ID is required
     *         - 404: No banners found
     */
    public function banners()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Get Banners API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'home');

        // Authenticate and get decoded token
        $decoded = $this->authenticate();
        if (!$decoded) {
            return; // authenticate() already sent error response
        }

        // Get company_id from GET parameter or from token
        $company_id = $this->input->get('company_id', true);

        if (empty($company_id)) {
            // Use company_id from authenticated user's token
            $company_id = $decoded->company_id;
        }

        $this->logger->info('Fetching banners', [
            'employee_id' => $decoded->employee_id,
            'company_id' => $company_id,
            'source' => $this->input->get('company_id', true) ? 'query_param' : 'token'
        ], 'home');

        // Validate company exists and is active
        $company = $this->common->getdatabytable('companies', [
            'id' => $company_id,
            'is_active' => 1
        ]);

        if (empty($company)) {
            $this->logger->warning('Company not found or inactive', [
                'company_id' => $company_id
            ], 'home');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid company',
                'data' => null
            ]);
            return;
        }

        // Get active banners for the company
        $banners = $this->banners->get_active_banners_by_company($company_id);

        if (empty($banners)) {
            $this->logger->info('No banners found for company', [
                'company_id' => $company_id
            ], 'home');
            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'No banners found',
                'data' => [
                    'banners' => [],
                    'total_count' => 0
                ]
            ]);
            return;
        }

        // Format banner data
        $banners_data = [];
        foreach ($banners as $banner) {
            $banners_data[] = [
                'id' => (int)$banner->id,
                'title' => $banner->title,
                'description' => $banner->description,
                'image_url' => $banner->image_path ? base_url($banner->image_path) : null,
                'action' => [
                    'type' => $banner->action_type,
                    'payload' => $banner->action_payload
                ],
                'display_order' => (int)$banner->display_order
            ];
        }

        $this->logger->info('Banners fetched successfully', [
            'company_id' => $company_id,
            'banners_count' => count($banners_data)
        ], 'home');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Banners fetched successfully',
            'data' => [
                'banners' => $banners_data,
                'total_count' => count($banners_data)
            ]
        ]);
    }

    /**
     * Get Featured Categories
     *
     * Returns list of featured/primary categories that have products in the selected store.
     * Only categories with active store_products for the given store are returned.
     * Filters by module type (QSR, KOT, PREMEAL) for both categories and products.
     *
     * @api GET /api/v1/user/home/categories?store_id=123&module=QSR
     *
     * @header Authorization Bearer {token} - JWT token from login
     * @query  store_id (required) - Store ID to fetch categories for
     * @query  module (required) - Module type: QSR, KOT, or PREMEAL
     *
     * @return void Outputs JSON response
     *         - 200: Success with categories list
     *         - 401: Unauthorized (missing or invalid token)
     *         - 400: Store ID/Module is required or invalid
     */
    public function categories()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Get Featured Categories API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'home');

        // Authenticate and get decoded token
        $decoded = $this->authenticate();
        if (!$decoded) {
            return; // authenticate() already sent error response
        }

        // Get store_id from GET parameter (required)
        $store_id = $this->input->get('store_id', true);
        $module = strtoupper($this->input->get('module', true));

        if (empty($store_id)) {
            $this->logger->warning('Store ID not provided', [
                'employee_id' => $decoded->employee_id
            ], 'home');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store ID is required',
                'data' => null
            ]);
            return;
        }

        // Validate module parameter
        $valid_modules = ['QSR', 'KOT', 'PREMEAL'];
        if (empty($module) || !in_array($module, $valid_modules)) {
            $this->logger->warning('Invalid module provided', [
                'employee_id' => $decoded->employee_id,
                'module' => $module
            ], 'home');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Valid module is required (QSR, KOT, or PREMEAL)',
                'data' => null
            ]);
            return;
        }

        $this->logger->info('Fetching categories for store', [
            'employee_id' => $decoded->employee_id,
            'store_id' => $store_id,
            'module' => $module
        ], 'home');

        // Validate store exists, is active, and belongs to user's company
        $store = $this->common->getdatabytable('stores', [
            'id' => $store_id,
            'company_id' => $decoded->company_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);


        if (empty($store)) {
            $this->logger->warning('Store not found or inactive', [
                'store_id' => $store_id,
                'company_id' => $decoded->company_id
            ], 'home');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid store',
                'data' => null
            ]);
            return;
        }

        // Get categories from model
        $categories = $this->categories->get_categories_by_store($store_id, $module);

        if (empty($categories)) {
            $this->logger->info('No categories found for store', [
                'store_id' => $store_id
            ], 'home');
            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'No categories found',
                'data' => [
                    'categories' => [],
                    'total_count' => 0
                ]
            ]);
            return;
        }

        // Format category data
        $categories_data = [];
        foreach ($categories as $category) {
            $categories_data[] = [
                'id' => (int)$category->id,
                'name' => $category->name,
                'description' => $category->description,
                'icon' => $category->icon ? base_url($category->icon) : null,
                'thumbnail' => $category->thumbnail ? base_url($category->thumbnail) : null,
                'is_primary' => (bool)$category->is_primary,
                'display_order' => (int)$category->display_order
            ];
        }

        $this->logger->info('Categories fetched successfully', [
            'store_id' => $store_id,
            'categories_count' => count($categories_data)
        ], 'home');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Categories fetched successfully',
            'data' => [
                'categories' => $categories_data,
                'total_count' => count($categories_data)
            ]
        ]);
    }

    /**
     * Get Featured Products
     *
     * Returns list of featured/popular products for a specific store.
     * Only products that are marked as featured or popular are returned.
     * Filters by module type (QSR, KOT, PREMEAL).
     *
     * @api GET /api/v1/user/home/featured?store_id=123&module=QSR
     * @api GET /api/v1/user/home/featured?store_id=123&module=QSR&limit=20
     *
     * @header Authorization Bearer {token} - JWT token from login
     * @query  store_id (required) - Store ID to fetch products for
     * @query  module (required) - Module type: QSR, KOT, or PREMEAL
     * @query  limit (optional) - Number of products to return (default: 10, max: 50)
     *
     * @return void Outputs JSON response
     *         - 200: Success with products list
     *         - 401: Unauthorized (missing or invalid token)
     *         - 400: Store ID/Module is required or invalid
     */
    public function featured()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Get Featured Products API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'home');

        // Authenticate and get decoded token
        $decoded = $this->authenticate();
        if (!$decoded) {
            return;
        }

        // Get parameters
        $store_id = $this->input->get('store_id', true);
        $module = strtoupper($this->input->get('module', true));
        $limit = (int)$this->input->get('limit', true) ?: 10;

        // Cap limit at 50
        if ($limit > 50) {
            $limit = 50;
        }

        if (empty($store_id)) {
            $this->logger->warning('Store ID not provided', [
                'employee_id' => $decoded->employee_id
            ], 'home');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store ID is required',
                'data' => null
            ]);
            return;
        }

        // Validate module parameter
        $valid_modules = ['QSR', 'KOT', 'PREMEAL'];
        if (empty($module) || !in_array($module, $valid_modules)) {
            $this->logger->warning('Invalid module provided', [
                'employee_id' => $decoded->employee_id,
                'module' => $module
            ], 'home');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Valid module is required (QSR, KOT, or PREMEAL)',
                'data' => null
            ]);
            return;
        }

        $this->logger->info('Fetching featured products for store', [
            'employee_id' => $decoded->employee_id,
            'store_id' => $store_id,
            'module' => $module,
            'limit' => $limit
        ], 'home');

        // Validate store exists, is active, and belongs to user's company
        $store = $this->common->getdatabytable('stores', [
            'id' => $store_id,
            'company_id' => $decoded->company_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($store)) {
            $this->logger->warning('Store not found or inactive', [
                'store_id' => $store_id,
                'company_id' => $decoded->company_id
            ], 'home');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid store',
                'data' => null
            ]);
            return;
        }

        // Get featured products from model
        $products = $this->products->get_featured_products_by_store($store_id, $module, $limit);

        if (empty($products)) {
            $this->logger->info('No featured products found for store', [
                'store_id' => $store_id,
                'module' => $module
            ], 'home');
            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'No featured products found',
                'data' => [
                    'products' => [],
                    'total_count' => 0
                ]
            ]);
            return;
        }


        $employee_id = $decoded->employee_id;

        // Format product data
        $products_data = [];
        foreach ($products as $product) {
            // Use store price if available, otherwise base price
            $price = !empty($product->store_price) ? $product->store_price : $product->base_price;

            // Check stock availability (NULL = unlimited, 0 = out of stock)
            $is_in_stock = ($product->available_stock === null || $product->available_stock > 0);

            // Check if item already exists in cart
            $existing_cart = $this->cart_model->get_existing_cart($employee_id, $store_id, $product->id, $module);
            $is_in_cart = !empty($existing_cart);
            $cart_quantity = $is_in_cart ? (int)$existing_cart->quantity : 0;
            $cart_id = $is_in_cart ? (int)$existing_cart->id : null;

            $products_data[] = [
                'id' => (int)$product->id,
                'name' => $product->name,
                'short_name' => $product->short_name,
                'description' => $product->description,
                'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                'price' => (float)$price,
                'base_price' => (float)$product->base_price,
                'discount_price' => $product->discount_price ? (float)$product->discount_price : null,
                'tax_percentage' => (float)$product->tax_percentage,
                'is_vegetarian' => (bool)$product->is_vegetarian,
                'is_vegan' => (bool)$product->is_vegan,
                'calories' => $product->calories ? (int)$product->calories : null,
                'is_featured' => (bool)$product->is_featured,
                'is_popular' => (bool)$product->is_popular,
                'is_in_stock' => $is_in_stock,
                'is_in_cart' => $is_in_cart,
                'cart_id' => $cart_id,
                'cart_quantity' => $cart_quantity,
                'category' => [
                    'id' => $product->category_id ? (int)$product->category_id : null,
                    'name' => $product->category_name
                ]
            ];
        }

        $this->logger->info('Featured products fetched successfully', [
            'store_id' => $store_id,
            'module' => $module,
            'products_count' => count($products_data)
        ], 'home');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Featured products fetched successfully',
            'data' => [
                'products' => $products_data,
                'total_count' => count($products_data)
            ]
        ]);
    }
}
