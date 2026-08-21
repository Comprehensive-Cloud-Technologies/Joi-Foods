<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Store PREMEAL Orders Controller
 *
 * Handles PREMEAL order management for store staff.
 * Allows viewing, approving, and rejecting PREMEAL bookings.
 *
 * @category  Controllers
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-09
 */
class Premeal extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('StorePremeal_model', 'premeal_model');
        $this->load->library('NotificationLib');
        $this->tokenHandler = new TokenHandler();

        // Load Monolog library for logging
        $this->load->library('monolog');
        $this->logger = new Monolog();
    }

    /**
     * Output JSON response
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

        if (isset($decoded->exp) && $decoded->exp < time()) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Token has expired',
                'data' => null
            ]);
            return false;
        }

        if (!isset($decoded->role) || $decoded->role !== 'store_staff') {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Access denied. Invalid role',
                'data' => null
            ]);
            return false;
        }

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
     * Get Pending PREMEAL Orders
     *
     * Returns list of pending PREMEAL bookings (primary orders only).
     * Each booking shows date range (start to end dates).
     *
     * POST /api/v1/store/premeal/pending
     *
     * @return void JSON response with pending orders
     */
    public function pending()
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

        $this->logger->info('Store PREMEAL Pending Orders API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_premeal');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $page = isset($post_data['page']) ? (int)$post_data['page'] : 1;
        $per_page = isset($post_data['per_page']) ? (int)$post_data['per_page'] : 20;

        if ($page < 1) $page = 1;
        if ($per_page < 1) $per_page = 20;
        if ($per_page > 50) $per_page = 50;

        $offset = ($page - 1) * $per_page;

        // Get pending orders
        $orders = $this->premeal_model->get_pending_orders($store_id, $per_page, $offset);
        $total_count = $this->premeal_model->count_pending_orders($store_id);

        // Format orders
        $orders_data = [];
        foreach ($orders as $order) {
            // Get date range for this booking
            $date_range = $this->premeal_model->get_order_date_range($order->id);

            // Get order items for display
            $items = $this->premeal_model->get_order_items($order->id);
            $items_summary = [];
            foreach ($items as $item) {
                $items_summary[] = $item->product_name . ' x' . $item->quantity;
            }

            $customer_name = trim($order->employee_first_name . ' ' . ($order->employee_last_name ?? ''));

            $orders_data[] = [
                'order_id' => (int)$order->id,
                'order_number' => $order->order_number,
                'customer' => [
                    'name' => $customer_name,
                    'phone' => $order->employee_phone,
                    'company' => $order->company_name
                ],
                'meal_type' => $order->meal_type,
                'schedule' => [
                    'start_date' => $date_range->start_date,
                    'end_date' => $date_range->end_date,
                    'total_days' => (int)$date_range->total_days,
                    'formatted_range' => date('d M', strtotime($date_range->start_date)) . ' - ' . date('d M Y', strtotime($date_range->end_date))
                ],
                'items_summary' => implode(', ', $items_summary),
                'total_amount' => (float)$order->total_amount * (int)$date_range->total_days,
                'per_day_amount' => (float)$order->total_amount,
                'formatted_total' => '₹' . number_format($order->total_amount * $date_range->total_days, 2),
                'created_at' => $order->created_at,
                'time_ago' => $this->time_ago($order->created_at)
            ];
        }

        $total_pages = $total_count > 0 ? ceil($total_count / $per_page) : 0;

        $this->logger->info('PREMEAL pending orders fetched', [
            'store_id' => $store_id,
            'count' => count($orders_data)
        ], 'store_premeal');

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
     * Get PREMEAL Order Details
     *
     * Returns full details of a PREMEAL booking including all scheduled dates.
     *
     * POST /api/v1/store/premeal/details
     *
     * @param int order_id (required) Primary order ID
     *
     * @return void JSON response with order details
     */
    public function details()
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

        $this->logger->info('Store PREMEAL Order Details API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_premeal');

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

        // Get primary order
        $order = $this->premeal_model->get_order_by_id($order_id, $store_id);

        if (empty($order)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Ensure it's a primary order
        if ($order->is_primary_order != 1) {
            // Get the primary order instead
            $order = $this->premeal_model->get_order_by_id($order->parent_order_id, $store_id);
            if (empty($order)) {
                $this->output([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Primary order not found',
                    'data' => null
                ]);
                return;
            }
        }

        // Get all orders in this booking (primary + children)
        $all_orders = $this->premeal_model->get_all_booking_orders($order->id);

        // Get items from primary order
        $items = $this->premeal_model->get_order_items($order->id);

        // Format items
        $items_data = [];
        foreach ($items as $item) {
            $items_data[] = [
                'product_id' => (int)$item->product_id,
                'product_name' => $item->product_name,
                'quantity' => (int)$item->quantity,
                'unit_price' => (float)$item->unit_price,
                'total_price' => (float)$item->total_amount,
                'thumbnail' => $item->thumbnail ? base_url($item->thumbnail) : null,
                'is_vegetarian' => (bool)$item->is_vegetarian
            ];
        }

        // Format scheduled orders
        $scheduled_orders = [];
        $total_booking_amount = 0;
        $total_company_contribution = 0;
        $total_employee_contribution = 0;

        foreach ($all_orders as $sub_order) {
            $total_booking_amount += (float)$sub_order->total_amount;
            $total_company_contribution += (float)$sub_order->company_contribution;
            $total_employee_contribution += (float)$sub_order->employee_contribution;

            $scheduled_orders[] = [
                'order_id' => (int)$sub_order->id,
                'order_number' => $sub_order->order_number,
                'scheduled_date' => $sub_order->scheduled_date,
                'formatted_date' => date('d M Y', strtotime($sub_order->scheduled_date)),
                'day_name' => date('l', strtotime($sub_order->scheduled_date)),
                'pickup_time' => $sub_order->pickup_time ? date('H:i', strtotime($sub_order->pickup_time)) : null,
                'pickup_code' => $sub_order->pickup_code,
                'status' => $sub_order->status,
                'total_amount' => (float)$sub_order->total_amount,
                'company_contribution' => (float)$sub_order->company_contribution,
                'employee_contribution' => (float)$sub_order->employee_contribution,
                'is_primary' => (bool)$sub_order->is_primary_order
            ];
        }

        $customer_name = trim($order->employee_first_name . ' ' . ($order->employee_last_name ?? ''));

        // Get date range
        $date_range = $this->premeal_model->get_order_date_range($order->id);

        $order_data = [
            'order_id' => (int)$order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'meal_type' => $order->meal_type,
            'customer' => [
                'name' => $customer_name,
                'phone' => $order->employee_phone,
                'email' => $order->employee_email,
                'company' => $order->company_name
            ],
            'schedule' => [
                'start_date' => $date_range->start_date,
                'end_date' => $date_range->end_date,
                'total_days' => (int)$date_range->total_days,
                'formatted_range' => date('d M', strtotime($date_range->start_date)) . ' - ' . date('d M Y', strtotime($date_range->end_date))
            ],
            'items' => $items_data,
            'pricing' => [
                'per_day_amount' => (float)$order->total_amount,
                'total_amount' => round($total_booking_amount, 2),
                'company_contribution' => round($total_company_contribution, 2),
                'employee_contribution' => round($total_employee_contribution, 2),
                'formatted_total' => '₹' . number_format($total_booking_amount, 2)
            ],
            'payment' => [
                'method' => $order->payment_method,
                'status' => $order->payment_status
            ],
            'scheduled_orders' => $scheduled_orders,
            'created_at' => $order->created_at,
            'formatted_date' => date('d M Y, h:i A', strtotime($order->created_at))
        ];

        $this->logger->info('PREMEAL order details fetched', [
            'order_id' => $order->id,
            'store_id' => $store_id
        ], 'store_premeal');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Order details fetched successfully',
            'data' => $order_data
        ]);
    }

    /**
     * Approve PREMEAL Order
     *
     * Approves a pending PREMEAL booking. All scheduled orders get approved.
     *
     * POST /api/v1/store/premeal/approve
     *
     * @param int order_id (required) Primary order ID
     *
     * @return void JSON response
     */
    public function approve()
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

        $this->logger->info('Store PREMEAL Approve Order API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_premeal');

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

        // Get order
        $order = $this->premeal_model->get_order_by_id($order_id, $store_id);

        if (empty($order)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Ensure it's a primary order
        if ($order->is_primary_order != 1) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Only primary orders can be approved. Please provide the main booking order ID.',
                'data' => null
            ]);
            return;
        }

        // Check status
        if ($order->status !== 'PENDING') {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order cannot be approved. Current status: ' . $order->status,
                'data' => null
            ]);
            return;
        }

        // Only the days still PENDING get confirmed — days the customer
        // already cancelled must NOT be resurrected.
        $pending_orders = $this->premeal_model->get_booking_orders_by_status($order->id, 'PENDING');

        // Update only the still-pending orders to CONFIRMED
        $this->premeal_model->update_all_orders_status($order->id, 'CONFIRMED', [
            'confirmed_by' => $staff_id
        ], 'PENDING');

        // Add status history for the confirmed orders only
        foreach ($pending_orders as $sub_order) {
            $this->premeal_model->add_status_history([
                'order_id' => $sub_order->id,
                'from_status' => 'PENDING',
                'to_status' => 'CONFIRMED',
                'changed_by_type' => 'STORE_STAFF',
                'changed_by_id' => $staff_id,
                'note' => 'PREMEAL booking approved',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $approved_count = count($pending_orders);

        // Send notification to employee (once for the primary order)
        $this->notificationlib->orderConfirmed($order->employee_id, $order);

        $date_range = $this->premeal_model->get_order_date_range($order->id);

        $this->logger->info('PREMEAL order approved', [
            'order_id' => $order->id,
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'total_orders' => $approved_count
        ], 'store_premeal');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Order approved successfully. ' . $approved_count . ' scheduled meal(s) confirmed.',
            'data' => [
                'order_id' => (int)$order->id,
                'order_number' => $order->order_number,
                'new_status' => 'CONFIRMED',
                'total_meals_approved' => $approved_count,
                'schedule' => [
                    'start_date' => $date_range->start_date,
                    'end_date' => $date_range->end_date
                ]
            ]
        ]);
    }

    /**
     * Reject PREMEAL Order
     *
     * Rejects a pending PREMEAL booking. All scheduled orders get rejected.
     * Employee contribution is refunded to wallet.
     *
     * POST /api/v1/store/premeal/reject
     *
     * @param int    order_id (required) Primary order ID
     * @param string reason (optional) Rejection reason
     *
     * @return void JSON response
     */
    public function reject()
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

        $this->logger->info('Store PREMEAL Reject Order API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_premeal');

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

        // Get order
        $order = $this->premeal_model->get_order_by_id($order_id, $store_id);

        if (empty($order)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Ensure it's a primary order
        if ($order->is_primary_order != 1) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Only primary orders can be rejected. Please provide the main booking order ID.',
                'data' => null
            ]);
            return;
        }

        // Check status
        if ($order->status !== 'PENDING') {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order cannot be rejected. Current status: ' . $order->status,
                'data' => null
            ]);
            return;
        }

        // Start transaction
        $this->db->trans_start();

        try {
            // Only the days still PENDING get rejected. Days the customer
            // already cancelled were refunded separately — don't touch them.
            $pending_orders = $this->premeal_model->get_booking_orders_by_status($order->id, 'PENDING');

            // Calculate total refund (employee contribution of PENDING orders only)
            $total_refund = $this->premeal_model->get_total_employee_contribution($order->id, 'PENDING');

            // Update only still-pending orders to REJECTED
            $this->premeal_model->update_all_orders_status($order->id, 'REJECTED', [
                'rejected_by' => $staff_id,
                'rejected_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason
            ], 'PENDING');

            // Process refund if there's amount to refund
            $refund_transaction_id = null;
            if ($total_refund > 0) {
                // Add refund transaction
                $this->db->insert('transaction', [
                    'transaction_uuid' => generate_uuid(),
                    'user_id' => $order->employee_id,
                    'order_id' => $order->id,
                    'amount' => $total_refund,
                    'transaction_type' => 1, // Credit to wallet
                    'transaction_label' => 'Refund for rejected PREMEAL booking ' . $order->order_number,
                    'transaction_date' => date('Y-m-d'),
                    'transaction_time' => date('Y-m-d H:i:s')
                ]);
                $refund_transaction_id = $this->db->insert_id();

                // Update refund status for the rejected orders only
                foreach ($pending_orders as $sub_order) {
                    $this->db->update('orders', [
                        'refund_amount' => $sub_order->employee_contribution,
                        'refund_status' => 'PROCESSED',
                        'refunded_at' => date('Y-m-d H:i:s'),
                        'refund_transaction_id' => $refund_transaction_id
                    ], ['id' => $sub_order->id]);
                }
            }

            // Add status history for the rejected orders only
            $note = 'PREMEAL booking rejected by store staff';
            if (!empty($reason)) {
                $note .= ': ' . $reason;
            }

            foreach ($pending_orders as $sub_order) {
                $this->premeal_model->add_status_history([
                    'order_id' => $sub_order->id,
                    'from_status' => 'PENDING',
                    'to_status' => 'REJECTED',
                    'changed_by_type' => 'STORE_STAFF',
                    'changed_by_id' => $staff_id,
                    'note' => $note,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Send notification to employee (once for the primary order)
            $this->notificationlib->orderRejected($order->employee_id, $order, $reason);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $rejected_count = count($pending_orders);

            $this->logger->info('PREMEAL order rejected', [
                'order_id' => $order->id,
                'staff_id' => $staff_id,
                'store_id' => $store_id,
                'total_orders' => $rejected_count,
                'refund_amount' => $total_refund
            ], 'store_premeal');

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'Order rejected successfully. Refund of ₹' . number_format($total_refund, 2) . ' has been credited to customer wallet.',
                'data' => [
                    'order_id' => (int)$order->id,
                    'order_number' => $order->order_number,
                    'new_status' => 'REJECTED',
                    'total_meals_rejected' => $rejected_count,
                    'refund_amount' => round($total_refund, 2),
                    'formatted_refund' => '₹' . number_format($total_refund, 2)
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->logger->error('PREMEAL order rejection failed', [
                'order_id' => $order_id,
                'error' => $e->getMessage()
            ], 'store_premeal');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to reject order. Please try again.',
                'data' => null
            ]);
        }
    }

    /**
     * Get Today's Orders (by date and meal type)
     *
     * Returns all orders for a specific date and meal type.
     * Used for kitchen display to see what needs to be prepared.
     *
     * POST /api/v1/store/premeal/today
     *
     * @param string date (required) Date in Y-m-d format
     * @param string meal_type (required) BREAKFAST, LUNCH, or DINNER
     *
     * @return void JSON response with orders for the day
     */
    public function today()
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

        $this->logger->info('Store PREMEAL Today Orders API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_premeal');

        $post_data = $this->input->post(null, true);
        $date = isset($post_data['date']) ? trim($post_data['date']) : date('Y-m-d');
        $meal_type = isset($post_data['meal_type']) ? strtoupper(trim($post_data['meal_type'])) : null;

        // Validate date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid date format. Use Y-m-d (e.g., 2026-01-09)',
                'data' => null
            ]);
            return;
        }

        // Validate meal_type
        if (empty($meal_type) || !in_array($meal_type, ['BREAKFAST', 'LUNCH', 'DINNER'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'meal_type is required. Must be BREAKFAST, LUNCH, or DINNER',
                'data' => null
            ]);
            return;
        }

        $page = isset($post_data['page']) ? (int)$post_data['page'] : 1;
        $per_page = isset($post_data['per_page']) ? (int)$post_data['per_page'] : 50;

        if ($page < 1) $page = 1;
        if ($per_page < 1) $per_page = 50;
        if ($per_page > 100) $per_page = 100;

        $offset = ($page - 1) * $per_page;

        // Get orders for the date (only CONFIRMED and active statuses)
        $statuses = ['CONFIRMED', 'PREPARING', 'READY', 'COMPLETED'];
        $orders = $this->premeal_model->get_orders_by_date($store_id, $date, $meal_type, $statuses, $per_page, $offset);
        $total_count = $this->premeal_model->count_orders_by_date($store_id, $date, $meal_type, $statuses);

        // Get summary counts
        $summary = $this->premeal_model->get_date_summary($store_id, $date, $meal_type);

        // Format orders
        $orders_data = [];
        foreach ($orders as $order) {
            // Get items for this order
            $items = $this->premeal_model->get_order_items($order->id);
            $items_summary = [];
            foreach ($items as $item) {
                $items_summary[] = $item->product_name . ' x' . $item->quantity;
            }

            $customer_name = trim($order->employee_first_name . ' ' . ($order->employee_last_name ?? ''));

            $orders_data[] = [
                'order_id' => (int)$order->id,
                'order_number' => $order->order_number,
                'pickup_code' => $order->pickup_code,
                'status' => $order->status,
                'customer' => [
                    'name' => $customer_name,
                    'phone' => $order->employee_phone,
                    'company' => $order->company_name
                ],
                'pickup_time' => $order->pickup_time ? date('H:i', strtotime($order->pickup_time)) : null,
                'items_summary' => implode(', ', $items_summary),
                'total_items' => (int)$order->total_items,
                'total_amount' => (float)$order->total_amount
            ];
        }

        $total_pages = $total_count > 0 ? ceil($total_count / $per_page) : 0;

        $this->logger->info('PREMEAL today orders fetched', [
            'store_id' => $store_id,
            'date' => $date,
            'meal_type' => $meal_type,
            'count' => count($orders_data)
        ], 'store_premeal');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($orders_data) ? 'No orders for this date' : 'Orders fetched successfully',
            'data' => [
                'date' => $date,
                'formatted_date' => date('l, d M Y', strtotime($date)),
                'meal_type' => $meal_type,
                'summary' => [
                    'confirmed' => $summary['confirmed'],
                    'preparing' => $summary['preparing'],
                    'ready' => $summary['ready'],
                    'completed' => $summary['completed'],
                    'total_active' => $summary['confirmed'] + $summary['preparing'] + $summary['ready']
                ],
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
     * Update Order Status (for individual day orders)
     *
     * Updates status for a single day's order.
     * Used when preparing/completing individual meals.
     *
     * POST /api/v1/store/premeal/update_status
     *
     * @param int    order_id (required) Order ID
     * @param string status (required) New status (PREPARING, READY, COMPLETED)
     *
     * @return void JSON response
     */
    public function update_status()
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

        $this->logger->info('Store PREMEAL Update Status API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_premeal');

        $post_data = $this->input->post(null, true);
        $order_id = isset($post_data['order_id']) ? (int)$post_data['order_id'] : null;
        $new_status = isset($post_data['status']) ? strtoupper(trim($post_data['status'])) : null;

        if (empty($order_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'order_id is required',
                'data' => null
            ]);
            return;
        }

        $valid_statuses = ['PREPARING', 'READY', 'COMPLETED'];
        if (empty($new_status) || !in_array($new_status, $valid_statuses)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'status is required. Must be PREPARING, READY, or COMPLETED',
                'data' => null
            ]);
            return;
        }

        // Get order
        $order = $this->premeal_model->get_order_by_id($order_id, $store_id);

        if (empty($order)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Validate status transition
        $allowed_transitions = [
            'CONFIRMED' => ['PREPARING', 'READY', 'COMPLETED'],
            'PREPARING' => ['READY', 'COMPLETED'],
            'READY' => ['COMPLETED']
        ];

        if (!isset($allowed_transitions[$order->status]) || !in_array($new_status, $allowed_transitions[$order->status])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Cannot change status from ' . $order->status . ' to ' . $new_status,
                'data' => null
            ]);
            return;
        }

        $old_status = $order->status;

        // Update status
        $this->premeal_model->update_order_status($order_id, $new_status);

        // Add status history
        $this->premeal_model->add_status_history([
            'order_id' => $order_id,
            'from_status' => $old_status,
            'to_status' => $new_status,
            'changed_by_type' => 'STORE_STAFF',
            'changed_by_id' => $staff_id,
            'note' => 'Status updated',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Send notification to employee based on new status
        if ($new_status === 'READY') {
            $this->notificationlib->orderReady($order->employee_id, $order);
        } elseif ($new_status === 'COMPLETED') {
            $this->notificationlib->orderCompleted($order->employee_id, $order);
        }

        $this->logger->info('PREMEAL order status updated', [
            'order_id' => $order_id,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'staff_id' => $staff_id
        ], 'store_premeal');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Order status updated to ' . $new_status,
            'data' => [
                'order_id' => (int)$order_id,
                'order_number' => $order->order_number,
                'old_status' => $old_status,
                'new_status' => $new_status
            ]
        ]);
    }

    /**
     * Calculate time ago string
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
}
