<?php
//Jai Sree Ram
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Catalog API Controller
 *
 * Handles catalog operations including categories and products
 * listing for the mobile app.
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
class Catalog extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Categories_model', 'categories');
        $this->load->model('Products_model', 'products');
        $this->load->model('Cart_model', 'cart_model');
        $this->load->model('StoreSchedule_model', 'schedule_model');
        $this->load->library('PolicyLib');
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
            $token = substr($authHeader, 7);
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
     * Validate store access
     *
     * @param int    $store_id   Store ID to validate
     * @param int    $company_id Company ID from token
     * @return object|bool Store object if valid, false otherwise
     */
    private function validate_store($store_id, $company_id)
    {
        $store = $this->common->getdatabytable('stores', [
            'id' => $store_id,
            'company_id' => $company_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($store)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid store',
                'data' => null
            ]);
            return false;
        }

        return $store;
    }

    /**
     * Validate module parameter and check employee access
     *
     * @param string $module      Module type
     * @param int    $employee_id Employee ID to check permissions
     * @return bool True if valid and has access, false otherwise
     */
    private function validate_module($module, $employee_id)
    {
        $valid_modules = ['QSR', 'KOT', 'PREMEAL'];
        if (empty($module) || !in_array($module, $valid_modules)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Valid module is required (QSR, KOT, or PREMEAL)',
                'data' => null
            ]);
            return false;
        }

        // Get employee data to check module access
        $employee = $this->common->getdatabytable('employees', [
            'id' => $employee_id,
            'is_active' => 1
        ]);

        if (empty($employee)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Employee not found or inactive',
                'data' => null
            ]);
            return false;
        }

        // Check if employee has access to the requested module
        $has_access = false;
        if ($module == 'QSR' && $employee->qsr_access == 1) {
            $has_access = true;
        } elseif ($module == 'KOT' && $employee->kot_permission == 1) {
            $has_access = true;
        } elseif ($module == 'PREMEAL' && $employee->premeal_access == 1) {
            $has_access = true;
        }

        if (!$has_access) {
            $this->logger->warning('Employee does not have access to module', [
                'employee_id' => $employee_id,
                'module' => $module
            ], 'catalog');
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'You do not have access to this module',
                'data' => null
            ]);
            return false;
        }

        return true;
    }

    /**
     * Get Categories by Store and Module
     *
     * Returns list of categories that have active products in the specified store.
     * Filters by module type (QSR, KOT, PREMEAL).
     *
     * @api GET /api/v1/user/catalog/categories?store_id=123&module=QSR
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

        $this->logger->info('Get Categories API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'catalog');

        // Authenticate and get decoded token
        $decoded = $this->authenticate();
        if (!$decoded) {
            return;
        }

        // Get parameters
        $store_id = $this->input->get('store_id', true);
        $module = strtoupper($this->input->get('module', true));

        // Validate store_id
        if (empty($store_id)) {
            $this->logger->warning('Store ID not provided', [
                'employee_id' => $decoded->employee_id
            ], 'catalog');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store ID is required',
                'data' => null
            ]);
            return;
        }

        // Validate module and employee access
        if (!$this->validate_module($module, $decoded->employee_id)) {
            return;
        }

        $this->logger->info('Fetching categories', [
            'employee_id' => $decoded->employee_id,
            'store_id' => $store_id,
            'module' => $module
        ], 'catalog');

        // Validate store
        $store = $this->validate_store($store_id, $decoded->company_id);
        if (!$store) {
            $this->logger->warning('Store validation failed', [
                'store_id' => $store_id,
                'company_id' => $decoded->company_id
            ], 'catalog');
            return;
        }

        // Get categories from model
        $categories = $this->categories->get_categories_by_store($store_id, $module);

        if (empty($categories)) {
            $this->logger->info('No categories found', [
                'store_id' => $store_id,
                'module' => $module
            ], 'catalog');
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
            'module' => $module,
            'categories_count' => count($categories_data)
        ], 'catalog');

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
     * Get Products by Store, Module and Category (Paginated)
     *
     * Returns paginated list of products for a specific store and category filtered by module.
     *
     * @api GET /api/v1/user/catalog/products?store_id=123&module=QSR&category_id=5
     * @api GET /api/v1/user/catalog/products?store_id=123&module=QSR&category_id=5&page=2&per_page=20
     *
     * @header Authorization Bearer {token} - JWT token from login
     * @query  store_id (required) - Store ID to fetch products for
     * @query  module (required) - Module type: QSR, KOT, or PREMEAL
     * @query  category_id (required) - Category ID to filter products
     * @query  page (optional) - Page number (default: 1)
     * @query  per_page (optional) - Products per page (default: 20, max: 50)
     *
     * @return void Outputs JSON response
     *         - 200: Success with paginated products list
     *         - 401: Unauthorized (missing or invalid token)
     *         - 400: Store ID/Module/Category ID is required or invalid
     */
    public function products()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Get Products API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'catalog');

        // Authenticate and get decoded token
        $decoded = $this->authenticate();
        if (!$decoded) {
            return;
        }

        // Get parameters
        $store_id = $this->input->get('store_id', true);
        $module = strtoupper($this->input->get('module', true));
        $category_id = $this->input->get('category_id', true);
        $page = (int)$this->input->get('page', true) ?: 1;
        $per_page = (int)$this->input->get('per_page', true) ?: 20;

        // Validate and cap per_page
        if ($per_page < 1) {
            $per_page = 20;
        }
        if ($per_page > 50) {
            $per_page = 50;
        }

        // Ensure page is at least 1
        if ($page < 1) {
            $page = 1;
        }

        // Calculate offset
        $offset = ($page - 1) * $per_page;

        // Validate store_id
        if (empty($store_id)) {
            $this->logger->warning('Store ID not provided', [
                'employee_id' => $decoded->employee_id
            ], 'catalog');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store ID is required',
                'data' => null
            ]);
            return;
        }

        // Validate category_id
        if (empty($category_id)) {
            $this->logger->warning('Category ID not provided', [
                'employee_id' => $decoded->employee_id
            ], 'catalog');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Category ID is required',
                'data' => null
            ]);
            return;
        }

        // Validate module
        if (!$this->validate_module($module, $decoded->employee_id)) {
            return;
        }

        $this->logger->info('Fetching products', [
            'employee_id' => $decoded->employee_id,
            'store_id' => $store_id,
            'module' => $module,
            'category_id' => $category_id,
            'page' => $page,
            'per_page' => $per_page
        ], 'catalog');

        // Validate store
        $store = $this->validate_store($store_id, $decoded->company_id);
        if (!$store) {
            $this->logger->warning('Store validation failed', [
                'store_id' => $store_id,
                'company_id' => $decoded->company_id
            ], 'catalog');
            return;
        }

        // Get total count for pagination
        $total_count = $this->products->get_products_count_by_store($store_id, $category_id, $module);

        // Calculate pagination info
        $total_pages = ceil($total_count / $per_page);
        $has_next = $page < $total_pages;
        $has_previous = $page > 1;

        // Get products from model with pagination
        $products = $this->products->get_products_by_store($store_id, $category_id, $module, $per_page, $offset);

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

            // Format images array
            $images = [];
            if (!empty($product->images)) {
                $images_array = json_decode($product->images, true);
                if (is_array($images_array)) {
                    foreach ($images_array as $img_path) {
                        $images[] = base_url($img_path);
                    }
                }
            }

            $products_data[] = [
                'id' => (int)$product->id,
                'name' => $product->name,
                'short_name' => $product->short_name,
                'description' => $product->description,
                'ingredients' => $product->ingredients,
                'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                'images' => $images,
                'price' => (float)$price,
                'base_price' => (float)$product->base_price,
                'discount_price' => $product->discount_price ? (float)$product->discount_price : null,
                'tax_percentage' => (float)$product->tax_percentage,
                'is_vegetarian' => (bool)$product->is_vegetarian,
                'is_vegan' => (bool)$product->is_vegan,
                'calories' => $product->calories ? (int)$product->calories : null,
                'meal_times' => [
                    'breakfast' => (bool)$product->breakfast,
                    'lunch' => (bool)$product->lunch,
                    'dinner' => (bool)$product->dinner
                ],
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

        $this->logger->info('Products fetched successfully', [
            'store_id' => $store_id,
            'module' => $module,
            'category_id' => $category_id,
            'products_count' => count($products_data),
            'page' => $page,
            'total_pages' => $total_pages
        ], 'catalog');

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
                    'has_next' => $has_next,
                    'has_previous' => $has_previous
                ]
            ]
        ]);
    }

    /**
     * Get Product Detail by ID
     *
     * Returns detailed information for a specific product.
     *
     * @api GET /api/v1/user/catalog/product_detail?store_id=123&module=QSR&product_id=45
     *
     * @header Authorization Bearer {token} - JWT token from login
     * @query  store_id (required) - Store ID to get store-specific pricing
     * @query  module (required) - Module type: QSR, KOT, or PREMEAL
     * @query  product_id (required) - Product ID to fetch details for
     *
     * @return void Outputs JSON response
     *         - 200: Success with product details
     *         - 401: Unauthorized (missing or invalid token)
     *         - 400: Store ID/Module/Product ID is required or invalid
     *         - 404: Product not found
     */
    public function product_detail()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Get Product Detail API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'catalog');

        // Authenticate and get decoded token
        $decoded = $this->authenticate();
        if (!$decoded) {
            return;
        }

        // Get parameters
        $store_id = $this->input->get('store_id', true);
        $module = strtoupper($this->input->get('module', true));
        $product_id = $this->input->get('product_id', true);

        // Validate store_id
        if (empty($store_id)) {
            $this->logger->warning('Store ID not provided', [
                'employee_id' => $decoded->employee_id
            ], 'catalog');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store ID is required',
                'data' => null
            ]);
            return;
        }

        // Validate product_id
        if (empty($product_id)) {
            $this->logger->warning('Product ID not provided', [
                'employee_id' => $decoded->employee_id
            ], 'catalog');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product ID is required',
                'data' => null
            ]);
            return;
        }

        // Validate module
        if (!$this->validate_module($module, $decoded->employee_id)) {
            return;
        }

        $this->logger->info('Fetching product detail', [
            'employee_id' => $decoded->employee_id,
            'store_id' => $store_id,
            'module' => $module,
            'product_id' => $product_id
        ], 'catalog');

        // Validate store
        $store = $this->validate_store($store_id, $decoded->company_id);
        if (!$store) {
            $this->logger->warning('Store validation failed', [
                'store_id' => $store_id,
                'company_id' => $decoded->company_id
            ], 'catalog');
            return;
        }

        // Get product detail from model
        $product = $this->products->get_product_detail($product_id, $store_id, $module);

        if (empty($product)) {
            $this->logger->info('Product not found', [
                'product_id' => $product_id,
                'store_id' => $store_id,
                'module' => $module
            ], 'catalog');
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Product not found',
                'data' => null
            ]);
            return;
        }

        // Use store price if available, otherwise base price
        $price = !empty($product->store_price) ? $product->store_price : $product->base_price;

        // Check stock availability (NULL = unlimited, 0 = out of stock)
        $is_in_stock = ($product->available_stock === null || $product->available_stock > 0);

        // Format images array
        $images = [];
        if (!empty($product->images)) {
            $images_array = json_decode($product->images, true);
            if (is_array($images_array)) {
                foreach ($images_array as $img_path) {
                    $images[] = base_url($img_path);
                }
            }
        }

        $employee_id = $decoded->employee_id;

        // Check if item already exists in cart
        $existing_cart = $this->cart_model->get_existing_cart($employee_id, $store_id, $product->id, $module);
        $is_in_cart = !empty($existing_cart);
        $cart_quantity = $is_in_cart ? (int)$existing_cart->quantity : 0;
        $cart_id = $is_in_cart ? (int)$existing_cart->id : null;

        // Format product data
        $product_data = [
            'id' => (int)$product->id,
            'name' => $product->name,
            'short_name' => $product->short_name,
            'description' => $product->description,
            'ingredients' => $product->ingredients,
            'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
            'images' => $images,
            'price' => (float)$price,
            'base_price' => (float)$product->base_price,
            'discount_price' => $product->discount_price ? (float)$product->discount_price : null,
            'tax_percentage' => (float)$product->tax_percentage,
            'is_vegetarian' => (bool)$product->is_vegetarian,
            'is_vegan' => (bool)$product->is_vegan,
            'calories' => $product->calories ? (int)$product->calories : null,
            'meal_times' => [
                'breakfast' => (bool)$product->breakfast,
                'lunch' => (bool)$product->lunch,
                'dinner' => (bool)$product->dinner
            ],
            'is_featured' => (bool)$product->is_featured,
            'is_popular' => (bool)$product->is_popular,
            'is_in_stock' => $is_in_stock,
            'is_in_cart' => $is_in_cart,
            'cart_id' => $cart_id,
            'cart_quantity' => $cart_quantity,
            'module' => $module,
            'category' => [
                'id' => $product->category_id ? (int)$product->category_id : null,
                'name' => $product->category_name
            ]
        ];

        // Add PREMEAL timings if module is PREMEAL
        if ($module == 'PREMEAL') {
            // Determine which meal type this product is for
            $meal_type = null;
            $serving_time = null;

            if ($product->breakfast) {
                $meal_type = 'BREAKFAST';
                $serving_time = $store->breakfast_time;
            } elseif ($product->lunch) {
                $meal_type = 'LUNCH';
                $serving_time = $store->lunch_time;
            } elseif ($product->dinner) {
                $meal_type = 'DINNER';
                $serving_time = $store->dinner_time;
            }

            // Get employee's policy for booking limits
            $policy = $this->policylib->get_employee_policy($decoded->employee_id, 'PREMEAL');
            $advance_booking_days = ($policy && $policy->advance_booking_days) ? (int)$policy->advance_booking_days : 7;
            $booking_cutoff_hours = ($policy && $policy->booking_cutoff_hours) ? (int)$policy->booking_cutoff_hours : 2;
            $daily_meal_limit = ($policy && $policy->daily_meal_limit) ? (int)$policy->daily_meal_limit : 1;

            // Calculate max booking date
            $max_booking_date = date('Y-m-d', strtotime("+{$advance_booking_days} days"));

            // Calculate cutoff time based on serving time and cutoff hours
            $cutoff_time = null;
            $today_available = false;
            $today_cutoff_reason = null;

            if ($serving_time) {
                $cutoff_time = date('H:i', strtotime("-{$booking_cutoff_hours} hours", strtotime($serving_time)));

                // Check if today is available (cutoff not passed)
                $current_time = date('H:i');
                if ($current_time < $cutoff_time) {
                    // Cutoff not passed, but also check if meal limit is not exceeded
                    $today_limit_check = $this->policylib->check_daily_limit(
                        $decoded->employee_id,
                        date('Y-m-d'),
                        $meal_type,
                        $policy
                    );

                    if ($today_limit_check['available']) {
                        $today_available = true;
                    } else {
                        $today_cutoff_reason = $today_limit_check['reason'] ?? 'Already booked for today';
                    }
                } else {
                    $today_cutoff_reason = 'Booking cutoff time (' . $cutoff_time . ') has passed for today';
                }
            } else {
                $today_cutoff_reason = 'Serving time not configured';
            }

            $product_data['premeal_info'] = [
                'meal_type' => $meal_type,
                'serving_time' => $serving_time ? substr($serving_time, 0, 5) : null,
                'cutoff_time' => $cutoff_time,
                'today' => [
                    'date' => date('Y-m-d'),
                    'available' => $today_available,
                    'reason' => $today_cutoff_reason
                ],
                'store_timings' => [
                    'breakfast_time' => $store->breakfast_time ? substr($store->breakfast_time, 0, 5) : null,
                    'lunch_time' => $store->lunch_time ? substr($store->lunch_time, 0, 5) : null,
                    'dinner_time' => $store->dinner_time ? substr($store->dinner_time, 0, 5) : null
                ],
                'booking_rules' => [
                    'advance_booking_days' => $advance_booking_days,
                    'max_booking_date' => $max_booking_date,
                    'daily_meal_limit' => $daily_meal_limit
                ]
            ];

            // Get weekly schedule for this product
            $schedules = $this->schedule_model->get_product_schedule($store_id, $product_id);
            $days_order = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];

            // Build schedule by day
            $schedule_by_day = [];
            foreach ($schedules as $schedule) {
                $schedule_by_day[$schedule->day_of_week] = $schedule;
            }

            $weekly_schedule = [];
            foreach ($days_order as $day) {
                if (isset($schedule_by_day[$day]) && $schedule_by_day[$day]->is_active) {
                    $s = $schedule_by_day[$day];
                    $menu_items = $s->menu_json ? json_decode($s->menu_json, true) : null;
                    $weekly_schedule[] = [
                        'day' => $day,
                        'available' => true,
                        'display_order' => (int)$s->display_order,
                        'menu_items' => $menu_items
                    ];
                } else {
                    $weekly_schedule[] = [
                        'day' => $day,
                        'available' => false,
                        'display_order' => 0,
                        'menu_items' => null
                    ];
                }
            }

            // Get only available days for quick reference
            $available_days = array_values(array_filter(array_map(function($s) {
                return $s['available'] ? $s['day'] : null;
            }, $weekly_schedule)));

            $product_data['weekly_schedule'] = [
                'schedule' => $weekly_schedule,
                'available_days' => $available_days,
                'total_available_days' => count($available_days)
            ];
        }

        $this->logger->info('Product detail fetched successfully', [
            'product_id' => $product_id,
            'store_id' => $store_id,
            'module' => $module
        ], 'catalog');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Product detail fetched successfully',
            'data' => [
                'product' => $product_data
            ]
        ]);
    }

    /**
     * Search Products by Keyword (Paginated)
     *
     * Returns paginated list of products matching the search keyword.
     * Searches in product name, short_name, and description.
     *
     * @api GET /api/v1/user/catalog/search?store_id=123&module=QSR&keyword=pizza
     * @api GET /api/v1/user/catalog/search?store_id=123&module=QSR&keyword=pizza&page=2&per_page=20
     *
     * @header Authorization Bearer {token} - JWT token from login
     * @query  store_id (required) - Store ID to search products in
     * @query  module (required) - Module type: QSR, KOT, or PREMEAL
     * @query  keyword (required) - Search keyword (min 2 characters)
     * @query  page (optional) - Page number (default: 1)
     * @query  per_page (optional) - Products per page (default: 20, max: 50)
     *
     * @return void Outputs JSON response
     *         - 200: Success with paginated search results
     *         - 401: Unauthorized (missing or invalid token)
     *         - 400: Store ID/Module/Keyword is required or invalid
     */
    public function search()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Search Products API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'catalog');

        // Authenticate and get decoded token
        $decoded = $this->authenticate();
        if (!$decoded) {
            return;
        }

        // Get parameters
        $store_id = $this->input->get('store_id', true);
        $module = strtoupper($this->input->get('module', true));
        $keyword = trim($this->input->get('keyword', true));
        $page = (int)$this->input->get('page', true) ?: 1;
        $per_page = (int)$this->input->get('per_page', true) ?: 20;

        // Validate and cap per_page
        if ($per_page < 1) {
            $per_page = 20;
        }
        if ($per_page > 50) {
            $per_page = 50;
        }

        // Ensure page is at least 1
        if ($page < 1) {
            $page = 1;
        }

        // Calculate offset
        $offset = ($page - 1) * $per_page;

        // Validate store_id
        if (empty($store_id)) {
            $this->logger->warning('Store ID not provided', [
                'employee_id' => $decoded->employee_id
            ], 'catalog');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store ID is required',
                'data' => null
            ]);
            return;
        }

        // Validate keyword
        if (empty($keyword)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Search keyword is required',
                'data' => null
            ]);
            return;
        }

        if (strlen($keyword) < 2) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Search keyword must be at least 2 characters',
                'data' => null
            ]);
            return;
        }

        // Validate module
        if (!$this->validate_module($module, $decoded->employee_id)) {
            return;
        }

        $this->logger->info('Searching products', [
            'employee_id' => $decoded->employee_id,
            'store_id' => $store_id,
            'module' => $module,
            'keyword' => $keyword,
            'page' => $page,
            'per_page' => $per_page
        ], 'catalog');

        // Validate store
        $store = $this->validate_store($store_id, $decoded->company_id);
        if (!$store) {
            $this->logger->warning('Store validation failed', [
                'store_id' => $store_id,
                'company_id' => $decoded->company_id
            ], 'catalog');
            return;
        }

        // Get total count for pagination
        $total_count = $this->products->search_products_count($store_id, $keyword, $module);

        // Calculate pagination info
        $total_pages = ceil($total_count / $per_page);
        $has_next = $page < $total_pages;
        $has_previous = $page > 1;


        $employee_id = $decoded->employee_id;

        // Get products from model with pagination
        $products = $this->products->search_products($store_id, $keyword, $module, $per_page, $offset);

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

            // Format images array
            $images = [];
            if (!empty($product->images)) {
                $images_array = json_decode($product->images, true);
                if (is_array($images_array)) {
                    foreach ($images_array as $img_path) {
                        $images[] = base_url($img_path);
                    }
                }
            }

            $products_data[] = [
                'id' => (int)$product->id,
                'name' => $product->name,
                'short_name' => $product->short_name,
                'description' => $product->description,
                'ingredients' => $product->ingredients,
                'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                'images' => $images,
                'price' => (float)$price,
                'base_price' => (float)$product->base_price,
                'discount_price' => $product->discount_price ? (float)$product->discount_price : null,
                'tax_percentage' => (float)$product->tax_percentage,
                'is_vegetarian' => (bool)$product->is_vegetarian,
                'is_vegan' => (bool)$product->is_vegan,
                'calories' => $product->calories ? (int)$product->calories : null,
                'meal_times' => [
                    'breakfast' => (bool)$product->breakfast,
                    'lunch' => (bool)$product->lunch,
                    'dinner' => (bool)$product->dinner
                ],
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

        $this->logger->info('Search completed successfully', [
            'store_id' => $store_id,
            'module' => $module,
            'keyword' => $keyword,
            'results_count' => count($products_data),
            'page' => $page,
            'total_pages' => $total_pages
        ], 'catalog');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($products_data) ? 'No products found' : 'Products found',
            'data' => [
                'keyword' => $keyword,
                'products' => $products_data,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total_count' => $total_count,
                    'total_pages' => $total_pages,
                    'has_next' => $has_next,
                    'has_previous' => $has_previous
                ]
            ]
        ]);
    }
}
