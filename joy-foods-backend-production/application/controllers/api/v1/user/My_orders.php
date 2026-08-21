<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * MyOrders API Controller
 *
 * Handles employee order history operations including listing orders
 * and viewing order details with cancellation eligibility.
 *
 * @category  Controllers
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-08
 */
class My_orders extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('MyOrders_model', 'orders_model');
        $this->load->model('Cart_model', 'cart_model');
        $this->load->helper('common');
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
     * Get human-readable status label
     *
     * @param string $status Order status
     * @return string Status label
     */
    private function get_status_label($status)
    {
        $labels = [
            'PENDING' => 'Pending',
            'CONFIRMED' => 'Confirmed',
            'PREPARING' => 'Preparing',
            'READY' => 'Ready for Pickup',
            'OUT_FOR_DELIVERY' => 'Out for Delivery',
            'DELIVERED' => 'Delivered',
            'COMPLETED' => 'Completed',
            'CANCELLED' => 'Cancelled',
            'REJECTED' => 'Rejected'
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get status color for UI
     *
     * @param string $status Order status
     * @return string Color hex code
     */
    private function get_status_color($status)
    {
        $colors = [
            'PENDING' => '#FFA500',      // Orange
            'CONFIRMED' => '#2196F3',    // Blue
            'PREPARING' => '#9C27B0',    // Purple
            'READY' => '#4CAF50',        // Green
            'OUT_FOR_DELIVERY' => '#00BCD4', // Cyan
            'DELIVERED' => '#4CAF50',    // Green
            'COMPLETED' => '#4CAF50',    // Green
            'CANCELLED' => '#F44336',    // Red
            'REJECTED' => '#F44336'      // Red
        ];

        return $colors[$status] ?? '#757575';
    }

    /**
     * Build status timeline for order tracking UI
     *
     * Normal flow:  PENDING -> CONFIRMED -> READY -> COMPLETED
     * Cancelled:    Placed -> Cancelled
     * Rejected:     Placed -> Rejected
     *
     * @param string $current_status Current order status
     * @return array Status timeline steps
     */
    private function build_status_timeline($current_status)
    {
        // Cancelled / Rejected - short timeline
        if ($current_status === 'CANCELLED' || $current_status === 'REJECTED') {
            $terminal_label = $current_status === 'CANCELLED' ? 'Cancelled' : 'Rejected';
            return [
                [
                    'code' => 'placed',
                    'text' => 'Placed',
                    'is_completed' => true,
                    'is_current' => false
                ],
                [
                    'code' => strtolower($current_status),
                    'text' => $terminal_label,
                    'is_completed' => false,
                    'is_current' => true
                ]
            ];
        }

        // Normal flow
        $steps = [
            ['code' => 'pending',   'text' => 'Pending'],
            ['code' => 'approved',  'text' => 'Approved'],
            ['code' => 'ready',     'text' => 'Ready'],
            ['code' => 'completed', 'text' => 'Completed'],
        ];

        // Map DB status to step code
        $status_to_step = [
            'PENDING'   => 'pending',
            'CONFIRMED' => 'approved',
            'PREPARING' => 'approved',
            'READY'     => 'ready',
            'COMPLETED' => 'completed',
        ];

        $current_step = $status_to_step[$current_status] ?? 'pending';

        $reached_current = false;
        $timeline = [];

        foreach ($steps as $step) {
            if ($step['code'] === $current_step) {
                $timeline[] = array_merge($step, [
                    'is_completed' => true,
                    'is_current' => true
                ]);
                $reached_current = true;
            } else {
                $timeline[] = array_merge($step, [
                    'is_completed' => !$reached_current,
                    'is_current' => false
                ]);
            }
        }

        return $timeline;
    }

    /**
     * Check if order can be cancelled
     *
     * @param object $order    Order object
     * @param object $policy   Policy object
     * @return array ['can_cancel' => bool, 'reason' => string]
     */
    private function check_cancellation_eligibility($order, $policy)
    {
        // Already cancelled or completed orders cannot be cancelled
        $non_cancellable_statuses = ['CANCELLED', 'REJECTED', 'COMPLETED', 'DELIVERED'];
        if (in_array($order->status, $non_cancellable_statuses)) {
            return [
                'can_cancel' => false,
                'reason' => 'Order is already ' . strtolower($this->get_status_label($order->status))
            ];
        }

        // QSR/KOT Orders: No cancellation after CONFIRMED
        if ($order->module == 'QSR' || $order->module == 'KOT') {
            $confirmed_statuses = ['CONFIRMED', 'PREPARING', 'READY', 'OUT_FOR_DELIVERY'];
            if (in_array($order->status, $confirmed_statuses)) {
                return [
                    'can_cancel' => false,
                    'reason' => $order->module . ' orders cannot be cancelled after confirmation'
                ];
            }
            // PENDING QSR orders can be cancelled
            return [
                'can_cancel' => true,
                'reason' => null
            ];
        }

        // PREMEAL Orders: Check cutoff time based on policy
        if ($order->module == 'PREMEAL') {
            // Get cancellation cutoff hours from policy (default 1 hour)
            $cutoff_hours = ($policy && $policy->cancellation_cutoff_hours)
                ? (int)$policy->cancellation_cutoff_hours
                : 1;

            // Get serving time based on meal type
            $serving_time = null;
            if ($order->meal_type == 'BREAKFAST') {
                $serving_time = $order->breakfast_time;
            } elseif ($order->meal_type == 'LUNCH') {
                $serving_time = $order->lunch_time;
            } elseif ($order->meal_type == 'DINNER') {
                $serving_time = $order->dinner_time;
            }

            if (empty($serving_time)) {
                return [
                    'can_cancel' => false,
                    'reason' => 'Unable to determine serving time'
                ];
            }

            // Calculate cutoff datetime
            $scheduled_date = $order->scheduled_date;
            $cutoff_datetime = strtotime("-{$cutoff_hours} hours", strtotime("$scheduled_date $serving_time"));
            $current_time = time();

            if ($current_time >= $cutoff_datetime) {
                $cutoff_formatted = date('d M Y, h:i A', $cutoff_datetime);
                return [
                    'can_cancel' => false,
                    'reason' => "Cancellation cutoff time ({$cutoff_formatted}) has passed"
                ];
            }

            // Already being prepared or further
            $preparing_statuses = ['PREPARING', 'READY', 'OUT_FOR_DELIVERY'];
            if (in_array($order->status, $preparing_statuses)) {
                return [
                    'can_cancel' => false,
                    'reason' => 'Order is already being prepared'
                ];
            }

            return [
                'can_cancel' => true,
                'reason' => null
            ];
        }

        return [
            'can_cancel' => false,
            'reason' => 'Unknown order type'
        ];
    }

    /**
     * Build pricing summary for order details
     *
     * @param object $order    Order object
     * @param float  $subtotal Calculated subtotal from items
     * @param float  $total_tax Calculated tax from items
     * @return array Pricing summary
     */
    private function build_pricing_summary($order, $subtotal, $total_tax)
    {
        $company_contribution = round((float)($order->company_contribution ?? 0), 2);
        $employee_contribution = round((float)($order->employee_contribution ?? 0), 2);
        $discount = round((float)($order->discount_amount ?? 0), 2);
        $wallet_deducted = round((float)($order->wallet_deducted ?? 0), 2);
        $total = round((float)$order->total_amount, 2);

        // online_paid = employee's share minus what wallet covered
        $online_paid = round($employee_contribution - $wallet_deducted, 2);
        if ($online_paid < 0) {
            $online_paid = 0;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($total_tax, 2),
            'discount' => $discount,
            'total' => $total,
            'company_contribution' => $company_contribution,
            'employee_contribution' => $employee_contribution,
            'wallet_deducted' => $wallet_deducted,
            'online_paid' => $online_paid,
            'formatted_subtotal' => '₹' . number_format($subtotal, 2),
            'formatted_tax' => '₹' . number_format($total_tax, 2),
            'formatted_discount' => '₹' . number_format($discount, 2),
            'formatted_total' => '₹' . number_format($total, 2),
            'formatted_company_contribution' => '₹' . number_format($company_contribution, 2),
            'formatted_employee_contribution' => '₹' . number_format($employee_contribution, 2),
            'amount_payable' => $employee_contribution,
            'formatted_wallet_deducted' => '₹' . number_format($wallet_deducted, 2),
            'formatted_online_paid' => '₹' . number_format($online_paid, 2),
            'formatted_amount_payable' => '₹' . number_format($employee_contribution, 2)
        ];
    }

    /**
     * Get Orders List
     *
     * Returns paginated list of orders for the authenticated employee.
     *
     * GET /api/v1/user/my_orders/list
     * GET /api/v1/user/my_orders/list?module=QSR&page=1&per_page=20
     *
     * Query Parameters:
     * - page (optional): Page number (default: 1)
     * - per_page (optional): Orders per page (default: 20, max: 50)
     *
     * @return void JSON response with orders list
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

        $this->logger->info('Get Orders List API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'my_orders');

        // Get pagination parameters
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

        // Get total count for pagination
        $total_count = $this->orders_model->get_orders_count($employee_id);

        // Calculate pagination info
        $total_pages = $total_count > 0 ? ceil($total_count / $per_page) : 0;
        $has_next = $page < $total_pages;
        $has_previous = $page > 1;

        // Get orders (all modules mixed)
        $orders = $this->orders_model->get_orders_list($employee_id, $per_page, $offset);

        // Format orders data
        $orders_data = [];
        foreach ($orders as $order) {
            // Format items
            $items = [];
            foreach ($order->items as $item) {
                $items[] = [
                    'id' => (int)$item->id,
                    'product_id' => (int)$item->product_id,
                    'name' => $item->product_name,
                    'short_name' => $item->short_name,
                    'is_vegetarian' => (bool)$item->is_vegetarian,
                    'thumbnail' => $item->thumbnail ? base_url($item->thumbnail) : null,
                    'quantity' => (int)$item->quantity,
                    'price' => (float)$item->unit_price,
                    'total_price' => (float)$item->total_amount
                ];
            }

            // For PREMEAL primary orders with multiple days, compute the effective status
            // from all day orders (primary + children). Show the next active day's status,
            // or COMPLETED only if every day is finished.
            $display_status = $order->status;
            $display_pickup_code = $order->pickup_code;
            $premeal_progress = null;

            if ($order->module == 'PREMEAL' && !empty($order->is_primary_order)) {
                $children = $this->orders_model->get_child_orders($order->id);

                // Build full set: primary + children, ordered by scheduled_date ASC
                $all_days = [(object)[
                    'status' => $order->status,
                    'pickup_code' => $order->pickup_code,
                    'scheduled_date' => $order->scheduled_date
                ]];
                foreach ($children as $child) {
                    $all_days[] = (object)[
                        'status' => $child->status,
                        'pickup_code' => $child->pickup_code,
                        'scheduled_date' => $child->scheduled_date
                    ];
                }
                usort($all_days, function ($a, $b) {
                    return strcmp($a->scheduled_date ?? '', $b->scheduled_date ?? '');
                });

                $terminal = ['COMPLETED', 'DELIVERED', 'CANCELLED', 'REJECTED'];
                $total_days = count($all_days);
                $completed_days = 0;
                $cancelled_days = 0;
                $next_active = null;

                foreach ($all_days as $day) {
                    $st = strtoupper($day->status);
                    if (in_array($st, ['COMPLETED', 'DELIVERED'])) {
                        $completed_days++;
                    } elseif (in_array($st, ['CANCELLED', 'REJECTED'])) {
                        $cancelled_days++;
                    } elseif (!$next_active) {
                        $next_active = $day;
                    }
                }

                if ($next_active) {
                    // At least one day still pending — show that day's status & pickup code
                    $display_status = $next_active->status;
                    $display_pickup_code = $next_active->pickup_code;
                } elseif ($completed_days > 0 && $cancelled_days === 0) {
                    $display_status = 'COMPLETED';
                } elseif ($cancelled_days === $total_days) {
                    $display_status = 'CANCELLED';
                } else {
                    // Mixed terminal — at least one completed and at least one cancelled
                    $display_status = 'COMPLETED';
                }

                $premeal_progress = [
                    'total_days' => $total_days,
                    'completed_days' => $completed_days,
                    'cancelled_days' => $cancelled_days,
                    'pending_days' => $total_days - $completed_days - $cancelled_days
                ];
            }

            $order_data = [
                'id' => (int)$order->id,
                'order_number' => $order->order_number,
                'module' => $order->module,
                'status' => $display_status,
                'status_label' => $this->get_status_label($display_status),
                'status_color' => $this->get_status_color($display_status),
                'total_amount' => (float)$order->total_amount,
                'formatted_amount' => '₹' . number_format($order->total_amount, 2),
                'items_count' => count($items),
                'items' => $items,
                'store' => [
                    'name' => $order->store_name,
                    'address' => trim($order->address_line1 . ', ' . $order->city, ', ')
                ],
                'is_scheduled' => $order->is_scheduled == 1,
                'created_at' => $order->created_at,
                'formatted_date' => date('d M Y, h:i A', strtotime($order->created_at))
            ];

            // Add PREMEAL specific fields
            $order_data['scheduled_date'] = $order->scheduled_date;
            $order_data['meal_type'] = $order->meal_type;
            $order_data['pickup_time'] = $order->pickup_time ? $order->pickup_time : null;
            if ($premeal_progress) {
                $order_data['premeal_progress'] = $premeal_progress;
            }

            // Add KOT specific fields
            if ($order->module == 'KOT') {
                $order_data['delivery_location'] = $order->delivery_location_name ?? null;
                $order_data['company_contribution'] = round((float)($order->company_contribution ?? 0), 2);
                $order_data['employee_contribution'] = round((float)($order->employee_contribution ?? 0), 2);
            }

            // Add pickup code for ready orders
            if (in_array($display_status, ['READY', 'CONFIRMED', 'PREPARING'])) {
                $order_data['pickup_code'] = $display_pickup_code;
            }

            $orders_data[] = $order_data;
        }

        $this->logger->info('Orders list fetched successfully', [
            'employee_id' => $employee_id,
            'orders_count' => count($orders_data),
            'page' => $page
        ], 'my_orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($orders_data) ? 'No orders found' : 'Orders fetched successfully',
            'data' => [
                'orders' => $orders_data,
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
     * Get Order Details
     *
     * Returns detailed information for a specific order including
     * items, payment info, and cancellation eligibility.
     *
     * GET /api/v1/user/my_orders/details?order_id=123
     *
     * Query Parameters:
     * - order_id (required): Order ID to fetch details for
     *
     * @return void JSON response with order details
     */
    public function details()
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
        $order_id = $this->input->get('order_id', true);

        $this->logger->info('Get Order Details API called', [
            'employee_id' => $employee_id,
            'order_id' => $order_id,
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'my_orders');

        // Validate order_id
        if (empty($order_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order ID is required',
                'data' => null
            ]);
            return;
        }

        // Get order details
        $order = $this->orders_model->get_order_details($order_id, $employee_id);

        if (empty($order)) {
            $this->logger->warning('Order not found', [
                'employee_id' => $employee_id,
                'order_id' => $order_id
            ], 'my_orders');
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Get employee policy for cancellation rules
        $policy = $this->orders_model->get_employee_policy($employee_id);

        // Check cancellation eligibility
        $cancellation = $this->check_cancellation_eligibility($order, $policy);

        //Items String.
        $items_string = '';
        $is_vegetarian = true; // Assume vegetarian unless we find a non-veg item

        // Format items
        $items = [];
        $subtotal = 0;
        $total_tax = 0;
        foreach ($order->items as $item) {
            $item_subtotal = (float)$item->base_price * (int)$item->quantity;
            $subtotal += $item_subtotal;
            $total_tax += (float)($item->tax_amount ?? 0);

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

            // Build items string for notification (e.g. "2x Veg Sandwich, 1x Coffee")
            $items_string .= ($items_string ? ', ' : '') . "{$item->quantity}x {$item->product_name}";
            if (!$item->is_vegetarian) {
                $is_vegetarian = false;
            }
        }

        // Build order details response
        $order_data = [
            'id' => (int)$order->id,
            'order_number' => $order->order_number,
            'module' => $order->module,
            'status' => $order->status,
            'status_label' => $this->get_status_label($order->status),
            'status_color' => $this->get_status_color($order->status),
            'statuses' => $this->build_status_timeline($order->status),
            'pickup_code' => $order->pickup_code,
            'store' => [
                'name' => $order->store_name,
                'address' => trim($order->address_line1 . ', ' . $order->city, ', '),
                'phone' => $order->store_phone
            ],
            'items' => $items,
            'items_count' => count($items),
            'pickup' => [
                'code' => $order->pickup_code,
                'qr_data' => $order->pickup_code ? "{$order->order_number}|{$order->pickup_code}" : null,
                'ready_at' => $order->ready_at,
                'formatted_ready_at' => $order->ready_at ? date('d M Y, h:i A', strtotime($order->ready_at)) : null
            ],
            'pricing' => $this->build_pricing_summary($order, $subtotal, $total_tax),
            'payment' => [
                'method' => $order->payment_method ?? 'WALLET',
                'status' => $order->payment_status ?? 'PAID'
            ],
            'cancellation' => [
                'can_cancel' => $cancellation['can_cancel'],
                'reason' => $cancellation['reason']
            ],
            'is_scheduled' => $order->is_scheduled == 1,
            'pickup_time' => $order->pickup_time ? $order->pickup_time : null,
            'formatted_pickup_date_time' => $order->pickup_time ? date('d M y h:i A', strtotime($order->pickup_time)) : null,
            'created_at' => $order->created_at,
            'formatted_date' => date('d M Y, h:i A', strtotime($order->created_at)),
            'collect_review' => (
                isset($order->is_review_required) && $order->is_review_required == 1
                && isset($order->is_reviewed) && $order->is_reviewed == 0
                && in_array($order->status, ['COMPLETED', 'DELIVERED'])
            )
        ];

        // Add PREMEAL specific fields
        if ($order->module == 'PREMEAL') {
            $order_data['scheduled_date'] = $order->scheduled_date;
            $order_data['formatted_scheduled_date'] = date('d M Y', strtotime($order->scheduled_date));
            $order_data['meal_type'] = $order->meal_type;
            $order_data['pickup_time'] = $order->pickup_time ? $order->pickup_time : null;

            // Per day summary (from primary order's amounts)
            $per_day_total = (float)$order->total_amount;
            $per_day_company = (float)($order->company_contribution ?? 0);
            $per_day_employee = (float)($order->employee_contribution ?? 0);

            $order_data['per_day_summary'] = [
                'total' => round($per_day_total, 2),
                'company_pays' => round($per_day_company, 2),
                'employee_pays' => round($per_day_employee, 2),
                'formatted_total' => '₹' . number_format($per_day_total, 2),
                'formatted_company_pays' => '₹' . number_format($per_day_company, 2),
                'formatted_employee_pays' => '₹' . number_format($per_day_employee, 2)
            ];

            // For primary orders, include scheduled orders and booking summary
            if ($order->is_primary_order == 1) {
                // Aggregate totals: start with primary order's amounts
                $booking_total = $per_day_total;
                $booking_company = $per_day_company;
                $booking_employee = $per_day_employee;
                $booking_wallet = (float)($order->wallet_deducted ?? 0);
                $booking_discount = (float)($order->discount_amount ?? 0);

                // Include primary order as first entry in scheduled_orders
                $scheduled_orders = [
                    [
                        'id' => (int)$order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'status_label' => $this->get_status_label($order->status),
                        'status_color' => $this->get_status_color($order->status),
                        'scheduled_date' => $order->scheduled_date,
                        'formatted_date' => date('d M Y', strtotime($order->scheduled_date)),
                        'day_name' => date('l', strtotime($order->scheduled_date)),
                        'meal_type' => $order->meal_type,
                        'is_vegetarian' => $is_vegetarian,
                        'items_string' => $items_string,
                        'pickup_time' => $order->pickup_time ? $order->pickup_time : null,
                        'pickup_code' => $order->pickup_code,
                        'total_amount' => $per_day_total,
                        'formatted_amount' => '₹' . number_format($per_day_total, 2),
                        'can_cancel' => $cancellation['can_cancel'],
                        'cancel_reason' => $cancellation['reason']
                    ]
                ];

                // Add child orders if any
                if (!empty($order->scheduled_orders)) {
                    foreach ($order->scheduled_orders as $child) {
                        // Accumulate child amounts into booking totals
                        $booking_total += (float)$child->total_amount;
                        $booking_company += (float)($child->company_contribution ?? 0);
                        $booking_employee += (float)($child->employee_contribution ?? 0);
                        $booking_wallet += (float)($child->wallet_deducted ?? 0);
                        $booking_discount += (float)($child->discount_amount ?? 0);

                        // Check cancellation for each child order
                        $child_order = $this->orders_model->get_order_by_id($child->id, $employee_id);
                        $child_cancellation = $this->check_cancellation_eligibility($child_order, $policy);

                        $scheduled_orders[] = [
                            'id' => (int)$child->id,
                            'order_number' => $child->order_number,
                            'status' => $child->status,
                            'status_label' => $this->get_status_label($child->status),
                            'status_color' => $this->get_status_color($child->status),
                            'scheduled_date' => $child->scheduled_date,
                            'formatted_date' => date('d M Y', strtotime($child->scheduled_date)),
                            'day_name' => date('l', strtotime($child->scheduled_date)),
                            'meal_type' => $child->meal_type,
                            'items_string' => $items_string,
                            'is_vegetarian' => $is_vegetarian,
                            'pickup_time' => $child->pickup_time ? $child->pickup_time : null,
                            'pickup_code' => $child->pickup_code,
                            'total_amount' => (float)$child->total_amount,
                            'formatted_amount' => '₹' . number_format($child->total_amount, 2),
                            'can_cancel' => $child_cancellation['can_cancel'],
                            'cancel_reason' => $child_cancellation['reason']
                        ];
                    }
                }

                $total_days = count($scheduled_orders);
                $amount_payable = round($booking_employee - $booking_discount - $booking_wallet, 2);
                if ($amount_payable < 0) {
                    $amount_payable = 0;
                }

                $order_data['booking_summary'] = [
                    'total_days' => $total_days,
                    'items_total' => round($booking_total, 2),
                    'company_contribution' => round($booking_company, 2),
                    'employee_contribution' => round($booking_employee, 2),
                    'discount' => round($booking_discount, 2),
                    'wallet_deducted' => round($booking_wallet, 2),
                    'online_paid' => round($amount_payable, 2),
                    'amount_payable' => $amount_payable,
                    'formatted_items_total' => '₹' . number_format($booking_total, 2),
                    'formatted_company_contribution' => '₹' . number_format($booking_company, 2),
                    'formatted_employee_contribution' => '₹' . number_format($booking_employee, 2),
                    'formatted_discount' => '₹' . number_format($booking_discount, 2),
                    'formatted_wallet_deducted' => '₹' . number_format($booking_wallet, 2),
                    'formatted_online_paid' => '₹' . number_format($amount_payable, 2),
                    'formatted_amount_payable' => '₹' . number_format($amount_payable, 2)
                ];

                $order_data['scheduled_orders'] = $scheduled_orders;
                $order_data['total_scheduled_days'] = $total_days;

                // Override pickup QR with the next active (non-completed) day's order
                // so the displayed QR rotates as each day's meal is delivered
                $active_terminal = ['COMPLETED', 'DELIVERED', 'CANCELLED', 'REJECTED'];
                $next_active = null;
                foreach ($scheduled_orders as $sched) {
                    if (!in_array(strtoupper($sched['status']), $active_terminal) && !empty($sched['pickup_code'])) {
                        $next_active = $sched;
                        break;
                    }
                }

                // Pick the day to show in the pickup QR section:
                //  - If any day is still pending (not completed/cancelled/rejected) → show that one
                //  - Otherwise (all days finished) → fall back to the LAST day's order so the
                //    user still sees a code/QR (useful for receipt/reference)
                $pickup_source = $next_active;
                if (!$pickup_source) {
                    for ($i = count($scheduled_orders) - 1; $i >= 0; $i--) {
                        if (!empty($scheduled_orders[$i]['pickup_code'])) {
                            $pickup_source = $scheduled_orders[$i];
                            break;
                        }
                    }
                }

                if ($pickup_source) {
                    $order_data['pickup_code'] = $pickup_source['pickup_code'];
                    $order_data['pickup'] = [
                        'code' => $pickup_source['pickup_code'],
                        'qr_data' => "{$pickup_source['order_number']}|{$pickup_source['pickup_code']}",
                        'order_number' => $pickup_source['order_number'],
                        'scheduled_date' => $pickup_source['scheduled_date'],
                        'formatted_date' => $pickup_source['formatted_date'],
                        'day_name' => $pickup_source['day_name'],
                        'pickup_time' => $pickup_source['pickup_time'] ?? null,
                        'ready_at' => $order->ready_at,
                        'formatted_ready_at' => $order->ready_at ? date('d M Y, h:i A', strtotime($order->ready_at)) : null
                    ];
                } else {
                    // No scheduled order with a pickup code at all
                    $order_data['pickup_code'] = null;
                    $order_data['pickup'] = [
                        'code' => null,
                        'qr_data' => null,
                        'order_number' => null,
                        'scheduled_date' => null,
                        'formatted_date' => null,
                        'day_name' => null,
                        'pickup_time' => null,
                        'ready_at' => null,
                        'formatted_ready_at' => null
                    ];
                }
            }
        }

        // Add KOT specific fields
        if ($order->module == 'KOT') {
            $order_data['delivery_location'] = $order->delivery_location_id ? [
                'id' => (int)$order->delivery_location_id,
                'name' => $order->delivery_location_name,
                'short_name' => $order->delivery_location_short_name,
                'floor' => $order->delivery_location_floor,
                'building' => $order->delivery_location_building
            ] : null;

            $order_data['department'] = $order->department_id ? [
                'id' => (int)$order->department_id,
                'name' => $order->department_name,
                'code' => $order->department_code
            ] : null;

            $order_data['policy'] = $order->policy_id ? [
                'id' => (int)$order->policy_id,
                'name' => $order->policy_name,
                'type' => $order->policy_type
            ] : null;

            $order_data['meal_type'] = $order->meal_type;
        }

        $this->logger->info('Order details fetched successfully', [
            'employee_id' => $employee_id,
            'order_id' => $order_id,
            'module' => $order->module,
            'status' => $order->status
        ], 'my_orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Order details fetched successfully',
            'data' => [
                'order' => $order_data
            ]
        ]);
    }

    /**
     * Cancel Order
     *
     * Cancels an order if eligible based on module rules.
     * - QSR/KOT: Only PENDING orders can be cancelled
     * - PREMEAL: Based on cancellation_cutoff_hours before serving time
     *
     * POST /api/v1/user/my_orders/cancel
     *
     * Body Parameters (JSON):
     * - order_id (required): Order ID to cancel
     * - reason (optional): Cancellation reason
     *
     * @return void JSON response with cancellation result
     */
    public function cancel()
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

        $order_id = $post_data['order_id'] ?? null;
        $reason = $post_data['reason'] ?? null;

        $this->logger->info('Cancel Order API called', [
            'employee_id' => $employee_id,
            'order_id' => $order_id,
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'my_orders');

        // Validate order_id
        if (empty($order_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order ID is required',
                'data' => null
            ]);
            return;
        }

        // Get order
        $order = $this->orders_model->get_order_by_id($order_id, $employee_id);

        if (empty($order)) {
            $this->logger->warning('Order not found for cancellation', [
                'employee_id' => $employee_id,
                'order_id' => $order_id
            ], 'my_orders');
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Get employee policy
        $policy = $this->orders_model->get_employee_policy($employee_id);

        // Check cancellation eligibility
        $cancellation = $this->check_cancellation_eligibility($order, $policy);

        if (!$cancellation['can_cancel']) {
            $this->logger->warning('Order cancellation not allowed', [
                'employee_id' => $employee_id,
                'order_id' => $order_id,
                'reason' => $cancellation['reason']
            ], 'my_orders');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => $cancellation['reason'],
                'data' => null
            ]);
            return;
        }

        // Cancel the order - status change, stock restore, refund and ledger
        // writes are atomic so a partial failure cannot leave a cancelled
        // order without its refund recorded
        $previous_status = $order->status;
        $refund_amount = (float)$order->employee_contribution;
        $refund_processed = false;

        $this->db->trans_start();

        try {
            $cancelled = $this->orders_model->cancel_order($order_id, $reason);

            if (!$cancelled) {
                throw new Exception('Failed to update order status');
            }

            // Add status history for audit trail
            $this->orders_model->add_status_history([
                'order_id' => $order_id,
                'from_status' => $previous_status,
                'to_status' => 'CANCELLED',
                'changed_by_type' => 'EMPLOYEE',
                'changed_by_id' => $employee_id,
                'note' => $reason ? 'Cancelled by employee: ' . $reason : 'Cancelled by employee',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // If a PREMEAL booking's primary day was cancelled, promote a new
            // primary so the remaining days stay visible to the store.
            if ($order->module == 'PREMEAL' && !empty($order->is_primary_order)) {
                $new_primary_id = $this->orders_model->promote_premeal_primary($order_id);
                if ($new_primary_id) {
                    $this->logger->info('PREMEAL primary reassigned after cancellation', [
                        'old_primary_id' => $order_id,
                        'new_primary_id' => $new_primary_id
                    ], 'my_orders');
                }
            }

            // Restore stock for cancelled order items
            $order_items = $this->orders_model->get_order_items($order_id);
            foreach ($order_items as $item) {
                $this->orders_model->restore_stock(
                    $order->store_id,
                    (int)$item->product_id,
                    (int)$item->quantity,
                    [
                        'reference_id'      => $order_id,
                        'order_number'      => $order->order_number,
                        'performed_by_type' => 'EMPLOYEE',
                        'performed_by_id'   => $employee_id,
                        'note'              => 'Stock restored on order cancellation by employee'
                    ]
                );
            }

            // Process refund to wallet - refund employee's paid amount (wallet + online), not company contribution
            if ($order->payment_status == 'PAID' && $refund_amount > 0 && $order->refund_status != 'PROCESSED') {
                // Credit amount back to wallet
                $refund_data = [
                    'user_id' => $employee_id,
                    'transaction_type' => 1, // Credit
                    'amount' => $refund_amount,
                    'transaction_label' => 'Refund for cancelled order #' . $order->order_number,
                    'order_id' => $order_id,
                    'transaction_date' => date('Y-m-d'),
                    'transaction_time' => date('Y-m-d H:i:s'),
                    'transaction_uuid' => generate_uuid()
                ];
                $this->db->insert('transaction', $refund_data);
                $wallet_transaction_id = $this->db->insert_id();

                // Record refund in order_payments (mirrors store-side refunds)
                $this->orders_model->add_order_payment([
                    'order_id' => $order_id,
                    'payment_type' => 'REFUND_CREDIT',
                    'amount' => $refund_amount,
                    'transaction_id' => $wallet_transaction_id,
                    'status' => 'SUCCESS',
                    'note' => 'Wallet refund for order cancelled by employee',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Update order refund status
                $this->db->where('id', $order_id);
                $this->db->update('orders', [
                    'refund_amount' => $refund_amount,
                    'refund_status' => 'PROCESSED',
                    'refunded_at' => date('Y-m-d H:i:s'),
                    'refund_transaction_id' => $wallet_transaction_id
                ]);

                // Log to refunds table for audit (refunded_by holds a store
                // staff id, so employee self-cancellations keep it NULL)
                $this->orders_model->log_refund([
                    'order_id' => $order_id,
                    'order_number' => $order->order_number,
                    'store_id' => $order->store_id,
                    'company_id' => $order->company_id,
                    'employee_id' => $employee_id,
                    'is_guest_order' => 0,
                    'guest_name' => null,
                    'guest_phone' => null,
                    'refund_method' => 'WALLET',
                    'amount' => $refund_amount,
                    'reason' => $reason,
                    'razorpay_payment_id' => null,
                    'razorpay_refund_id' => null,
                    'wallet_transaction_id' => $wallet_transaction_id,
                    'status' => 'PROCESSED',
                    'refunded_by' => null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $refund_processed = true;
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->logger->error('Failed to cancel order', [
                'employee_id' => $employee_id,
                'order_id' => $order_id,
                'error' => $e->getMessage()
            ], 'my_orders');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to cancel order. Please try again.',
                'data' => null
            ]);
            return;
        }

        // Send cancellation notification (after commit so a rollback cannot
        // notify about an order that was not actually cancelled)
        $this->notificationlib->orderCancelled($employee_id, $order);

        if ($refund_processed) {
            $this->logger->info('Refund processed for cancelled order', [
                'employee_id' => $employee_id,
                'order_id' => $order_id,
                'refund_amount' => $refund_amount
            ], 'my_orders');
        }

        $this->logger->info('Order cancelled successfully', [
            'employee_id' => $employee_id,
            'order_id' => $order_id,
            'module' => $order->module,
            'stock_restored' => count($order_items) . ' item(s)'
        ], 'my_orders');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => $refund_processed ? 'Order cancelled successfully. Refund credited to wallet.' : 'Order cancelled successfully',
            'data' => [
                'order_id' => (int)$order_id,
                'order_number' => $order->order_number,
                'refund_amount' => $refund_processed ? $refund_amount : 0,
                'formatted_refund' => '₹' . number_format($refund_processed ? $refund_amount : 0, 2)
            ]
        ]);
    }

    /**
     * Bulk Cancel Orders
     *
     * Cancels multiple orders at once. Each order is validated individually.
     * Useful for cancelling multiple PREMEAL scheduled dates in one call.
     *
     * POST /api/v1/user/my_orders/bulk_cancel
     *
     * Body Parameters (form-data):
     * - order_ids (required): Comma-separated order IDs (e.g. "10,11,12")
     * - reason (optional): Cancellation reason (applied to all)
     *
     * @return void JSON response with per-order results
     */
    public function bulk_cancel()
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

        $order_ids_raw = $post_data['order_ids'] ?? null;
        $reason = $post_data['reason'] ?? null;

        $this->logger->info('Bulk Cancel Orders API called', [
            'employee_id' => $employee_id,
            'order_ids' => $order_ids_raw,
            'ip' => $this->input->ip_address()
        ], 'my_orders');

        // Validate order_ids
        if (empty($order_ids_raw)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'order_ids is required',
                'data' => null
            ]);
            return;
        }

        // Parse comma-separated IDs and sanitise
        $order_ids = array_unique(array_filter(array_map(function ($id) {
            return (int) trim($id);
        }, explode(',', $order_ids_raw))));

        if (empty($order_ids)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'No valid order IDs provided',
                'data' => null
            ]);
            return;
        }

        if (count($order_ids) > 30) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Maximum 30 orders can be cancelled at once',
                'data' => null
            ]);
            return;
        }

        // Get employee policy once (for PREMEAL cutoff checks)
        $policy = $this->orders_model->get_employee_policy($employee_id);

        $cancelled_orders = [];
        $failed_orders = [];
        $total_refund = 0;

        // Wrap everything in a transaction
        $this->db->trans_start();

        try {
            foreach ($order_ids as $order_id) {
                // Get order (verify ownership)
                $order = $this->orders_model->get_order_by_id($order_id, $employee_id);

                if (empty($order)) {
                    $failed_orders[] = [
                        'order_id' => $order_id,
                        'reason' => 'Order not found'
                    ];
                    continue;
                }

                // Check cancellation eligibility
                $cancellation = $this->check_cancellation_eligibility($order, $policy);

                if (!$cancellation['can_cancel']) {
                    $failed_orders[] = [
                        'order_id' => $order_id,
                        'order_number' => $order->order_number,
                        'reason' => $cancellation['reason']
                    ];
                    continue;
                }

                // Cancel the order
                $previous_status = $order->status;
                $cancelled = $this->orders_model->cancel_order($order_id, $reason);

                if (!$cancelled) {
                    $failed_orders[] = [
                        'order_id' => $order_id,
                        'order_number' => $order->order_number,
                        'reason' => 'Failed to cancel'
                    ];
                    continue;
                }

                // Add status history
                $this->orders_model->add_status_history([
                    'order_id' => $order_id,
                    'from_status' => $previous_status,
                    'to_status' => 'CANCELLED',
                    'changed_by_type' => 'EMPLOYEE',
                    'changed_by_id' => $employee_id,
                    'note' => $reason ? 'Cancelled by employee: ' . $reason : 'Cancelled by employee',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // If a PREMEAL booking's primary day was cancelled, promote a
                // new primary so the remaining days stay visible to the store.
                if ($order->module == 'PREMEAL' && !empty($order->is_primary_order)) {
                    $this->orders_model->promote_premeal_primary($order_id);
                }

                // Restore stock
                $order_items = $this->orders_model->get_order_items($order_id);
                foreach ($order_items as $item) {
                    $this->orders_model->restore_stock(
                        $order->store_id,
                        (int)$item->product_id,
                        (int)$item->quantity,
                        [
                            'reference_id'      => $order_id,
                            'order_number'      => $order->order_number,
                            'performed_by_type' => 'EMPLOYEE',
                            'performed_by_id'   => $employee_id,
                            'note'              => 'Stock restored on bulk order cancellation by employee'
                        ]
                    );
                }

                // Send cancellation notification
                $this->notificationlib->orderCancelled($employee_id, $order);

                // Process refund
                $refund_amount = (float)$order->employee_contribution;

                if ($order->payment_status == 'PAID' && $refund_amount > 0 && $order->refund_status != 'PROCESSED') {
                    $this->db->insert('transaction', [
                        'user_id' => $employee_id,
                        'transaction_type' => 1,
                        'amount' => $refund_amount,
                        'transaction_label' => 'Refund for cancelled order #' . $order->order_number,
                        'order_id' => $order_id,
                        'transaction_date' => date('Y-m-d'),
                        'transaction_time' => date('Y-m-d H:i:s'),
                        'transaction_uuid' => generate_uuid()
                    ]);
                    $wallet_transaction_id = $this->db->insert_id();

                    // Record refund in order_payments (mirrors store-side refunds)
                    $this->orders_model->add_order_payment([
                        'order_id' => $order_id,
                        'payment_type' => 'REFUND_CREDIT',
                        'amount' => $refund_amount,
                        'transaction_id' => $wallet_transaction_id,
                        'status' => 'SUCCESS',
                        'note' => 'Wallet refund for order cancelled by employee (bulk)',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    $this->db->where('id', $order_id);
                    $this->db->update('orders', [
                        'refund_amount' => $refund_amount,
                        'refund_status' => 'PROCESSED',
                        'refunded_at' => date('Y-m-d H:i:s'),
                        'refund_transaction_id' => $wallet_transaction_id
                    ]);

                    // Log to refunds table for audit (refunded_by holds a store
                    // staff id, so employee self-cancellations keep it NULL)
                    $this->orders_model->log_refund([
                        'order_id' => $order_id,
                        'order_number' => $order->order_number,
                        'store_id' => $order->store_id,
                        'company_id' => $order->company_id,
                        'employee_id' => $employee_id,
                        'is_guest_order' => 0,
                        'guest_name' => null,
                        'guest_phone' => null,
                        'refund_method' => 'WALLET',
                        'amount' => $refund_amount,
                        'reason' => $reason,
                        'razorpay_payment_id' => null,
                        'razorpay_refund_id' => null,
                        'wallet_transaction_id' => $wallet_transaction_id,
                        'status' => 'PROCESSED',
                        'refunded_by' => null,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    $total_refund += $refund_amount;
                }

                $cancelled_orders[] = [
                    'order_id' => (int)$order_id,
                    'order_number' => $order->order_number,
                    'refund_amount' => round($refund_amount, 2)
                ];
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $this->logger->info('Bulk cancel completed', [
                'employee_id' => $employee_id,
                'cancelled' => count($cancelled_orders),
                'failed' => count($failed_orders),
                'total_refund' => $total_refund
            ], 'my_orders');

            $message = count($cancelled_orders) . ' order(s) cancelled successfully';
            if ($total_refund > 0) {
                $message .= '. ₹' . number_format($total_refund, 2) . ' refunded to wallet';
            }
            if (!empty($failed_orders)) {
                $message .= '. ' . count($failed_orders) . ' order(s) could not be cancelled';
            }

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => $message,
                'data' => [
                    'cancelled' => $cancelled_orders,
                    'failed' => $failed_orders,
                    'total_refund' => round($total_refund, 2),
                    'formatted_total_refund' => '₹' . number_format($total_refund, 2)
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->logger->error('Bulk cancel failed', [
                'employee_id' => $employee_id,
                'error' => $e->getMessage()
            ], 'my_orders');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to cancel orders. Please try again.',
                'data' => null
            ]);
        }
    }

    /**
     * Reorder - Add items from a previous order to cart
     *
     * POST /api/v1/user/my_orders/reorder
     *
     * Required parameters (form-data):
     * - order_id: Previous order ID to reorder from
     *
     * Only for QSR and KOT modules.
     * Validates store access, employee module permissions, and product availability.
     * If item already in cart with qty >= reorder qty: skip.
     * If item already in cart with qty < reorder qty: update to reorder qty.
     * If item not in cart: add with reorder qty.
     */
    public function reorder()
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

        $order_id = $this->input->post('order_id', true);

        if (empty($order_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'order_id is required',
                'data' => null
            ]);
            return;
        }

        // Get order and validate ownership
        $order = $this->common->getdatabytable('orders', [
            'id' => $order_id,
            'employee_id' => $employee_id
        ]);

        if (empty($order)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
            return;
        }

        // Only allow reorder for completed or cancelled orders
        $reorderable_statuses = ['COMPLETED', 'DELIVERED', 'CANCELLED'];
        if (!in_array($order->status, $reorderable_statuses)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Only completed or cancelled orders can be reordered',
                'data' => null
            ]);
            return;
        }

        // Only QSR and KOT modules
        $module = $order->module;
        if (!in_array($module, ['QSR', 'KOT'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Reorder is only available for QSR and KOT orders',
                'data' => null
            ]);
            return;
        }

        // Validate store still belongs to employee's company and is active
        $store = $this->common->getdatabytable('stores', [
            'id' => $order->store_id,
            'company_id' => $company_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($store)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store is no longer accessible',
                'data' => null
            ]);
            return;
        }

        // Validate employee still has access to this module
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
            return;
        }

        $has_access = false;
        if ($module == 'QSR' && $employee->qsr_access == 1) $has_access = true;
        if ($module == 'KOT' && $employee->kot_permission == 1) $has_access = true;

        if (!$has_access) {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'You do not have access to ' . $module . ' module',
                'data' => null
            ]);
            return;
        }

        // Get order items
        $order_items = $this->orders_model->get_order_items($order_id);

        if (empty($order_items)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'No items found in this order',
                'data' => null
            ]);
            return;
        }

        $added = [];
        $updated = [];
        $skipped = [];
        $unavailable = [];

        foreach ($order_items as $item) {
            // Validate product is still available in this store/module
            $product = $this->validate_reorder_product($item->product_id, $order->store_id, $module);

            if (!$product) {
                $unavailable[] = [
                    'product_id' => (int)$item->product_id,
                    'name' => $item->product_name
                ];
                continue;
            }

            $reorder_qty = (int)$item->quantity;

            // Check stock availability
            if ($product->available_stock !== null && (int)$product->available_stock < $reorder_qty) {
                if ((int)$product->available_stock == 0) {
                    $unavailable[] = [
                        'product_id' => (int)$item->product_id,
                        'name' => $item->product_name,
                        'reason' => 'Out of stock'
                    ];
                    continue;
                }
                $reorder_qty = (int)$product->available_stock;
            }

            // Check if item already in cart
            $existing = $this->cart_model->get_existing_cart($employee_id, $order->store_id, $item->product_id, $module);

            if ($existing) {
                $cart_qty = (int)$existing->quantity;
                if ($reorder_qty <= $cart_qty) {
                    $skipped[] = [
                        'product_id' => (int)$item->product_id,
                        'name' => $item->product_name,
                        'cart_qty' => $cart_qty,
                        'reorder_qty' => (int)$item->quantity
                    ];
                } else {
                    $this->cart_model->update_quantity($existing->id, $reorder_qty);
                    $updated[] = [
                        'product_id' => (int)$item->product_id,
                        'name' => $item->product_name,
                        'old_qty' => $cart_qty,
                        'new_qty' => $reorder_qty
                    ];
                }
            } else {
                $cart_data = [
                    'employee_id' => $employee_id,
                    'store_id' => $order->store_id,
                    'product_id' => $item->product_id,
                    'quantity' => $reorder_qty,
                    'module' => $module,
                    'note' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->cart_model->add_to_cart($cart_data);
                $added[] = [
                    'product_id' => (int)$item->product_id,
                    'name' => $item->product_name,
                    'quantity' => $reorder_qty
                ];
            }
        }

        $cart_count = $this->cart_model->get_cart_count($employee_id, $order->store_id, $module);

        $this->logger->info('Reorder processed', [
            'employee_id' => $employee_id,
            'order_id' => $order_id,
            'module' => $module,
            'added' => count($added),
            'updated' => count($updated),
            'skipped' => count($skipped),
            'unavailable' => count($unavailable)
        ], 'my_orders');

        $total_processed = count($added) + count($updated);
        $parts = [];
        if ($total_processed > 0) $parts[] = $total_processed . ' item(s) added to cart';
        if (count($skipped) > 0) $parts[] = count($skipped) . ' item(s) already in cart';
        if (count($unavailable) > 0) $parts[] = count($unavailable) . ' item(s) unavailable';

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => !empty($parts) ? implode(', ', $parts) : 'No items could be added to cart',
            'data' => [
                'store_id' => (int)$order->store_id,
                'module' => $module,
                'cart_items_count' => (int)$cart_count,
                'added' => $added,
                'updated' => $updated,
                'skipped' => $skipped,
                'unavailable' => $unavailable
            ]
        ]);
    }

    /**
     * Validate product for reorder (silent - no error output)
     *
     * @param int    $product_id Product ID
     * @param int    $store_id   Store ID
     * @param string $module     Module type
     * @return object|bool Product object or false
     */
    private function validate_reorder_product($product_id, $store_id, $module)
    {
        $product = $this->common->getdatabytable('products', [
            'id' => $product_id,
            'is_active' => 1,
            'is_available' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($product)) return false;

        if ($module == 'QSR' && $product->qsr_enabled != 1) return false;
        if ($module == 'KOT' && $product->kot_enabled != 1) return false;

        $store_product = $this->common->getdatabytable('store_products', [
            'store_id' => $store_id,
            'product_id' => $product_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($store_product)) return false;

        $product->store_price = $store_product->price;
        $product->available_stock = $store_product->available_stock;

        return $product;
    }
}
