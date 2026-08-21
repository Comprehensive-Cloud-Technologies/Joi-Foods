<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Store Schedule Controller
 *
 * Handles premeal schedule management for store staff.
 * Allows viewing products, schedule details, and updating schedules.
 *
 * @category  Controllers
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-12
 */
class Schedule extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    // All days of week
    private $days = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('StoreSchedule_model', 'schedule_model');
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
     * Get Premeal Products
     *
     * Returns list of premeal-enabled products assigned to this store.
     *
     * POST /api/v1/store/schedule/products
     *
     * @param int category_id (optional) Filter by category ID
     * @param int page (optional) Page number (default: 1)
     * @param int per_page (optional) Items per page (default: 50, max: 100)
     *
     * @return void JSON response with premeal products
     */
    public function products()
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
        $client_id = $auth->client_id;

        $this->logger->info('Store Schedule Products API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_schedule');

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
        $products = $this->schedule_model->get_premeal_products($store_id, $category_id, $per_page, $offset);
        $total_count = $this->schedule_model->count_premeal_products($store_id, $category_id);

        // Format products
        $products_data = [];
        foreach ($products as $product) {
            // Check if product has any schedule
            $schedules = $this->schedule_model->get_product_schedule($store_id, $product->id);
            $scheduled_days = array_map(function($s) { return $s->day_of_week; }, $schedules);

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
                'is_active' => (bool)$product->store_is_active,
                'scheduled_days' => $scheduled_days,
                'schedule_count' => count($scheduled_days)
            ];
        }

        // Pagination info
        $total_pages = $total_count > 0 ? ceil($total_count / $per_page) : 0;

        $this->logger->info('Premeal products fetched', [
            'store_id' => $store_id,
            'category_id' => $category_id,
            'count' => count($products_data)
        ], 'store_schedule');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($products_data) ? 'No premeal products found' : 'Products fetched successfully',
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
     * Get Premeal Categories
     *
     * Returns list of categories that have premeal products assigned to this store.
     *
     * POST /api/v1/store/schedule/categories
     *
     * @return void JSON response with categories
     */
    public function categories()
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

        $this->logger->info('Store Schedule Categories API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_schedule');

        // Get categories
        $categories = $this->schedule_model->get_premeal_categories($store_id);

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

        $this->logger->info('Premeal categories fetched', [
            'store_id' => $store_id,
            'count' => count($categories_data)
        ], 'store_schedule');

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
     * Get Schedule Details
     *
     * Returns schedule details for a product for all 7 days.
     * Days without schedule will have null values.
     *
     * POST /api/v1/store/schedule/details
     *
     * @param int product_id (required) Product ID
     *
     * @return void JSON response with schedule for all days
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
        $client_id = $auth->client_id;

        $this->logger->info('Store Schedule Details API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_schedule');

        // Get parameters
        $post_data = $this->input->post(null, true);
        $product_id = isset($post_data['product_id']) ? (int)$post_data['product_id'] : null;

        if (empty($product_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'product_id is required',
                'data' => null
            ]);
            return;
        }

        // Check if product exists for this store
        $product = $this->schedule_model->get_product($product_id, $store_id);

        if (empty($product)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Product not found or not available for premeal',
                'data' => null
            ]);
            return;
        }

        // Get existing schedules
        $schedules = $this->schedule_model->get_product_schedule($store_id, $product_id);

        // Convert to associative array by day
        $schedule_by_day = [];
        foreach ($schedules as $schedule) {
            $schedule_by_day[$schedule->day_of_week] = $schedule;
        }

        // Build response for all 7 days
        $days_data = [];
        foreach ($this->days as $day) {
            if (isset($schedule_by_day[$day])) {
                $s = $schedule_by_day[$day];
                $days_data[] = [
                    'day' => $day,
                    'has_schedule' => true,
                    'schedule_id' => (int)$s->id,
                    'display_order' => (int)$s->display_order,
                    'menu_json' => $s->menu_json ? json_decode($s->menu_json, true) : null,
                    'is_active' => (bool)$s->is_active,
                    'created_at' => $s->created_at,
                    'updated_at' => $s->updated_at
                ];
            } else {
                $days_data[] = [
                    'day' => $day,
                    'has_schedule' => false,
                    'schedule_id' => null,
                    'display_order' => 0,
                    'menu_json' => null,
                    'is_active' => false,
                    'created_at' => null,
                    'updated_at' => null
                ];
            }
        }

        // Count scheduled days
        $scheduled_count = count(array_filter($days_data, function($d) { return $d['has_schedule']; }));

        $this->logger->info('Schedule details fetched', [
            'product_id' => $product_id,
            'store_id' => $store_id,
            'scheduled_days' => $scheduled_count
        ], 'store_schedule');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Schedule details fetched successfully',
            'data' => [
                'product' => [
                    'id' => (int)$product->id,
                    'name' => $product->name,
                    'short_name' => $product->short_name,
                    'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                    'is_vegetarian' => (bool)$product->is_vegetarian,
                    'category' => [
                        'id' => $product->category_id ? (int)$product->category_id : null,
                        'name' => $product->category_name
                    ],
                    'price' => (float)$product->price
                ],
                'schedule' => $days_data,
                'summary' => [
                    'total_scheduled_days' => $scheduled_count,
                    'scheduled_days' => array_values(array_filter(array_map(function($d) {
                        return $d['has_schedule'] ? $d['day'] : null;
                    }, $days_data)))
                ]
            ]
        ]);
    }

    /**
     * Update Schedule
     *
     * Updates schedule for a product for all 7 days.
     * Pass schedule data for each day - days with empty/null will be removed.
     *
     * POST /api/v1/store/schedule/update
     *
     * @param int   product_id (required) Product ID
     * @param array schedule (required) Array of day schedules:
     *              [
     *                  { "day": "MONDAY", "is_active": true, "display_order": 1, "menu_json": {...} },
     *                  { "day": "TUESDAY", "is_active": false },  // Will be removed
     *                  ...
     *              ]
     *
     * @return void JSON response
     */
    public function update()
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
        $client_id = $auth->client_id;

        $this->logger->info('Store Schedule Update API called', [
            'staff_id' => $staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_schedule');

        // Get JSON input
        $json_input = file_get_contents('php://input');
        $post_data = json_decode($json_input, true);

        if (empty($post_data)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid JSON input',
                'data' => null
            ]);
            return;
        }

        $product_id = isset($post_data['product_id']) ? (int)$post_data['product_id'] : null;
        $schedule_input = isset($post_data['schedule']) ? $post_data['schedule'] : null;

        if (empty($product_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'product_id is required',
                'data' => null
            ]);
            return;
        }

        if (empty($schedule_input) || !is_array($schedule_input)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'schedule array is required',
                'data' => null
            ]);
            return;
        }

        // Check if product exists for this store
        $product = $this->schedule_model->get_product($product_id, $store_id);

        if (empty($product)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Product not found or not available for premeal',
                'data' => null
            ]);
            return;
        }

        // Get store for client_id
        $store = $this->schedule_model->get_store($store_id);
        if (empty($store)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Store not found',
                'data' => null
            ]);
            return;
        }

        // Parse schedule input into associative array by day
        $schedule_by_day = [];
        foreach ($schedule_input as $item) {
            if (isset($item['day'])) {
                $day = strtoupper(trim($item['day']));
                if (in_array($day, $this->days)) {
                    $schedule_by_day[$day] = $item;
                }
            }
        }

        // Get existing schedules
        $existing_schedules = $this->schedule_model->get_product_schedule($store_id, $product_id);
        $existing_by_day = [];
        foreach ($existing_schedules as $s) {
            $existing_by_day[$s->day_of_week] = $s;
        }

        // Process each day
        $updated_days = [];
        $added_days = [];
        $removed_days = [];

        $this->db->trans_start();

        foreach ($this->days as $day) {
            $has_input = isset($schedule_by_day[$day]);
            $has_existing = isset($existing_by_day[$day]);

            if ($has_input) {
                $input = $schedule_by_day[$day];
                $is_active = isset($input['is_active']) ? (bool)$input['is_active'] : true;
                $display_order = isset($input['display_order']) ? (int)$input['display_order'] : 0;
                $menu_json = isset($input['menu_json']) ? $input['menu_json'] : null;

                // If is_active is false and no existing schedule, skip
                if (!$is_active && !$has_existing) {
                    continue;
                }

                // If is_active is false and has existing, soft delete
                if (!$is_active && $has_existing) {
                    $this->schedule_model->delete_schedule($existing_by_day[$day]->id);
                    $removed_days[] = $day;
                    continue;
                }

                // Prepare data
                $schedule_data = [
                    'display_order' => $display_order,
                    'menu_json' => is_array($menu_json) ? json_encode($menu_json) : $menu_json,
                    'is_active' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $staff_id
                ];

                if ($has_existing) {
                    // Update existing
                    $this->schedule_model->update_schedule($existing_by_day[$day]->id, $schedule_data);
                    $updated_days[] = $day;
                } else {
                    // Add new
                    $schedule_data['client_id'] = $store->client_id;
                    $schedule_data['store_id'] = $store_id;
                    $schedule_data['product_id'] = $product_id;
                    $schedule_data['day_of_week'] = $day;
                    $schedule_data['created_at'] = date('Y-m-d H:i:s');
                    $schedule_data['created_by'] = $staff_id;

                    $this->schedule_model->add_schedule($schedule_data);
                    $added_days[] = $day;
                }
            } else {
                // No input for this day - if exists, soft delete
                if ($has_existing) {
                    $this->schedule_model->delete_schedule($existing_by_day[$day]->id);
                    $removed_days[] = $day;
                }
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->logger->error('Schedule update failed', [
                'product_id' => $product_id,
                'store_id' => $store_id
            ], 'store_schedule');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update schedule. Please try again.',
                'data' => null
            ]);
            return;
        }

        // Get updated schedule
        $final_schedules = $this->schedule_model->get_product_schedule($store_id, $product_id);
        $scheduled_days = array_map(function($s) { return $s->day_of_week; }, $final_schedules);

        $this->logger->info('Schedule updated successfully', [
            'product_id' => $product_id,
            'store_id' => $store_id,
            'added' => $added_days,
            'updated' => $updated_days,
            'removed' => $removed_days
        ], 'store_schedule');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Schedule updated successfully',
            'data' => [
                'product_id' => $product_id,
                'product_name' => $product->name,
                'changes' => [
                    'added' => $added_days,
                    'updated' => $updated_days,
                    'removed' => $removed_days
                ],
                'current_schedule' => $scheduled_days,
                'total_scheduled_days' => count($scheduled_days)
            ]
        ]);
    }
}
