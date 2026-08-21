<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Store Orders Controller
 *
 * Handles order management for store staff (cashiers, managers, kitchen staff).
 * Displays and manages QSR and KOT orders for the store.
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
        $this->load->model('StoreOrders_model', 'orders_model');
        $this->load->model('DeliveryLocations_model', 'locations_model');
        $this->load->library('NotificationLib');
        $this->load->library('RazorpayLib', null, 'razorpaylib');
        $this->load->helper('common');
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
     * Calculate relative time ago string
     *
     * @param string $datetime DateTime string
     * @return string Relative time (e.g., "5 min ago")
     */
    private function time_ago($datetime)
    {
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        }
        if ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        }
        if ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        }
        if ($diff->h > 0) {
            return $diff->h . ' hr' . ($diff->h > 1 ? 's' : '') . ' ago';
        }
        if ($diff->i > 0) {
            return $diff->i . ' min' . ($diff->i > 1 ? 's' : '') . ' ago';
        }
        return 'Just now';
    }

    /**
     * Format items summary string
     *
     * @param array $items Order items
     * @return string Formatted string like "Pizza (2), Burger (1)"
     */
    private function format_items_summary($items)
    {
        $summary_parts = [];
        foreach ($items as $item) {
            $summary_parts[] = $item->product_name . ' (' . $item->quantity . ')';
        }
        return implode(', ', $summary_parts);
    }

    /**
     * Format order for list response
     *
     * @param object $order Order object
     * @param array  $items Order items
     * @return array Formatted order data
     */
    private function format_order_for_list($order, $items)
    {
        // Determine pickup type
        $pickup_type = $order->is_scheduled ? 'Scheduled' : 'Instant';

        // Format customer name — guest or employee
        $is_guest = !empty($order->is_guest_order);
        if ($is_guest) {
            $customer_name = $order->guest_name ?? 'Guest';
        } else {
            $customer_name = trim($order->employee_first_name . ' ' . ($order->employee_last_name ?? ''));
        }

        // Delivery location for KOT orders
        $delivery_location = null;
        if ($order->module === 'KOT' && !empty($order->delivery_location_id)) {
            $loc = $this->locations_model->get_by_id($order->delivery_location_id, $order->store_id);
            if ($loc) {
                $delivery_location = [
                    'id' => (int)$loc->id,
                    'name' => $loc->name,
                    'short_name' => $loc->short_name,
                    'floor' => $loc->floor,
                    'building' => $loc->building
                ];
            }
        }

        return [
            'order_id' => (int)$order->id,
            'order_number' => $order->order_number,
            'module' => $order->module,
            'pickup_type' => $pickup_type,
            'is_scheduled' => (bool)$order->is_scheduled,
            'pickup_time' => $order->pickup_time ? date('H:i', strtotime($order->pickup_time)) : null,
            'pickup_code' => $order->pickup_code,
            'time_ago' => $this->time_ago($order->created_at),
            'created_at' => $order->created_at,
            'is_guest_order' => $is_guest,
            'customer_name' => $customer_name,
            'customer_phone' => $is_guest ? ($order->guest_phone ?? null) : null,
            'delivery_location' => $delivery_location,
            'items_summary' => $this->format_items_summary($items),
            'total_amount' => (float)$order->total_amount,
            'total_items' => (int)$order->total_items,
            'prep_time' => isset($order->prep_time) ? (int)$order->prep_time : null,
            'status' => $order->status
        ];
    }

    /**
     * Get Pending Orders
     *
     * Returns list of pending orders (status: PENDING) for the store.
     * These orders can be accepted or rejected by store staff.
     *
     * @api POST /api/v1/store/orders/pending
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param string module (optional) Filter by module: QSR, KOT (default: both)
     * @param int    page (optional) Page number (default: 1)
     * @param int    per_page (optional) Items per page (default: 20, max: 50)
     *
     * @return void Outputs JSON response
     *         - 200: List of pending orders
     *         - 401: Unauthorized
     */
    public function pending()
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

        $this->logger->info('Store Pending Orders API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_orders');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $module = isset($post_data['module']) ? strtoupper(trim($post_data['module'])) : null;
        $page = isset($post_data['page']) ? (int)$post_data['page'] : 1;
        $per_page = isset($post_data['per_page']) ? (int)$post_data['per_page'] : 20;

        // Validate module if provided
        if (!empty($module) && !in_array($module, ['QSR', 'KOT'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid module. Must be QSR or KOT',
                'data' => null
            ]);
            return;
        }

        // Validate pagination
        if ($page < 1) $page = 1;
        if ($per_page < 1) $per_page = 20;
        if ($per_page > 50) $per_page = 50;

        $offset = ($page - 1) * $per_page;

        // Get pending orders
        $statuses = ['PENDING'];
        $orders = $this->orders_model->get_orders_by_status($store_id, $statuses, $module, $per_page, $offset);
        $total_count = $this->orders_model->count_orders_by_status($store_id, $statuses, $module);

        // Format orders
        $orders_data = [];
        foreach ($orders as $order) {
            $items = $this->orders_model->get_order_items($order->id);
            $orders_data[] = $this->format_order_for_list($order, $items);
        }

        // Pagination info
        $total_pages = ceil($total_count / $per_page);

        $this->logger->info('Pending orders fetched', [
            'store_id' => $store_id,
            'count' => count($orders_data)
        ], 'store_orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($orders_data) ? 'No pending orders' : 'Pending orders fetched successfully',
            'data' => [
                'orders' => $orders_data,
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
     * Get Confirmed Orders
     *
     * Returns list of confirmed/in-progress orders for the store.
     * Status: CONFIRMED, PREPARING, READY
     *
     * @api POST /api/v1/store/orders/confirmed
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param string module (optional) Filter by module: QSR, KOT (default: both)
     * @param int    page (optional) Page number (default: 1)
     * @param int    per_page (optional) Items per page (default: 20, max: 50)
     *
     * @return void Outputs JSON response
     *         - 200: List of confirmed orders
     *         - 401: Unauthorized
     */
    public function confirmed()
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

        $this->logger->info('Store Confirmed Orders API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_orders');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $module = isset($post_data['module']) ? strtoupper(trim($post_data['module'])) : null;
        $page = isset($post_data['page']) ? (int)$post_data['page'] : 1;
        $per_page = isset($post_data['per_page']) ? (int)$post_data['per_page'] : 20;

        // Validate module if provided
        if (!empty($module) && !in_array($module, ['QSR', 'KOT'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid module. Must be QSR or KOT',
                'data' => null
            ]);
            return;
        }

        // Validate pagination
        if ($page < 1) $page = 1;
        if ($per_page < 1) $per_page = 20;
        if ($per_page > 50) $per_page = 50;

        $offset = ($page - 1) * $per_page;

        // Get confirmed/in-progress orders
        $statuses = ['CONFIRMED', 'PREPARING', 'READY'];
        $orders = $this->orders_model->get_orders_by_status($store_id, $statuses, $module, $per_page, $offset);
        $total_count = $this->orders_model->count_orders_by_status($store_id, $statuses, $module);

        // Format orders
        $orders_data = [];
        foreach ($orders as $order) {
            $items = $this->orders_model->get_order_items($order->id);
            $orders_data[] = $this->format_order_for_list($order, $items);
        }

        // Pagination info
        $total_pages = ceil($total_count / $per_page);

        $this->logger->info('Confirmed orders fetched', [
            'store_id' => $store_id,
            'count' => count($orders_data)
        ], 'store_orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($orders_data) ? 'No confirmed orders' : 'Confirmed orders fetched successfully',
            'data' => [
                'orders' => $orders_data,
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
     * Get Completed Orders
     *
     * Returns list of completed orders for the store.
     * Status: COMPLETED, CANCELLED, REJECTED
     *
     * @api POST /api/v1/store/orders/completed
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param string module (optional) Filter by module: QSR, KOT (default: both)
     * @param int    page (optional) Page number (default: 1)
     * @param int    per_page (optional) Items per page (default: 20, max: 50)
     *
     * @return void Outputs JSON response
     *         - 200: List of completed orders
     *         - 401: Unauthorized
     */
    public function completed()
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

        $this->logger->info('Store Completed Orders API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_orders');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $module = isset($post_data['module']) ? strtoupper(trim($post_data['module'])) : null;
        $page = isset($post_data['page']) ? (int)$post_data['page'] : 1;
        $per_page = isset($post_data['per_page']) ? (int)$post_data['per_page'] : 20;

        // Validate module if provided
        if (!empty($module) && !in_array($module, ['QSR', 'KOT'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid module. Must be QSR or KOT',
                'data' => null
            ]);
            return;
        }

        // Validate pagination
        if ($page < 1) $page = 1;
        if ($per_page < 1) $per_page = 20;
        if ($per_page > 50) $per_page = 50;

        $offset = ($page - 1) * $per_page;

        // Get completed orders
        $statuses = ['COMPLETED', 'CANCELLED', 'REJECTED'];
        $orders = $this->orders_model->get_orders_by_status($store_id, $statuses, $module, $per_page, $offset);
        $total_count = $this->orders_model->count_orders_by_status($store_id, $statuses, $module);

        // Format orders
        $orders_data = [];
        foreach ($orders as $order) {
            $items = $this->orders_model->get_order_items($order->id);
            $orders_data[] = $this->format_order_for_list($order, $items);
        }

        // Pagination info
        $total_pages = ceil($total_count / $per_page);

        $this->logger->info('Completed orders fetched', [
            'store_id' => $store_id,
            'count' => count($orders_data)
        ], 'store_orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($orders_data) ? 'No completed orders' : 'Completed orders fetched successfully',
            'data' => [
                'orders' => $orders_data,
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
     * Get Order Details
     *
     * Returns full details of a specific order including items, payments, and customer info.
     *
     * @api POST /api/v1/store/orders/details
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param int order_id (required) Order ID
     *
     * @return void Outputs JSON response
     *         - 200: Order details
     *         - 400: Order not found
     *         - 401: Unauthorized
     */
    public function details()
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

        $this->logger->info('Order Details API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_orders');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $order_id = isset($post_data['order_id']) ? (int)$post_data['order_id'] : null;

        if (empty($order_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'order_id is required',
                'data' => null
            ]);
            return;
        }

        // Get order with details
        $order = $this->orders_model->get_order_with_details($order_id, $store_id);

        if (empty($order)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Get order items with product details
        $items = $this->orders_model->get_order_items_detailed($order_id);

        // Get order payments
        $payments = $this->orders_model->get_order_payments($order_id);

        // Format items for response
        $items_data = [];
        $item_number = 1;
        foreach ($items as $item) {
            $items_data[] = [
                'sl_no' => $item_number++,
                'product_id' => (int)$item->product_id,
                'product_name' => $item->product_name,
                'quantity' => (int)$item->quantity,
                'unit_price' => (float)$item->unit_price,
                'total_price' => (float)$item->total_amount
            ];
        }

        // Calculate payment summary
        $wallet_paid = 0;
        $company_subsidy = 0;
        $cash_paid = 0;
        $total_paid = 0;

        foreach ($payments as $payment) {
            $amount = (float)$payment->amount;
            switch ($payment->payment_type) {
                case 'WALLET_DEBIT':
                    $wallet_paid += $amount;
                    break;
                case 'COMPANY_SUBSIDY':
                    $company_subsidy += $amount;
                    break;
                case 'ONLINE_PAYMENT':
                    $cash_paid += $amount;
                    break;
            }
            if ($payment->status === 'SUCCESS' && !in_array($payment->payment_type, ['REFUND_CREDIT'])) {
                $total_paid += $amount;
            }
        }

        // Determine payment status
        $payment_status = 'UNPAID';
        $total_amount = (float)$order->total_amount;
        if ($total_paid >= $total_amount) {
            $payment_status = 'PAID';
        } elseif ($total_paid > 0) {
            $payment_status = 'PARTIAL';
        }

        // Format customer info — guest or employee
        $is_guest = !empty($order->is_guest_order);
        if ($is_guest) {
            $customer_name = $order->guest_name ?? 'Guest';
            $customer_phone = $order->guest_phone ?? null;
            $customer_email = null;
        } else {
            $customer_name = trim($order->employee_first_name . ' ' . ($order->employee_last_name ?? ''));
            $customer_phone = $order->employee_phone;
            $customer_email = $order->employee_email;
        }

        // Determine pickup type
        $pickup_type = $order->is_scheduled ? 'Scheduled' : 'Instant';

        // Build response
        $order_data = [
            'order_id' => (int)$order->id,
            'order_number' => $order->order_number,
            'module' => $order->module,
            'status' => $order->status,
            'is_guest_order' => $is_guest,
            'pickup_type' => $pickup_type,
            'is_scheduled' => (bool)$order->is_scheduled,
            'pickup_time' => $order->pickup_time ? date('H:i', strtotime($order->pickup_time)) : null,
            'pickup_code' => $order->pickup_code,
            'time_ago' => $this->time_ago($order->created_at),
            'created_at' => $order->created_at,
            'confirmed_at' => $order->confirmed_at,
            'ready_at' => $order->ready_at,
            'completed_at' => $order->completed_at,

            // Customer info
            'customer' => [
                'name' => $customer_name,
                'phone' => $customer_phone,
                'email' => $customer_email,
                'is_guest' => $is_guest
            ],

            // Delivery location (KOT orders)
            'delivery_location' => !empty($order->delivery_location_id) ? (function() use ($order) {
                $loc = $this->locations_model->get_by_id($order->delivery_location_id, $order->store_id);
                return $loc ? [
                    'id' => (int)$loc->id,
                    'name' => $loc->name,
                    'short_name' => $loc->short_name,
                    'floor' => $loc->floor,
                    'building' => $loc->building
                ] : null;
            })() : null,

            // Items
            'items' => $items_data,
            'total_items' => (int)$order->total_items,

            // Pricing
            'subtotal' => (float)$order->subtotal,
            'tax_amount' => (float)$order->tax_amount,
            'discount_amount' => (float)$order->discount_amount,
            'total_amount' => $total_amount,
            'is_tax_inclusive' => true, // GST inclusive

            // Payment info
            'payment_status' => $payment_status,
            'payment_summary' => [
                'wallet_paid' => $wallet_paid,
                'company_subsidy' => $company_subsidy,
                'cash_paid' => $cash_paid,
                'total_paid' => $total_paid,
                'balance_due' => max(0, $total_amount - $total_paid)
            ],

            // Notes
            'customer_note' => $order->customer_note,
            'staff_note' => $order->staff_note
        ];

        $this->logger->info('Order details fetched', [
            'order_id' => $order_id,
            'store_id' => $store_id
        ], 'store_orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Order details fetched successfully',
            'data' => $order_data
        ]);
    }

    /**
     * Accept Order
     *
     * Accepts a pending order (changes status from PENDING to CONFIRMED).
     * Sets ready_at based on preparation time.
     *
     * @api POST /api/v1/store/orders/accept
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param int order_id (required) Order ID to accept
     * @param int prep_time (required) Preparation time in minutes
     *
     * @return void Outputs JSON response
     *         - 200: Order accepted successfully
     *         - 400: Invalid order or already processed
     *         - 401: Unauthorized
     */
    public function accept()
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

        $this->logger->info('Accept Order API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_orders');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $order_id = isset($post_data['order_id']) ? (int)$post_data['order_id'] : null;
        $prep_time = isset($post_data['prep_time']) ? (int)$post_data['prep_time'] : null;

        if (empty($order_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'order_id is required',
                'data' => null
            ]);
            return;
        }

        if (empty($prep_time) || $prep_time < 1) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'prep_time is required (in minutes)',
                'data' => null
            ]);
            return;
        }

        // Cap prep_time to reasonable limit (max 120 minutes)
        if ($prep_time > 120) {
            $prep_time = 120;
        }

        // Get order and verify it belongs to this store
        $order = $this->orders_model->get_order_by_id($order_id, $store_id);

        if (empty($order)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Check if order is in PENDING status
        if ($order->status !== 'PENDING') {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order cannot be accepted. Current status: ' . $order->status,
                'data' => null
            ]);
            return;
        }

        // Calculate ready_at time
        $ready_at = date('Y-m-d H:i:s', strtotime("+{$prep_time} minutes"));

        // Update order status with ready_at and confirmed_by
        $updated = $this->orders_model->update_order_status($order_id, 'CONFIRMED', [
            'prep_time' => $prep_time,
            'ready_at' => $ready_at,
            'confirmed_by' => $staff_id
        ]);

        if (!$updated) {
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to accept order. Please try again.',
                'data' => null
            ]);
            return;
        }

        // Add status history
        $this->orders_model->add_status_history([
            'order_id' => $order_id,
            'from_status' => 'PENDING',
            'to_status' => 'CONFIRMED',
            'changed_by_type' => 'STORE_STAFF',
            'changed_by_id' => $staff_id,
            'note' => 'Order accepted. Ready in ' . $prep_time . ' minutes',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Send notification to employee (skip for guest orders)
        $order->prep_time = $prep_time;
        if (empty($order->is_guest_order) && !empty($order->employee_id)) {
            $this->notificationlib->orderConfirmed($order->employee_id, $order);
        }

        $this->logger->info('Order accepted', [
            'order_id' => $order_id,
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'prep_time' => $prep_time,
            'ready_at' => $ready_at
        ], 'store_orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Order accepted successfully',
            'data' => [
                'order_id' => $order_id,
                'order_number' => $order->order_number,
                'new_status' => 'CONFIRMED',
                'prep_time' => $prep_time,
                'ready_at' => $ready_at
            ]
        ]);
    }

    /**
     * Reject Order
     *
     * Rejects a pending order (changes status from PENDING to REJECTED).
     * Refunds wallet amount if applicable.
     *
     * @api POST /api/v1/store/orders/reject
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param int    order_id (required) Order ID to reject
     * @param string reason (optional) Reason for rejection
     *
     * @return void Outputs JSON response
     *         - 200: Order rejected successfully
     *         - 400: Invalid order or already processed
     *         - 401: Unauthorized
     */
    public function reject()
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

        $this->logger->info('Reject Order API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_orders');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $order_id = isset($post_data['order_id']) ? (int)$post_data['order_id'] : null;
        $reason = isset($post_data['reason']) ? trim($post_data['reason']) : null;

        if (empty($order_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'order_id is required',
                'data' => null
            ]);
            return;
        }

        // Get order and verify it belongs to this store
        $order = $this->orders_model->get_order_by_id($order_id, $store_id);

        if (empty($order)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Check if order is in PENDING status
        if ($order->status !== 'PENDING') {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order cannot be rejected. Current status: ' . $order->status,
                'data' => null
            ]);
            return;
        }

        // Start transaction for refund
        $this->db->trans_start();

        try {
            // Update order status with rejection details
            $this->orders_model->update_order_status($order_id, 'REJECTED', [
                'rejected_at' => date('Y-m-d H:i:s'),
                'rejected_by' => $staff_id,
                'rejection_reason' => $reason
            ]);

            // --- Refund handling ---
            $refund_amount = 0;
            $refund_transaction_id = null;
            $razorpay_refund_id = null;
            $is_guest = !empty($order->is_guest_order);

            if ($is_guest) {
                // === GUEST ORDER: Refund via Razorpay (no wallet) ===
                $online_payment = $this->orders_model->get_online_payment($order_id);

                if ($online_payment && !empty($online_payment->razorpay_payment_id) && $order->refund_status !== 'PROCESSED') {
                    $online_refund_amount = (float)$online_payment->amount;
                    $refund_status = 'FAILED';

                    if ($this->razorpaylib->init($order->company_id)) {
                        $refund_result = $this->razorpaylib->createRefund(
                            $online_payment->razorpay_payment_id,
                            $online_refund_amount,
                            ['order_number' => $order->order_number, 'reason' => $reason ?? 'Order rejected by store']
                        );

                        if ($refund_result['success']) {
                            $razorpay_refund_id = $refund_result['refund']['id'];
                            $refund_amount = $online_refund_amount;
                            $refund_status = 'PROCESSED';

                            // Record razorpay refund in order_payments
                            $this->orders_model->add_order_payment([
                                'order_id' => $order_id,
                                'payment_type' => 'REFUND_CREDIT',
                                'amount' => $online_refund_amount,
                                'razorpay_payment_id' => $online_payment->razorpay_payment_id,
                                'razorpay_order_id' => $online_payment->razorpay_order_id,
                                'status' => 'SUCCESS',
                                'note' => 'Razorpay refund for rejected guest order. Refund ID: ' . $razorpay_refund_id,
                                'created_at' => date('Y-m-d H:i:s')
                            ]);

                            // Update refund fields in orders table
                            $this->orders_model->update_order_refund($order_id, [
                                'refund_amount' => $refund_amount,
                                'refund_status' => 'PROCESSED',
                                'refunded_at' => date('Y-m-d H:i:s')
                            ]);

                            $this->logger->info('Razorpay refund processed for guest order', [
                                'order_id' => $order_id,
                                'razorpay_refund_id' => $razorpay_refund_id,
                                'amount' => $online_refund_amount
                            ], 'store_orders');
                        } else {
                            $this->logger->error('Razorpay refund failed for guest order', [
                                'order_id' => $order_id,
                                'payment_id' => $online_payment->razorpay_payment_id,
                                'error' => $refund_result['message']
                            ], 'store_orders');
                        }
                    }

                    // Log to refunds table (even if failed, for audit)
                    $this->orders_model->log_refund([
                        'order_id' => $order_id,
                        'order_number' => $order->order_number,
                        'store_id' => $store_id,
                        'company_id' => $order->company_id,
                        'employee_id' => null,
                        'is_guest_order' => 1,
                        'guest_name' => $order->guest_name,
                        'guest_phone' => $order->guest_phone,
                        'refund_method' => 'RAZORPAY',
                        'amount' => $online_refund_amount,
                        'reason' => $reason,
                        'razorpay_payment_id' => $online_payment->razorpay_payment_id,
                        'razorpay_refund_id' => $razorpay_refund_id,
                        'wallet_transaction_id' => null,
                        'status' => $refund_status,
                        'refunded_by' => $staff_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                // === EMPLOYEE ORDER: Refund what the employee actually paid (wallet + online) to wallet ===
                // orders.total_amount includes the company contribution on KOT/PREMEAL,
                // so the refund must come from the recorded payments instead
                $total_employee_refund = $this->orders_model->get_employee_paid_amount($order_id);
                if ($total_employee_refund <= 0) {
                    // Fallback for legacy orders without order_payments rows
                    $total_employee_refund = (float)($order->employee_contribution ?? 0);
                }

                $already_refunded = $order->refund_status === 'PROCESSED' || $this->orders_model->has_refund($order_id);
                if ($already_refunded) {
                    $this->logger->warning('Refund skipped - already recorded for this order', [
                        'order_id' => $order_id,
                        'refund_status' => $order->refund_status
                    ], 'store_orders');
                }

                if (!$already_refunded && $total_employee_refund > 0 && !empty($order->employee_id)) {
                    // Credit full amount back to employee wallet
                    $refund_transaction_id = $this->orders_model->insert_refund_transaction([
                        'transaction_uuid' => generate_uuid(),
                        'user_id' => $order->employee_id,
                        'order_id' => $order_id,
                        'amount' => $total_employee_refund,
                        'transaction_type' => 1,
                        'source' => 'ORDER_REFUND',
                        'transaction_label' => 'Refund for rejected order ' . $order->order_number,
                        'transaction_date' => date('Y-m-d')
                    ]);

                    $refund_amount = $total_employee_refund;

                    // Record refund in order_payments
                    $this->orders_model->add_order_payment([
                        'order_id' => $order_id,
                        'payment_type' => 'REFUND_CREDIT',
                        'amount' => $refund_amount,
                        'transaction_id' => $refund_transaction_id,
                        'status' => 'SUCCESS',
                        'note' => 'Wallet refund for rejected order',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    // Update refund fields in orders table
                    $this->orders_model->update_order_refund($order_id, [
                        'refund_amount' => $refund_amount,
                        'refund_status' => 'PROCESSED',
                        'refunded_at' => date('Y-m-d H:i:s'),
                        'refund_transaction_id' => $refund_transaction_id
                    ]);

                    // Log to refunds table
                    $this->orders_model->log_refund([
                        'order_id' => $order_id,
                        'order_number' => $order->order_number,
                        'store_id' => $store_id,
                        'company_id' => $order->company_id,
                        'employee_id' => $order->employee_id,
                        'is_guest_order' => 0,
                        'guest_name' => null,
                        'guest_phone' => null,
                        'refund_method' => 'WALLET',
                        'amount' => $refund_amount,
                        'reason' => $reason,
                        'razorpay_payment_id' => null,
                        'razorpay_refund_id' => null,
                        'wallet_transaction_id' => $refund_transaction_id,
                        'status' => 'PROCESSED',
                        'refunded_by' => $staff_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            // Restore stock
            $items = $this->orders_model->get_order_items($order_id);
            foreach ($items as $item) {
                $this->orders_model->restore_stock($store_id, $item->product_id, $item->quantity, [
                    'reference_id'      => $order_id,
                    'order_number'      => $order->order_number,
                    'performed_by_type' => 'STORE_STAFF',
                    'performed_by_id'   => $staff_id,
                    'note'              => 'Stock restored on order rejection by store staff'
                ]);
            }

            // Add status history
            $note = 'Order rejected by store staff';
            if (!empty($reason)) {
                $note .= ': ' . $reason;
            }

            $this->orders_model->add_status_history([
                'order_id' => $order_id,
                'from_status' => 'PENDING',
                'to_status' => 'REJECTED',
                'changed_by_type' => 'STORE_STAFF',
                'changed_by_id' => $staff_id,
                'note' => $note,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Send notification to employee (skip for guest orders)
            if (!$is_guest && !empty($order->employee_id)) {
                $this->notificationlib->orderRejected($order->employee_id, $order, $reason);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $this->logger->info('Order rejected', [
                'order_id' => $order_id,
                'staff_id' => $staff_id,
                'store_id' => $store_id,
                'is_guest' => $is_guest,
                'refund_amount' => $refund_amount,
                'razorpay_refund_id' => $razorpay_refund_id
            ], 'store_orders');

            $response_data = [
                'order_id' => $order_id,
                'order_number' => $order->order_number,
                'new_status' => 'REJECTED',
                'is_guest_order' => $is_guest,
                'total_refunded' => $refund_amount
            ];

            if ($is_guest) {
                $response_data['refund_method'] = 'RAZORPAY';
                $response_data['razorpay_refund_id'] = $razorpay_refund_id;
            } else {
                $response_data['refund_method'] = 'WALLET';
                $response_data['wallet_refunded'] = $refund_amount;
            }

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'Order rejected successfully',
                'data' => $response_data
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->logger->error('Order rejection failed', [
                'order_id' => $order_id,
                'error' => $e->getMessage()
            ], 'store_orders');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to reject order. Please try again.',
                'data' => null
            ]);
        }
    }

    /**
     * Cancel an Accepted Order
     *
     * Cancels an order that has already been accepted (status CONFIRMED,
     * PREPARING or READY). Unlike reject() — which only works on PENDING
     * orders — this is for situations discovered after acceptance
     * (out of stock, customer no-show, kitchen issue, etc.).
     *
     * Refund is optional: the staff explicitly chooses whether to refund
     * the customer (process_refund = 1) or cancel without refund (0).
     * Stock is always restored regardless of the refund choice.
     *
     * @api POST /api/v1/store/orders/cancel
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param int    order_id       (required) Order ID to cancel
     * @param string reason         (required) Reason for cancellation
     * @param int    process_refund (required) 1 = refund the customer, 0 = no refund
     *
     * @return void Outputs JSON response
     *         - 200: Order cancelled successfully
     *         - 400: Invalid order, wrong status or missing params
     *         - 401: Unauthorized
     */
    public function cancel()
    {
        if (!$this->check_auth()) {
            return;
        }

        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $store_id = $auth->store_id;
        $staff_id = $auth->staff_id;

        $this->logger->info('Cancel Order API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_orders');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $order_id = isset($post_data['order_id']) ? (int)$post_data['order_id'] : null;
        $reason = isset($post_data['reason']) ? trim($post_data['reason']) : null;
        $process_refund = isset($post_data['process_refund']) ? (int)$post_data['process_refund'] : null;

        if (empty($order_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'order_id is required',
                'data' => null
            ]);
            return;
        }

        if (empty($reason)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'A cancellation reason is required',
                'data' => null
            ]);
            return;
        }

        if ($process_refund === null || !in_array($process_refund, [0, 1], true)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'process_refund is required (1 to refund the customer, 0 for no refund)',
                'data' => null
            ]);
            return;
        }

        // Get order and verify it belongs to this store
        $order = $this->orders_model->get_order_by_id($order_id, $store_id);

        if (empty($order)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Only accepted (post-acceptance, pre-completion) orders can be cancelled here.
        // PENDING orders should use reject(); terminal orders cannot be cancelled.
        $allowed_statuses = ['CONFIRMED', 'PREPARING', 'READY'];
        if (!in_array($order->status, $allowed_statuses)) {
            $message = 'Order cannot be cancelled. Current status: ' . $order->status;
            if ($order->status === 'PENDING') {
                $message = 'Use the reject action for orders that are still pending.';
            }
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => $message,
                'data' => null
            ]);
            return;
        }

        $old_status = $order->status;
        $is_guest = !empty($order->is_guest_order);

        // Start transaction
        $this->db->trans_start();

        try {
            // Update order status to CANCELLED
            $this->orders_model->update_order_status($order_id, 'CANCELLED', [
                'cancelled_at' => date('Y-m-d H:i:s'),
                'cancelled_by' => $staff_id,
                'cancellation_reason' => $reason
            ]);

            // --- Refund handling (only if staff chose to refund) ---
            $refund_amount = 0;
            $refund_transaction_id = null;
            $razorpay_refund_id = null;
            $refund_done = false;

            if ($process_refund === 1) {
                if ($is_guest) {
                    // === GUEST ORDER: Refund via Razorpay ===
                    $online_payment = $this->orders_model->get_online_payment($order_id);

                    if ($online_payment && !empty($online_payment->razorpay_payment_id) && $order->refund_status !== 'PROCESSED') {
                        $online_refund_amount = (float)$online_payment->amount;
                        $refund_status = 'FAILED';

                        if ($this->razorpaylib->init($order->company_id)) {
                            $refund_result = $this->razorpaylib->createRefund(
                                $online_payment->razorpay_payment_id,
                                $online_refund_amount,
                                ['order_number' => $order->order_number, 'reason' => $reason]
                            );

                            if ($refund_result['success']) {
                                $razorpay_refund_id = $refund_result['refund']['id'];
                                $refund_amount = $online_refund_amount;
                                $refund_status = 'PROCESSED';
                                $refund_done = true;

                                $this->orders_model->add_order_payment([
                                    'order_id' => $order_id,
                                    'payment_type' => 'REFUND_CREDIT',
                                    'amount' => $online_refund_amount,
                                    'razorpay_payment_id' => $online_payment->razorpay_payment_id,
                                    'razorpay_order_id' => $online_payment->razorpay_order_id,
                                    'status' => 'SUCCESS',
                                    'note' => 'Razorpay refund for cancelled guest order. Refund ID: ' . $razorpay_refund_id,
                                    'created_at' => date('Y-m-d H:i:s')
                                ]);

                                $this->orders_model->update_order_refund($order_id, [
                                    'refund_amount' => $refund_amount,
                                    'refund_status' => 'PROCESSED',
                                    'refunded_at' => date('Y-m-d H:i:s')
                                ]);

                                $this->logger->info('Razorpay refund processed for cancelled guest order', [
                                    'order_id' => $order_id,
                                    'razorpay_refund_id' => $razorpay_refund_id,
                                    'amount' => $online_refund_amount
                                ], 'store_orders');
                            } else {
                                $this->logger->error('Razorpay refund failed for cancelled guest order', [
                                    'order_id' => $order_id,
                                    'error' => $refund_result['message']
                                ], 'store_orders');
                            }
                        }

                        // Log to refunds table (even if failed, for audit)
                        $this->orders_model->log_refund([
                            'order_id' => $order_id,
                            'order_number' => $order->order_number,
                            'store_id' => $store_id,
                            'company_id' => $order->company_id,
                            'employee_id' => null,
                            'is_guest_order' => 1,
                            'guest_name' => $order->guest_name,
                            'guest_phone' => $order->guest_phone,
                            'refund_method' => 'RAZORPAY',
                            'amount' => $online_refund_amount,
                            'reason' => $reason,
                            'razorpay_payment_id' => $online_payment->razorpay_payment_id,
                            'razorpay_refund_id' => $razorpay_refund_id,
                            'wallet_transaction_id' => null,
                            'status' => $refund_status,
                            'refunded_by' => $staff_id,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                } else {
                    // === EMPLOYEE ORDER: Refund what the employee actually paid (wallet + online) to wallet ===
                    // orders.total_amount includes the company contribution on KOT/PREMEAL,
                    // so the refund must come from the recorded payments instead
                    $total_employee_refund = $this->orders_model->get_employee_paid_amount($order_id);
                    if ($total_employee_refund <= 0) {
                        // Fallback for legacy orders without order_payments rows
                        $total_employee_refund = (float)($order->employee_contribution ?? 0);
                    }

                    $already_refunded = $order->refund_status === 'PROCESSED' || $this->orders_model->has_refund($order_id);
                    if ($already_refunded) {
                        $this->logger->warning('Refund skipped - already recorded for this order', [
                            'order_id' => $order_id,
                            'refund_status' => $order->refund_status
                        ], 'store_orders');
                    }

                    if (!$already_refunded && $total_employee_refund > 0 && !empty($order->employee_id)) {
                        $refund_transaction_id = $this->orders_model->insert_refund_transaction([
                            'transaction_uuid' => generate_uuid(),
                            'user_id' => $order->employee_id,
                            'order_id' => $order_id,
                            'amount' => $total_employee_refund,
                            'transaction_type' => 1,
                            'source' => 'ORDER_REFUND',
                            'transaction_label' => 'Refund for cancelled order ' . $order->order_number,
                            'transaction_date' => date('Y-m-d')
                        ]);

                        $refund_amount = $total_employee_refund;
                        $refund_done = true;

                        $this->orders_model->add_order_payment([
                            'order_id' => $order_id,
                            'payment_type' => 'REFUND_CREDIT',
                            'amount' => $refund_amount,
                            'transaction_id' => $refund_transaction_id,
                            'status' => 'SUCCESS',
                            'note' => 'Wallet refund for cancelled order',
                            'created_at' => date('Y-m-d H:i:s')
                        ]);

                        $this->orders_model->update_order_refund($order_id, [
                            'refund_amount' => $refund_amount,
                            'refund_status' => 'PROCESSED',
                            'refunded_at' => date('Y-m-d H:i:s'),
                            'refund_transaction_id' => $refund_transaction_id
                        ]);

                        $this->orders_model->log_refund([
                            'order_id' => $order_id,
                            'order_number' => $order->order_number,
                            'store_id' => $store_id,
                            'company_id' => $order->company_id,
                            'employee_id' => $order->employee_id,
                            'is_guest_order' => 0,
                            'guest_name' => null,
                            'guest_phone' => null,
                            'refund_method' => 'WALLET',
                            'amount' => $refund_amount,
                            'reason' => $reason,
                            'razorpay_payment_id' => null,
                            'razorpay_refund_id' => null,
                            'wallet_transaction_id' => $refund_transaction_id,
                            'status' => 'PROCESSED',
                            'refunded_by' => $staff_id,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }

            // Restore stock (always — cancelled items return to inventory)
            $items = $this->orders_model->get_order_items($order_id);
            foreach ($items as $item) {
                $this->orders_model->restore_stock($store_id, $item->product_id, $item->quantity, [
                    'reference_id'      => $order_id,
                    'order_number'      => $order->order_number,
                    'performed_by_type' => 'STORE_STAFF',
                    'performed_by_id'   => $staff_id,
                    'note'              => 'Stock restored on order cancellation by store staff'
                ]);
            }

            // Add status history
            $note = 'Order cancelled by store staff: ' . $reason;
            $note .= $process_refund === 1
                ? ' (refund ' . ($refund_done ? 'processed' : 'attempted')  . ')'
                : ' (no refund)';

            $this->orders_model->add_status_history([
                'order_id' => $order_id,
                'from_status' => $old_status,
                'to_status' => 'CANCELLED',
                'changed_by_type' => 'STORE_STAFF',
                'changed_by_id' => $staff_id,
                'note' => $note,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Send notification to employee (skip for guest orders)
            if (!$is_guest && !empty($order->employee_id)) {
                $this->notificationlib->orderCancelled($order->employee_id, $order);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $this->logger->info('Order cancelled by store', [
                'order_id' => $order_id,
                'staff_id' => $staff_id,
                'store_id' => $store_id,
                'from_status' => $old_status,
                'is_guest' => $is_guest,
                'process_refund' => $process_refund,
                'refund_amount' => $refund_amount
            ], 'store_orders');

            $response_data = [
                'order_id' => $order_id,
                'order_number' => $order->order_number,
                'new_status' => 'CANCELLED',
                'is_guest_order' => $is_guest,
                'refund_requested' => $process_refund === 1,
                'total_refunded' => $refund_amount
            ];

            if ($process_refund === 1) {
                $response_data['refund_method'] = $is_guest ? 'RAZORPAY' : 'WALLET';
                if ($is_guest) {
                    $response_data['razorpay_refund_id'] = $razorpay_refund_id;
                }
            }

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => $process_refund === 1
                    ? 'Order cancelled and customer refunded'
                    : 'Order cancelled without refund',
                'data' => $response_data
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->logger->error('Order cancellation failed', [
                'order_id' => $order_id,
                'error' => $e->getMessage()
            ], 'store_orders');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to cancel order. Please try again.',
                'data' => null
            ]);
        }
    }

    /**
     * Mark Order as Ready
     *
     * Marks a confirmed order as ready for pickup.
     * Changes status from CONFIRMED or PREPARING to READY.
     *
     * @api POST /api/v1/store/orders/mark_ready
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param int order_id (required) Order ID to mark as ready
     *
     * @return void Outputs JSON response
     *         - 200: Order marked as ready successfully
     *         - 400: Invalid order or wrong status
     *         - 401: Unauthorized
     */
    public function mark_ready()
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

        $this->logger->info('Mark Ready API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_orders');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $order_id = isset($post_data['order_id']) ? (int)$post_data['order_id'] : null;

        if (empty($order_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'order_id is required',
                'data' => null
            ]);
            return;
        }

        // Get order and verify it belongs to this store
        $order = $this->orders_model->get_order_by_id($order_id, $store_id);

        if (empty($order)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Check if order is in CONFIRMED or PREPARING status
        $allowed_statuses = ['CONFIRMED', 'PREPARING'];
        if (!in_array($order->status, $allowed_statuses)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order cannot be marked as ready. Current status: ' . $order->status,
                'data' => null
            ]);
            return;
        }

        $old_status = $order->status;
        $ready_at = date('Y-m-d H:i:s');

        // Update order status to READY
        $updated = $this->orders_model->update_order_status($order_id, 'READY', [
            'ready_at' => $ready_at
        ]);

        if (!$updated) {
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to mark order as ready. Please try again.',
                'data' => null
            ]);
            return;
        }

        // Add status history
        $this->orders_model->add_status_history([
            'order_id' => $order_id,
            'from_status' => $old_status,
            'to_status' => 'READY',
            'changed_by_type' => 'STORE_STAFF',
            'changed_by_id' => $staff_id,
            'note' => 'Order marked as ready for pickup',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Send notification to employee (skip for guest orders)
        if (empty($order->is_guest_order) && !empty($order->employee_id)) {
            $this->notificationlib->orderReady($order->employee_id, $order);
        }

        $this->logger->info('Order marked as ready', [
            'order_id' => $order_id,
            'order_number' => $order->order_number,
            'staff_id' => $staff_id,
            'store_id' => $store_id
        ], 'store_orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Order marked as ready successfully',
            'data' => [
                'order_id' => $order_id,
                'order_number' => $order->order_number,
                'new_status' => 'READY',
                'ready_at' => $ready_at
            ]
        ]);
    }

    /**
     * Confirm Delivery / Pickup
     *
     * Confirms order delivery/pickup by verifying the pickup code.
     * Changes status from READY to COMPLETED.
     * Accepts either order_id or order_number for lookup.
     *
     * @api POST /api/v1/store/orders/confirm_delivery
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @param int    order_id (optional) Order ID (required if order_number not provided)
     * @param string order_number (optional) Order Number (required if order_id not provided)
     * @param string pickup_code (required) The pickup verification code
     *
     * @return void Outputs JSON response
     *         - 200: Delivery confirmed successfully
     *         - 400: Invalid order, wrong pin, or order not ready
     *         - 401: Unauthorized
     */
    public function confirm_delivery()
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

        $this->logger->info('Confirm Delivery API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_orders');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $order_id = isset($post_data['order_id']) ? (int)$post_data['order_id'] : null;
        $order_number = isset($post_data['order_number']) ? trim($post_data['order_number']) : null;
        $pickup_code = isset($post_data['pickup_code']) ? strtoupper(trim($post_data['pickup_code'])) : null;
        $rfid_card_number = isset($post_data['rfid_card_number']) ? trim($post_data['rfid_card_number']) : null;

        // Validate - need either order_id or order_number
        if (empty($order_id) && empty($order_number)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'order_id or order_number is required',
                'data' => null
            ]);
            return;
        }

        // Validate verification - need either pickup_code OR rfid_card_number
        if (empty($pickup_code) && empty($rfid_card_number)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'pickup_code or rfid_card_number is required',
                'data' => null
            ]);
            return;
        }

        // Find order - by order_id or order_number
        $order = null;
        if (!empty($order_id)) {
            $order = $this->orders_model->get_order_by_id($order_id, $store_id);
        } elseif (!empty($order_number)) {
            $order = $this->orders_model->get_order_by_number($order_number, $store_id);
        }

        if (empty($order)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Verify identity - pickup_code takes priority, fall back to RFID
        $verification_method = null;
        if (!empty($pickup_code)) {
            if (strtoupper($order->pickup_code) !== $pickup_code) {
                $this->logger->warning('Invalid pickup code attempt', [
                    'order_id' => $order->id,
                    'store_id' => $store_id,
                    'provided_code' => $pickup_code
                ], 'store_orders');

                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Invalid pickup code',
                    'data' => null
                ]);
                return;
            }
            $verification_method = 'PICKUP_CODE';
        } else {
            // RFID verification - guest orders cannot be verified by RFID
            if (!empty($order->is_guest_order) || empty($order->employee_id)) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'RFID verification is not allowed for guest orders. Use pickup_code.',
                    'data' => null
                ]);
                return;
            }

            // Lookup employee by RFID card number, scoped to the order's company
            $employee = $this->common->getdatabytable('employees', [
                'rfid_card_number' => $rfid_card_number,
                'company_id' => $order->company_id,
                'is_active' => 1
            ]);

            if (empty($employee)) {
                $this->logger->warning('Invalid RFID card attempt', [
                    'order_id' => $order->id,
                    'store_id' => $store_id,
                    'provided_rfid' => $rfid_card_number
                ], 'store_orders');

                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Invalid RFID card number',
                    'data' => null
                ]);
                return;
            }

            // Card must belong to the same employee who placed the order
            if ((int)$employee->id !== (int)$order->employee_id) {
                $this->logger->warning('RFID card / order employee mismatch', [
                    'order_id' => $order->id,
                    'store_id' => $store_id,
                    'order_employee_id' => $order->employee_id,
                    'rfid_employee_id' => $employee->id
                ], 'store_orders');

                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'RFID card does not match the order owner',
                    'data' => null
                ]);
                return;
            }

            $verification_method = 'RFID';
        }

        // Check if order is in READY or CONFIRMED status (allow both for flexibility)
        $allowed_statuses = ['READY', 'CONFIRMED', 'PREPARING'];
        if (!in_array($order->status, $allowed_statuses)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order cannot be marked as delivered. Current status: ' . $order->status,
                'data' => null
            ]);
            return;
        }

        $old_status = $order->status;

        // Update order status to COMPLETED
        $updated = $this->orders_model->update_order_status($order->id, 'COMPLETED', [
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        if (!$updated) {
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to confirm delivery. Please try again.',
                'data' => null
            ]);
            return;
        }

        // Add status history
        $note = $verification_method === 'RFID'
            ? 'Order delivered/picked up. Verified with RFID card.'
            : 'Order delivered/picked up. Verified with pickup code.';

        $this->orders_model->add_status_history([
            'order_id' => $order->id,
            'from_status' => $old_status,
            'to_status' => 'COMPLETED',
            'changed_by_type' => 'STORE_STAFF',
            'changed_by_id' => $staff_id,
            'note' => $note,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Send notification to employee (skip for guest orders)
        if (empty($order->is_guest_order) && !empty($order->employee_id)) {
            $this->notificationlib->orderCompleted($order->employee_id, $order);
        }

        $this->logger->info('Order delivery confirmed', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'verification_method' => $verification_method
        ], 'store_orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Delivery confirmed successfully',
            'data' => [
                'order_id' => (int)$order->id,
                'order_number' => $order->order_number,
                'new_status' => 'COMPLETED',
                'completed_at' => date('Y-m-d H:i:s'),
                'verification_method' => $verification_method
            ]
        ]);
    }

    /**
     * Auto-detect PREMEAL meal type from current server time
     *
     * 04:00 - 11:00 → BREAKFAST
     * 11:00 - 16:00 → LUNCH
     * 16:00 - 23:59 → DINNER
     * 00:00 - 04:00 → DINNER (late-night pickups belong to previous evening's dinner)
     *
     * @return string Detected meal type
     */
    private function detect_meal_type()
    {
        $hour = (int)date('H');
        if ($hour >= 4 && $hour < 11)  return 'BREAKFAST';
        if ($hour >= 11 && $hour < 16) return 'LUNCH';
        return 'DINNER';
    }

    /**
     * Lookup orders by RFID card tap
     *
     * Used by the store app's RFID-tap flow. Resolves the employee from the
     * scanned card and returns their eligible orders at this store.
     *
     *  QSR / KOT  → any active order at this store (status in CONFIRMED, PREPARING, READY)
     *  PREMEAL    → today's order matching the current meal_type (or override)
     *
     * Does NOT mutate any order. The staff app then presents the matched order(s)
     * for selection and calls confirm_delivery to actually complete the pickup.
     *
     * @api POST /api/v1/store/orders/lookup_by_rfid
     *
     * @header Auth          Static API key
     * @header Authorization Bearer JWT (store_staff role)
     *
     * @param string rfid_card_number (required) RFID card number read from HID reader
     * @param string meal_type        (optional) PREMEAL meal_type override (BREAKFAST|LUNCH|DINNER)
     *
     * @return void Outputs JSON response
     *         - 200: One or more orders matched
     *         - 400: Missing rfid_card_number
     *         - 401: Unauthorized
     *         - 404: Card not recognized OR no matching orders
     */
    public function lookup_by_rfid()
    {
        if (!$this->check_auth()) {
            return;
        }

        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $store_id = $auth->store_id;
        $staff_id = $auth->staff_id;

        // Accept input from POST form-data, query string, JSON body, or raw form-encoded body
        $post_data = $this->input->post(null, true) ?: [];

        // Last-resort fallback: read raw input and try both JSON + form-encoded parsing
        if (empty($post_data)) {
            $raw = @file_get_contents('php://input');
            if (!empty($raw)) {
                $json = json_decode($raw, true);
                if (is_array($json)) {
                    $post_data = $json;
                } else {
                    parse_str($raw, $parsed);
                    if (!empty($parsed) && is_array($parsed)) {
                        $post_data = $parsed;
                    }
                }
            }
        }

        // Also merge $_POST as a fallback (in case CI's parser missed it)
        if (empty($post_data) && !empty($_POST)) {
            $post_data = $_POST;
        }

        $rfid_card_number = null;
        if (!empty($post_data['rfid_card_number'])) {
            $rfid_card_number = trim($post_data['rfid_card_number']);
        } elseif (!empty($this->input->get('rfid_card_number', true))) {
            $rfid_card_number = trim($this->input->get('rfid_card_number', true));
        }

        $meal_type_override = null;
        if (!empty($post_data['meal_type'])) {
            $meal_type_override = strtoupper(trim($post_data['meal_type']));
        } elseif (!empty($this->input->get('meal_type', true))) {
            $meal_type_override = strtoupper(trim($this->input->get('meal_type', true)));
        }

        $this->logger->info('Lookup by RFID API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'rfid_present' => !empty($rfid_card_number),
            'meal_type_override' => $meal_type_override,
            'content_type' => $this->input->server('CONTENT_TYPE'),
            'content_length' => $this->input->server('CONTENT_LENGTH'),
            'post_keys' => array_keys($post_data),
            'raw_body_length' => strlen(@file_get_contents('php://input')),
            'ip' => $this->input->ip_address()
        ], 'store_orders');

        if (empty($rfid_card_number)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'rfid_card_number is required. Send it as form-data (Content-Type: application/x-www-form-urlencoded) or JSON body.',
                'data' => null
            ]);
            return;
        }

        // Validate meal_type override if provided
        if (!empty($meal_type_override) && !in_array($meal_type_override, ['BREAKFAST', 'LUNCH', 'DINNER'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid meal_type. Must be BREAKFAST, LUNCH or DINNER',
                'data' => null
            ]);
            return;
        }

        // Resolve store (need store_type + company_id)
        $store = $this->common->getdatabytable('stores', [
            'id' => $store_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($store)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Store not found',
                'data' => null
            ]);
            return;
        }

        $store_type = $store->store_type;

        if (!in_array($store_type, ['QSR', 'KOT', 'PREMEAL'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'RFID lookup not supported for this store type',
                'data' => null
            ]);
            return;
        }

        // Resolve employee by RFID card (scoped to store's company)
        $employee = $this->common->getdatabytable('employees', [
            'rfid_card_number' => $rfid_card_number,
            'company_id' => $store->company_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($employee)) {
            $this->logger->warning('Unknown RFID card tapped at store', [
                'store_id' => $store_id,
                'provided_rfid' => $rfid_card_number
            ], 'store_orders');

            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Card not recognized for this store',
                'data' => null
            ]);
            return;
        }

        // Fetch department name (for the employee block in response)
        $department = null;
        if (!empty($employee->department_id)) {
            $department = $this->common->getdatabytable('company_departments', [
                'id' => $employee->department_id
            ]);
        }

        $employee_block = [
            'id' => (int)$employee->id,
            'employee_code' => $employee->employee_code,
            'name' => trim($employee->first_name . ' ' . ($employee->last_name ?? '')),
            'phone' => $employee->phone,
            'company_id' => (int)$employee->company_id,
            'department' => $department ? $department->name : null
        ];

        // Resolve filters based on store type
        $scheduled_date = null;
        $meal_type = null;
        $auto_detected_meal_type = null;

        if ($store_type === 'PREMEAL') {
            $scheduled_date = date('Y-m-d');
            if (!empty($meal_type_override)) {
                $meal_type = $meal_type_override;
            } else {
                $meal_type = $this->detect_meal_type();
                $auto_detected_meal_type = $meal_type;
            }
        }

        // Fetch eligible orders
        $orders = $this->orders_model->get_orders_for_pickup(
            $store_id,
            $employee->id,
            $store_type,
            $scheduled_date,
            $meal_type
        );

        if (empty($orders)) {
            $message = $store_type === 'PREMEAL'
                ? 'No pending ' . strtolower($meal_type) . ' orders for today'
                : 'No orders ready for pickup for this card';

            $this->logger->info('RFID lookup matched employee but no orders', [
                'store_id' => $store_id,
                'employee_id' => $employee->id,
                'store_type' => $store_type,
                'meal_type' => $meal_type
            ], 'store_orders');

            $this->output([
                'status' => 404,
                'success' => false,
                'message' => $message,
                'data' => [
                    'employee' => $employee_block,
                    'orders' => [],
                    'match_type' => 'NONE',
                    'auto_detected_meal_type' => $auto_detected_meal_type
                ]
            ]);
            return;
        }

        // Format orders for response
        $orders_data = [];
        foreach ($orders as $order) {
            $items = $this->orders_model->get_order_items($order->id);

            $delivery_location = null;
            if ($store_type === 'KOT' && !empty($order->delivery_location_id)) {
                $delivery_location = [
                    'id' => (int)$order->delivery_location_id,
                    'name' => $order->delivery_location_name,
                    'short_name' => $order->delivery_location_short_name,
                    'floor' => $order->delivery_location_floor,
                    'building' => $order->delivery_location_building
                ];
            }

            $orders_data[] = [
                'order_id' => (int)$order->id,
                'order_number' => $order->order_number,
                'module' => $order->module,
                'status' => $order->status,
                'pickup_code' => $order->pickup_code,
                'scheduled_date' => $order->scheduled_date,
                'meal_type' => $order->meal_type,
                'pickup_time' => $order->pickup_time ? date('H:i', strtotime($order->pickup_time)) : null,
                'total_amount' => (float)$order->total_amount,
                'formatted_amount' => '₹' . number_format($order->total_amount, 2),
                'items_count' => count($items),
                'items_summary' => $this->format_items_summary($items),
                'delivery_location' => $delivery_location,
                'created_at' => $order->created_at,
                'ready_at' => $order->ready_at ?? null,
                'time_ago' => $this->time_ago($order->created_at)
            ];
        }

        $match_type = count($orders_data) === 1 ? 'SINGLE' : 'MULTIPLE';

        $this->logger->info('RFID lookup returned orders', [
            'store_id' => $store_id,
            'employee_id' => $employee->id,
            'store_type' => $store_type,
            'match_type' => $match_type,
            'count' => count($orders_data)
        ], 'store_orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => count($orders_data) === 1 ? 'Order ready for pickup' : 'Multiple orders found — please select one',
            'data' => [
                'employee' => $employee_block,
                'orders' => $orders_data,
                'match_type' => $match_type,
                'auto_detected_meal_type' => $auto_detected_meal_type
            ]
        ]);
    }
}
