<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Guest Orders API Controller
 *
 * Handles QSR checkout for guest ordering.
 * No JWT authentication — only API key header.
 * Payment: 100% Razorpay (no wallet, no coupon, no policy).
 * Guest provides name + phone at checkout.
 */
class Orders extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Stores_model', 'stores_model');
        $this->load->model('Orders_model', 'orders_model');
        $this->load->model('Guest_model', 'guest_model');
        $this->load->model('DeliveryLocations_model', 'locations_model');
        $this->load->library('RazorpayLib', null, 'razorpaylib');
        $this->load->helper('common');
    }

    private function output($data)
    {
        header("Content-Type: application/json; charset=UTF-8");
        if (isset($data['status'])) {
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

    /**
     * Resolve store from store_code and validate guest booking
     */
    private function resolve_store($store_code)
    {
        if (empty($store_code)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'store_code is required',
                'data' => null
            ]);
            return false;
        }

        $store = $this->stores_model->get_store_by_code($store_code);

        if (empty($store) || !$store->is_active) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Store not found or inactive',
                'data' => null
            ]);
            return false;
        }

        if (!$store->is_operational) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store is currently not operational',
                'data' => null
            ]);
            return false;
        }

        $company = $this->common->getdatabytable('companies', [
            'id' => $store->company_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($company) || empty($company->allow_guest_booking)) {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Guest ordering is not available for this store',
                'data' => null
            ]);
            return false;
        }

        // Only QSR and KOT supported for guest ordering
        if (!in_array($store->store_type, ['QSR', 'KOT'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Guest ordering is not supported for this store type',
                'data' => null
            ]);
            return false;
        }

        $store->company_id = $company->id;
        $store->client_id = $company->client_id;
        return $store;
    }

    /**
     * Get guest session ID from header
     */
    private function get_session_id()
    {
        $headers = $this->input->request_headers();
        return isset($headers['X-Guest-Session']) ? $headers['X-Guest-Session'] : null;
    }

    /**
     * Get delivery locations for KOT store
     *
     * POST /api/v1/guest/orders/delivery_locations
     * Input: store_code
     */
    public function delivery_locations()
    {
        if (!$this->check_auth()) return;

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $locations = $this->locations_model->get_active_by_store($store->id);

        $result = [];
        foreach ($locations as $loc) {
            $result[] = [
                'id' => (int)$loc->id,
                'name' => $loc->name,
                'short_name' => $loc->short_name,
                'floor' => $loc->floor,
                'building' => $loc->building
            ];
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($result) ? 'No delivery locations found' : 'Delivery locations fetched',
            'data' => [
                'locations' => $result,
                'total' => count($result)
            ]
        ]);
    }

    /**
     * Initiate guest order (QSR or KOT)
     *
     * Validates cart, calculates totals, creates Razorpay order, stores pending_order.
     *
     * POST /api/v1/guest/orders/initiate
     * Input: store_code, guest_name, guest_phone, delivery_location_id (required for KOT)
     * Header: X-Guest-Session (required)
     */
    public function initiate()
    {
        if (!$this->check_auth()) return;

        $session_id = $this->get_session_id();
        if (empty($session_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'X-Guest-Session header is required',
                'data' => null
            ]);
            return;
        }

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        // Validate guest info
        $guest_name = trim($this->input->post('guest_name', true));
        $guest_phone = trim($this->input->post('guest_phone', true));

        if (empty($guest_name)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Guest name is required',
                'data' => null
            ]);
            return;
        }

        if (empty($guest_phone) || strlen($guest_phone) < 10) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Valid phone number is required (minimum 10 digits)',
                'data' => null
            ]);
            return;
        }

        // KOT: validate delivery location
        $delivery_location_id = null;
        $location = null;
        if ($store->store_type === 'KOT') {
            $delivery_location_id = (int)$this->input->post('delivery_location_id', true);
            if (empty($delivery_location_id)) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'delivery_location_id is required for KOT orders',
                    'data' => null
                ]);
                return;
            }

            $location = $this->locations_model->get_by_id($delivery_location_id, $store->id);
            if (!$location || !$location->is_active) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Invalid or inactive delivery location',
                    'data' => null
                ]);
                return;
            }
        }

        // Get cart items
        $cart_items = $this->guest_model->get_cart_items($session_id, $store->id);

        if (empty($cart_items)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Cart is empty',
                'data' => null
            ]);
            return;
        }

        // Validate stock and calculate totals
        $items_data = [];
        $subtotal = 0;
        $total_tax = 0;
        $insufficient_stock = [];

        foreach ($cart_items as $item) {
            $quantity = (int)$item->quantity;
            $available_stock = $item->available_stock;

            // Check stock
            $is_in_stock = ($available_stock === null || (int)$available_stock >= $quantity);
            if (!$is_in_stock) {
                $insufficient_stock[] = [
                    'product_id' => (int)$item->product_id,
                    'product_name' => $item->product_name,
                    'requested' => $quantity,
                    'available' => $available_stock === null ? null : (int)$available_stock
                ];
                continue;
            }

            $inclusive_price = !empty($item->store_price) ? (float)$item->store_price : (float)$item->base_price;
            $tax_percentage = (float)$item->tax_percentage;

            $unit_base_price = round(calculate_base_price($inclusive_price, $tax_percentage), 2);
            $unit_gst = round(calculate_gst_amount($inclusive_price, $tax_percentage), 2);

            $item_subtotal = round($unit_base_price * $quantity, 2);
            $item_tax = round($unit_gst * $quantity, 2);
            $item_total = round($inclusive_price * $quantity, 2);

            $subtotal += $item_subtotal;
            $total_tax += $item_tax;

            $items_data[] = [
                'product_id' => (int)$item->product_id,
                'product_name' => $item->product_name,
                'short_name' => $item->short_name,
                'thumbnail' => $item->thumbnail,
                'quantity' => $quantity,
                'unit_price' => $inclusive_price,
                'tax_percentage' => $tax_percentage,
                'base_price' => $unit_base_price,
                'tax_amount' => $unit_gst,
                'subtotal' => $item_subtotal,
                'total' => $item_total,
                'note' => $item->note,
                'is_vegetarian' => (bool)$item->is_vegetarian
            ];
        }

        // Block if any stock issues
        if (!empty($insufficient_stock)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Some items have insufficient stock',
                'data' => [
                    'insufficient_stock' => $insufficient_stock
                ]
            ]);
            return;
        }

        $subtotal = round($subtotal, 2);
        $total_tax = round($total_tax, 2);
        $total_amount = round($subtotal + $total_tax, 2);

        // Guest pays 100% via Razorpay
        $online_payment_amount = $total_amount;

        // Initialize Razorpay
        if (!$this->razorpaylib->init($store->company_id)) {
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Payment gateway configuration error',
                'data' => null
            ]);
            return;
        }

        $receipt_id = 'GUEST_' . substr($session_id, 0, 8) . '_' . time();

        $razorpay_result = $this->razorpaylib->createOrder([
            'amount' => $online_payment_amount,
            'receipt' => $receipt_id,
            'notes' => [
                'session_id' => $session_id,
                'store_id' => $store->id,
                'module' => $store->store_type,
                'guest_name' => $guest_name,
                'guest_phone' => $guest_phone
            ]
        ]);

        if (!$razorpay_result['success']) {
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to create payment order: ' . ($razorpay_result['message'] ?? 'Unknown error'),
                'data' => null
            ]);
            return;
        }

        $razorpay_order = $razorpay_result['order'];

        // Store pending order
        $pending_data = [
            'employee_id' => null,
            'session_id' => $session_id,
            'store_id' => $store->id,
            'module' => $store->store_type,
            'razorpay_order_id' => $razorpay_order['id'],
            'amount' => $online_payment_amount,
            'wallet_amount' => 0,
            'coupon_id' => null,
            'coupon_code' => null,
            'discount_amount' => 0,
            'subtotal' => $subtotal,
            'tax_amount' => $total_tax,
            'total_amount' => $total_amount,
            'guest_name' => $guest_name,
            'guest_phone' => $guest_phone,
            'items_json' => json_encode([
                'delivery_location_id' => $delivery_location_id,
                'items' => $items_data
            ]),
            'status' => 'PENDING',
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
        ];

        $pending_id = $this->guest_model->store_pending_order($pending_data);

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Payment order created successfully',
            'data' => [
                'pending_order_id' => $pending_id,
                'razorpay_order_id' => $razorpay_order['id'],
                'razorpay_key' => $this->razorpaylib->getKeyId(),
                'amount' => $online_payment_amount,
                'currency' => 'INR',
                'guest_name' => $guest_name,
                'guest_phone' => $guest_phone,
                'delivery_location' => $location ? [
                    'id' => (int)$location->id,
                    'name' => $location->name,
                    'short_name' => $location->short_name,
                    'floor' => $location->floor,
                    'building' => $location->building
                ] : null,
                'summary' => [
                    'subtotal' => $subtotal,
                    'tax_amount' => $total_tax,
                    'total_amount' => $total_amount,
                    'amount_payable' => $online_payment_amount,
                    'items_count' => count($items_data),
                    'formatted' => [
                        'subtotal' => number_format($subtotal, 2),
                        'tax_amount' => number_format($total_tax, 2),
                        'total_amount' => number_format($total_amount, 2),
                        'amount_payable' => number_format($online_payment_amount, 2)
                    ]
                ]
            ]
        ]);
    }

    /**
     * Complete guest QSR order after Razorpay payment
     *
     * Verifies payment, creates order, order_items, order_payments, deducts stock, clears cart.
     *
     * POST /api/v1/guest/orders/complete
     * Input: store_code, razorpay_order_id, razorpay_payment_id, razorpay_signature
     * Header: X-Guest-Session (required)
     */
    public function complete()
    {
        if (!$this->check_auth()) return;

        $session_id = $this->get_session_id();
        if (empty($session_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'X-Guest-Session header is required',
                'data' => null
            ]);
            return;
        }

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $razorpay_order_id = $this->input->post('razorpay_order_id', true);
        $razorpay_payment_id = $this->input->post('razorpay_payment_id', true);
        $razorpay_signature = $this->input->post('razorpay_signature', true);

        if (empty($razorpay_order_id) || empty($razorpay_payment_id) || empty($razorpay_signature)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'razorpay_order_id, razorpay_payment_id and razorpay_signature are required',
                'data' => null
            ]);
            return;
        }

        // Initialize Razorpay and verify payment
        if (!$this->razorpaylib->init($store->company_id)) {
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Payment gateway configuration error',
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
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Payment verification failed. Invalid signature.',
                'data' => null
            ]);
            return;
        }

        // Get pending order
        $pending_order = $this->guest_model->get_pending_by_razorpay($razorpay_order_id, $session_id);

        if (empty($pending_order)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Pending order not found or already processed',
                'data' => null
            ]);
            return;
        }

        // Check expiry
        if (strtotime($pending_order->expires_at) < time()) {
            $this->guest_model->update_pending_order($pending_order->id, [
                'status' => 'EXPIRED'
            ]);
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order session has expired. Please try again.',
                'data' => null
            ]);
            return;
        }

        // Start DB transaction
        $this->db->trans_start();

        try {
            // Generate order number and pickup code
            $order_number = $this->orders_model->generate_order_number($store->store_type);
            $pickup_code = generate_pickup_code(6);

            // Parse items_json (contains delivery_location_id + items)
            $pending_json = json_decode($pending_order->items_json, true);
            $delivery_location_id = !empty($pending_json['delivery_location_id']) ? (int)$pending_json['delivery_location_id'] : null;
            $items = $pending_json['items'] ?? [];

            if (empty($items)) {
                throw new Exception('Invalid order data — no items found');
            }

            // Create order
            $order_data = [
                'order_number' => $order_number,
                'employee_id' => null,
                'company_id' => $store->company_id,
                'store_id' => $store->id,
                'module' => $store->store_type,
                'is_guest_order' => 1,
                'guest_name' => $pending_order->guest_name,
                'guest_phone' => $pending_order->guest_phone,
                'status' => 'PENDING',
                'pickup_code' => $pickup_code,
                'delivery_location_id' => $delivery_location_id,
                'department_id' => null,
                'subtotal' => $pending_order->subtotal,
                'tax_amount' => $pending_order->tax_amount,
                'total_amount' => $pending_order->total_amount,
                'company_contribution' => 0,
                'employee_contribution' => $pending_order->total_amount,
                'discount_amount' => 0,
                'wallet_deducted' => 0,
                'payment_status' => 'PAID',
                'payment_method' => 'ONLINE',
                'paid_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $order_id = $this->orders_model->create_order($order_data);

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
                    'tax_amount' => round($item['tax_amount'] * $item['quantity'], 2),
                    'subtotal' => $item['subtotal'],
                    'total_amount' => $item['total'],
                    'note' => $item['note'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $total_quantity += $item['quantity'];
            }

            $this->orders_model->create_order_items($order_items);

            // Record online payment
            $this->orders_model->add_order_payment([
                'order_id' => $order_id,
                'payment_type' => 'ONLINE_PAYMENT',
                'amount' => $pending_order->amount,
                'transaction_id' => null,
                'razorpay_payment_id' => $razorpay_payment_id,
                'razorpay_order_id' => $razorpay_order_id,
                'status' => 'SUCCESS',
                'note' => 'Guest order payment',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Deduct stock
            foreach ($items as $item) {
                $this->orders_model->deduct_stock($store->id, $item['product_id'], $item['quantity'], [
                    'reference_id'      => $order_id,
                    'order_number'      => $order_number,
                    'performed_by_type' => 'GUEST',
                    'note'              => 'Stock deducted on guest order placement'
                ]);
            }

            // Clear guest cart
            $this->guest_model->clear_cart($session_id, $store->id);

            // Update pending order status
            $this->guest_model->update_pending_order($pending_order->id, [
                'status' => 'COMPLETED'
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->output([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Failed to process order. Please contact support.',
                    'data' => null
                ]);
                return;
            }

            // Generate order token for guest to track order
            $order_token = $this->generate_order_token($order_id);

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order' => [
                        'id' => (int)$order_id,
                        'order_number' => $order_number,
                        'order_token' => $order_token,
                        'pickup_code' => $pickup_code,
                        'status' => 'PENDING',
                        'total_amount' => (float)$pending_order->total_amount,
                        'online_paid' => (float)$pending_order->amount,
                        'items_count' => $total_quantity,
                        'qr_data' => "{$order_number}|{$pickup_code}",
                        'guest_name' => $pending_order->guest_name,
                        'guest_phone' => $pending_order->guest_phone,
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    'store' => [
                        'id' => (int)$store->id,
                        'name' => $store->name,
                        'store_type' => $store->store_type,
                        'address' => trim(implode(', ', array_filter([
                            $store->address_line1 ?? '',
                            $store->city ?? '',
                            $store->state ?? ''
                        ])))
                    ],
                    'delivery_location' => $delivery_location_id ? (function() use ($delivery_location_id, $store) {
                        $loc = $this->locations_model->get_by_id($delivery_location_id, $store->id);
                        return $loc ? [
                            'id' => (int)$loc->id,
                            'name' => $loc->name,
                            'floor' => $loc->floor,
                            'building' => $loc->building
                        ] : null;
                    })() : null,
                    'pricing' => [
                        'subtotal' => (float)$pending_order->subtotal,
                        'tax_amount' => (float)$pending_order->tax_amount,
                        'total_amount' => (float)$pending_order->total_amount,
                        'online_paid' => (float)$pending_order->amount,
                        'formatted_subtotal' => number_format($pending_order->subtotal, 2),
                        'formatted_tax' => number_format($pending_order->tax_amount, 2),
                        'formatted_total' => number_format($pending_order->total_amount, 2),
                        'formatted_online_paid' => number_format($pending_order->amount, 2)
                    ],
                    'payment' => [
                        'method' => 'ONLINE',
                        'status' => 'PAID'
                    ]
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Guest order completion failed: ' . $e->getMessage());
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'An error occurred while processing your order',
                'data' => null
            ]);
        }
    }

    /**
     * Generate a signed token for guest order access
     *
     * Format: base64(order_id) . '.' . hmac_signature
     */
    private function generate_order_token($order_id)
    {
        $payload = base64_encode($order_id . '|' . time());
        $signature = hash_hmac('sha256', $payload, config_item('api_authorization'));
        return $payload . '.' . substr($signature, 0, 32);
    }

    /**
     * Verify order token and extract order_id
     *
     * @return int|false Order ID or false
     */
    private function verify_order_token($token)
    {
        if (empty($token) || strpos($token, '.') === false) {
            return false;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) return false;

        $payload = $parts[0];
        $provided_sig = $parts[1];

        $expected_sig = substr(hash_hmac('sha256', $payload, config_item('api_authorization')), 0, 32);

        if (!hash_equals($expected_sig, $provided_sig)) {
            return false;
        }

        $decoded = base64_decode($payload);
        $data = explode('|', $decoded);
        if (empty($data[0])) return false;

        return (int)$data[0];
    }

    /**
     * Build status timeline for guest order
     */
    private function build_status_timeline($current_status)
    {
        if ($current_status === 'CANCELLED' || $current_status === 'REJECTED') {
            $terminal_label = $current_status === 'CANCELLED' ? 'Cancelled' : 'Rejected';
            return [
                ['code' => 'placed', 'text' => 'Placed', 'is_completed' => true, 'is_current' => false],
                ['code' => strtolower($current_status), 'text' => $terminal_label, 'is_completed' => false, 'is_current' => true]
            ];
        }

        $steps = [
            ['code' => 'pending',   'text' => 'Pending'],
            ['code' => 'confirmed', 'text' => 'Approved'],
            ['code' => 'ready',     'text' => 'Ready'],
            ['code' => 'completed', 'text' => 'Completed'],
        ];

        $status_order = ['PENDING' => 0, 'CONFIRMED' => 1, 'PREPARING' => 1, 'READY' => 2, 'COMPLETED' => 3];
        $current_index = isset($status_order[$current_status]) ? $status_order[$current_status] : 0;

        $timeline = [];
        foreach ($steps as $i => $step) {
            $timeline[] = [
                'code' => $step['code'],
                'text' => $step['text'],
                'is_completed' => $i < $current_index,
                'is_current' => $i === $current_index
            ];
        }
        return $timeline;
    }

    /**
     * Get status label
     */
    private function get_status_label($status)
    {
        $labels = [
            'PENDING' => 'Order Placed',
            'CONFIRMED' => 'Confirmed',
            'PREPARING' => 'Preparing',
            'READY' => 'Ready for Pickup',
            'COMPLETED' => 'Completed',
            'CANCELLED' => 'Cancelled',
            'REJECTED' => 'Rejected'
        ];
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    /**
     * Get status color
     */
    private function get_status_color($status)
    {
        $colors = [
            'PENDING' => '#FFA500',
            'CONFIRMED' => '#2196F3',
            'PREPARING' => '#9C27B0',
            'READY' => '#4CAF50',
            'COMPLETED' => '#28a745',
            'CANCELLED' => '#dc3545',
            'REJECTED' => '#dc3545'
        ];
        return isset($colors[$status]) ? $colors[$status] : '#666666';
    }

    /**
     * Get guest order details by token
     *
     * POST /api/v1/guest/orders/details
     * Input: order_token
     */
    public function details()
    {
        if (!$this->check_auth()) return;

        $order_token = $this->input->post('order_token', true);
        if (empty($order_token)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'order_token is required',
                'data' => null
            ]);
            return;
        }

        $order_id = $this->verify_order_token($order_token);
        if (!$order_id) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid or expired order token',
                'data' => null
            ]);
            return;
        }

        // Get order with store details
        $order = $this->guest_model->get_guest_order_with_store($order_id);

        if (empty($order)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Get order items with product details
        $raw_items = $this->guest_model->get_guest_order_items($order_id);

        $items = [];
        $subtotal = 0;
        $total_tax = 0;
        foreach ($raw_items as $item) {
            $subtotal += (float)$item->base_price * (int)$item->quantity;
            $total_tax += (float)$item->tax_amount;

            $items[] = [
                'id' => (int)$item->id,
                'product_id' => (int)$item->product_id,
                'name' => $item->product_name,
                'short_name' => $item->short_name,
                'thumbnail' => $item->thumbnail ? base_url($item->thumbnail) : null,
                'quantity' => (int)$item->quantity,
                'price' => (float)$item->unit_price,
                'tax_amount' => (float)($item->tax_amount ?? 0),
                'total_price' => (float)$item->total_amount,
                'is_vegetarian' => (bool)$item->is_vegetarian,
                'notes' => $item->note
            ];
        }

        // Build response matching employee my_orders/details pattern
        $order_data = [
            'id' => (int)$order->id,
            'order_number' => $order->order_number,
            'order_token' => $order_token,
            'module' => $order->module,
            'status' => $order->status,
            'status_label' => $this->get_status_label($order->status),
            'status_color' => $this->get_status_color($order->status),
            'statuses' => $this->build_status_timeline($order->status),
            'pickup_code' => $order->pickup_code,
            'store' => [
                'name' => $order->store_name,
                'store_type' => $order->module,
                'address' => trim(implode(', ', array_filter([
                    $order->address_line1,
                    $order->city,
                    $order->state
                ]))),
                'phone' => $order->store_phone
            ],
            'delivery_location' => !empty($order->delivery_location_id) ? (function() use ($order) {
                $loc = $this->locations_model->get_by_id($order->delivery_location_id, $order->store_id);
                return $loc ? [
                    'id' => (int)$loc->id,
                    'name' => $loc->name,
                    'floor' => $loc->floor,
                    'building' => $loc->building
                ] : null;
            })() : null,
            'guest' => [
                'name' => $order->guest_name,
                'phone' => $order->guest_phone
            ],
            'items' => $items,
            'items_count' => count($items),
            'pickup' => [
                'code' => $order->pickup_code,
                'qr_data' => $order->pickup_code ? "{$order->order_number}|{$order->pickup_code}" : null,
                'ready_at' => $order->ready_at ?? null,
                'formatted_ready_at' => !empty($order->ready_at) ? date('d M Y, h:i A', strtotime($order->ready_at)) : null
            ],
            'pricing' => [
                'subtotal' => round($subtotal, 2),
                'tax' => round($total_tax, 2),
                'total' => (float)$order->total_amount,
                'online_paid' => (float)$order->total_amount,
                'formatted_subtotal' => number_format($subtotal, 2),
                'formatted_tax' => number_format($total_tax, 2),
                'formatted_total' => number_format($order->total_amount, 2),
                'formatted_online_paid' => number_format($order->total_amount, 2)
            ],
            'payment' => [
                'method' => $order->payment_method ?? 'ONLINE',
                'status' => $order->payment_status ?? 'PAID'
            ],
            'refund' => [
                'amount' => (float)($order->refund_amount ?? 0),
                'status' => $order->refund_status ?? null,
                'formatted_amount' => number_format($order->refund_amount ?? 0, 2)
            ],
            'created_at' => $order->created_at,
            'formatted_date' => date('d M Y, h:i A', strtotime($order->created_at))
        ];

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Order details fetched successfully',
            'data' => [
                'order' => $order_data
            ]
        ]);
    }
}
