<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Orders API Controller
 *
 * Handles order operations for QSR, KOT, and PREMEAL modules.
 * Currently implements QSR order flow.
 *
 * @category  Controllers
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-03
 */
class Orders extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Cart_model', 'cart_model');
        $this->load->model('Orders_model', 'orders_model');
        $this->load->model('DeliveryLocations_model', 'locations_model');
        $this->load->library('CouponLib');
        $this->load->library('RazorpayLib');
        $this->load->library('PolicyLib');
        $this->load->library('NotificationLib');
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
     * Validate module access for employee
     *
     * @param string $module      Module type
     * @param int    $employee_id Employee ID
     * @return object|bool Employee object if valid, false otherwise
     */
    private function validate_module_access($module, $employee_id)
    {
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
     * QSR Order Initiate
     *
     * Validates cart, coupon, wallet and creates Razorpay order if needed.
     * Returns order summary with payment details.
     *
     * POST /api/v1/user/orders/qsr_initiate
     *
     * Required parameters (form-data):
     * - store_id: Store ID
     *
     * Optional parameters:
     * - wallet_amount: Amount to use from wallet (default: 0)
     * - coupon_code: Coupon code to apply
     *
     * @return void JSON response with order summary and Razorpay order (if payment needed)
     */
    public function qsr_initiate()
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

        $this->logger->info('QSR Order Initiate API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address()
        ], 'orders');

        // Get form-data input
        $post_data = $this->input->post(null, true);

        $store_id = isset($post_data['store_id']) ? (int) $post_data['store_id'] : null;
        $wallet_amount = isset($post_data['wallet_amount']) ? (float) $post_data['wallet_amount'] : 0;
        $coupon_code = isset($post_data['coupon_code']) ? trim($post_data['coupon_code']) : null;
        $pickup_time = isset($post_data['pickup_time']) ? trim($post_data['pickup_time']) : null;

        // Validate store
        $store = $this->validate_store($store_id, $company_id);
        if (!$store) {
            return;
        }

        // Validate QSR access
        $employee = $this->validate_module_access('QSR', $employee_id);
        if (!$employee) {
            return;
        }

        // Validate pickup_time if provided (must be today and at least 15 minutes from now)
        $is_scheduled = false;
        $validated_pickup_time = null;

        if (!empty($pickup_time)) {
            // Expected format: HH:MM (24-hour) or YYYY-MM-DD HH:MM
            $pickup_datetime = null;

            // Check if it's just time (HH:MM) - assume today
            if (preg_match('/^\d{2}:\d{2}$/', $pickup_time)) {
                $pickup_datetime = DateTime::createFromFormat('Y-m-d H:i', date('Y-m-d') . ' ' . $pickup_time);
            }
            // Check if it's full datetime (YYYY-MM-DD HH:MM)
            elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $pickup_time)) {
                $pickup_datetime = DateTime::createFromFormat('Y-m-d H:i', $pickup_time);
            }
            // Check if it's full datetime with seconds (YYYY-MM-DD HH:MM:SS)
            elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $pickup_time)) {
                $pickup_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $pickup_time);
            }

            if (!$pickup_datetime) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Invalid pickup_time format. Use HH:MM or YYYY-MM-DD HH:MM',
                    'data' => null
                ]);
                return;
            }

            // Check if pickup time is today
            if ($pickup_datetime->format('Y-m-d') !== date('Y-m-d')) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Scheduled pickup is only available for today',
                    'data' => null
                ]);
                return;
            }

            // Check if pickup time is at least 15 minutes from now
            $min_pickup_time = new DateTime();
            $min_pickup_time->modify('+15 minutes');

            if ($pickup_datetime < $min_pickup_time) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Pickup time must be at least 15 minutes from now',
                    'data' => null
                ]);
                return;
            }

            $is_scheduled = true;
            $validated_pickup_time = $pickup_datetime->format('Y-m-d H:i:s');
        }

        // Get cart items
        $cart_items = $this->cart_model->get_cart_items($employee_id, $store_id, 'QSR');

        if (empty($cart_items)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Your cart is empty',
                'data' => null
            ]);
            return;
        }

        // Calculate cart totals
        $subtotal = 0;
        $total_tax = 0;
        $total_items = 0;
        $insufficient_stock_products = [];
        $items_data = [];

        foreach ($cart_items as $item) {
            $quantity = (int) $item->quantity;
            $available_stock = $item->available_stock;

            // Check if sufficient stock available for requested quantity
            // NULL available_stock means unlimited stock
            $is_in_stock = ($available_stock === null || (int) $available_stock >= $quantity);

            if (!$is_in_stock) {
                $insufficient_stock_products[] = [
                    'product_name' => $item->product_name,
                    'requested_qty' => $quantity,
                    'available_qty' => (int) $available_stock
                ];
            }

            $inclusive_price = !empty($item->store_price) ? (float) $item->store_price : (float) $item->base_price;
            $tax_percentage = (float) $item->tax_percentage;

            $unit_base_price = calculate_base_price($inclusive_price, $tax_percentage);
            $unit_gst = calculate_gst_amount($inclusive_price, $tax_percentage);

            $item_subtotal = $unit_base_price * $quantity;
            $item_tax = $unit_gst * $quantity;
            $item_total = $inclusive_price * $quantity;

            $total_items += $quantity;
            $subtotal += $item_subtotal;
            $total_tax += $item_tax;

            $items_data[] = [
                'cart_id' => (int) $item->cart_id,
                'product_id' => (int) $item->product_id,
                'product_name' => $item->product_name,
                'thumbnail' => !empty($item->thumbnail) ? base_url($item->thumbnail) : null,
                'is_vegetarian' => isset($item->is_vegetarian) ? (bool) $item->is_vegetarian : null,
                'quantity' => $quantity,
                'unit_price' => round($inclusive_price, 2),
                'tax_percentage' => round($tax_percentage, 2),
                'base_price' => round($unit_base_price, 2),
                'tax_amount' => round($unit_gst, 2),
                'subtotal' => round($item_subtotal, 2),
                'total' => round($item_total, 2),
                'note' => $item->note,
                'is_in_stock' => $is_in_stock,
                'available_stock' => $available_stock === null ? null : (int) $available_stock
            ];
        }

        // Check if any items have insufficient stock - block order
        if (!empty($insufficient_stock_products)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Some items in your cart have insufficient stock. Please adjust quantities to proceed.',
                'data' => [
                    'insufficient_stock_products' => $insufficient_stock_products,
                    'items' => $items_data
                ]
            ]);
            return;
        }

        $amount_before_discount = round($subtotal + $total_tax, 2);

        // Validate and apply coupon if provided
        $coupon = null;
        $discount_amount = 0;
        $coupon_message = '';

        if (!empty($coupon_code)) {
            // Get client_id from company
            $company = $this->common->getdatabytable('companies', ['id' => $company_id]);
            $client_id = $company ? $company->client_id : 0;

            $coupon_result = $this->couponlib->validateCoupon([
                'coupon_code' => $coupon_code,
                'order_amount' => $amount_before_discount,
                'employee_id' => $employee_id,
                'company_id' => $company_id,
                'client_id' => $client_id,
                'module' => 'QSR'
            ]);

            if ($coupon_result['valid']) {
                $coupon = $coupon_result['coupon'];
                $discount_amount = $coupon_result['discount_amount'];
                $coupon_message = 'Coupon applied: ₹' . number_format($discount_amount, 2) . ' off';
            } else {
                // Return coupon error
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => $coupon_result['message'],
                    'data' => null
                ]);
                return;
            }
        }

        $total_after_discount = round($amount_before_discount - $discount_amount, 2);

        // Validate wallet amount
        $wallet = getWalletMoneyArray($employee_id);
        $available_balance = round((float) $wallet['available'], 2);

        if ($wallet_amount < 0) {
            $wallet_amount = 0;
        }

        // Cap wallet amount to available balance and total after discount
        if ($wallet_amount > $available_balance) {
            $wallet_amount = $available_balance;
        }

        if ($wallet_amount > $total_after_discount) {
            $wallet_amount = $total_after_discount;
        }

        $wallet_amount = round($wallet_amount, 2);

        // Calculate online payment amount
        $online_payment_amount = round($total_after_discount - $wallet_amount, 2);

        // Prepare response data
        $response_data = [
            'store' => [
                'id' => (int) $store->id,
                'name' => $store->name,
                'short_name' => $store->short_name
            ],
            'items' => $items_data,
            'summary' => [
                'total_items' => $total_items,
                'subtotal' => round($subtotal, 2),
                'total_tax' => round($total_tax, 2),
                'amount_before_discount' => $amount_before_discount,
                'discount_amount' => round($discount_amount, 2),
                'total_after_discount' => $total_after_discount,
                'wallet_balance' => $available_balance,
                'wallet_to_use' => $wallet_amount,
                'online_payment_amount' => $online_payment_amount
            ],
            'coupon' => $coupon ? [
                'id' => (int) $coupon->id,
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => (float) $coupon->discount_value
            ] : null,
            'razorpay' => null,
            'payment_required' => $online_payment_amount > 0
        ];

        // Add coupon message if any
        if (!empty($coupon_message)) {
            $response_data['coupon_message'] = $coupon_message;
        }

        // Create Razorpay order if online payment is needed
        if ($online_payment_amount > 0) {
            // Initialize Razorpay with company's credentials
            if (!$this->razorpaylib->init($company_id)) {
                $this->logger->error('Failed to initialize Razorpay', [
                    'employee_id' => $employee_id,
                    'company_id' => $company_id
                ], 'orders');

                $this->output([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Payment service not configured. Please contact support.',
                    'data' => null
                ]);
                return;
            }

            $receipt_id = 'QSR_' . $employee_id . '_' . time();

            $razorpay_result = $this->razorpaylib->createOrder([
                'amount' => $online_payment_amount,
                'receipt' => $receipt_id,
                'notes' => [
                    'employee_id' => $employee_id,
                    'store_id' => $store_id,
                    'module' => 'QSR'
                ]
            ]);

            if (!$razorpay_result['success']) {
                $this->logger->error('Razorpay order creation failed', [
                    'employee_id' => $employee_id,
                    'amount' => $online_payment_amount,
                    'error' => $razorpay_result['message']
                ], 'orders');

                $this->output([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Payment order failed: ' . ($razorpay_result['message'] ?? 'Unknown error'),
                    'data' => null
                ]);
                return;
            }

            $razorpay_order = $razorpay_result['order'];

            // Store pending order data for verification
            $pending_order_data = [
                'employee_id' => $employee_id,
                'store_id' => $store_id,
                'module' => 'QSR',
                'razorpay_order_id' => $razorpay_order['id'],
                'amount' => $online_payment_amount,
                'wallet_amount' => $wallet_amount,
                'coupon_id' => $coupon ? $coupon->id : null,
                'coupon_code' => $coupon ? $coupon->code : null,
                'discount_amount' => $discount_amount,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($total_tax, 2),
                'total_amount' => $total_after_discount,
                'pickup_time' => $validated_pickup_time,
                'items_json' => json_encode($items_data),
                'status' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
            ];

            $pending_id = $this->orders_model->store_pending_order($pending_order_data);
            $response_data['pending_order_id'] = $pending_id;

            $response_data['razorpay'] = [
                'key' => $this->razorpaylib->getKeyId(),
                'amount' => $razorpay_order['amount'],
                'currency' => $razorpay_order['currency'],
                'name' => config_item('application_name') ?: 'Joy Foods',
                'description' => 'Pay with Card / Net Banking / Wallets / UPI',
                'image' => base_url('assets/images/logo.png'),
                'order_id' => $razorpay_order['id'],
                'prefill' => [
                    'name' => trim($employee->first_name . ' ' . ($employee->last_name ?? '')),
                    'email' => $employee->email ?? '',
                    'contact' => $employee->phone ?? ''
                ],
                'notes' => [
                    'employee_id' => (string) $employee_id,
                    'store_id' => (string) $store_id,
                    'module' => 'QSR'
                ],
                'theme' => [
                    'color' => '#BD3839'
                ]
            ];
        } else {
            // No online payment needed - store pending order with wallet-only payment
            $pending_order_data = [
                'employee_id' => $employee_id,
                'store_id' => $store_id,
                'module' => 'QSR',
                'razorpay_order_id' => null,
                'amount' => 0,
                'wallet_amount' => $wallet_amount,
                'coupon_id' => $coupon ? $coupon->id : null,
                'coupon_code' => $coupon ? $coupon->code : null,
                'discount_amount' => $discount_amount,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($total_tax, 2),
                'total_amount' => $total_after_discount,
                'pickup_time' => $validated_pickup_time,
                'items_json' => json_encode($items_data),
                'status' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
            ];

            $pending_id = $this->orders_model->store_pending_order($pending_order_data);
            $response_data['pending_order_id'] = $pending_id;
        }

        // Add scheduling info to response
        if ($is_scheduled) {
            $response_data['schedule'] = [
                'is_scheduled' => true,
                'pickup_time' => $validated_pickup_time
            ];
        }

        $this->logger->info('QSR Order initiated successfully', [
            'employee_id' => $employee_id,
            'store_id' => $store_id,
            'total_amount' => $total_after_discount,
            'wallet_amount' => $wallet_amount,
            'online_payment' => $online_payment_amount
        ], 'orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Order initiated successfully',
            'data' => $response_data
        ]);
    }

    /**
     * QSR Order Complete
     *
     * Verifies payment (if applicable) and creates the order.
     *
     * POST /api/v1/user/orders/qsr_complete
     *
     * Required parameters (form-data):
     * - store_id: Store ID
     *
     * For online payment:
     * - razorpay_order_id: Razorpay order ID
     * - razorpay_payment_id: Razorpay payment ID
     * - razorpay_signature: Razorpay signature
     *
     * For wallet-only payment:
     * - pending_order_id: Pending order ID from initiate response
     *
     * @return void JSON response with order details
     */
    public function qsr_complete()
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

        $this->logger->info('QSR Order Complete API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address()
        ], 'orders');

        // Get form-data input
        $post_data = $this->input->post(null, true);

        $store_id = isset($post_data['store_id']) ? (int) $post_data['store_id'] : null;
        $razorpay_order_id = isset($post_data['razorpay_order_id']) ? trim($post_data['razorpay_order_id']) : null;
        $razorpay_payment_id = isset($post_data['razorpay_payment_id']) ? trim($post_data['razorpay_payment_id']) : null;
        $razorpay_signature = isset($post_data['razorpay_signature']) ? trim($post_data['razorpay_signature']) : null;
        $pending_order_id = isset($post_data['pending_order_id']) ? (int) $post_data['pending_order_id'] : null;

        // Validate store
        $store = $this->validate_store($store_id, $company_id);
        if (!$store) {
            return;
        }

        // Validate QSR access
        $employee = $this->validate_module_access('QSR', $employee_id);
        if (!$employee) {
            return;
        }

        // Determine payment method and get pending order
        $pending_order = null;

        if (!empty($razorpay_order_id)) {
            // Online payment - verify Razorpay
            if (empty($razorpay_payment_id) || empty($razorpay_signature)) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Payment verification details are required',
                    'data' => null
                ]);
                return;
            }

            // Initialize Razorpay with company's credentials
            if (!$this->razorpaylib->init($company_id)) {
                $this->logger->error('Failed to initialize Razorpay for verification', [
                    'employee_id' => $employee_id,
                    'company_id' => $company_id
                ], 'orders');

                $this->output([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Payment service not configured. Please contact support.',
                    'data' => null
                ]);
                return;
            }

            // Verify signature
            $is_valid = $this->razorpaylib->verifyPaymentSignature(
                $razorpay_order_id,
                $razorpay_payment_id,
                $razorpay_signature
            );

            if (!$is_valid) {
                $this->logger->error('Razorpay signature verification failed', [
                    'employee_id' => $employee_id,
                    'razorpay_order_id' => $razorpay_order_id
                ], 'orders');

                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Payment verification failed. Please contact support.',
                    'data' => null
                ]);
                return;
            }

            // Get pending order
            $pending_order = $this->orders_model->get_pending_order($razorpay_order_id);

        } elseif (!empty($pending_order_id)) {
            // Wallet-only payment
            $pending_order = $this->db->get_where('pending_orders', [
                'id' => $pending_order_id,
                'employee_id' => $employee_id,
                'status' => 'PENDING'
            ])->row();

            // Reject if this pending order requires online payment
            if ($pending_order && $pending_order->amount > 0 && !empty($pending_order->razorpay_order_id)) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Online payment is required for this order. Please complete the payment.',
                    'data' => null
                ]);
                return;
            }

        } else {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Payment information is required',
                'data' => null
            ]);
            return;
        }

        // Validate pending order
        if (empty($pending_order)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order session expired or invalid. Please try again.',
                'data' => null
            ]);
            return;
        }

        // Check if pending order belongs to this employee
        if ($pending_order->employee_id != $employee_id) {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Unauthorized order access',
                'data' => null
            ]);
            return;
        }

        // Check if pending order's store matches the provided store_id
        if ($pending_order->store_id != $store_id) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store mismatch. Order was initiated for a different store.',
                'data' => null
            ]);
            return;
        }

        // Check expiry
        if (strtotime($pending_order->expires_at) < time()) {
            $this->orders_model->update_pending_order($pending_order->id, 'EXPIRED');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order session has expired. Please initiate a new order.',
                'data' => null
            ]);
            return;
        }

        // Start transaction
        $this->db->trans_start();

        // Atomically claim the pending order so concurrent completion calls
        // (client retry, double-tap, network replay) cannot both create an order
        if (!$this->orders_model->claim_pending_order($pending_order->id)) {
            $this->db->trans_rollback();
            $this->output_duplicate_completion($pending_order->id);
            return;
        }

        try {
            // Generate order number and pickup code
            $order_number = $this->orders_model->generate_order_number('QSR');
            $pickup_code = generate_pickup_code(6); // 6-digit code

            // Check if order is scheduled
            $is_scheduled = !empty($pending_order->pickup_time);

            // Prepare order data
            $order_data = [
                'order_number' => $order_number,
                'employee_id' => $employee_id,
                'company_id' => $company_id,
                'store_id' => $store_id,
                'module' => 'QSR',
                'pending_order_id' => $pending_order->id,
                'status' => 'PENDING',
                'pickup_code' => $pickup_code,
                'pickup_time' => $pending_order->pickup_time,
                'is_scheduled' => $is_scheduled ? 1 : 0,
                'subtotal' => $pending_order->subtotal,
                'tax_amount' => $pending_order->tax_amount,
                'amount_before_discount' => $pending_order->subtotal + $pending_order->tax_amount,
                'coupon_id' => $pending_order->coupon_id,
                'coupon_code' => $pending_order->coupon_code,
                'discount_amount' => $pending_order->discount_amount,
                'total_amount' => $pending_order->total_amount,
                'company_contribution' => 0, // QSR is 100% user payment
                'employee_contribution' => $pending_order->total_amount,
                'wallet_deducted' => $pending_order->wallet_amount,
                'payment_status' => 'PAID',
                'payment_method' => $pending_order->wallet_amount > 0 && $pending_order->amount > 0 ? 'MIXED' : ($pending_order->wallet_amount > 0 ? 'WALLET' : 'ONLINE'),
                'paid_at' => date('Y-m-d H:i:s'),
                'total_items' => 0,
                'unique_items' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Create order
            $order_id = $this->orders_model->create_order($order_data);

            if (!$order_id) {
                throw new Exception('Failed to create order');
            }

            // Parse items and create order items
            $items = json_decode($pending_order->items_json, true);
            $order_items = [];
            $total_quantity = 0;

            foreach ($items as $item) {
                $order_items[] = [
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_percentage' => $item['tax_percentage'],
                    'base_price' => $item['base_price'],
                    'tax_amount' => $item['tax_amount'] * $item['quantity'],
                    'subtotal' => $item['subtotal'],
                    'total_amount' => $item['total'],
                    'note' => $item['note'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $total_quantity += $item['quantity'];
            }

            $this->orders_model->create_order_items($order_items);

            // Update order with item counts
            $this->orders_model->update_order($order_id, [
                'total_items' => $total_quantity,
                'unique_items' => count($order_items)
            ]);

            // Deduct wallet if used (insert into transaction table for wallet balance)
            if ($pending_order->wallet_amount > 0) {
                $this->db->insert('transaction', [
                    'transaction_uuid' => generate_uuid(),
                    'user_id' => $employee_id,
                    'order_id' => $order_id,
                    'amount' => $pending_order->wallet_amount,
                    'transaction_type' => 2, // Debit from wallet
                    'transaction_label' => 'Payment for order ' . $order_number,
                    'transaction_date' => date('Y-m-d')
                ]);

                $wallet_transaction_id = $this->db->insert_id();

                // Record wallet payment in order_payments with transaction reference
                $this->orders_model->add_order_payment([
                    'order_id' => $order_id,
                    'payment_type' => 'WALLET_DEBIT',
                    'amount' => $pending_order->wallet_amount,
                    'transaction_id' => $wallet_transaction_id,
                    'status' => 'SUCCESS',
                    'note' => 'Wallet deduction for order',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Record online payment if applicable (order_payments only, not transaction table)
            if (!empty($razorpay_payment_id) && $pending_order->amount > 0) {
                $this->orders_model->add_order_payment([
                    'order_id' => $order_id,
                    'payment_type' => 'ONLINE_PAYMENT',
                    'amount' => $pending_order->amount,
                    'transaction_id' => null,
                    'razorpay_payment_id' => $razorpay_payment_id,
                    'razorpay_order_id' => $razorpay_order_id,
                    'status' => 'SUCCESS',
                    'note' => null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Apply coupon usage
            if (!empty($pending_order->coupon_id)) {
                $this->couponlib->applyCoupon(
                    $pending_order->coupon_id,
                    $employee_id,
                    $order_id,
                    $pending_order->discount_amount
                );
            }

            // Add status history
            $this->orders_model->add_status_history([
                'order_id' => $order_id,
                'from_status' => null,
                'to_status' => 'PENDING',
                'changed_by_type' => 'EMPLOYEE',
                'changed_by_id' => $employee_id,
                'note' => 'Order placed',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Deduct stock from store_products
            foreach ($items as $item) {
                $this->orders_model->deduct_stock($store_id, $item['product_id'], $item['quantity'], [
                    'reference_id'      => $order_id,
                    'order_number'      => $order_number,
                    'performed_by_type' => 'EMPLOYEE',
                    'performed_by_id'   => $employee_id,
                    'note'              => 'Stock deducted on order placement'
                ]);
            }

            // Clear cart
            $this->cart_model->clear_cart($employee_id, $store_id, 'QSR');

            // Update pending order status
            $this->orders_model->update_pending_order($pending_order->id, 'COMPLETED', [
                'order_id' => $order_id,
                'completed_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $this->logger->info('QSR Order created successfully', [
                'order_id' => $order_id,
                'order_number' => $order_number,
                'employee_id' => $employee_id,
                'total_amount' => $pending_order->total_amount
            ], 'orders');

            // Prepare response
            $this->output([
                'status' => 200,
                'success' => true,
                'message' => $is_scheduled ? 'Order scheduled successfully' : 'Order placed successfully',
                'data' => [
                    'order' => [
                        'id' => (int) $order_id,
                        'order_number' => $order_number,
                        'pickup_code' => $pickup_code,
                        'status' => 'PENDING',
                        'is_scheduled' => $is_scheduled,
                        'pickup_time' => $pending_order->pickup_time,
                        'total_amount' => (float) $pending_order->total_amount,
                        'wallet_deducted' => (float) $pending_order->wallet_amount,
                        'online_paid' => (float) $pending_order->amount,
                        'discount_amount' => (float) $pending_order->discount_amount,
                        'items_count' => $total_quantity,
                        'qr_data'   => "$order_number|$pickup_code",
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    'store' => [
                        'id' => (int) $store->id,
                        'name' => $store->name,
                        'address' => trim(implode(', ', array_filter([
                            $store->address_line1,
                            $store->city,
                            $store->state
                        ])))
                    ]
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->logger->error('QSR Order creation failed', [
                'employee_id' => $employee_id,
                'error' => $e->getMessage()
            ], 'orders');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to create order. Please try again.',
                'data' => null
            ]);
        }
    }

    /**
     * Respond to a completion call that lost the atomic claim on a pending order
     *
     * If the winning call already created the order, return it so client
     * retries converge to success instead of a spurious failure.
     *
     * @param int $pending_order_id Pending order ID
     * @return void JSON response
     */
    private function output_duplicate_completion($pending_order_id)
    {
        $pending = $this->db->get_where('pending_orders', ['id' => $pending_order_id])->row();

        if ($pending && !empty($pending->order_id)) {
            $order = $this->db->get_where('orders', ['id' => $pending->order_id])->row();

            if ($order) {
                $this->logger->info('Duplicate completion call resolved to existing order', [
                    'pending_order_id' => $pending_order_id,
                    'order_id' => $order->id
                ], 'orders');

                $this->output([
                    'status' => 200,
                    'success' => true,
                    'message' => 'Order already placed',
                    'data' => [
                        'order' => [
                            'id' => (int) $order->id,
                            'order_number' => $order->order_number,
                            'pickup_code' => $order->pickup_code,
                            'status' => $order->status,
                            'total_amount' => (float) $order->total_amount,
                            'wallet_deducted' => (float) $order->wallet_deducted,
                            'qr_data' => $order->order_number . '|' . $order->pickup_code,
                            'created_at' => $order->created_at
                        ],
                        'already_completed' => true
                    ]
                ]);
                return;
            }
        }

        $this->logger->warning('Duplicate completion call rejected', [
            'pending_order_id' => $pending_order_id,
            'pending_status' => $pending ? $pending->status : null
        ], 'orders');

        $this->output([
            'status' => 409,
            'success' => false,
            'message' => 'This payment is already being processed. Please check My Orders before retrying.',
            'data' => null
        ]);
    }

    // ==================== PREMEAL ORDERING ====================

    /**
     * PREMEAL Feasibility Check
     *
     * Validates scheduling request and returns pricing breakdown for all dates.
     * Checks daily meal limits, cutoff times, and calculates policy contributions.
     *
     * POST /api/v1/user/orders/premeal_check
     *
     * Required parameters (form-data):
     * - store_id: Store ID
     * - product_id: Product ID
     * - quantity: Quantity per day
     * - scheduled_dates: JSON array of dates ["2026-01-05", "2026-01-06"]
     *
     * Optional parameters:
     * - coupon_code: Coupon code to apply
     *
     * @return void JSON response with feasibility and pricing breakdown
     */
    public function premeal_check()
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

        $this->logger->info('PREMEAL Check API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address()
        ], 'orders');

        // Get form-data input
        $post_data = $this->input->post(null, true);

        $store_id = isset($post_data['store_id']) ? (int) $post_data['store_id'] : null;
        $product_id = isset($post_data['product_id']) ? (int) $post_data['product_id'] : null;
        $quantity = isset($post_data['quantity']) ? (int) $post_data['quantity'] : 1;
        $scheduled_dates = isset($post_data['scheduled_dates']) ? $post_data['scheduled_dates'] : null;
        $coupon_code = isset($post_data['coupon_code']) ? trim($post_data['coupon_code']) : null;

        // Validate store
        $store = $this->validate_store($store_id, $company_id);
        if (!$store) {
            return;
        }

        // Check store is PREMEAL type
        if ($store->store_type !== 'PREMEAL') {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'This store does not support PREMEAL orders',
                'data' => null
            ]);
            return;
        }

        // Validate PREMEAL access
        $employee = $this->validate_module_access('PREMEAL', $employee_id);
        if (!$employee) {
            return;
        }

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

        // Validate quantity
        if ($quantity < 1) {
            $quantity = 1;
        }

        // Parse scheduled_dates
        if (is_string($scheduled_dates)) {
            $scheduled_dates = json_decode($scheduled_dates, true);
        }

        if (empty($scheduled_dates) || !is_array($scheduled_dates)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'scheduled_dates is required (array of dates)',
                'data' => null
            ]);
            return;
        }

        // Get product details
        $product = $this->get_premeal_product($product_id, $store_id);
        if (!$product) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product not found or not available for PREMEAL',
                'data' => null
            ]);
            return;
        }

        // Determine meal type from product
        $meal_type = null;
        if ($product->breakfast) {
            $meal_type = 'BREAKFAST';
        } elseif ($product->lunch) {
            $meal_type = 'LUNCH';
        } elseif ($product->dinner) {
            $meal_type = 'DINNER';
        }

        if (!$meal_type) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product does not have a valid meal type configured',
                'data' => null
            ]);
            return;
        }

        // Get serving time from store
        $serving_time = null;
        switch ($meal_type) {
            case 'BREAKFAST':
                $serving_time = $store->breakfast_time;
                break;
            case 'LUNCH':
                $serving_time = $store->lunch_time;
                break;
            case 'DINNER':
                $serving_time = $store->dinner_time;
                break;
        }

        // Get employee's policy
        $policy = $this->policylib->get_employee_policy($employee_id, 'PREMEAL');

        // Check if PREMEAL is enabled in policy
        if ($policy && !$this->policylib->is_premeal_enabled($policy)) {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'PREMEAL is not enabled in your policy',
                'data' => null
            ]);
            return;
        }

        // Check if meal type is enabled in policy
        if ($policy && !$this->policylib->is_meal_type_enabled($policy, $meal_type)) {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => "{$meal_type} is not enabled in your policy",
                'data' => null
            ]);
            return;
        }

        // Calculate unit price
        $unit_price = !empty($product->store_price) ? (float) $product->store_price : (float) $product->base_price;
        $day_subtotal = $unit_price * $quantity;

        // Validate each date - ALL dates must be valid
        $dates_result = [];
        $failed_dates = [];
        $total_company_contribution = 0;
        $total_employee_share = 0;

        foreach ($scheduled_dates as $date) {
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $failed_dates[] = [
                    'date' => $date,
                    'reason' => 'Invalid date format. Use YYYY-MM-DD'
                ];
                continue;
            }

            // Validate date using PolicyLib
            $date_validation = $this->policylib->validate_date_for_booking(
                $employee_id,
                $date,
                $meal_type,
                $store,
                $policy
            );

            if ($date_validation['is_available']) {
                // Calculate contribution for this date
                $contribution = $this->policylib->calculate_contribution($policy, $day_subtotal);

                $dates_result[] = [
                    'date' => $date,
                    'day_name' => $date_validation['day_name'],
                    'cutoff_time' => $date_validation['cutoff_time'] ?? null,
                    'serving_time' => $date_validation['serving_time'] ?? null,
                    'meal_limit' => $date_validation['meal_limit'] ?? null,
                    'subtotal' => round($day_subtotal, 2),
                    'items_string' => '1x '.$product->name, // For now we assume 1 product per order, can be enhanced later
                    'is_vegetarian' => (bool)$product->is_vegetarian,
                    'company_contribution' => $contribution['company_contribution'],
                    'employee_share' => $contribution['employee_contribution']
                ];

                $total_company_contribution += $contribution['company_contribution'];
                $total_employee_share += $contribution['employee_contribution'];
            } else {
                $failed_dates[] = [
                    'date' => $date,
                    'day_name' => $date_validation['day_name'] ?? null,
                    'reason' => $date_validation['reason'],
                    'cutoff_passed' => $date_validation['cutoff_passed'] ?? false,
                    'meal_limit' => $date_validation['meal_limit'] ?? null
                ];
            }
        }

        // If ANY date failed validation, reject the entire request
        if (!empty($failed_dates)) {
            $failed_list = $this->format_failed_dates($failed_dates);
            $total_failed = count(array_filter($failed_dates, function ($fd) {
                return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fd['date']);
            }));

            $message = $total_failed === 1
                ? $failed_list . ' is not available. Please select a different date.'
                : $failed_list . ' are not available. Please select different dates.';

            $this->output([
                'status' => 400,
                'success' => false,
                'message' => $message,
                'data' => [
                    'failed_dates' => $failed_dates,
                    'product' => [
                        'id' => (int) $product->id,
                        'name' => $product->name,
                        'meal_type' => $meal_type
                    ]
                ]
            ]);
            return;
        }

        // All dates are valid - calculate totals
        $gross_total = $day_subtotal * count($dates_result);

        // Apply coupon if provided
        $coupon = null;
        $coupon_discount = 0;

        if (!empty($coupon_code) && count($dates_result) > 0) {
            // Get client_id from company
            $company = $this->common->getdatabytable('companies', ['id' => $company_id]);
            $client_id = $company ? $company->client_id : 0;

            $coupon_result = $this->couponlib->validateCoupon([
                'coupon_code' => $coupon_code,
                'order_amount' => $total_employee_share,
                'employee_id' => $employee_id,
                'company_id' => $company_id,
                'client_id' => $client_id,
                'module' => 'PREMEAL'
            ]);

            if ($coupon_result['valid']) {
                $coupon = $coupon_result['coupon'];
                $coupon_discount = $coupon_result['discount_amount'];
            }
            // Don't fail if coupon is invalid, just don't apply it
        }

        $final_payable = round($total_employee_share - $coupon_discount, 2);

        // Get wallet balance
        $wallet = getWalletMoneyArray($employee_id);
        $wallet_balance = round((float) $wallet['available'], 2);

        // Prepare response
        $response_data = [
            'product' => [
                'id' => (int) $product->id,
                'name' => $product->name,
                'short_name' => $product->short_name,
                'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                'meal_type' => $meal_type,
                'serving_time' => $serving_time ? substr($serving_time, 0, 5) : null
            ],
            'quantity' => $quantity,
            'unit_price' => round($unit_price, 2),
            'dates' => $dates_result,
            'summary' => [
                'total_days' => count($dates_result),
                'gross_total' => round($gross_total, 2),
                'total_company_contribution' => round($total_company_contribution, 2),
                'total_employee_share' => round($total_employee_share, 2),
                'coupon_discount' => round($coupon_discount, 2),
                'final_payable' => $final_payable,
                'wallet_balance' => $wallet_balance
            ]
        ];

        $this->logger->info('PREMEAL Check completed', [
            'employee_id' => $employee_id,
            'product_id' => $product_id,
            'total_days' => count($dates_result),
            'final_payable' => $final_payable
        ], 'orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Scheduling feasible',
            'data' => $response_data
        ]);
    }

    /**
     * PREMEAL Place Order
     *
     * Creates orders for all valid scheduled dates.
     *
     * POST /api/v1/user/orders/premeal_order
     *
     * Required parameters (form-data):
     * - store_id: Store ID
     * - product_id: Product ID
     * - quantity: Quantity per day
     * - scheduled_dates: JSON array of dates ["2026-01-05", "2026-01-06"]
     *
     * Optional parameters:
     * - coupon_code: Coupon code to apply
     * - wallet_amount: Amount to use from wallet (default: 0)
     *
     * @return void JSON response with order details or payment info
     */
    public function premeal_order()
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

        $this->logger->info('PREMEAL Order API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address()
        ], 'orders');

        // Get form-data input
        $post_data = $this->input->post(null, true);

        $store_id = isset($post_data['store_id']) ? (int) $post_data['store_id'] : null;
        $product_id = isset($post_data['product_id']) ? (int) $post_data['product_id'] : null;
        $quantity = isset($post_data['quantity']) ? (int) $post_data['quantity'] : 1;
        $scheduled_dates = isset($post_data['scheduled_dates']) ? $post_data['scheduled_dates'] : null;
        $coupon_code = isset($post_data['coupon_code']) ? trim($post_data['coupon_code']) : null;
        $wallet_amount = isset($post_data['wallet_amount']) ? (float) $post_data['wallet_amount'] : 0;

        // Validate store
        $store = $this->validate_store($store_id, $company_id);
        if (!$store) {
            return;
        }

        // Check store is PREMEAL type
        if ($store->store_type !== 'PREMEAL') {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'This store does not support PREMEAL orders',
                'data' => null
            ]);
            return;
        }

        // Validate PREMEAL access
        $employee = $this->validate_module_access('PREMEAL', $employee_id);
        if (!$employee) {
            return;
        }

        // Validate product
        if (empty($product_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'product_id is required',
                'data' => null
            ]);
            return;
        }

        // Validate quantity
        if ($quantity < 1) {
            $quantity = 1;
        }

        // Parse scheduled_dates
        if (is_string($scheduled_dates)) {
            $scheduled_dates = json_decode($scheduled_dates, true);
        }

        if (empty($scheduled_dates) || !is_array($scheduled_dates)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'scheduled_dates is required (array of dates)',
                'data' => null
            ]);
            return;
        }

        // Get product details
        $product = $this->get_premeal_product($product_id, $store_id);
        if (!$product) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product not found or not available for PREMEAL',
                'data' => null
            ]);
            return;
        }

        // Determine meal type
        $meal_type = null;
        if ($product->breakfast) {
            $meal_type = 'BREAKFAST';
        } elseif ($product->lunch) {
            $meal_type = 'LUNCH';
        } elseif ($product->dinner) {
            $meal_type = 'DINNER';
        }

        if (!$meal_type) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product does not have a valid meal type configured',
                'data' => null
            ]);
            return;
        }

        // Get serving time
        $serving_time = null;
        switch ($meal_type) {
            case 'BREAKFAST':
                $serving_time = $store->breakfast_time;
                break;
            case 'LUNCH':
                $serving_time = $store->lunch_time;
                break;
            case 'DINNER':
                $serving_time = $store->dinner_time;
                break;
        }

        // Get employee's policy
        $policy = $this->policylib->get_employee_policy($employee_id, 'PREMEAL');

        // Calculate unit price
        $unit_price = !empty($product->store_price) ? (float) $product->store_price : (float) $product->base_price;
        $day_subtotal = $unit_price * $quantity;

        // Re-validate all dates - ALL must be valid
        $valid_dates = [];
        $failed_dates = [];
        $total_company_contribution = 0;
        $total_employee_share = 0;

        foreach ($scheduled_dates as $date) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $failed_dates[] = [
                    'date' => $date,
                    'reason' => 'Invalid date format'
                ];
                continue;
            }

            $date_validation = $this->policylib->validate_date_for_booking(
                $employee_id,
                $date,
                $meal_type,
                $store,
                $policy
            );

            if ($date_validation['is_available']) {
                $contribution = $this->policylib->calculate_contribution($policy, $day_subtotal);

                $valid_dates[] = [
                    'date' => $date,
                    'subtotal' => round($day_subtotal, 2),
                    'company_contribution' => $contribution['company_contribution'],
                    'employee_share' => $contribution['employee_contribution'],
                    'serving_time' => $serving_time
                ];

                $total_company_contribution += $contribution['company_contribution'];
                $total_employee_share += $contribution['employee_contribution'];
            } else {
                $failed_dates[] = [
                    'date' => $date,
                    'reason' => $date_validation['reason']
                ];
            }
        }

        // If ANY date failed, reject entire order
        if (!empty($failed_dates)) {
            $failed_list = $this->format_failed_dates($failed_dates);
            $total_failed = count(array_filter($failed_dates, function ($fd) {
                return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fd['date']);
            }));

            $fail_message = $total_failed === 1
                ? $failed_list . ' is not available. Please select a different date.'
                : $failed_list . ' are not available. Please select different dates.';

            $this->output([
                'status' => 400,
                'success' => false,
                'message' => $fail_message,
                'data' => [
                    'failed_dates' => $failed_dates
                ]
            ]);
            return;
        }

        if (empty($valid_dates)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'No valid dates available for scheduling',
                'data' => null
            ]);
            return;
        }

        // Apply coupon
        $coupon = null;
        $coupon_discount = 0;

        if (!empty($coupon_code)) {
            $company = $this->common->getdatabytable('companies', ['id' => $company_id]);
            $client_id = $company ? $company->client_id : 0;

            $coupon_result = $this->couponlib->validateCoupon([
                'coupon_code' => $coupon_code,
                'order_amount' => $total_employee_share,
                'employee_id' => $employee_id,
                'company_id' => $company_id,
                'client_id' => $client_id,
                'module' => 'PREMEAL'
            ]);

            if ($coupon_result['valid']) {
                $coupon = $coupon_result['coupon'];
                $coupon_discount = $coupon_result['discount_amount'];
            } else {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => $coupon_result['message'],
                    'data' => null
                ]);
                return;
            }
        }

        $final_payable = round($total_employee_share - $coupon_discount, 2);

        // Handle payment - same logic as QSR
        $wallet = getWalletMoneyArray($employee_id);
        $available_balance = round((float) $wallet['available'], 2);

        // Validate and cap wallet amount
        if ($wallet_amount < 0) {
            $wallet_amount = 0;
        }

        // Cap wallet amount to available balance and final payable
        if ($wallet_amount > $available_balance) {
            $wallet_amount = $available_balance;
        }

        if ($wallet_amount > $final_payable) {
            $wallet_amount = $final_payable;
        }

        $wallet_amount = round($wallet_amount, 2);

        // Calculate gateway amount
        $gateway_amount = round($final_payable - $wallet_amount, 2);

        // If no payment needed (FREE policy)
        if ($final_payable <= 0) {
            $wallet_amount = 0;
            $gateway_amount = 0;
        }

        // Distribute coupon discount across orders proportionally
        $discount_per_order = count($valid_dates) > 0 ? round($coupon_discount / count($valid_dates), 2) : 0;

        // If gateway payment is needed, create Razorpay order
        if ($gateway_amount > 0) {
            // Initialize Razorpay with company's credentials
            if (!$this->razorpaylib->init($company_id)) {
                $this->logger->error('Failed to initialize Razorpay for PREMEAL', [
                    'employee_id' => $employee_id,
                    'company_id' => $company_id
                ], 'orders');

                $this->output([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Payment service not configured. Please contact support.',
                    'data' => null
                ]);
                return;
            }

            $receipt_id = 'PM_' . $employee_id . '_' . time();

            $razorpay_result = $this->razorpaylib->createOrder([
                'amount' => $gateway_amount,
                'receipt' => $receipt_id,
                'notes' => [
                    'employee_id' => $employee_id,
                    'store_id' => $store_id,
                    'module' => 'PREMEAL',
                    'dates_count' => count($valid_dates)
                ]
            ]);

            if (!$razorpay_result['success']) {
                $this->logger->error('Razorpay order creation failed for PREMEAL', [
                    'employee_id' => $employee_id,
                    'amount' => $gateway_amount,
                    'error' => $razorpay_result['message']
                ], 'orders');

                $this->output([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Payment order failed: ' . ($razorpay_result['message'] ?? 'Unknown error'),
                    'data' => null
                ]);
                return;
            }

            $razorpay_order = $razorpay_result['order'];

            // Store pending PREMEAL order data (using existing schema)
            // PREMEAL-specific data stored in items_json
            $premeal_data = [
                'product_id' => $product_id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'meal_type' => $meal_type,
                'valid_dates' => $valid_dates,
                'total_company_contribution' => $total_company_contribution,
                'total_employee_share' => $total_employee_share
            ];

            $pending_data = [
                'employee_id' => $employee_id,
                'store_id' => $store_id,
                'module' => 'PREMEAL',
                'razorpay_order_id' => $razorpay_order['id'],
                'amount' => $gateway_amount,
                'wallet_amount' => $wallet_amount,
                'coupon_id' => $coupon ? $coupon->id : null,
                'coupon_code' => $coupon ? $coupon->code : null,
                'discount_amount' => $coupon_discount,
                'subtotal' => $total_employee_share,
                'tax_amount' => 0,
                'total_amount' => $final_payable,
                'items_json' => json_encode($premeal_data),
                'status' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
            ];

            $pending_id = $this->orders_model->store_pending_order($pending_data);

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'Payment required to complete scheduling',
                'data' => [
                    'payment_required' => true,
                    'pending_order_id' => $pending_id,
                    'razorpay' => [
                        'key' => $this->razorpaylib->getKeyId(),
                        'amount' => $razorpay_order['amount'],
                        'currency' => $razorpay_order['currency'],
                        'name' => config_item('application_name') ?: 'Joy Foods',
                        'description' => 'PREMEAL Order - ' . count($valid_dates) . ' meals',
                        'order_id' => $razorpay_order['id'],
                        'prefill' => [
                            'name' => trim($employee->first_name . ' ' . ($employee->last_name ?? '')),
                            'email' => $employee->email ?? '',
                            'contact' => $employee->phone ?? ''
                        ],
                        'theme' => [
                            'color' => '#BD3839'
                        ]
                    ],
                    'summary' => [
                        'total_days' => count($valid_dates),
                        'total_company_contribution' => round($total_company_contribution, 2),
                        'total_employee_share' => round($total_employee_share, 2),
                        'coupon_discount' => round($coupon_discount, 2),
                        'wallet_to_use' => round($wallet_amount, 2),
                        'gateway_amount' => round($gateway_amount, 2)
                    ]
                ]
            ]);
            return;
        }

        // No gateway payment needed - create orders directly
        $this->create_premeal_orders(
            $employee_id,
            $company_id,
            $store,
            $product,
            $quantity,
            $meal_type,
            $valid_dates,
            $policy,
            $wallet_amount,
            0, // gateway_amount
            $coupon,
            $coupon_discount,
            null, // razorpay_order_id
            null  // razorpay_payment_id
        );
    }

    /**
     * PREMEAL Payment Complete (Callback)
     *
     * Verifies Razorpay payment and creates PREMEAL orders.
     *
     * POST /api/v1/user/orders/premeal_complete
     *
     * Required parameters:
     * - pending_order_id: Pending order ID
     * - razorpay_order_id: Razorpay order ID
     * - razorpay_payment_id: Razorpay payment ID
     * - razorpay_signature: Razorpay signature
     *
     * @return void JSON response with order details
     */
    public function premeal_complete()
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

        $this->logger->info('PREMEAL Complete API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address()
        ], 'orders');

        // Get form-data input
        $post_data = $this->input->post(null, true);

        $pending_order_id = isset($post_data['pending_order_id']) ? (int) $post_data['pending_order_id'] : null;
        $razorpay_order_id = isset($post_data['razorpay_order_id']) ? trim($post_data['razorpay_order_id']) : null;
        $razorpay_payment_id = isset($post_data['razorpay_payment_id']) ? trim($post_data['razorpay_payment_id']) : null;
        $razorpay_signature = isset($post_data['razorpay_signature']) ? trim($post_data['razorpay_signature']) : null;

        // Validate required fields
        if (empty($pending_order_id) || empty($razorpay_order_id) || empty($razorpay_payment_id) || empty($razorpay_signature)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'All payment verification fields are required',
                'data' => null
            ]);
            return;
        }

        // Initialize Razorpay with company's credentials
        if (!$this->razorpaylib->init($company_id)) {
            $this->logger->error('Failed to initialize Razorpay for PREMEAL verification', [
                'employee_id' => $employee_id,
                'company_id' => $company_id
            ], 'orders');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Payment service not configured. Please contact support.',
                'data' => null
            ]);
            return;
        }

        // Verify Razorpay signature
        $is_valid = $this->razorpaylib->verifyPaymentSignature(
            $razorpay_order_id,
            $razorpay_payment_id,
            $razorpay_signature
        );

        if (!$is_valid) {
            $this->logger->error('PREMEAL Razorpay signature verification failed', [
                'employee_id' => $employee_id,
                'razorpay_order_id' => $razorpay_order_id
            ], 'orders');

            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Payment verification failed. Please contact support.',
                'data' => null
            ]);
            return;
        }

        // Get pending order
        $pending_order = $this->db->get_where('pending_orders', [
            'id' => $pending_order_id,
            'employee_id' => $employee_id,
            'razorpay_order_id' => $razorpay_order_id,
            'module' => 'PREMEAL',
            'status' => 'PENDING'
        ])->row();

        if (empty($pending_order)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order session expired or invalid',
                'data' => null
            ]);
            return;
        }

        // Check expiry
        if (strtotime($pending_order->expires_at) < time()) {
            $this->orders_model->update_pending_order($pending_order->id, 'EXPIRED');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order session has expired. Please try again.',
                'data' => null
            ]);
            return;
        }

        // Parse PREMEAL data from items_json
        $premeal_data = json_decode($pending_order->items_json, true);

        if (empty($premeal_data) || empty($premeal_data['product_id']) || empty($premeal_data['valid_dates'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid order data. Please try again.',
                'data' => null
            ]);
            return;
        }

        // Get required data
        $store = $this->common->getdatabytable('stores', ['id' => $pending_order->store_id]);
        $product = $this->get_premeal_product($premeal_data['product_id'], $pending_order->store_id);
        $policy = $this->policylib->get_employee_policy($employee_id, 'PREMEAL');

        if (!$product) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product not found or no longer available',
                'data' => null
            ]);
            return;
        }

        // Get coupon if used
        $coupon = null;
        if (!empty($pending_order->coupon_id)) {
            $coupon = $this->common->getdatabytable('coupons', ['id' => $pending_order->coupon_id]);
        }

        // Create orders
        $this->create_premeal_orders(
            $employee_id,
            $company_id,
            $store,
            $product,
            $premeal_data['quantity'],
            $premeal_data['meal_type'],
            $premeal_data['valid_dates'],
            $policy,
            $pending_order->wallet_amount,
            $pending_order->amount,
            $coupon,
            $pending_order->discount_amount,
            $razorpay_order_id,
            $razorpay_payment_id,
            $pending_order->id
        );
    }

    /**
     * Create PREMEAL orders for validated dates
     *
     * @param int $employee_id
     * @param int $company_id
     * @param object $store
     * @param object $product
     * @param int $quantity
     * @param string $meal_type
     * @param array $valid_dates
     * @param object|null $policy
     * @param float $wallet_amount
     * @param float $gateway_amount
     * @param object|null $coupon
     * @param float $coupon_discount
     * @param string|null $razorpay_order_id
     * @param string|null $razorpay_payment_id
     * @param int|null $pending_order_id
     * @return void
     */
    private function create_premeal_orders(
        $employee_id,
        $company_id,
        $store,
        $product,
        $quantity,
        $meal_type,
        $valid_dates,
        $policy,
        $wallet_amount,
        $gateway_amount,
        $coupon,
        $coupon_discount,
        $razorpay_order_id,
        $razorpay_payment_id,
        $pending_order_id = null
    ) {
        // Start transaction
        $this->db->trans_start();

        // Atomically claim the pending order (gateway flow only - the
        // free/wallet flow calls this helper without a pending order)
        if (!empty($pending_order_id) && !$this->orders_model->claim_pending_order($pending_order_id)) {
            $this->db->trans_rollback();
            $this->output_duplicate_completion($pending_order_id);
            return;
        }

        try {
            $created_orders = [];
            $total_orders = count($valid_dates);
            $discount_per_order = $total_orders > 0 ? round($coupon_discount / $total_orders, 2) : 0;
            $wallet_per_order = $total_orders > 0 ? round($wallet_amount / $total_orders, 2) : 0;
            $gateway_per_order = $total_orders > 0 ? round($gateway_amount / $total_orders, 2) : 0;

            // Track primary order ID for parent-child relationship
            $primary_order_id = null;

            foreach ($valid_dates as $index => $date_info) {
                $date = $date_info['date'];
                $subtotal = $date_info['subtotal'];
                $company_contribution = $date_info['company_contribution'];
                $employee_share = $date_info['employee_share'];
                $serving_time = $date_info['serving_time'];

                // Apply discount for this order (last order gets the rounding
                // remainder, same as the wallet/gateway splits below)
                $order_discount = ($index == $total_orders - 1)
                    ? round($coupon_discount - ($discount_per_order * ($total_orders - 1)), 2)
                    : $discount_per_order;
                $order_employee_final = round($employee_share - $order_discount, 2);

                // Generate order number and pickup code
                $order_number = $this->orders_model->generate_order_number('PM');
                $pickup_code = generate_pickup_code(6);

                // Determine payment method for this order
                $order_wallet = ($index == $total_orders - 1)
                    ? ($wallet_amount - ($wallet_per_order * ($total_orders - 1)))  // Last order gets remainder
                    : $wallet_per_order;
                $order_gateway = ($index == $total_orders - 1)
                    ? ($gateway_amount - ($gateway_per_order * ($total_orders - 1)))
                    : $gateway_per_order;

                $payment_method = 'ONLINE';
                if ($order_wallet > 0 && $order_gateway > 0) {
                    $payment_method = 'MIXED';
                } elseif ($order_wallet > 0 && $order_gateway <= 0) {
                    $payment_method = 'WALLET';
                } elseif ($order_employee_final <= 0) {
                    $payment_method = 'POLICY';
                }

                // Determine if this is primary (first) or child order
                $is_primary = ($index === 0);

                // Combine date and serving_time for pickup_time (datetime field)
                $pickup_datetime = null;
                if ($serving_time) {
                    $pickup_datetime = $date . ' ' . $serving_time;
                }

                // Create order
                $order_data = [
                    'order_number' => $order_number,
                    'employee_id' => $employee_id,
                    'company_id' => $company_id,
                    'store_id' => $store->id,
                    'module' => 'PREMEAL',
                    'pending_order_id' => ($is_primary && !empty($pending_order_id)) ? $pending_order_id : null,
                    'status' => 'PENDING',
                    'pickup_code' => $pickup_code,
                    'scheduled_date' => $date,
                    'meal_type' => $meal_type,
                    'pickup_time' => $pickup_datetime,
                    'is_scheduled' => 1,
                    'is_primary_order' => $is_primary ? 1 : 0,
                    'parent_order_id' => $is_primary ? null : $primary_order_id,
                    'subtotal' => $subtotal,
                    'tax_amount' => 0,
                    'amount_before_discount' => $subtotal,
                    'coupon_id' => $coupon ? $coupon->id : null,
                    'coupon_code' => $coupon ? $coupon->code : null,
                    'discount_amount' => $order_discount,
                    'total_amount' => $subtotal,
                    'policy_id' => $policy ? $policy->id : null,
                    'company_contribution' => $company_contribution,
                    'employee_contribution' => $order_employee_final,
                    'wallet_deducted' => round($order_wallet, 2),
                    'payment_status' => 'PAID',
                    'payment_method' => $payment_method,
                    'paid_at' => date('Y-m-d H:i:s'),
                    'total_items' => $quantity,
                    'unique_items' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $order_id = $this->orders_model->create_order($order_data);

                // Store primary order ID for child orders
                if ($is_primary) {
                    $primary_order_id = $order_id;
                }

                if (!$order_id) {
                    throw new Exception('Failed to create order for date: ' . $date);
                }

                // Create order item
                $unit_price = !empty($product->store_price) ? (float) $product->store_price : (float) $product->base_price;

                $this->orders_model->create_order_items([[
                    'order_id' => $order_id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'tax_percentage' => 0,
                    'base_price' => $unit_price,
                    'tax_amount' => 0,
                    'subtotal' => $subtotal,
                    'total_amount' => $subtotal,
                    'created_at' => date('Y-m-d H:i:s')
                ]]);

                // Record wallet payment
                if ($order_wallet > 0) {
                    $this->db->insert('transaction', [
                        'transaction_uuid' => generate_uuid(),
                        'user_id' => $employee_id,
                        'order_id' => $order_id,
                        'amount' => $order_wallet,
                        'transaction_type' => 2,
                        'transaction_label' => 'PREMEAL payment for ' . $date,
                        'transaction_date' => date('Y-m-d')
                    ]);

                    $wallet_transaction_id = $this->db->insert_id();

                    $this->orders_model->add_order_payment([
                        'order_id' => $order_id,
                        'payment_type' => 'WALLET_DEBIT',
                        'amount' => $order_wallet,
                        'transaction_id' => $wallet_transaction_id,
                        'status' => 'SUCCESS',
                        'note' => 'Wallet payment for PREMEAL order',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // Record gateway payment
                if ($order_gateway > 0 && !empty($razorpay_payment_id)) {
                    $this->orders_model->add_order_payment([
                        'order_id' => $order_id,
                        'payment_type' => 'ONLINE_PAYMENT',
                        'amount' => $order_gateway,
                        'razorpay_payment_id' => $razorpay_payment_id,
                        'razorpay_order_id' => $razorpay_order_id,
                        'status' => 'SUCCESS',
                        'note' => 'Online payment for PREMEAL order',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // Record company subsidy
                if ($company_contribution > 0) {
                    $this->orders_model->add_order_payment([
                        'order_id' => $order_id,
                        'payment_type' => 'COMPANY_SUBSIDY',
                        'amount' => $company_contribution,
                        'status' => 'SUCCESS',
                        'note' => 'Company subsidy for PREMEAL order',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // Add status history
                $this->orders_model->add_status_history([
                    'order_id' => $order_id,
                    'from_status' => null,
                    'to_status' => 'PENDING',
                    'changed_by_type' => 'EMPLOYEE',
                    'changed_by_id' => $employee_id,
                    'note' => $is_primary ? 'PREMEAL primary order placed' : 'PREMEAL child order for ' . $date,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $created_orders[] = [
                    'order_id' => (int) $order_id,
                    'order_number' => $order_number,
                    'scheduled_date' => $date,
                    'meal_type' => $meal_type,
                    'pickup_time' => $serving_time ? substr($serving_time, 0, 5) : null,
                    'pickup_code' => $pickup_code,
                    'qr_data'   => "$order_number|$pickup_code",
                    'status' => 'PENDING',
                    'is_primary' => $is_primary,
                    'parent_order_id' => $is_primary ? null : (int) $primary_order_id,
                    'total_amount' => round($subtotal, 2),
                    'company_contribution' => round($company_contribution, 2),
                    'employee_paid' => round($order_employee_final, 2)
                ];
            }

            // Apply coupon usage (once for all orders)
            if (!empty($coupon) && $coupon_discount > 0) {
                $this->couponlib->applyCoupon(
                    $coupon->id,
                    $employee_id,
                    $created_orders[0]['order_id'],
                    $coupon_discount
                );
            }

            // Update pending order status if exists
            if (!empty($pending_order_id)) {
                $this->orders_model->update_pending_order($pending_order_id, 'COMPLETED', [
                    'order_id' => $primary_order_id,
                    'completed_at' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            // Get updated wallet balance
            $wallet = getWalletMoneyArray($employee_id);
            $wallet_balance_after = round((float) $wallet['available'], 2);

            $this->logger->info('PREMEAL Orders created successfully', [
                'employee_id' => $employee_id,
                'primary_order_id' => $primary_order_id,
                'orders_count' => count($created_orders),
                'total_wallet_used' => $wallet_amount,
                'total_gateway_paid' => $gateway_amount
            ], 'orders');

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => count($created_orders) . ' meal(s) booked successfully',
                'data' => [
                    'primary_order_id' => (int) $primary_order_id,
                    'orders' => $created_orders,
                    'payment_summary' => [
                        'total_company_contribution' => round(array_sum(array_column($created_orders, 'company_contribution')), 2),
                        'total_employee_paid' => round(array_sum(array_column($created_orders, 'employee_paid')), 2),
                        'wallet_used' => round($wallet_amount, 2),
                        'gateway_paid' => round($gateway_amount, 2),
                        'coupon_discount' => round($coupon_discount, 2),
                        'payment_status' => 'COMPLETED'
                    ],
                    'wallet_balance_after' => $wallet_balance_after
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->logger->error('PREMEAL Order creation failed', [
                'employee_id' => $employee_id,
                'error' => $e->getMessage()
            ], 'orders');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to create orders. Please try again.',
                'data' => null
            ]);
        }
    }

    /**
     * Get PREMEAL product with store-specific pricing
     *
     * @param int $product_id
     * @param int $store_id
     * @return object|null
     */
    private function get_premeal_product($product_id, $store_id)
    {
        return $this->db
            ->select('p.*, sp.price as store_price, sp.available_stock')
            ->from('products p')
            ->join('store_products sp', 'sp.product_id = p.id AND sp.store_id = ' . (int)$store_id . ' AND sp.is_active = 1 AND sp.deleted_at IS NULL', 'left')
            ->where('p.id', $product_id)
            ->where('p.premeal_enabled', 1)
            ->where('p.is_active', 1)
            ->where('p.deleted_at IS NULL')
            ->get()
            ->row();
    }

    /**
     * Format failed dates into a compact string grouped by month
     *
     * Examples:
     *   ["2026-03-13"]                    => "13 Mar"
     *   ["2026-03-13","2026-03-14"]       => "13, 14 Mar"
     *   ["2026-03-30","2026-03-31","2026-04-01"] => "30, 31 Mar & 1 Apr"
     *
     * @param array $failed_dates Array with 'date' keys
     * @return string Formatted date string
     */
    private function format_failed_dates($failed_dates)
    {
        $by_month = [];
        foreach ($failed_dates as $fd) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fd['date'])) {
                continue;
            }
            $month = date('M', strtotime($fd['date']));
            $day = (int) date('d', strtotime($fd['date']));
            $by_month[$month][] = $day;
        }

        $parts = [];
        foreach ($by_month as $month => $days) {
            $parts[] = implode(', ', $days) . ' ' . $month;
        }

        return implode(' & ', $parts);
    }

    // ==================== KOT ORDERING ====================

    /**
     * Get delivery locations for a store
     *
     * POST /api/v1/user/orders/delivery_locations
     *
     * Required parameters (form-data):
     * - store_id: Store ID
     *
     * @return void JSON response with active delivery locations
     */
    public function delivery_locations()
    {
        if (!$this->check_auth()) {
            return;
        }

        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $store_id = (int) $this->input->post('store_id');

        $store = $this->validate_store($store_id, $auth->company_id);
        if (!$store) {
            return;
        }

        $locations = $this->locations_model->get_active_by_store($store_id);

        $result = [];
        foreach ($locations as $loc) {
            $result[] = [
                'id' => (int) $loc->id,
                'name' => $loc->name,
                'short_name' => $loc->short_name,
                'floor' => $loc->floor,
                'building' => $loc->building
            ];
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Delivery locations fetched',
            'data' => [
                'locations' => $result,
                'total' => count($result)
            ]
        ]);
    }

    /**
     * Get departments for employee's company
     *
     * POST /api/v1/user/orders/departments
     *
     * @return void JSON response with active departments
     */
    public function departments()
    {
        if (!$this->check_auth()) {
            return;
        }

        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $departments = $this->db
            ->select('id, name, code')
            ->from('company_departments')
            ->where('company_id', $auth->company_id)
            ->where('is_active', 1)
            ->where('deleted_at IS NULL', NULL, FALSE)
            ->order_by('name', 'ASC')
            ->get()
            ->result();

        $result = [];
        foreach ($departments as $dept) {
            $result[] = [
                'id' => (int) $dept->id,
                'name' => $dept->name,
                'code' => $dept->code
            ];
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Departments fetched',
            'data' => [
                'departments' => $result,
                'total' => count($result)
            ]
        ]);
    }

    /**
     * KOT Checkout Summary
     *
     * Read-only summary of cart with policy contribution, coupon preview
     * and wallet balance. Does NOT create pending order or Razorpay order.
     * Call this before kot_initiate to show the checkout screen.
     *
     * POST /api/v1/user/orders/kot_checkout_summary
     *
     * Required parameters (form-data):
     * - store_id: Store ID
     *
     * Optional parameters:
     * - coupon_code: Coupon code to preview
     * - wallet_amount: Wallet amount to preview (default: 0)
     *
     * @return void JSON response with checkout summary
     */
    public function kot_checkout_summary()
    {
        if (!$this->check_auth()) {
            return;
        }

        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $employee_id = $auth->employee_id;
        $company_id = $auth->company_id;

        $this->logger->info('KOT Checkout Summary API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address()
        ], 'orders');

        $post_data = $this->input->post(null, true);

        $store_id = isset($post_data['store_id']) ? (int) $post_data['store_id'] : null;
        $coupon_code = isset($post_data['coupon_code']) ? trim($post_data['coupon_code']) : null;
        $wallet_amount = isset($post_data['wallet_amount']) ? (float) $post_data['wallet_amount'] : 0;

        // Validate store
        $store = $this->validate_store($store_id, $company_id);
        if (!$store) {
            return;
        }

        // Validate KOT access
        $employee = $this->validate_module_access('KOT', $employee_id);
        if (!$employee) {
            return;
        }

        // Get cart items
        $cart_items = $this->cart_model->get_cart_items($employee_id, $store_id, 'KOT');

        if (empty($cart_items)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Your cart is empty',
                'data' => null
            ]);
            return;
        }

        // Calculate cart totals
        $subtotal = 0;
        $total_tax = 0;
        $total_items = 0;
        $items_data = [];

        foreach ($cart_items as $item) {
            $quantity = (int) $item->quantity;
            $available_stock = $item->available_stock;

            $is_in_stock = ($available_stock === null || (int) $available_stock >= $quantity);

            $inclusive_price = !empty($item->store_price) ? (float) $item->store_price : (float) $item->base_price;
            $tax_percentage = (float) $item->tax_percentage;

            $unit_base_price = calculate_base_price($inclusive_price, $tax_percentage);
            $unit_gst = calculate_gst_amount($inclusive_price, $tax_percentage);

            $item_subtotal = $unit_base_price * $quantity;
            $item_tax = $unit_gst * $quantity;
            $item_total = $inclusive_price * $quantity;

            $total_items += $quantity;
            $subtotal += $item_subtotal;
            $total_tax += $item_tax;

            $items_data[] = [
                'cart_id' => (int) $item->cart_id,
                'product_id' => (int) $item->product_id,
                'product_name' => $item->product_name,
                'thumbnail' => !empty($item->thumbnail) ? base_url($item->thumbnail) : null,
                'is_vegetarian' => isset($item->is_vegetarian) ? (bool) $item->is_vegetarian : null,
                'quantity' => $quantity,
                'unit_price' => round($inclusive_price, 2),
                'tax_percentage' => round($tax_percentage, 2),
                'base_price' => round($unit_base_price, 2),
                'tax_amount' => round($unit_gst, 2),
                'subtotal' => round($item_subtotal, 2),
                'total' => round($item_total, 2),
                'note' => $item->note,
                'is_in_stock' => $is_in_stock,
                'available_stock' => $available_stock === null ? null : (int) $available_stock
            ];
        }

        $gross_total = round($subtotal + $total_tax, 2);

        // Get policy for KOT
        $policy = $this->policylib->get_employee_policy($employee_id, 'KOT');

        $company_contribution = 0;
        $employee_share = $gross_total;
        $policy_data = null;
        $daily_limit_info = null;

        if ($policy && $this->policylib->is_kot_enabled($policy)) {
            // Check daily limit
            $limit_check = $this->policylib->check_daily_limit(
                $employee_id,
                date('Y-m-d'),
                'SNACKS',
                $policy,
                'KOT'
            );

            $daily_limit_info = [
                'daily_limit' => $limit_check['daily_limit'] ?? null,
                'used_today' => $limit_check['used'] ?? 0,
                'remaining' => $limit_check['remaining'] ?? 0,
                'can_order' => $limit_check['available']
            ];

            if ($limit_check['available']) {
                $contribution = $this->policylib->calculate_contribution($policy, $gross_total);
                $company_contribution = $contribution['company_contribution'];
                $employee_share = $contribution['employee_contribution'];
            }

            $policy_data = [
                'id' => (int) $policy->id,
                'name' => $policy->name,
                'type' => $policy->policy_type,
                'is_enabled' => true,
                'daily_limit' => $daily_limit_info
            ];
        }

        // Apply coupon to employee share only (preview)
        $coupon_data = null;
        $discount_amount = 0;

        if (!empty($coupon_code)) {
            $company = $this->common->getdatabytable('companies', ['id' => $company_id]);
            $client_id = $company ? $company->client_id : 0;

            $coupon_result = $this->couponlib->validateCoupon([
                'coupon_code' => $coupon_code,
                'order_amount' => $employee_share,
                'employee_id' => $employee_id,
                'company_id' => $company_id,
                'client_id' => $client_id,
                'module' => 'KOT'
            ]);

            if ($coupon_result['valid']) {
                $coupon = $coupon_result['coupon'];
                $discount_amount = $coupon_result['discount_amount'];
                $coupon_data = [
                    'id' => (int) $coupon->id,
                    'code' => $coupon->code,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => (float) $coupon->discount_value,
                    'discount_amount' => round($discount_amount, 2),
                    'message' => 'Coupon applied: ₹' . number_format($discount_amount, 2) . ' off',
                    'is_valid' => true
                ];
            } else {
                $coupon_data = [
                    'code' => $coupon_code,
                    'is_valid' => false,
                    'message' => $coupon_result['message']
                ];
            }
        }

        $employee_payable = round($employee_share - $discount_amount, 2);
        if ($employee_payable < 0) {
            $employee_payable = 0;
        }

        // Wallet balance
        $wallet = getWalletMoneyArray($employee_id);
        $available_balance = round((float) $wallet['available'], 2);

        // Preview wallet usage
        if ($wallet_amount < 0) {
            $wallet_amount = 0;
        }
        if ($wallet_amount > $available_balance) {
            $wallet_amount = $available_balance;
        }
        if ($wallet_amount > $employee_payable) {
            $wallet_amount = $employee_payable;
        }
        $wallet_amount = round($wallet_amount, 2);

        $online_payment_amount = round($employee_payable - $wallet_amount, 2);

        // Get delivery locations for this store
        $locations = $this->locations_model->get_active_by_store($store_id);
        $locations_data = [];
        foreach ($locations as $loc) {
            $locations_data[] = [
                'id' => (int) $loc->id,
                'name' => $loc->name,
                'short_name' => $loc->short_name,
                'floor' => $loc->floor,
                'building' => $loc->building
            ];
        }

        // Get departments for employee's company
        $departments = $this->db
            ->select('id, name, code')
            ->from('company_departments')
            ->where('company_id', $company_id)
            ->where('is_active', 1)
            ->where('deleted_at IS NULL', NULL, FALSE)
            ->order_by('name', 'ASC')
            ->get()
            ->result();

        $departments_data = [];
        foreach ($departments as $dept) {
            $departments_data[] = [
                'id' => (int) $dept->id,
                'name' => $dept->name,
                'code' => $dept->code
            ];
        }

        $response_data = [
            'store' => [
                'id' => (int) $store->id,
                'name' => $store->name,
                'short_name' => $store->short_name
            ],
            'items' => $items_data,
            'summary' => [
                'total_items' => $total_items,
                'unique_items' => count($items_data),
                'subtotal' => round($subtotal, 2),
                'total_tax' => round($total_tax, 2),
                'gross_total' => $gross_total,
                'company_contribution' => round($company_contribution, 2),
                'employee_share' => round($employee_share, 2),
                'discount_amount' => round($discount_amount, 2),
                'employee_payable' => $employee_payable,
                'wallet_balance' => $available_balance,
                'wallet_to_use' => $wallet_amount,
                'online_payment_amount' => $online_payment_amount,
                'payment_required' => $online_payment_amount > 0
            ],
            'policy' => $policy_data,
            'coupon' => $coupon_data,
            'delivery_locations' => $locations_data,
            'departments' => $departments_data
        ];

        $this->logger->info('KOT Checkout Summary fetched', [
            'employee_id' => $employee_id,
            'store_id' => $store_id,
            'gross_total' => $gross_total,
            'employee_payable' => $employee_payable
        ], 'orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Checkout summary fetched',
            'data' => $response_data
        ]);
    }

    /**
     * KOT Order Initiate
     *
     * Validates cart, applies policy contribution, coupon, wallet
     * and creates Razorpay order if needed.
     *
     * POST /api/v1/user/orders/kot_initiate
     *
     * Required parameters (form-data):
     * - store_id: Store ID
     * - delivery_location_id: Delivery location ID
     * - department_id: Department ID
     *
     * Optional parameters:
     * - wallet_amount: Amount to use from wallet (default: 0)
     * - coupon_code: Coupon code to apply
     *
     * @return void JSON response with order summary and payment details
     */
    public function kot_initiate()
    {
        if (!$this->check_auth()) {
            return;
        }

        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $employee_id = $auth->employee_id;
        $company_id = $auth->company_id;

        $this->logger->info('KOT Order Initiate API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address()
        ], 'orders');

        $post_data = $this->input->post(null, true);

        $store_id = isset($post_data['store_id']) ? (int) $post_data['store_id'] : null;
        $delivery_location_id = isset($post_data['delivery_location_id']) ? (int) $post_data['delivery_location_id'] : null;
        $department_id = isset($post_data['department_id']) ? (int) $post_data['department_id'] : null;
        $wallet_amount = isset($post_data['wallet_amount']) ? (float) $post_data['wallet_amount'] : 0;
        $coupon_code = isset($post_data['coupon_code']) ? trim($post_data['coupon_code']) : null;

        // Validate store
        $store = $this->validate_store($store_id, $company_id);
        if (!$store) {
            return;
        }

        // Validate KOT access
        $employee = $this->validate_module_access('KOT', $employee_id);
        if (!$employee) {
            return;
        }

        // Validate delivery location
        if (empty($delivery_location_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'delivery_location_id is required',
                'data' => null
            ]);
            return;
        }

        $location = $this->locations_model->get_by_id($delivery_location_id, $store_id);
        if (!$location || !$location->is_active) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid or inactive delivery location',
                'data' => null
            ]);
            return;
        }

        // Validate department
        if (empty($department_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'department_id is required',
                'data' => null
            ]);
            return;
        }

        $department = $this->common->getdatabytable('company_departments', [
            'id' => $department_id,
            'company_id' => $company_id,
            'is_active' => 1
        ]);

        if (empty($department)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid or inactive department',
                'data' => null
            ]);
            return;
        }

        // Get cart items
        $cart_items = $this->cart_model->get_cart_items($employee_id, $store_id, 'KOT');

        if (empty($cart_items)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Your cart is empty',
                'data' => null
            ]);
            return;
        }

        // Calculate cart totals (same GST-inclusive logic as QSR)
        $subtotal = 0;
        $total_tax = 0;
        $total_items = 0;
        $insufficient_stock_products = [];
        $items_data = [];

        foreach ($cart_items as $item) {
            $quantity = (int) $item->quantity;
            $available_stock = $item->available_stock;

            $is_in_stock = ($available_stock === null || (int) $available_stock >= $quantity);

            if (!$is_in_stock) {
                $insufficient_stock_products[] = [
                    'product_name' => $item->product_name,
                    'requested_qty' => $quantity,
                    'available_qty' => (int) $available_stock
                ];
            }

            $inclusive_price = !empty($item->store_price) ? (float) $item->store_price : (float) $item->base_price;
            $tax_percentage = (float) $item->tax_percentage;

            $unit_base_price = calculate_base_price($inclusive_price, $tax_percentage);
            $unit_gst = calculate_gst_amount($inclusive_price, $tax_percentage);

            $item_subtotal = $unit_base_price * $quantity;
            $item_tax = $unit_gst * $quantity;
            $item_total = $inclusive_price * $quantity;

            $total_items += $quantity;
            $subtotal += $item_subtotal;
            $total_tax += $item_tax;

            $items_data[] = [
                'cart_id' => (int) $item->cart_id,
                'product_id' => (int) $item->product_id,
                'product_name' => $item->product_name,
                'thumbnail' => !empty($item->thumbnail) ? base_url($item->thumbnail) : null,
                'is_vegetarian' => isset($item->is_vegetarian) ? (bool) $item->is_vegetarian : null,
                'quantity' => $quantity,
                'unit_price' => round($inclusive_price, 2),
                'tax_percentage' => round($tax_percentage, 2),
                'base_price' => round($unit_base_price, 2),
                'tax_amount' => round($unit_gst, 2),
                'subtotal' => round($item_subtotal, 2),
                'total' => round($item_total, 2),
                'note' => $item->note,
                'is_in_stock' => $is_in_stock,
                'available_stock' => $available_stock === null ? null : (int) $available_stock
            ];
        }

        // Check stock
        if (!empty($insufficient_stock_products)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Some items in your cart have insufficient stock. Please adjust quantities to proceed.',
                'data' => [
                    'insufficient_stock_products' => $insufficient_stock_products,
                    'items' => $items_data
                ]
            ]);
            return;
        }

        $gross_total = round($subtotal + $total_tax, 2);

        // Get policy for KOT
        $policy = $this->policylib->get_employee_policy($employee_id, 'KOT');

        $company_contribution = 0;
        $employee_share = $gross_total;
        $policy_id = null;
        $policy_type = null;

        if ($policy && $this->policylib->is_kot_enabled($policy)) {
            // Check daily limit
            $limit_check = $this->policylib->check_daily_limit(
                $employee_id,
                date('Y-m-d'),
                'SNACKS',
                $policy,
                'KOT'
            );

            if (!$limit_check['available']) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => $limit_check['reason'] ?? 'Daily KOT order limit reached',
                    'data' => [
                        'daily_limit' => $limit_check['daily_limit'] ?? null,
                        'orders_today' => $limit_check['used'] ?? null
                    ]
                ]);
                return;
            }

            // Calculate contribution split
            $contribution = $this->policylib->calculate_contribution($policy, $gross_total);
            $company_contribution = $contribution['company_contribution'];
            $employee_share = $contribution['employee_contribution'];
            $policy_id = $policy->id;
            $policy_type = $policy->policy_type;
        }

        // Apply coupon to employee share only
        $coupon = null;
        $discount_amount = 0;
        $coupon_message = '';

        if (!empty($coupon_code)) {
            $company = $this->common->getdatabytable('companies', ['id' => $company_id]);
            $client_id = $company ? $company->client_id : 0;

            $coupon_result = $this->couponlib->validateCoupon([
                'coupon_code' => $coupon_code,
                'order_amount' => $employee_share,
                'employee_id' => $employee_id,
                'company_id' => $company_id,
                'client_id' => $client_id,
                'module' => 'KOT'
            ]);

            if ($coupon_result['valid']) {
                $coupon = $coupon_result['coupon'];
                $discount_amount = $coupon_result['discount_amount'];
                $coupon_message = 'Coupon applied: ₹' . number_format($discount_amount, 2) . ' off';
            } else {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => $coupon_result['message'],
                    'data' => null
                ]);
                return;
            }
        }

        $employee_payable = round($employee_share - $discount_amount, 2);

        // If FREE policy covers everything
        if ($employee_payable <= 0) {
            $employee_payable = 0;
            $wallet_amount = 0;
        }

        // Validate wallet amount
        $wallet = getWalletMoneyArray($employee_id);
        $available_balance = round((float) $wallet['available'], 2);

        if ($wallet_amount < 0) {
            $wallet_amount = 0;
        }
        if ($wallet_amount > $available_balance) {
            $wallet_amount = $available_balance;
        }
        if ($wallet_amount > $employee_payable) {
            $wallet_amount = $employee_payable;
        }
        $wallet_amount = round($wallet_amount, 2);

        $online_payment_amount = round($employee_payable - $wallet_amount, 2);

        // Prepare KOT-specific data for pending order
        $kot_extra = [
            'delivery_location_id' => $delivery_location_id,
            'department_id' => $department_id,
            'policy_id' => $policy_id,
            'policy_type' => $policy_type,
            'company_contribution' => $company_contribution,
            'employee_share' => $employee_share,
            'items' => $items_data
        ];

        // Build response
        $response_data = [
            'store' => [
                'id' => (int) $store->id,
                'name' => $store->name,
                'short_name' => $store->short_name
            ],
            'delivery_location' => [
                'id' => (int) $location->id,
                'name' => $location->name,
                'short_name' => $location->short_name,
                'floor' => $location->floor,
                'building' => $location->building
            ],
            'department' => [
                'id' => (int) $department->id,
                'name' => $department->name
            ],
            'items' => $items_data,
            'summary' => [
                'total_items' => $total_items,
                'subtotal' => round($subtotal, 2),
                'total_tax' => round($total_tax, 2),
                'gross_total' => $gross_total,
                'company_contribution' => round($company_contribution, 2),
                'employee_share' => round($employee_share, 2),
                'discount_amount' => round($discount_amount, 2),
                'employee_payable' => $employee_payable,
                'wallet_balance' => $available_balance,
                'wallet_to_use' => $wallet_amount,
                'online_payment_amount' => $online_payment_amount
            ],
            'policy' => $policy ? [
                'id' => (int) $policy->id,
                'name' => $policy->name,
                'type' => $policy->policy_type
            ] : null,
            'coupon' => $coupon ? [
                'id' => (int) $coupon->id,
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => (float) $coupon->discount_value
            ] : null,
            'razorpay' => null,
            'payment_required' => $online_payment_amount > 0
        ];

        if (!empty($coupon_message)) {
            $response_data['coupon_message'] = $coupon_message;
        }

        // Create Razorpay order if online payment needed
        if ($online_payment_amount > 0) {
            if (!$this->razorpaylib->init($company_id)) {
                $this->logger->error('Failed to initialize Razorpay for KOT', [
                    'employee_id' => $employee_id,
                    'company_id' => $company_id
                ], 'orders');

                $this->output([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Payment service not configured. Please contact support.',
                    'data' => null
                ]);
                return;
            }

            $receipt_id = 'KOT_' . $employee_id . '_' . time();

            $razorpay_result = $this->razorpaylib->createOrder([
                'amount' => $online_payment_amount,
                'receipt' => $receipt_id,
                'notes' => [
                    'employee_id' => $employee_id,
                    'store_id' => $store_id,
                    'module' => 'KOT'
                ]
            ]);

            if (!$razorpay_result['success']) {
                $this->logger->error('Razorpay order creation failed for KOT', [
                    'employee_id' => $employee_id,
                    'amount' => $online_payment_amount,
                    'error' => $razorpay_result['message']
                ], 'orders');

                $this->output([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Payment order failed: ' . ($razorpay_result['message'] ?? 'Unknown error'),
                    'data' => null
                ]);
                return;
            }

            $razorpay_order = $razorpay_result['order'];

            $pending_order_data = [
                'employee_id' => $employee_id,
                'store_id' => $store_id,
                'module' => 'KOT',
                'razorpay_order_id' => $razorpay_order['id'],
                'amount' => $online_payment_amount,
                'wallet_amount' => $wallet_amount,
                'coupon_id' => $coupon ? $coupon->id : null,
                'coupon_code' => $coupon ? $coupon->code : null,
                'discount_amount' => $discount_amount,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($total_tax, 2),
                'total_amount' => $gross_total,
                'items_json' => json_encode($kot_extra),
                'status' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
            ];

            $pending_id = $this->orders_model->store_pending_order($pending_order_data);
            $response_data['pending_order_id'] = $pending_id;

            $response_data['razorpay'] = [
                'key' => $this->razorpaylib->getKeyId(),
                'amount' => $razorpay_order['amount'],
                'currency' => $razorpay_order['currency'],
                'name' => config_item('application_name') ?: 'Joy Foods',
                'description' => 'Pay with Card / Net Banking / Wallets / UPI',
                'image' => base_url('assets/images/logo.png'),
                'order_id' => $razorpay_order['id'],
                'prefill' => [
                    'name' => trim($employee->first_name . ' ' . ($employee->last_name ?? '')),
                    'email' => $employee->email ?? '',
                    'contact' => $employee->phone ?? ''
                ],
                'notes' => [
                    'employee_id' => (string) $employee_id,
                    'store_id' => (string) $store_id,
                    'module' => 'KOT'
                ],
                'theme' => [
                    'color' => '#BD3839'
                ]
            ];
        } else {
            // No online payment - wallet-only or free
            $pending_order_data = [
                'employee_id' => $employee_id,
                'store_id' => $store_id,
                'module' => 'KOT',
                'razorpay_order_id' => null,
                'amount' => 0,
                'wallet_amount' => $wallet_amount,
                'coupon_id' => $coupon ? $coupon->id : null,
                'coupon_code' => $coupon ? $coupon->code : null,
                'discount_amount' => $discount_amount,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($total_tax, 2),
                'total_amount' => $gross_total,
                'items_json' => json_encode($kot_extra),
                'status' => 'PENDING',
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
            ];

            $pending_id = $this->orders_model->store_pending_order($pending_order_data);
            $response_data['pending_order_id'] = $pending_id;
        }

        $this->logger->info('KOT Order initiated successfully', [
            'employee_id' => $employee_id,
            'store_id' => $store_id,
            'gross_total' => $gross_total,
            'company_contribution' => $company_contribution,
            'employee_payable' => $employee_payable,
            'wallet_amount' => $wallet_amount,
            'online_payment' => $online_payment_amount
        ], 'orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Order initiated successfully',
            'data' => $response_data
        ]);
    }

    /**
     * KOT Order Complete
     *
     * Verifies payment (if applicable) and creates the KOT order.
     *
     * POST /api/v1/user/orders/kot_complete
     *
     * Required parameters (form-data):
     * - store_id: Store ID
     *
     * For online payment:
     * - razorpay_order_id: Razorpay order ID
     * - razorpay_payment_id: Razorpay payment ID
     * - razorpay_signature: Razorpay signature
     *
     * For wallet-only / free payment:
     * - pending_order_id: Pending order ID from initiate response
     *
     * @return void JSON response with order details
     */
    public function kot_complete()
    {
        if (!$this->check_auth()) {
            return;
        }

        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $employee_id = $auth->employee_id;
        $company_id = $auth->company_id;

        $this->logger->info('KOT Order Complete API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address()
        ], 'orders');

        $post_data = $this->input->post(null, true);

        $store_id = isset($post_data['store_id']) ? (int) $post_data['store_id'] : null;
        $razorpay_order_id = isset($post_data['razorpay_order_id']) ? trim($post_data['razorpay_order_id']) : null;
        $razorpay_payment_id = isset($post_data['razorpay_payment_id']) ? trim($post_data['razorpay_payment_id']) : null;
        $razorpay_signature = isset($post_data['razorpay_signature']) ? trim($post_data['razorpay_signature']) : null;
        $pending_order_id = isset($post_data['pending_order_id']) ? (int) $post_data['pending_order_id'] : null;

        // Validate store
        $store = $this->validate_store($store_id, $company_id);
        if (!$store) {
            return;
        }

        // Validate KOT access
        $employee = $this->validate_module_access('KOT', $employee_id);
        if (!$employee) {
            return;
        }

        // Determine payment method and get pending order
        $pending_order = null;

        if (!empty($razorpay_order_id)) {
            // Online payment - verify Razorpay
            if (empty($razorpay_payment_id) || empty($razorpay_signature)) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Payment verification details are required',
                    'data' => null
                ]);
                return;
            }

            if (!$this->razorpaylib->init($company_id)) {
                $this->logger->error('Failed to initialize Razorpay for KOT verification', [
                    'employee_id' => $employee_id,
                    'company_id' => $company_id
                ], 'orders');

                $this->output([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Payment service not configured. Please contact support.',
                    'data' => null
                ]);
                return;
            }

            $is_valid = $this->razorpaylib->verifyPaymentSignature(
                $razorpay_order_id,
                $razorpay_payment_id,
                $razorpay_signature
            );

            if (!$is_valid) {
                $this->logger->error('KOT Razorpay signature verification failed', [
                    'employee_id' => $employee_id,
                    'razorpay_order_id' => $razorpay_order_id
                ], 'orders');

                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Payment verification failed. Please contact support.',
                    'data' => null
                ]);
                return;
            }

            $pending_order = $this->orders_model->get_pending_order($razorpay_order_id);

        } elseif (!empty($pending_order_id)) {
            // Wallet-only or free payment
            $pending_order = $this->db->get_where('pending_orders', [
                'id' => $pending_order_id,
                'employee_id' => $employee_id,
                'module' => 'KOT',
                'status' => 'PENDING'
            ])->row();

            // Reject if this pending order requires online payment
            if ($pending_order && $pending_order->amount > 0 && !empty($pending_order->razorpay_order_id)) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Online payment is required for this order. Please complete the payment.',
                    'data' => null
                ]);
                return;
            }

        } else {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Payment information is required',
                'data' => null
            ]);
            return;
        }

        // Validate pending order
        if (empty($pending_order)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order session expired or invalid. Please try again.',
                'data' => null
            ]);
            return;
        }

        if ($pending_order->employee_id != $employee_id) {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Unauthorized order access',
                'data' => null
            ]);
            return;
        }

        if ($pending_order->store_id != $store_id) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store mismatch. Order was initiated for a different store.',
                'data' => null
            ]);
            return;
        }

        if (strtotime($pending_order->expires_at) < time()) {
            $this->orders_model->update_pending_order($pending_order->id, 'EXPIRED');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order session has expired. Please initiate a new order.',
                'data' => null
            ]);
            return;
        }

        // Parse KOT extra data from items_json
        $kot_extra = json_decode($pending_order->items_json, true);

        if (empty($kot_extra) || empty($kot_extra['items'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid order data. Please try again.',
                'data' => null
            ]);
            return;
        }

        $delivery_location_id = $kot_extra['delivery_location_id'];
        $department_id = $kot_extra['department_id'];
        $policy_id = $kot_extra['policy_id'];
        $company_contribution = (float) $kot_extra['company_contribution'];
        $employee_share = (float) $kot_extra['employee_share'];
        $items = $kot_extra['items'];

        // Start transaction
        $this->db->trans_start();

        // Atomically claim the pending order so concurrent completion calls
        // (client retry, double-tap, network replay) cannot both create an order
        if (!$this->orders_model->claim_pending_order($pending_order->id)) {
            $this->db->trans_rollback();
            $this->output_duplicate_completion($pending_order->id);
            return;
        }

        try {
            $order_number = $this->orders_model->generate_order_number('KOT');
            $pickup_code = generate_pickup_code(6);

            // Calculate employee payable after discount
            $employee_payable = round($employee_share - $pending_order->discount_amount, 2);
            if ($employee_payable < 0) {
                $employee_payable = 0;
            }

            // Determine payment method
            $payment_method = 'ONLINE';
            if ($pending_order->wallet_amount > 0 && $pending_order->amount > 0) {
                $payment_method = 'MIXED';
            } elseif ($pending_order->wallet_amount > 0 && $pending_order->amount <= 0) {
                $payment_method = 'WALLET';
            } elseif ($employee_payable <= 0) {
                $payment_method = 'POLICY';
            }

            $order_data = [
                'order_number' => $order_number,
                'employee_id' => $employee_id,
                'company_id' => $company_id,
                'store_id' => $store_id,
                'module' => 'KOT',
                'meal_type' => 'SNACKS',
                'pending_order_id' => $pending_order->id,
                'status' => 'PENDING',
                'pickup_code' => $pickup_code,
                'delivery_location_id' => $delivery_location_id,
                'department_id' => $department_id,
                'subtotal' => $pending_order->subtotal,
                'tax_amount' => $pending_order->tax_amount,
                'amount_before_discount' => $pending_order->subtotal + $pending_order->tax_amount,
                'coupon_id' => $pending_order->coupon_id,
                'coupon_code' => $pending_order->coupon_code,
                'discount_amount' => $pending_order->discount_amount,
                'total_amount' => $pending_order->total_amount,
                'policy_id' => $policy_id,
                'company_contribution' => $company_contribution,
                'employee_contribution' => $employee_payable,
                'wallet_deducted' => $pending_order->wallet_amount,
                'payment_status' => 'PAID',
                'payment_method' => $payment_method,
                'paid_at' => date('Y-m-d H:i:s'),
                'total_items' => 0,
                'unique_items' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $order_id = $this->orders_model->create_order($order_data);

            if (!$order_id) {
                throw new Exception('Failed to create KOT order');
            }

            // Create order items
            $order_items = [];
            $total_quantity = 0;

            foreach ($items as $item) {
                $order_items[] = [
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_percentage' => $item['tax_percentage'],
                    'base_price' => $item['base_price'],
                    'tax_amount' => $item['tax_amount'] * $item['quantity'],
                    'subtotal' => $item['subtotal'],
                    'total_amount' => $item['total'],
                    'note' => $item['note'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $total_quantity += $item['quantity'];
            }

            $this->orders_model->create_order_items($order_items);

            // Update order with item counts
            $this->orders_model->update_order($order_id, [
                'total_items' => $total_quantity,
                'unique_items' => count($order_items)
            ]);

            // Deduct wallet if used
            if ($pending_order->wallet_amount > 0) {
                $this->db->insert('transaction', [
                    'transaction_uuid' => generate_uuid(),
                    'user_id' => $employee_id,
                    'order_id' => $order_id,
                    'amount' => $pending_order->wallet_amount,
                    'transaction_type' => 2,
                    'transaction_label' => 'Payment for order ' . $order_number,
                    'transaction_date' => date('Y-m-d')
                ]);

                $wallet_transaction_id = $this->db->insert_id();

                $this->orders_model->add_order_payment([
                    'order_id' => $order_id,
                    'payment_type' => 'WALLET_DEBIT',
                    'amount' => $pending_order->wallet_amount,
                    'transaction_id' => $wallet_transaction_id,
                    'status' => 'SUCCESS',
                    'note' => 'Wallet deduction for KOT order',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Record online payment
            if (!empty($razorpay_payment_id) && $pending_order->amount > 0) {
                $this->orders_model->add_order_payment([
                    'order_id' => $order_id,
                    'payment_type' => 'ONLINE_PAYMENT',
                    'amount' => $pending_order->amount,
                    'transaction_id' => null,
                    'razorpay_payment_id' => $razorpay_payment_id,
                    'razorpay_order_id' => $razorpay_order_id,
                    'status' => 'SUCCESS',
                    'note' => null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Record company subsidy
            if ($company_contribution > 0) {
                $this->orders_model->add_order_payment([
                    'order_id' => $order_id,
                    'payment_type' => 'COMPANY_SUBSIDY',
                    'amount' => $company_contribution,
                    'status' => 'SUCCESS',
                    'note' => 'Company subsidy for KOT order',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Apply coupon usage
            if (!empty($pending_order->coupon_id)) {
                $this->couponlib->applyCoupon(
                    $pending_order->coupon_id,
                    $employee_id,
                    $order_id,
                    $pending_order->discount_amount
                );
            }

            // Add status history
            $this->orders_model->add_status_history([
                'order_id' => $order_id,
                'from_status' => null,
                'to_status' => 'PENDING',
                'changed_by_type' => 'EMPLOYEE',
                'changed_by_id' => $employee_id,
                'note' => 'KOT order placed',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Deduct stock
            foreach ($items as $item) {
                $this->orders_model->deduct_stock($store_id, $item['product_id'], $item['quantity'], [
                    'reference_id'      => $order_id,
                    'order_number'      => $order_number,
                    'performed_by_type' => 'EMPLOYEE',
                    'performed_by_id'   => $employee_id,
                    'note'              => 'Stock deducted on KOT order placement'
                ]);
            }

            // Clear cart
            $this->cart_model->clear_cart($employee_id, $store_id, 'KOT');

            // Update pending order status
            $this->orders_model->update_pending_order($pending_order->id, 'COMPLETED', [
                'order_id' => $order_id,
                'completed_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            // Get delivery location name for response
            $location = $this->locations_model->get_by_id($delivery_location_id, $store_id);

            $this->logger->info('KOT Order created successfully', [
                'order_id' => $order_id,
                'order_number' => $order_number,
                'employee_id' => $employee_id,
                'total_amount' => $pending_order->total_amount,
                'company_contribution' => $company_contribution
            ], 'orders');

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order' => [
                        'id' => (int) $order_id,
                        'order_number' => $order_number,
                        'pickup_code' => $pickup_code,
                        'status' => 'PENDING',
                        'module' => 'KOT',
                        'total_amount' => (float) $pending_order->total_amount,
                        'company_contribution' => round($company_contribution, 2),
                        'employee_paid' => round($employee_payable, 2),
                        'wallet_deducted' => (float) $pending_order->wallet_amount,
                        'online_paid' => (float) $pending_order->amount,
                        'discount_amount' => (float) $pending_order->discount_amount,
                        'items_count' => $total_quantity,
                        'qr_data' => "$order_number|$pickup_code",
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    'delivery_location' => $location ? [
                        'id' => (int) $location->id,
                        'name' => $location->name,
                        'short_name' => $location->short_name,
                        'floor' => $location->floor,
                        'building' => $location->building
                    ] : null,
                    'store' => [
                        'id' => (int) $store->id,
                        'name' => $store->name,
                        'address' => trim(implode(', ', array_filter([
                            $store->address_line1,
                            $store->city,
                            $store->state
                        ])))
                    ]
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->logger->error('KOT Order creation failed', [
                'employee_id' => $employee_id,
                'error' => $e->getMessage()
            ], 'orders');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to create order. Please try again.',
                'data' => null
            ]);
        }
    }
}
