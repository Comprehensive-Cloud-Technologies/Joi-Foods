<?php
//Jai Sree Ram
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Stores API Controller
 *
 * Handles store operations for employees including listing stores
 * based on employee's company and module access permissions.
 *
 * @category  Controllers
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2025-12-28
 */
class Stores extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Stores_model', 'stores');
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
     * Get Employee Stores
     *
     * Returns list of stores based on employee's company and module access permissions.
     * Employees only see stores they have access to based on their QSR, KOT, and PREMEAL permissions.
     *
     * @api GET /api/v1/user/stores/get_stores
     *
     * @header Authorization Bearer {token} - JWT token from login
     *
     * @return void Outputs JSON response
     *         - 200: Success with stores list
     *         - 401: Unauthorized (missing or invalid token)
     *         - 404: No stores found
     */
    public function get_stores()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Get Stores API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'stores');

        // Authenticate and get decoded token
        $decoded = $this->authenticate();
        if (!$decoded) {
            return; // authenticate() already sent error response
        }

        // Get employee details from token
        $employee_id = $decoded->employee_id;
        $company_id = $decoded->company_id;

        $this->logger->info('Token decoded successfully', [
            'employee_id' => $employee_id,
            'company_id' => $company_id
        ], 'stores');

        // Get employee data to check permissions
        $employee = $this->common->getdatabytable('employees', ['id' => $employee_id, 'is_active' => 1]);

        if (empty($employee)) {
            $this->logger->warning('Employee not found', ['employee_id' => $employee_id], 'stores');
            $this->output([
                'status' => 404,
                'message' => 'Employee not found'
            ]);
            return;
        }

        // Build store type filter based on employee permissions
        $allowed_store_types = [];
        if ($employee->qsr_access == 1) {
            $allowed_store_types[] = 'QSR';
        }
        if ($employee->kot_permission == 1) {
            $allowed_store_types[] = 'KOT';
        }
        if ($employee->premeal_access == 1) {
            $allowed_store_types[] = 'PREMEAL';
        }

        if (empty($allowed_store_types)) {
            $this->logger->info('Employee has no store access permissions', [
                'employee_id' => $employee_id
            ], 'stores');
            $this->output([
                'status' => 200,
                'message' => 'No store access permissions',
                'data' => [
                    'stores' => []
                ]
            ]);
            return;
        }

        $this->logger->info('Employee permissions', [
            'employee_id' => $employee_id,
            'allowed_store_types' => $allowed_store_types
        ], 'stores');

        // Get stores based on company and permissions
        $stores = $this->stores->get_stores_by_permissions($company_id, $allowed_store_types);

        if (empty($stores)) {
            $this->logger->info('No stores found for employee', [
                'employee_id' => $employee_id,
                'company_id' => $company_id,
                'allowed_types' => $allowed_store_types
            ], 'stores');
            $this->output([
                'status' => 404,
                'message' => 'No stores found'
            ]);
            return;
        }

        // Format store data
        $stores_data = [];
        foreach ($stores as $store) {
            $stores_data[] = [
                'id' => (int)$store->id,
                'store_code' => $store->store_code,
                'name' => $store->name,
                'short_name' => $store->short_name,
                'store_type' => $store->store_type,
                'thumbnail' => $store->thumbnail ? base_url($store->thumbnail) : null,
                'primary_email' => $store->primary_email,
                'primary_phone' => $store->primary_phone,
                'address' => [
                    'line1' => $store->address_line1,
                    'city' => $store->city,
                    'state' => $store->state
                ],
                'is_operational' => (bool)$store->is_operational
            ];
        }

        $this->logger->info('Stores fetched successfully', [
            'employee_id' => $employee_id,
            'stores_count' => count($stores_data)
        ], 'stores');

        $this->output([
            'status' => 200,
            'message' => 'Stores fetched successfully',
            'data' => [
                'stores' => $stores_data,
                'total_count' => count($stores_data)
            ]
        ]);
    }

    /**
     * Validate Store by Code
     *
     * Validates a store code belongs to the employee's company and the employee
     * has permission to access that store's module type.
     *
     * @api POST /api/v1/user/stores/validate_store
     *
     * @header Authorization Bearer {token} - JWT token from login
     * @param string store_code - Store code to validate
     *
     * @return void Outputs JSON response with store data or error
     */
    public function validate_store()
    {
        if (!$this->check_auth()) {
            return;
        }

        $decoded = $this->authenticate();
        if (!$decoded) {
            return;
        }

        $employee_id = $decoded->employee_id;
        $company_id = $decoded->company_id;

        $store_code = $this->input->post('store_code', true);

        if (empty($store_code)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store code is required'
            ]);
            return;
        }

        // Get store by code
        $store = $this->stores->get_store_by_code($store_code);

        if (empty($store)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Store not found'
            ]);
            return;
        }

        // Check store belongs to employee's company
        if ($store->company_id != $company_id) {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'This store does not belong to your company'
            ]);
            return;
        }

        // Check store is active
        if (!$store->is_active) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'This store is currently inactive'
            ]);
            return;
        }

        // Check employee has permission for this store's module
        $employee = $this->common->getdatabytable('employees', ['id' => $employee_id, 'is_active' => 1]);

        if (empty($employee)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Employee not found'
            ]);
            return;
        }

        $has_permission = false;
        $store_type = $store->store_type;

        if ($store_type == 'QSR' && $employee->qsr_access == 1) {
            $has_permission = true;
        } elseif ($store_type == 'KOT' && $employee->kot_permission == 1) {
            $has_permission = true;
        } elseif ($store_type == 'PREMEAL' && $employee->premeal_access == 1) {
            $has_permission = true;
        }

        if (!$has_permission) {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'You do not have access to this store module'
            ]);
            return;
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Store validated successfully',
            'data' => [
                'store' => [
                    'id' => (int)$store->id,
                    'store_code' => $store->store_code,
                    'name' => $store->name,
                    'short_name' => $store->short_name,
                    'store_type' => $store->store_type,
                    'thumbnail' => $store->thumbnail ? base_url($store->thumbnail) : null,
                    'primary_email' => $store->primary_email,
                    'primary_phone' => $store->primary_phone,
                    'address' => [
                        'line1' => $store->address_line1,
                        'city' => $store->city,
                        'state' => $store->state
                    ],
                    'is_operational' => (bool)$store->is_operational
                ]
            ]
        ]);
    }
}
