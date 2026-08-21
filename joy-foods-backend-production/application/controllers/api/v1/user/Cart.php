<?php
//Jai Sree Ram
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Cart API Controller
 *
 * Handles cart operations for the mobile app.
 *
 * @category  Controllers
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-01
 */
class Cart extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Products_model', 'products');
        $this->load->model('Cart_model', 'cart_model');
        $this->load->helper('common');
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
     * @param int $store_id   Store ID to validate
     * @param int $company_id Company ID from token
     * @return object|bool Store object if valid, false otherwise
     */
    private function validate_store($store_id, $company_id)
    {
        if (empty($store_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'store_id is required',
                'data' => null
            ]);
            return false;
        }

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
                'message' => 'Invalid store or store not accessible',
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
     * @return object|bool Employee object if valid, false otherwise
     */
    private function validate_module($module, $employee_id)
    {
        $valid_modules = ['QSR', 'KOT', 'PREMEAL'];
        if (empty($module) || !in_array(strtoupper($module), $valid_modules)) {
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
        $module = strtoupper($module);
        $has_access = false;
        if ($module == 'QSR' && $employee->qsr_access == 1) {
            $has_access = true;
        } elseif ($module == 'KOT' && $employee->kot_permission == 1) {
            $has_access = true;
        } elseif ($module == 'PREMEAL' && $employee->premeal_access == 1) {
            $has_access = true;
        }

        if (!$has_access) {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'You do not have access to ' . $module . ' module',
                'data' => null
            ]);
            return false;
        }

        return $employee;
    }

    /**
     * Validate product for cart
     *
     * @param int    $product_id Product ID
     * @param int    $store_id   Store ID
     * @param string $module     Module type (QSR, KOT, PREMEAL)
     * @return object|bool Product object with store price if valid, false otherwise
     */
    private function validate_product($product_id, $store_id, $module)
    {
        if (empty($product_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'product_id is required',
                'data' => null
            ]);
            return false;
        }

        // Get product
        $product = $this->common->getdatabytable('products', [
            'id' => $product_id,
            'is_active' => 1,
            'is_available' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($product)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Product not found or not available',
                'data' => null
            ]);
            return false;
        }

        // Check if product has the module enabled
        $module = strtoupper($module);
        $module_enabled = false;
        if ($module == 'QSR' && $product->qsr_enabled == 1) {
            $module_enabled = true;
        } elseif ($module == 'KOT' && $product->kot_enabled == 1) {
            $module_enabled = true;
        } elseif ($module == 'PREMEAL' && $product->premeal_enabled == 1) {
            $module_enabled = true;
        }

        if (!$module_enabled) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product is not available in ' . $module . ' module',
                'data' => null
            ]);
            return false;
        }

        // Check if product is available in this store (store_products)
        $store_product = $this->common->getdatabytable('store_products', [
            'store_id' => $store_id,
            'product_id' => $product_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($store_product)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product is not available in this store',
                'data' => null
            ]);
            return false;
        }

        // Add store price to product object
        $product->store_price = $store_product->price;
        $product->store_product_id = $store_product->id;
        $product->available_stock = $store_product->available_stock;

        return $product;
    }

    /**
     * Validate cart item belongs to employee
     *
     * @param int $cart_id     Cart ID
     * @param int $employee_id Employee ID from token
     * @return object|bool Cart object if valid, false otherwise
     */
    private function validate_cart_item($cart_id, $employee_id)
    {
        if (empty($cart_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'cart_id is required',
                'data' => null
            ]);
            return false;
        }

        $cart = $this->cart_model->get_cart_item($cart_id, $employee_id);

        if (empty($cart)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Cart item not found',
                'data' => null
            ]);
            return false;
        }

        return $cart;
    }

    /**
     * Add product to cart
     *
     * POST /api/v1/user/cart/add
     *
     * Required parameters (form-data):
     * - store_id: Store ID
     * - product_id: Product ID
     * - module: QSR, KOT, or PREMEAL
     *
     * Optional parameters:
     * - quantity: Quantity (default: 1)
     * - note: Special instructions
     * - scheduled_date: For PREMEAL only (YYYY-MM-DD)
     * - meal_type: For PREMEAL only (BREAKFAST, LUNCH, DINNER, SNACKS)
     */
    public function add()
    {
        // Check API key
        if (!$this->check_auth()) {
            return;
        }

        // Authenticate user
        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $employee_id = $auth->employee_id;
        $company_id = $auth->company_id;

        // Get form-data input
        $post_data = $this->input->post(null, true);

        $store_id = isset($post_data['store_id']) ? (int)$post_data['store_id'] : null;
        $product_id = isset($post_data['product_id']) ? (int)$post_data['product_id'] : null;
        $module = isset($post_data['module']) ? strtoupper(trim($post_data['module'])) : null;
        $quantity = isset($post_data['quantity']) ? (int)$post_data['quantity'] : 1;
        $note = isset($post_data['note']) ? trim($post_data['note']) : null;
        $scheduled_date = isset($post_data['scheduled_date']) ? trim($post_data['scheduled_date']) : null;
        $meal_type = isset($post_data['meal_type']) ? strtoupper(trim($post_data['meal_type'])) : null;

        // Validate quantity
        if ($quantity < 1) {
            $quantity = 1;
        }

        // Validate store
        $store = $this->validate_store($store_id, $company_id);
        if (!$store) {
            return;
        }

        // Validate module and employee access
        $employee = $this->validate_module($module, $employee_id);
        if (!$employee) {
            return;
        }

        // Validate product
        $product = $this->validate_product($product_id, $store_id, $module);
        if (!$product) {
            return;
        }

        // For PREMEAL, validate scheduled_date and meal_type
        if ($module == 'PREMEAL') {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'PREMEAL module is not supported in this version',
                'data' => null
            ]);
        }

        // Check if item already exists in cart
        $existing_cart = $this->cart_model->get_existing_cart($employee_id, $store_id, $product_id, $module);

        // Calculate total quantity (existing + new)
        $total_quantity = $existing_cart ? ($existing_cart->quantity + $quantity) : $quantity;

        // Check if sufficient stock is available
        // NULL available_stock means unlimited stock
        if ($product->available_stock !== null && (int)$product->available_stock < $total_quantity) {
            $available = (int)$product->available_stock;
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => $available == 0
                    ? 'This product is currently out of stock'
                    : 'Insufficient stock. Only ' . $available . ' available',
                'data' => [
                    'available_stock' => $available,
                    'requested_qty' => $total_quantity,
                    'current_cart_qty' => $existing_cart ? (int)$existing_cart->quantity : 0
                ]
            ]);
            return;
        }

        if ($existing_cart) {
            // Update quantity
            $new_quantity = $existing_cart->quantity + $quantity;
            $this->cart_model->update_quantity($existing_cart->id, $new_quantity, $note ?: $existing_cart->note);

            $cart_id = $existing_cart->id;
            $message = 'Cart updated successfully';
        } else {
            // Insert new cart item
            $cart_data = [
                'employee_id' => $employee_id,
                'store_id' => $store_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'module' => $module,
                'note' => $note,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($module == 'PREMEAL') {
                $cart_data['scheduled_date'] = $scheduled_date;
                $cart_data['meal_type'] = $meal_type;
            }

            $cart_id = $this->cart_model->add_to_cart($cart_data);
            $message = 'Product added to cart successfully';
        }

        // Get cart item count
        $cart_count = $this->cart_model->get_cart_count($employee_id, $store_id, $module);

        // Use store price if available, otherwise base price
        $price = !empty($product->store_price) ? $product->store_price : $product->base_price;

        $this->logger->info('Product added to cart', [
            'cart_id' => $cart_id,
            'employee_id' => $employee_id,
            'store_id' => $store_id,
            'product_id' => $product_id,
            'module' => $module,
            'quantity' => $quantity
        ], 'cart');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => $message,
            'data' => [
                'cart_id' => (int)$cart_id,
                'product' => [
                    'id' => (int)$product->id,
                    'name' => $product->name,
                    'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                    'price' => (float)$price,
                    'quantity' => $existing_cart ? (int)($existing_cart->quantity + $quantity) : (int)$quantity
                ],
                'cart_items_count' => (int)$cart_count
            ]
        ]);
    }

    /**
     * Increment cart item quantity by 1
     *
     * POST /api/v1/user/cart/increment
     *
     * Required parameters (form-data):
     * - cart_id: Cart ID
     */
    public function increment()
    {
        // Check API key
        if (!$this->check_auth()) {
            return;
        }

        // Authenticate user
        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $employee_id = $auth->employee_id;

        // Get form-data input
        $post_data = $this->input->post(null, true);
        $cart_id = isset($post_data['cart_id']) ? (int)$post_data['cart_id'] : null;

        // Validate cart item
        $cart = $this->validate_cart_item($cart_id, $employee_id);
        if (!$cart) {
            return;
        }

        // Get current stock for this product in the store
        $store_product = $this->common->getdatabytable('store_products', [
            'store_id' => $cart->store_id,
            'product_id' => $cart->product_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        $new_quantity = $cart->quantity + 1;

        // Check if sufficient stock is available for increment
        // NULL available_stock means unlimited stock
        if ($store_product && $store_product->available_stock !== null && (int)$store_product->available_stock < $new_quantity) {
            $available = (int)$store_product->available_stock;
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Cannot add more. Only ' . $available . ' available in stock',
                'data' => [
                    'cart_id' => (int)$cart_id,
                    'available_stock' => $available,
                    'current_qty' => (int)$cart->quantity
                ]
            ]);
            return;
        }

        // Increment quantity
        $this->cart_model->update_quantity($cart_id, $new_quantity);

        $this->logger->info('Cart item incremented', [
            'cart_id' => $cart_id,
            'employee_id' => $employee_id,
            'new_quantity' => $new_quantity
        ], 'cart');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Quantity updated successfully',
            'data' => [
                'cart_id' => (int)$cart_id,
                'quantity' => (int)$new_quantity
            ]
        ]);
    }

    /**
     * Decrement cart item quantity by 1
     * If quantity becomes 0, item is removed from cart
     *
     * POST /api/v1/user/cart/decrement
     *
     * Required parameters (form-data):
     * - cart_id: Cart ID
     */
    public function decrement()
    {
        // Check API key
        if (!$this->check_auth()) {
            return;
        }

        // Authenticate user
        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $employee_id = $auth->employee_id;

        // Get form-data input
        $post_data = $this->input->post(null, true);
        $cart_id = isset($post_data['cart_id']) ? (int)$post_data['cart_id'] : null;

        // Validate cart item
        $cart = $this->validate_cart_item($cart_id, $employee_id);
        if (!$cart) {
            return;
        }

        $new_quantity = $cart->quantity - 1;

        if ($new_quantity <= 0) {
            // Remove item from cart
            $this->cart_model->remove_from_cart($cart_id);

            $this->logger->info('Cart item removed (quantity reached 0)', [
                'cart_id' => $cart_id,
                'employee_id' => $employee_id
            ], 'cart');

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'Item removed from cart',
                'data' => [
                    'cart_id' => (int)$cart_id,
                    'removed' => true
                ]
            ]);
        } else {
            // Update quantity
            $this->cart_model->update_quantity($cart_id, $new_quantity);

            $this->logger->info('Cart item decremented', [
                'cart_id' => $cart_id,
                'employee_id' => $employee_id,
                'new_quantity' => $new_quantity
            ], 'cart');

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'Quantity updated successfully',
                'data' => [
                    'cart_id' => (int)$cart_id,
                    'quantity' => (int)$new_quantity
                ]
            ]);
        }
    }

    /**
     * Remove item from cart
     *
     * POST /api/v1/user/cart/remove
     *
     * Required parameters (form-data):
     * - cart_id: Cart ID
     */
    public function remove()
    {
        // Check API key
        if (!$this->check_auth()) {
            return;
        }

        // Authenticate user
        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $employee_id = $auth->employee_id;

        // Get form-data input
        $post_data = $this->input->post(null, true);
        $cart_id = isset($post_data['cart_id']) ? (int)$post_data['cart_id'] : null;

        // Validate cart item
        $cart = $this->validate_cart_item($cart_id, $employee_id);
        if (!$cart) {
            return;
        }

        // Remove item from cart
        $this->cart_model->remove_from_cart($cart_id);

        $this->logger->info('Cart item removed', [
            'cart_id' => $cart_id,
            'employee_id' => $employee_id,
            'product_id' => $cart->product_id
        ], 'cart');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Item removed from cart',
            'data' => [
                'cart_id' => (int)$cart_id,
                'removed' => true
            ]
        ]);
    }

    /**
     * Get cart items list with summary
     *
     * GET /api/v1/user/cart/list?store_id=123&module=QSR
     *
     * Required parameters (query):
     * - store_id: Store ID
     * - module: QSR, KOT, or PREMEAL
     */
    public function list()
    {
        // Check API key
        if (!$this->check_auth()) {
            return;
        }

        // Authenticate user
        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $employee_id = $auth->employee_id;
        $company_id = $auth->company_id;

        // Get query parameters
        $store_id = $this->input->get('store_id', true);
        $module = $this->input->get('module', true) ? strtoupper(trim($this->input->get('module', true))) : null;

        // Validate store
        $store = $this->validate_store($store_id, $company_id);
        if (!$store) {
            return;
        }

        // Validate module and employee access
        $employee = $this->validate_module($module, $employee_id);
        if (!$employee) {
            return;
        }

        // Get cart items from model
        $cart_items = $this->cart_model->get_cart_items($employee_id, $store_id, $module);

        // Initialize summary values
        $total_items = 0;
        $subtotal = 0;
        $total_tax = 0;
        $items_data = [];
        $insufficient_stock_products = [];

        // Process cart items
        foreach ($cart_items as $item) {
            // Use store price if available, otherwise base price (price is GST inclusive)
            $inclusive_price = !empty($item->store_price) ? (float)$item->store_price : (float)$item->base_price;
            $quantity = (int)$item->quantity;
            $tax_percentage = (float)$item->tax_percentage;
            $available_stock = $item->available_stock;

            // Calculate using helper functions (GST inclusive formula)
            $unit_base_price = calculate_base_price($inclusive_price, $tax_percentage);
            $unit_gst = calculate_gst_amount($inclusive_price, $tax_percentage);

            // Calculate item totals
            $item_subtotal = $unit_base_price * $quantity;
            $item_tax = $unit_gst * $quantity;
            $item_total = $inclusive_price * $quantity;

            // Add to totals
            $total_items += $quantity;
            $subtotal += $item_subtotal;
            $total_tax += $item_tax;

            // Check if sufficient stock available for cart quantity
            // NULL available_stock = unlimited stock
            $is_in_stock = ($available_stock === null || (int)$available_stock >= $quantity);

            // Track products with insufficient stock
            if (!$is_in_stock) {
                $insufficient_stock_products[] = [
                    'product_name' => $item->product_name,
                    'requested_qty' => $quantity,
                    'available_qty' => (int)$available_stock
                ];
            }

            // Format item data
            $item_data = [
                'cart_id' => (int)$item->cart_id,
                'product' => [
                    'id' => (int)$item->product_id,
                    'name' => $item->product_name,
                    'short_name' => $item->short_name,
                    'thumbnail' => $item->thumbnail ? base_url($item->thumbnail) : null,
                    'is_vegetarian' => (bool)$item->is_vegetarian,
                    'is_vegan' => (bool)$item->is_vegan,
                    'is_in_stock' => $is_in_stock,
                    'available_stock' => $available_stock === null ? null : (int)$available_stock
                ],
                'quantity' => $quantity,
                'unit_price' => round($inclusive_price, 2),
                'tax_percentage' => round($tax_percentage, 2),
                'item_subtotal' => round($item_subtotal, 2),
                'item_tax' => round($item_tax, 2),
                'item_total' => round($item_total, 2),
                'note' => $item->note
            ];

            // Add PREMEAL specific fields
            if ($module == 'PREMEAL') {
                $item_data['scheduled_date'] = $item->scheduled_date;
                $item_data['meal_type'] = $item->meal_type;
            }

            $items_data[] = $item_data;
        }

        // Discount (as of now 0, can be implemented later)
        $discount = 0;

        // Calculate total payable
        $total_payable = $subtotal + $total_tax - $discount;

        // Check if order can be processed (no items with insufficient stock)
        $order_process = empty($insufficient_stock_products);
        $order_process_msg = '';
        if (!$order_process) {
            $product_names = array_column($insufficient_stock_products, 'product_name');
            $order_process_msg = 'Insufficient stock for: ' . implode(', ', $product_names) . '. Please adjust quantities.';
        }

        // Cart summary
        $summary = [
            'total_items' => $total_items,
            'total_taxable_value' => round($subtotal, 2),
            'total_tax' => round($total_tax, 2),
            'discount' => round($discount, 2),
            'total_payable' => round($total_payable, 2)
        ];

        // Store info
        $store_info = [
            'id' => (int)$store->id,
            'name' => $store->name,
            'short_name' => $store->short_name,
            'address' => trim(implode(', ', array_filter([
                $store->address_line1,
                $store->address_line2,
                $store->city,
                $store->state,
                $store->pincode
            ]))),
            'phone' => $store->primary_phone
        ];

        // Get employee wallet balance
        $wallet = getWalletMoneyArray($employee_id);
        $available_balance = round((float)$wallet['available'], 2);

        $this->logger->info('Cart list fetched', [
            'employee_id' => $employee_id,
            'store_id' => $store_id,
            'module' => $module,
            'items_count' => count($items_data),
            'total_payable' => $total_payable,
            'order_process' => $order_process
        ], 'cart');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($items_data) ? 'Cart is empty' : 'Cart fetched successfully',
            'data' => [
                'store' => $store_info,
                'items' => $items_data,
                'summary' => $summary,
                'available_balance' => $available_balance,
                'order_process' => $order_process,
                'order_process_msg' => $order_process_msg,
                'insufficient_stock_products' => $insufficient_stock_products
            ]
        ]);
    }

    /**
     * Get Cart Count
     *
     * Returns the number of items in the cart for a given store and module.
     *
     * GET /api/v1/user/cart/count
     *
     * Required query parameters:
     * - store_id: Store ID
     * - module: Module type (QSR, KOT)
     *
     * @return void JSON response with cart count
     */
    public function count()
    {
        // Check API key
        if (!$this->check_auth()) {
            return;
        }

        // Authenticate user
        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $employee_id = $auth->employee_id;
        $company_id = $auth->company_id;

        // Get query parameters
        $store_id = $this->input->get('store_id', true);
        $module = $this->input->get('module', true) ? strtoupper(trim($this->input->get('module', true))) : null;

        // Validate store
        $store = $this->validate_store($store_id, $company_id);
        if (!$store) {
            return;
        }

        // Validate module and employee access
        $employee = $this->validate_module($module, $employee_id);
        if (!$employee) {
            return;
        }

        // Get cart count
        $count = $this->cart_model->get_cart_count($employee_id, $store_id, $module);

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Cart count fetched successfully',
            'data' => [
                'count' => (int) $count
            ]
        ]);
    }
}
