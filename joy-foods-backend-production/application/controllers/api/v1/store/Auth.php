<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Store Staff Authentication Controller
 *
 * Handles authentication for store staff members (cashiers, managers, kitchen staff).
 * Store staff login using store_code, email, and password.
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
class Auth extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('StoreStaff_model', 'staff_model');
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
     * Validate email format
     *
     * @param string $email Email address
     * @return bool True if valid
     */
    private function is_valid_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Store Staff Login
     *
     * Authenticates a store staff member using store_code, email, and password.
     * Returns a JWT token on successful authentication.
     *
     * @api POST /api/v1/store/auth/login
     *
     * @param string store_code The store code (e.g., JOY-001)
     * @param string email      The staff member's email address
     * @param string password   The staff member's password
     *
     * @return void Outputs JSON response
     *         - 200: Login successful with JWT token and staff details
     *         - 400: Missing required fields or invalid email format
     *         - 401: Invalid credentials
     *         - 403: Store or account is inactive
     */
    public function login()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Store Staff Login API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'store_auth');

        $post_data = $this->input->post(null, true);

        // Validate required fields
        if (empty($post_data['store_code']) || empty($post_data['email']) || empty($post_data['password'])) {
            $this->logger->warning('Missing required fields for store staff login', [
                'has_store_code' => !empty($post_data['store_code']),
                'has_email' => !empty($post_data['email']),
                'has_password' => !empty($post_data['password'])
            ], 'store_auth');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store code, email and password are required',
                'data' => null
            ]);
            return;
        }

        $store_code = strtoupper(trim($post_data['store_code']));
        $email = trim($post_data['email']);
        $password = $post_data['password'];

        // Validate email format
        if (!$this->is_valid_email($email)) {
            $this->logger->warning('Invalid email format for store staff login', ['email' => $email], 'store_auth');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid email format',
                'data' => null
            ]);
            return;
        }

        // Find store by code
        $store = $this->staff_model->get_store_by_code($store_code);

        if (empty($store)) {
            $this->logger->warning('Store not found', ['store_code' => $store_code], 'store_auth');
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid store code or credentials',
                'data' => null
            ]);
            return;
        }

        // Check if store is active
        if (!$store->is_active) {
            $this->logger->warning('Store is inactive', ['store_id' => $store->id, 'store_code' => $store_code], 'store_auth');
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'This store is currently inactive. Please contact your administrator.',
                'data' => null
            ]);
            return;
        }

        // Find staff member by email and store
        $staff = $this->staff_model->get_staff_for_login($store->id, $email);

        if (empty($staff)) {
            $this->logger->warning('Staff member not found', [
                'store_id' => $store->id,
                'email' => $email
            ], 'store_auth');
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid store code or credentials',
                'data' => null
            ]);
            return;
        }

        // Verify password
        if (!password_verify($password, $staff->password_hash)) {
            $this->logger->warning('Invalid password for store staff', [
                'store_id' => $store->id,
                'staff_id' => $staff->id,
                'email' => $email
            ], 'store_auth');
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid store code or credentials',
                'data' => null
            ]);
            return;
        }

        // Check if staff account is active
        if (!$staff->is_active) {
            $this->logger->warning('Staff account is inactive', [
                'staff_id' => $staff->id,
                'email' => $email
            ], 'store_auth');
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Your account is inactive. Please contact your administrator.',
                'data' => null
            ]);
            return;
        }

        // Get client_id from store for token
        $client_id = $store->client_id;

        // Generate JWT token
        $token_payload = [
            'staff_id' => $staff->id,
            'store_id' => $store->id,
            'client_id' => $client_id,
            'email' => $staff->email,
            'role' => 'store_staff',
            'staff_role' => $staff->role,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24 * 7), // 7 days expiry
        ];

        $token = $this->tokenHandler->GenerateToken($token_payload);

        // Update last login timestamp
        $this->staff_model->update_last_login($staff->id, $this->input->ip_address());

        $this->logger->info('Store staff logged in successfully', [
            'staff_id' => $staff->id,
            'store_id' => $store->id,
            'email' => $email
        ], 'store_auth');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'staff' => [
                    'id' => (int)$staff->id,
                    'staff_code' => $staff->staff_code,
                    'first_name' => $staff->first_name,
                    'last_name' => $staff->last_name,
                    'email' => $staff->email,
                    'phone' => $staff->phone,
                    'role' => $staff->role
                ],
                'store' => [
                    'id' => (int)$store->id,
                    'store_code' => $store->store_code,
                    'name' => $store->name,
                    'short_name' => $store->short_name,
                    'store_type' => $store->store_type,
                    'is_operational' => (bool)$store->is_operational
                ]
            ]
        ]);
    }

    /**
     * Verify Store Staff Session
     *
     * Validates the current session by checking the JWT token,
     * staff status, and store status.
     *
     * @api POST /api/v1/store/auth/verify_session
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @return void Outputs JSON response
     *         - 200: Session is valid with staff and store details
     *         - 401: Token is required
     *         - 401: Invalid or expired token
     *         - 401: Staff not found or inactive
     *         - 401: Store not found or inactive
     */
    public function verify_session()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Store Staff Verify Session API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'store_auth');

        // Get Bearer token
        $token = $this->check_bearer_token();

        if (empty($token)) {
            $this->logger->warning('No token provided for session verification', [], 'store_auth');
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Token is required',
                'data' => null
            ]);
            return;
        }

        // Decode token
        $decoded = $this->decode_token($token);

        if (empty($decoded)) {
            $this->logger->warning('Invalid or expired token', [], 'store_auth');
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid or expired token',
                'data' => null
            ]);
            return;
        }

        // Check token expiry
        if (isset($decoded->exp) && $decoded->exp < time()) {
            $this->logger->warning('Token expired', ['staff_id' => $decoded->staff_id ?? null], 'store_auth');
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Token has expired',
                'data' => null
            ]);
            return;
        }

        // Validate role
        if (!isset($decoded->role) || $decoded->role !== 'store_staff') {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Access denied. Invalid role',
                'data' => null
            ]);
            return;
        }

        // Check if staff exists and is active
        $staff = $this->staff_model->get_by_id($decoded->staff_id);

        if (empty($staff)) {
            $this->logger->warning('Staff not found', ['staff_id' => $decoded->staff_id], 'store_auth');
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Staff account not found',
                'data' => null
            ]);
            return;
        }

        if (!$staff->is_active) {
            $this->logger->warning('Staff account is inactive', ['staff_id' => $decoded->staff_id], 'store_auth');
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Staff account is inactive',
                'data' => null
            ]);
            return;
        }

        // Check if store exists and is active
        $store = $this->staff_model->get_store_by_id($decoded->store_id);

        if (empty($store)) {
            $this->logger->warning('Store not found', ['store_id' => $decoded->store_id], 'store_auth');
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Store not found',
                'data' => null
            ]);
            return;
        }

        if (!$store->is_active) {
            $this->logger->warning('Store is inactive', ['store_id' => $decoded->store_id], 'store_auth');
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Store is inactive',
                'data' => null
            ]);
            return;
        }

        $this->logger->info('Store staff session verified successfully', [
            'staff_id' => $staff->id,
            'store_id' => $store->id
        ], 'store_auth');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Session is valid',
            'data' => [
                'staff' => [
                    'id' => (int)$staff->id,
                    'staff_code' => $staff->staff_code,
                    'first_name' => $staff->first_name,
                    'last_name' => $staff->last_name,
                    'email' => $staff->email,
                    'phone' => $staff->phone,
                    'role' => $staff->role
                ],
                'store' => [
                    'id' => (int)$store->id,
                    'store_code' => $store->store_code,
                    'name' => $store->name,
                    'short_name' => $store->short_name,
                    'store_type' => $store->store_type,
                    'is_operational' => (bool)$store->is_operational,
                    'breakfast_time' => $store->breakfast_time ? substr($store->breakfast_time, 0, 5) : null,
                    'lunch_time' => $store->lunch_time ? substr($store->lunch_time, 0, 5) : null,
                    'dinner_time' => $store->dinner_time ? substr($store->dinner_time, 0, 5) : null
                ]
            ]
        ]);
    }

    /**
     * Change Password
     *
     * Allows store staff to change their password.
     *
     * @api POST /api/v1/store/auth/change_password
     *
     * @header Authorization Bearer <token> The JWT token from login
     * @param string current_password  Current password
     * @param string new_password      New password (min 6 characters)
     * @param string confirm_password  Confirmation of new password
     *
     * @return void Outputs JSON response
     *         - 200: Password changed successfully
     *         - 400: Missing required fields or validation errors
     *         - 401: Invalid current password
     */
    public function change_password()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Store Staff Change Password API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'store_auth');

        // Get Bearer token
        $token = $this->check_bearer_token();

        if (empty($token)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Token is required',
                'data' => null
            ]);
            return;
        }

        // Decode token
        $decoded = $this->decode_token($token);

        if (empty($decoded) || !isset($decoded->staff_id)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid or expired token',
                'data' => null
            ]);
            return;
        }

        // Validate role
        if (!isset($decoded->role) || $decoded->role !== 'store_staff') {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Access denied',
                'data' => null
            ]);
            return;
        }

        $post_data = $this->input->post(null, true);

        // Validate required fields
        if (empty($post_data['current_password']) || empty($post_data['new_password'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Current password and new password are required',
                'data' => null
            ]);
            return;
        }

        $current_password = $post_data['current_password'];
        $new_password = $post_data['new_password'];
        $confirm_password = isset($post_data['confirm_password']) ? $post_data['confirm_password'] : $new_password;

        // Validate new password length
        if (strlen($new_password) < 6) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'New password must be at least 6 characters',
                'data' => null
            ]);
            return;
        }

        // Validate password match
        if ($new_password !== $confirm_password) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'New passwords do not match',
                'data' => null
            ]);
            return;
        }

        // Get staff
        $staff = $this->staff_model->get_password_hash($decoded->staff_id);

        if (empty($staff)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Staff account not found',
                'data' => null
            ]);
            return;
        }

        // Verify current password
        if (!password_verify($current_password, $staff->password_hash)) {
            $this->logger->warning('Invalid current password for change password', [
                'staff_id' => $staff->id
            ], 'store_auth');
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Current password is incorrect',
                'data' => null
            ]);
            return;
        }

        // Update password
        $this->staff_model->update_password($staff->id, password_hash($new_password, PASSWORD_DEFAULT));

        $this->logger->info('Store staff password changed successfully', [
            'staff_id' => $staff->id
        ], 'store_auth');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Password changed successfully',
            'data' => null
        ]);
    }

    /**
     * Get Profile
     *
     * Returns the current staff member's profile with store details.
     *
     * @api POST /api/v1/store/auth/profile
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @return void Outputs JSON response
     *         - 200: Profile data with staff and store details
     *         - 401: Unauthorized
     */
    public function profile()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Store Staff Profile API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'store_auth');

        // Get Bearer token
        $token = $this->check_bearer_token();

        if (empty($token)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Token is required',
                'data' => null
            ]);
            return;
        }

        // Decode token
        $decoded = $this->decode_token($token);

        if (empty($decoded) || !isset($decoded->staff_id)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid or expired token',
                'data' => null
            ]);
            return;
        }

        // Check token expiry
        if (isset($decoded->exp) && $decoded->exp < time()) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Token has expired',
                'data' => null
            ]);
            return;
        }

        // Validate role
        if (!isset($decoded->role) || $decoded->role !== 'store_staff') {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Access denied. Invalid role',
                'data' => null
            ]);
            return;
        }

        // Get staff details
        $staff = $this->staff_model->get_by_id($decoded->staff_id);

        if (empty($staff)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Staff account not found',
                'data' => null
            ]);
            return;
        }

        if (!$staff->is_active) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Staff account is inactive',
                'data' => null
            ]);
            return;
        }

        // Get store details
        $store = $this->staff_model->get_store_by_id($decoded->store_id);

        if (empty($store)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Store not found',
                'data' => null
            ]);
            return;
        }

        $this->logger->info('Store staff profile fetched', [
            'staff_id' => $staff->id,
            'store_id' => $store->id
        ], 'store_auth');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Profile fetched successfully',
            'data' => [
                'staff' => [
                    'id' => (int)$staff->id,
                    'staff_code' => $staff->staff_code,
                    'first_name' => $staff->first_name,
                    'last_name' => $staff->last_name,
                    'email' => $staff->email,
                    'phone' => $staff->phone,
                    'role' => $staff->role,
                    'last_login_at' => $staff->last_login_at
                ],
                'store' => [
                    'id' => (int)$store->id,
                    'store_code' => $store->store_code,
                    'name' => $store->name,
                    'short_name' => $store->short_name,
                    'store_type' => $store->store_type,
                    'is_operational' => (bool)$store->is_operational,
                    'address' => trim(implode(', ', array_filter([
                        $store->address_line1,
                        $store->address_line2,
                        $store->city,
                        $store->state,
                        $store->pincode
                    ]))),
                    'breakfast_time' => $store->breakfast_time ? substr($store->breakfast_time, 0, 5) : null,
                    'lunch_time' => $store->lunch_time ? substr($store->lunch_time, 0, 5) : null,
                    'dinner_time' => $store->dinner_time ? substr($store->dinner_time, 0, 5) : null
                ]
            ]
        ]);
    }

    /**
     * Logout
     *
     * Logs out the store staff member.
     * Note: JWT tokens are stateless, so this is mainly for logging purposes.
     *
     * @api POST /api/v1/store/auth/logout
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @return void Outputs JSON response
     *         - 200: Logout successful
     */
    public function logout()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        // Get Bearer token
        $token = $this->check_bearer_token();

        if (!empty($token)) {
            $decoded = $this->decode_token($token);
            if (!empty($decoded) && isset($decoded->staff_id)) {
                $this->logger->info('Store staff logged out', [
                    'staff_id' => $decoded->staff_id,
                    'store_id' => $decoded->store_id ?? null
                ], 'store_auth');
            }
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Logout successful',
            'data' => null
        ]);
    }
}
