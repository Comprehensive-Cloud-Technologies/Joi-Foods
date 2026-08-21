<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Profile API Controller
 *
 * Handles employee profile operations including viewing profile,
 * updating profile, and wallet management.
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
class Profile extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Profile_model', 'profile_model');
        $this->load->helper('common');
        $this->load->library('RazorpayLib');
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
     * Generate avatar URL using UI Avatars API
     *
     * @param string $name Full name for avatar
     * @return string Avatar URL
     */
    private function generate_avatar_url($name)
    {
        $encoded_name = urlencode($name);
        return "https://ui-avatars.com/api/?bold=true&background=BD3839&name={$encoded_name}&rounded=true&color=fff&size=200";
    }

    /**
     * Get My Profile
     *
     * Returns the authenticated employee's profile details including
     * personal info, company info, and wallet balance.
     *
     * GET /api/v1/user/profile/my_profile
     *
     * @return void JSON response with profile details
     */
    public function my_profile()
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

        $this->logger->info('Get My Profile API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'profile');

        // Get employee profile with company details
        $employee = $this->profile_model->get_employee_profile($employee_id);

        if (empty($employee)) {
            $this->logger->warning('Employee not found', [
                'employee_id' => $employee_id
            ], 'profile');
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Employee not found',
                'data' => null
            ]);
            return;
        }

        // Get wallet balance
        $wallet = getWalletMoneyArray($employee_id);
        $available_balance = round((float)$wallet['available'], 2);

        // Build full name
        $full_name = trim($employee->first_name . ' ' . ($employee->last_name ?? ''));
        $display_name = $employee->display_name ?? $full_name;

        // Get profile picture or generate avatar
        $profile_picture = null;
        if (!empty($employee->profile_picture_url)) {
            // Check if it's a relative path or absolute URL
            if (filter_var($employee->profile_picture_url, FILTER_VALIDATE_URL)) {
                $profile_picture = $employee->profile_picture_url;
            } else {
                $profile_picture = base_url($employee->profile_picture_url);
            }
        } else {
            // Generate avatar using UI Avatars
            $profile_picture = $this->generate_avatar_url($display_name);
        }

        // Format profile data
        $profile_data = [
            'id' => (int)$employee->id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'full_name' => $full_name,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'profile_picture' => $profile_picture,
            'wallet' => [
                'available_balance' => $available_balance,
                'formatted_balance' => '₹' . number_format($available_balance, 2)
            ]
        ];

        $this->logger->info('Profile fetched successfully', [
            'employee_id' => $employee_id
        ], 'profile');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Profile fetched successfully',
            'data' => [
                'profile' => $profile_data
            ]
        ]);
    }

    /**
     * Edit Profile
     *
     * Updates the authenticated employee's profile details.
     * Only allows updating: first_name, last_name, phone, profile_picture
     *
     * POST /api/v1/user/profile/edit_profile
     *
     * Parameters (form-data):
     * - first_name (optional): First name
     * - last_name (optional): Last name
     * - phone (optional): Phone number
     * - profile_picture (optional): Profile picture file (jpg, jpeg, png, max 2MB)
     *
     * @return void JSON response with updated profile
     */
    public function edit_profile()
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

        $this->logger->info('Edit Profile API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'profile');

        // Get current employee data
        $employee = $this->profile_model->get_employee_profile($employee_id);

        if (empty($employee)) {
            $this->logger->warning('Employee not found for edit', [
                'employee_id' => $employee_id
            ], 'profile');
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Employee not found',
                'data' => null
            ]);
            return;
        }

        // Get form-data input
        $post_data = $this->input->post(null, true);

        // Build update data (only allowed fields)
        $update_data = [];

        // First name
        if (isset($post_data['first_name']) && !empty(trim($post_data['first_name']))) {
            $first_name = trim($post_data['first_name']);
            if (strlen($first_name) < 2 || strlen($first_name) > 100) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'First name must be between 2 and 100 characters',
                    'data' => null
                ]);
                return;
            }
            $update_data['first_name'] = $first_name;
        }

        // Last name
        if (isset($post_data['last_name'])) {
            $last_name = trim($post_data['last_name']);
            if (strlen($last_name) > 100) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Last name must be less than 100 characters',
                    'data' => null
                ]);
                return;
            }
            $update_data['last_name'] = $last_name ?: null;
        }

        // Profile picture upload
        if (!empty($_FILES['profile_picture']['name'])) {
            // Configure upload
            $upload_path = './uploads/profile_pictures/';

            // Create directory if it doesn't exist
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            $config['upload_path'] = $upload_path;
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = 2048; // 2MB
            $config['file_name'] = 'profile_' . $employee_id . '_' . time();
            $config['overwrite'] = false;

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('profile_picture')) {
                $error = $this->upload->display_errors('', '');
                $this->logger->warning('Profile picture upload failed', [
                    'employee_id' => $employee_id,
                    'error' => $error
                ], 'profile');
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Failed to upload profile picture: ' . $error,
                    'data' => null
                ]);
                return;
            }

            $upload_data = $this->upload->data();
            $profile_picture_path = 'uploads/profile_pictures/' . $upload_data['file_name'];

            // Delete old profile picture if exists
            if (!empty($employee->profile_picture_url) && file_exists('./' . $employee->profile_picture_url)) {
                @unlink('./' . $employee->profile_picture_url);
            }

            $update_data['profile_picture_url'] = $profile_picture_path;

            $this->logger->info('Profile picture uploaded', [
                'employee_id' => $employee_id,
                'file' => $profile_picture_path
            ], 'profile');
        }

        // Phone number
        if (isset($post_data['phone'])) {
            $phone = trim($post_data['phone']);
            if (!empty($phone)) {
                // Validate phone format (10 digits)
                if (!preg_match('/^[0-9]{10}$/', $phone)) {
                    $this->output([
                        'status' => 400,
                        'success' => false,
                        'message' => 'Phone number must be 10 digits',
                        'data' => null
                    ]);
                    return;
                }

                // Check if phone already exists for another employee
                if ($this->profile_model->phone_exists($phone, $company_id, $employee_id)) {
                    $this->output([
                        'status' => 400,
                        'success' => false,
                        'message' => 'Phone number is already in use by another employee',
                        'data' => null
                    ]);
                    return;
                }

                $update_data['phone'] = $phone;
            } else {
                $update_data['phone'] = null;
            }
        }

        // Check if there's anything to update
        if (empty($update_data)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'No valid fields to update',
                'data' => null
            ]);
            return;
        }

        $this->logger->info('Updating profile', [
            'employee_id' => $employee_id,
            'fields' => array_keys($update_data)
        ], 'profile');

        // Update profile
        $updated = $this->profile_model->update_profile($employee_id, $update_data);

        if (!$updated) {
            $this->logger->error('Failed to update profile', [
                'employee_id' => $employee_id
            ], 'profile');
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update profile. Please try again.',
                'data' => null
            ]);
            return;
        }

        // Get updated profile
        $updated_employee = $this->profile_model->get_employee_profile($employee_id);

        // Build full name
        $full_name = trim($updated_employee->first_name . ' ' . ($updated_employee->last_name ?? ''));

        // Get profile picture or generate avatar
        $profile_picture = null;
        if (!empty($updated_employee->profile_picture_url)) {
            if (filter_var($updated_employee->profile_picture_url, FILTER_VALIDATE_URL)) {
                $profile_picture = $updated_employee->profile_picture_url;
            } else {
                $profile_picture = base_url($updated_employee->profile_picture_url);
            }
        } else {
            $profile_picture = $this->generate_avatar_url($full_name);
        }

        // Format response
        $profile_data = [
            'id' => (int)$updated_employee->id,
            'first_name' => $updated_employee->first_name,
            'last_name' => $updated_employee->last_name,
            'full_name' => $full_name,
            'email' => $updated_employee->email,
            'phone' => $updated_employee->phone,
            'profile_picture' => $profile_picture
        ];

        $this->logger->info('Profile updated successfully', [
            'employee_id' => $employee_id
        ], 'profile');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'profile' => $profile_data
            ]
        ]);
    }

    /**
     * Get Wallet Summary and Transactions
     *
     * Returns wallet balance summary and paginated transaction history.
     *
     * GET /api/v1/user/profile/wallet
     * GET /api/v1/user/profile/wallet?page=1&per_page=20
     *
     * Query Parameters:
     * - page (optional): Page number (default: 1)
     * - per_page (optional): Transactions per page (default: 20, max: 50)
     *
     * @return void JSON response with wallet summary and transactions
     */
    public function wallet()
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

        $this->logger->info('Get Wallet API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'profile');

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

        // Get wallet summary
        $wallet_summary = $this->profile_model->get_wallet_summary($employee_id);

        // Get employee name for avatar
        $employee = $this->profile_model->get_employee_profile($employee_id);
        $full_name = $employee ? trim($employee->first_name . ' ' . ($employee->last_name ?? '')) : 'User';
        $display_name = $employee && $employee->display_name ? $employee->display_name : $full_name;

        // Get profile picture or generate avatar
        $profile_picture = null;
        if ($employee && !empty($employee->profile_picture_url)) {
            if (filter_var($employee->profile_picture_url, FILTER_VALIDATE_URL)) {
                $profile_picture = $employee->profile_picture_url;
            } else {
                $profile_picture = base_url($employee->profile_picture_url);
            }
        } else {
            $profile_picture = $this->generate_avatar_url($display_name);
        }

        // Get total count for pagination
        $total_count = $this->profile_model->get_wallet_transactions_count($employee_id);

        // Calculate pagination info
        $total_pages = $total_count > 0 ? ceil($total_count / $per_page) : 0;
        $has_next = $page < $total_pages;
        $has_previous = $page > 1;

        // Get transactions
        $transactions = $this->profile_model->get_wallet_transactions($employee_id, $per_page, $offset);

        // Format transactions
        $transactions_data = [];
        foreach ($transactions as $txn) {
            // transaction_type: 1 = Credit, 2 = Debit
            $is_credit = $txn->transaction_type == 1;

            $transactions_data[] = [
                'id' => (int)$txn->transaction_id,
                'uuid' => $txn->transaction_uuid,
                'type' => $is_credit ? 'CREDIT' : 'DEBIT',
                'amount' => round((float)$txn->amount, 2),
                'formatted_amount' => ($is_credit ? '+' : '-') . '₹' . number_format($txn->amount, 2),
                'label' => $txn->transaction_label,
                'date' => $txn->transaction_date,
                'time' => date('H:i', strtotime($txn->transaction_time)),
                'formatted_date' => date('d M, Y', strtotime($txn->transaction_date)),
                'order' => $txn->order_id ? [
                    'id' => (int)$txn->order_id,
                    'order_number' => $txn->order_number,
                    'store_name' => $txn->store_name
                ] : null
            ];
        }

        $this->logger->info('Wallet data fetched successfully', [
            'employee_id' => $employee_id,
            'balance' => $wallet_summary['available_balance'],
            'transactions_count' => count($transactions_data),
            'page' => $page
        ], 'profile');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($transactions_data) ? 'No transactions found' : 'Wallet data fetched successfully',
            'data' => [
                'user' => [
                    'name' => $display_name,
                    'profile_picture' => $profile_picture
                ],
                'wallet' => [
                    'available_balance' => $wallet_summary['available_balance'],
                    'total_credits' => $wallet_summary['total_credits'],
                    'total_debits' => $wallet_summary['total_debits'],
                    'formatted_balance' => '₹' . number_format($wallet_summary['available_balance'], 2)
                ],
                'transactions' => $transactions_data,
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
     * Change Password
     *
     * Allows authenticated employee to change their password.
     * Requires current password verification before setting new password.
     *
     * POST /api/v1/user/profile/change_password
     *
     * Parameters (form-data):
     * - current_password (required): Current password for verification
     * - new_password (required): New password (min 6 characters)
     * - confirm_password (required): Must match new_password
     *
     * @return void JSON response with success/error message
     */
    public function change_password()
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

        $this->logger->info('Change Password API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'profile');

        // Get form-data input
        $post_data = $this->input->post(null, true);

        $current_password = $post_data['current_password'] ?? null;
        $new_password = $post_data['new_password'] ?? null;
        $confirm_password = $post_data['confirm_password'] ?? null;

        // Validate current_password
        if (empty($current_password)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Current password is required',
                'data' => null
            ]);
            return;
        }

        // Validate new_password
        if (empty($new_password)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'New password is required',
                'data' => null
            ]);
            return;
        }

        // Validate password length
        if (strlen($new_password) < 6) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'New password must be at least 6 characters',
                'data' => null
            ]);
            return;
        }

        // Validate confirm_password
        if (empty($confirm_password)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Confirm password is required',
                'data' => null
            ]);
            return;
        }

        // Check if passwords match
        if ($new_password !== $confirm_password) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'New password and confirm password do not match',
                'data' => null
            ]);
            return;
        }

        // Check if new password is same as current
        if ($current_password === $new_password) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'New password must be different from current password',
                'data' => null
            ]);
            return;
        }

        // Get employee with password
        $employee = $this->common->getdatabytable('employees', [
            'id' => $employee_id,
            'is_active' => 1
        ]);

        if (empty($employee)) {
            $this->logger->warning('Employee not found for password change', [
                'employee_id' => $employee_id
            ], 'profile');
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Employee not found',
                'data' => null
            ]);
            return;
        }

        // Verify current password
        if (!password_verify($current_password, $employee->password_hash)) {
            $this->logger->warning('Invalid current password attempt', [
                'employee_id' => $employee_id
            ], 'profile');
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Current password is incorrect',
                'data' => null
            ]);
            return;
        }

        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password in database
        $this->db->where('id', $employee_id);
        $updated = $this->db->update('employees', [
            'password_hash' => $hashed_password,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if (!$updated) {
            $this->logger->error('Failed to update password', [
                'employee_id' => $employee_id
            ], 'profile');
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update password. Please try again.',
                'data' => null
            ]);
            return;
        }

        $this->logger->info('Password changed successfully', [
            'employee_id' => $employee_id
        ], 'profile');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Password changed successfully',
            'data' => null
        ]);
    }

    /**
     * Wallet Recharge Initiate
     *
     * Creates a Razorpay order for wallet recharge.
     * Returns payment details to complete the recharge via Razorpay.
     *
     * POST /api/v1/user/profile/recharge_initiate
     *
     * Parameters (form-data):
     * - amount (required): Recharge amount in INR (min 10, max 50000)
     *
     * @return void JSON response with Razorpay order details
     */
    public function recharge_initiate()
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

        $this->logger->info('Wallet Recharge Initiate API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'profile');

        // Get form-data input
        $post_data = $this->input->post(null, true);

        $amount = isset($post_data['amount']) ? (float) $post_data['amount'] : 0;

        // Validate amount
        if ($amount <= 0) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Amount is required',
                'data' => null
            ]);
            return;
        }

        // Minimum recharge amount
        if ($amount < 10) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Minimum recharge amount is ₹10',
                'data' => null
            ]);
            return;
        }

        // Maximum recharge amount
        if ($amount > 50000) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Maximum recharge amount is ₹50,000',
                'data' => null
            ]);
            return;
        }

        // Get employee details for prefill
        $employee = $this->profile_model->get_employee_profile($employee_id);

        if (empty($employee)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Employee not found',
                'data' => null
            ]);
            return;
        }

        // Initialize Razorpay with company's credentials
        if (!$this->razorpaylib->init($auth->company_id)) {
            $this->logger->error('Failed to initialize Razorpay', [
                'employee_id' => $employee_id,
                'company_id' => $auth->company_id
            ], 'profile');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Payment service not configured. Please contact support.',
                'data' => null
            ]);
            return;
        }

        // Create Razorpay order
        $receipt_id = 'RECHARGE_' . $employee_id . '_' . time();

        $razorpay_result = $this->razorpaylib->createOrder([
            'amount' => $amount,
            'receipt' => $receipt_id,
            'notes' => [
                'employee_id' => (string) $employee_id,
                'type' => 'WALLET_RECHARGE',
                'amount' => (string) $amount
            ]
        ]);

        if (!$razorpay_result['success']) {
            $this->logger->error('Razorpay order creation failed for wallet recharge', [
                'employee_id' => $employee_id,
                'amount' => $amount,
                'error' => $razorpay_result['message']
            ], 'profile');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to initiate payment. Please try again.',
                'data' => null
            ]);
            return;
        }

        $razorpay_order = $razorpay_result['order'];
        $full_name = trim($employee->first_name . ' ' . ($employee->last_name ?? ''));

        // Store pending recharge in database for verification (don't rely on Razorpay notes)
        $recharge_data = [
            'employee_id' => $employee_id,
            'razorpay_order_id' => $razorpay_order['id'],
            'amount' => $amount,
            'status' => 'PENDING',
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
        ];

        $this->db->insert('wallet_recharges', $recharge_data);
        $recharge_id = $this->db->insert_id();

        $this->logger->info('Wallet recharge initiated', [
            'employee_id' => $employee_id,
            'amount' => $amount,
            'razorpay_order_id' => $razorpay_order['id'],
            'recharge_id' => $recharge_id
        ], 'profile');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Payment initiated successfully',
            'data' => [
                'amount' => $amount,
                'formatted_amount' => '₹' . number_format($amount, 2),
                'razorpay' => [
                    'key' => $this->razorpaylib->getKeyId(),
                    'amount' => $razorpay_order['amount'],
                    'currency' => $razorpay_order['currency'],
                    'name' => config_item('application_name') ?: 'Joy Foods',
                    'description' => 'Wallet Recharge',
                    'image' => base_url('assets/images/logo.png'),
                    'order_id' => $razorpay_order['id'],
                    'prefill' => [
                        'name' => $full_name,
                        'email' => $employee->email ?? '',
                        'contact' => $employee->phone ?? ''
                    ],
                    'theme' => [
                        'color' => '#BD3839'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Wallet Recharge Complete
     *
     * Verifies Razorpay payment and credits wallet.
     * Creates transaction record for the recharge.
     *
     * POST /api/v1/user/profile/recharge_complete
     *
     * Parameters (form-data):
     * - razorpay_order_id (required): Razorpay order ID
     * - razorpay_payment_id (required): Razorpay payment ID
     * - razorpay_signature (required): Razorpay signature for verification
     *
     * @return void JSON response with transaction details
     */
    public function recharge_complete()
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

        $this->logger->info('Wallet Recharge Complete API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'profile');

        // Get form-data input
        $post_data = $this->input->post(null, true);

        $razorpay_order_id = isset($post_data['razorpay_order_id']) ? trim($post_data['razorpay_order_id']) : null;
        $razorpay_payment_id = isset($post_data['razorpay_payment_id']) ? trim($post_data['razorpay_payment_id']) : null;
        $razorpay_signature = isset($post_data['razorpay_signature']) ? trim($post_data['razorpay_signature']) : null;

        // Validate required fields
        if (empty($razorpay_order_id) || empty($razorpay_payment_id) || empty($razorpay_signature)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'All payment verification fields are required (razorpay_order_id, razorpay_payment_id, razorpay_signature)',
                'data' => null
            ]);
            return;
        }

        // Verify pending recharge from database (don't rely on Razorpay notes)
        $pending_recharge = $this->db->get_where('wallet_recharges', [
            'razorpay_order_id' => $razorpay_order_id,
            'status' => 'PENDING'
        ])->row();

        if (empty($pending_recharge)) {
            $this->logger->error('Pending recharge not found', [
                'employee_id' => $employee_id,
                'razorpay_order_id' => $razorpay_order_id
            ], 'profile');

            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid or expired recharge request',
                'data' => null
            ]);
            return;
        }

        // Verify employee_id matches
        if ((int) $pending_recharge->employee_id != $employee_id) {
            $this->logger->error('Employee ID mismatch in wallet recharge', [
                'employee_id' => $employee_id,
                'pending_employee_id' => $pending_recharge->employee_id,
                'razorpay_order_id' => $razorpay_order_id
            ], 'profile');

            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Unauthorized payment',
                'data' => null
            ]);
            return;
        }

        // Check if recharge has expired
        if (strtotime($pending_recharge->expires_at) < time()) {
            // Mark as expired
            $this->db->where('id', $pending_recharge->id);
            $this->db->update('wallet_recharges', ['status' => 'EXPIRED']);

            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Recharge request has expired. Please initiate again.',
                'data' => null
            ]);
            return;
        }

        // Initialize Razorpay with company's credentials
        if (!$this->razorpaylib->init($auth->company_id)) {
            $this->logger->error('Failed to initialize Razorpay for recharge verification', [
                'employee_id' => $employee_id,
                'company_id' => $auth->company_id
            ], 'profile');

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
            $this->logger->error('Wallet recharge Razorpay signature verification failed', [
                'employee_id' => $employee_id,
                'razorpay_order_id' => $razorpay_order_id
            ], 'profile');

            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Payment verification failed',
                'data' => null
            ]);
            return;
        }

        // Get recharge amount from our database (more reliable than Razorpay)
        $recharge_amount = round((float) $pending_recharge->amount, 2);

        // Mark pending recharge as completed
        $this->db->where('id', $pending_recharge->id);
        $this->db->update('wallet_recharges', [
            'status' => 'COMPLETED',
            'razorpay_payment_id' => $razorpay_payment_id,
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        // Check if this payment was already processed (prevent double credit)
        $existing_txn = $this->db->get_where('transaction', [
            'user_id' => $employee_id,
            'transaction_label' => 'Wallet recharge - ' . $razorpay_payment_id
        ])->row();

        if (!empty($existing_txn)) {
            $this->logger->warning('Duplicate wallet recharge attempt', [
                'employee_id' => $employee_id,
                'razorpay_payment_id' => $razorpay_payment_id
            ], 'profile');

            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'This payment has already been processed',
                'data' => null
            ]);
            return;
        }

        // Create transaction record
        $transaction_uuid = generate_uuid();
        $transaction_data = [
            'transaction_uuid' => $transaction_uuid,
            'user_id' => $employee_id,
            'order_id' => null,
            'transaction_type' => 1, // Credit
            'amount' => $recharge_amount,
            'transaction_label' => 'Wallet recharge - ' . $razorpay_payment_id,
            'transaction_date' => date('Y-m-d'),
            'transaction_time' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('transaction', $transaction_data);
        $transaction_id = $this->db->insert_id();

        if (!$transaction_id) {
            $this->logger->error('Failed to create wallet recharge transaction', [
                'employee_id' => $employee_id,
                'amount' => $recharge_amount,
                'razorpay_payment_id' => $razorpay_payment_id
            ], 'profile');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to complete recharge. Please contact support.',
                'data' => null
            ]);
            return;
        }

        // Get updated wallet balance
        $wallet = getWalletMoneyArray($employee_id);
        $new_balance = round((float) $wallet['available'], 2);

        $this->logger->info('Wallet recharge completed successfully', [
            'employee_id' => $employee_id,
            'amount' => $recharge_amount,
            'transaction_id' => $transaction_id,
            'new_balance' => $new_balance,
            'razorpay_payment_id' => $razorpay_payment_id
        ], 'profile');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Wallet recharged successfully',
            'data' => [
                'transaction' => [
                    'id' => $transaction_id,
                    'uuid' => $transaction_uuid,
                    'amount' => $recharge_amount,
                    'formatted_amount' => '+₹' . number_format($recharge_amount, 2),
                    'type' => 'CREDIT',
                    'label' => 'Wallet recharge via Razorpay',
                    'date' => date('d M Y, h:i A')
                ],
                'wallet' => [
                    'new_balance' => $new_balance,
                    'formatted_balance' => '₹' . number_format($new_balance, 2)
                ]
            ]
        ]);
    }

    /**
     * Delete Account
     *
     * Soft deletes the authenticated employee's account by setting is_active to 0.
     * Account will be deactivated and user will no longer be able to login.
     *
     * DELETE /api/v1/user/profile/delete_account
     *
     * @return void JSON response with confirmation message
     */
    public function delete_account()
    {
        // Check API key
        if (!$this->check_auth()) {
            return;
        }

        // Only allow DELETE method
        if ($this->input->method() !== 'delete') {
            $this->output([
                'status' => 405,
                'success' => false,
                'message' => 'Method not allowed. Use DELETE request.',
                'data' => null
            ]);
            return;
        }

        // Authenticate user
        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $employee_id = $auth->employee_id;

        $this->logger->info('Delete Account API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'profile');

        // Verify employee exists
        $employee = $this->profile_model->get_employee_profile($employee_id);

        if (empty($employee)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Employee not found',
                'data' => null
            ]);
            return;
        }

        // Soft delete: set is_active to 0
        $this->db->where('id', $employee_id);
        $updated = $this->db->update('employees', [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if (!$updated) {
            $this->logger->error('Failed to delete account', [
                'employee_id' => $employee_id
            ], 'profile');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to process request. Please try again.',
                'data' => null
            ]);
            return;
        }

        $this->logger->info('Account deletion requested', [
            'employee_id' => $employee_id,
            'email' => $employee->email
        ], 'profile');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Request received and will process this soon',
            'data' => null
        ]);
    }
}
