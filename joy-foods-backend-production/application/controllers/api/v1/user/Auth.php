<?php
//Jai Sree Ram
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Authentication Controller
 * 
 * Handles user registration, login, and authentication operations
 * for the Joy Foods application.
 *
 * @category  Controllers
 * @package   Joy_Foods_Auth
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2025-12-01
 */
class Auth extends CI_Controller
{
    private $tokenHandler;
    private $logger;


    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Employees_model', 'employees');
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
        // Case-insensitive header lookup: HTTP/2 clients (e.g. the mobile app)
        // send lowercase header names, so 'Auth' can arrive as 'auth'.
        // get_request_header() matches case-insensitively; the old
        // $headers['Auth'] check was case-sensitive and failed over HTTP/2.
        $auth = $this->input->get_request_header('Auth');
        if ($auth !== null && $auth == config_item('api_authorization')) {
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
        return null; // No valid token found
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
     * Validate email format
     */
    private function is_valid_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Verify Company
     *
     * Validates if a company exists and is active using the company code.
     * This is typically the first step in the employee login flow.
     *
     * @api POST /api/v1/user/auth/verify_company
     *
     * @param string company_code The unique company code to verify
     *
     * @return void Outputs JSON response
     *         - 200: Company verified successfully with company details (id, name, code)
     *         - 400: Company code is required
     *         - 404: Invalid company code
     */
    public function verify_company(){

        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Verify Company API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'auth');

        $post_data = $this->input->post(null, true);

        if(empty($post_data['company_code'])){
            $this->output([
                'status' => 400,
                'message' => 'Company is required'
            ]);
            return;
        }

        $company_code = $post_data['company_code'];

        $this->logger->info('Company code received', ['company_code' => $company_code], 'auth');

        $where_data = array(
            'company_code'  => $company_code,
            'is_active'     => 1
        );

        $company_data = $this->common->getdatabytable('companies', $where_data);

       if(empty($company_data)){
            $this->logger->warning('Invalid company code', ['company_code' => $company_code], 'auth');
            $this->output([
                'status' => 404,
                'message' => 'Invalid company code'
            ]);
            return;
       }

        $this->logger->info('Company verified successfully', ['company_code' => $company_code, 'id' => $company_data->id], 'auth');

        $response = array(
            'status' => 200,
            'message' => 'Company verified successfully',
            'data' => array(
                'id'            => $company_data->id,
                'company_name'  => $company_data->name,
                'company_code'  => $company_data->company_code,
            )
        );

        $this->output($response);
    }


    /**
     * Employee Login
     *
     * Authenticates an employee using email, password, and company ID.
     * Returns a JWT token on successful authentication.
     *
     * @api POST /api/v1/user/auth/login
     *
     * @param string email      The employee's email address
     * @param string password   The employee's password
     * @param int    company_id The company ID (obtained from verify_company)
     *
     * @return void Outputs JSON response
     *         - 200: Login successful with JWT token and employee details
     *         - 400: Missing required fields or invalid email format
     *         - 401: Invalid email or password
     */
    public function login(){

        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Employee Login API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'auth');

        $post_data = $this->input->post(null, true);
        if(empty($post_data['email']) || empty($post_data['password']) || empty($post_data['company_id'])){
            $this->logger->warning('Missing required fields for login', ['post_data' => $post_data], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'Email, Password and Company ID are required'
            ]);
            return;
        }

        $email = $post_data['email'];
        $password = $post_data['password'];
        $company_id = $post_data['company_id'];

        if(!$this->is_valid_email($email)){
            $this->logger->warning('Invalid email format', ['email' => $email], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'Invalid email format'
            ]);
            return;
        }

        $where_data = array(
            'email'         => $email,
            'company_id'   => $company_id,
            'is_active'     => 1
        );

        $employee_data = $this->common->getdatabytable('employees', $where_data);

        if(empty($employee_data) || !password_verify($password, $employee_data->password_hash)){
            $this->logger->warning('Invalid email or password', ['email' => $email, 'company_id' => $company_id], 'auth');
            $this->output([
                'status' => 401,
                'message' => 'Invalid email or password'
            ]);
            return;
        }


        // Generate JWT token
        $token_payload = array(
            'employee_id'   => $employee_data->id,
            'company_id'    => $employee_data->company_id,
            'email'         => $employee_data->email,
            'role'          => 'employee',
            'iat'           => time(),
            'exp'           => time() + (60 * 60 * 24 * 30), // 30 days expiry
        );

        $token = $this->tokenHandler->GenerateToken($token_payload);

        $this->logger->info('Employee logged in successfully', [
            'employee_id' => $employee_data->id,
            'company_id' => $employee_data->company_id,
            'email' => $employee_data->email
        ], 'auth');

        $response = array(
            'status' => 200,
            'message' => 'Login successful',
            'data' => array(
                'token' => $token,
                'employee' => array(
                    'id'            => $employee_data->id,
                    'employee_code' => $employee_data->employee_code,
                    'first_name'    => $employee_data->first_name,
                    'last_name'     => $employee_data->last_name,
                    'email'         => $employee_data->email,
                    'company_id'    => $employee_data->company_id,
                    'kot_permission'=> (bool) $employee_data->kot_permission,
                    'qsr_access'    => (bool) $employee_data->qsr_access,
                    'premeal_access'=> (bool) $employee_data->premeal_access,
                )
            )
        );

        $this->output($response);
    }


    /**
     * Employee Signup
     *
     * Registers a new employee account for QSR module access only.
     * No policies are attached during self-registration.
     *
     * @api POST /api/v1/user/auth/signup
     *
     * @param string first_name  The employee's first name
     * @param string last_name   The employee's last name (optional)
     * @param string email       The employee's email address
     * @param string password    The employee's password (min 6 characters)
     * @param int    company_id  The company ID (obtained from verify_company)
     *
     * @return void Outputs JSON response
     *         - 200: Signup successful with JWT token and employee details
     *         - 400: Missing required fields, invalid email, or weak password
     *         - 404: Company not found or inactive
     *         - 409: Email already exists for this company
     */
    public function signup()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Employee Signup API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'auth');

        $post_data = $this->input->post(null, true);

        // Validate required fields
        if (empty($post_data['first_name']) || empty($post_data['email']) || empty($post_data['password']) || empty($post_data['company_id'])) {
            $this->logger->warning('Missing required fields for signup', [
                'has_first_name' => !empty($post_data['first_name']),
                'has_email' => !empty($post_data['email']),
                'has_password' => !empty($post_data['password']),
                'has_company_id' => !empty($post_data['company_id'])
            ], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'First name, Email, Password and Company ID are required'
            ]);
            return;
        }

        $first_name = trim($post_data['first_name']);
        $last_name = isset($post_data['last_name']) ? trim($post_data['last_name']) : null;
        $email = trim($post_data['email']);
        $password = $post_data['password'];
        $company_id = $post_data['company_id'];

        // Validate email format
        if (!$this->is_valid_email($email)) {
            $this->logger->warning('Invalid email format during signup', ['email' => $email], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'Invalid email format'
            ]);
            return;
        }

        // Validate password length
        if (strlen($password) < 6) {
            $this->logger->warning('Password too short during signup', ['email' => $email], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'Password must be at least 6 characters'
            ]);
            return;
        }

        // Check if company exists and is active
        $company_data = $this->common->getdatabytable('companies', [
            'id' => $company_id,
            'is_active' => 1
        ]);

        if (empty($company_data)) {
            $this->logger->warning('Company not found or inactive during signup', ['company_id' => $company_id], 'auth');
            $this->output([
                'status' => 404,
                'message' => 'Company not found or inactive'
            ]);
            return;
        }

        // Check if email already exists for this company
        $existing_employee = $this->common->getdatabytable('employees', [
            'email' => $email,
            'company_id' => $company_id
        ]);

        if (!empty($existing_employee)) {
            $this->logger->warning('Email already exists during signup', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 409,
                'message' => 'Email already registered for this company'
            ]);
            return;
        }

        // Generate employee code
        $employee_code = $this->employees->generate_employee_code($company_id, $company_data->company_code);

        // Prepare employee data - Only QSR access, no policies
        $employee_data = [
            'company_id' => $company_id,
            'employee_code' => $employee_code,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'qsr_access' => 1,
            'premeal_access' => 0,
            'kot_permission' => 0,
            'delivery_access' => 0,
            'is_active' => 1,
            'is_registered' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Insert employee
        $employee_id = $this->employees->insert_employee($employee_data);

        if (!$employee_id) {
            $this->logger->error('Failed to create employee account', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 500,
                'message' => 'Failed to create account. Please try again.'
            ]);
            return;
        }

        $this->logger->info('Employee account created successfully', [
            'employee_id' => $employee_id,
            'email' => $email,
            'company_id' => $company_id
        ], 'auth');

        // Generate JWT token
        $token_payload = array(
            'employee_id' => $employee_id,
            'company_id' => $company_id,
            'email' => $email,
            'role' => 'employee',
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24 * 30), // 30 days expiry
        );

        $token = $this->tokenHandler->GenerateToken($token_payload);

        $response = array(
            'status' => 200,
            'message' => 'Account created successfully',
            'data' => array(
                'token' => $token,
                'employee' => array(
                    'id' => $employee_id,
                    'employee_code' => $employee_code,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'company_id' => $company_id,
                    'kot_permission' => false,
                    'qsr_access' => true,
                    'premeal_access' => false,
                )
            )
        );

        $this->output($response);
    }

    /**
     * Verify Session
     *
     * Validates the current session by checking the JWT token,
     * employee status, and company status.
     *
     * @api POST /api/v1/user/auth/verify_session
     *
     * @header Authorization Bearer <token> The JWT token from login
     *
     * @return void Outputs JSON response
     *         - 200: Session is valid with employee and company details
     *         - 401: Token is required
     *         - 401: Invalid or expired token
     *         - 401: Token has expired
     *         - 401: Employee not found or inactive
     *         - 401: Company not found or inactive
     */
    public function verify_session(){

        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Verify Session API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'auth');

        // Get Bearer token
        $token = $this->check_bearer_token();

        if(empty($token)){
            $this->logger->warning('No token provided for session verification', [], 'auth');
            $this->output([
                'status' => 401,
                'message' => 'Token is required'
            ]);
            return;
        }

        // Decode token
        $decoded = $this->decode_token($token);

        if(empty($decoded)){
            $this->logger->warning('Invalid or expired token', [], 'auth');
            $this->output([
                'status' => 401,
                'message' => 'Invalid or expired token'
            ]);
            return;
        }

        // Check token expiry
        if(isset($decoded->exp) && $decoded->exp < time()){
            $this->logger->warning('Token expired', ['employee_id' => $decoded->employee_id ?? null], 'auth');
            $this->output([
                'status' => 401,
                'message' => 'Token has expired'
            ]);
            return;
        }

        // Check if employee exists and is active
        $employee_data = $this->common->getdatabytable('employees', [
            'id' => $decoded->employee_id,
            'is_active' => 1
        ]);

        if(empty($employee_data)){
            $this->logger->warning('Employee not found or inactive', ['employee_id' => $decoded->employee_id], 'auth');
            $this->output([
                'status' => 401,
                'message' => 'Employee not found or inactive'
            ]);
            return;
        }

        // Check if company exists and is active
        $company_data = $this->common->getdatabytable('companies', [
            'id' => $decoded->company_id,
            'is_active' => 1
        ]);

        if(empty($company_data)){
            $this->logger->warning('Company not found or inactive', ['company_id' => $decoded->company_id], 'auth');
            $this->output([
                'status' => 401,
                'message' => 'Company not found or inactive'
            ]);
            return;
        }

        $this->logger->info('Session verified successfully', [
            'employee_id' => $employee_data->id,
            'company_id' => $company_data->id
        ], 'auth');

        $response = array(
            'status' => 200,
            'message' => 'Session is valid',
            'data' => array(
                'employee' => array(
                    'id'            => $employee_data->id,
                    'employee_code' => $employee_data->employee_code,
                    'first_name'    => $employee_data->first_name,
                    'last_name'     => $employee_data->last_name,
                    'email'         => $employee_data->email,
                    'company_id'    => $employee_data->company_id,
                    'kot_permission'=> (bool) $employee_data->kot_permission,
                    'qsr_access'    => (bool) $employee_data->qsr_access,
                    'premeal_access'=> (bool) $employee_data->premeal_access,
                ),
                'company' => array(
                    'id'            => $company_data->id,
                    'name'          => $company_data->name,
                    'company_code'  => $company_data->company_code,
                )
            )
        );

        $this->output($response);
    }


    /**
     * Forgot Password - Send OTP
     *
     * Sends a 6-digit OTP to the employee's email for password reset.
     *
     * @api POST /api/v1/user/auth/forgot_password
     *
     * @param string email      The employee's email address
     * @param int    company_id The company ID
     *
     * @return void Outputs JSON response
     *         - 200: OTP sent successfully
     *         - 400: Missing required fields or invalid email
     *         - 404: Employee not found
     *         - 429: Too many requests (rate limiting)
     *         - 500: Failed to send email
     */
    public function forgot_password()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Forgot Password API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'auth');

        $post_data = $this->input->post(null, true);

        // Validate required fields
        if (empty($post_data['email']) || empty($post_data['company_id'])) {
            $this->logger->warning('Missing required fields for forgot password', [
                'has_email' => !empty($post_data['email']),
                'has_company_id' => !empty($post_data['company_id'])
            ], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'Email and Company ID are required'
            ]);
            return;
        }

        $email = trim($post_data['email']);
        $company_id = $post_data['company_id'];

        // Validate email format
        if (!$this->is_valid_email($email)) {
            $this->logger->warning('Invalid email format for forgot password', ['email' => $email], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'Invalid email format'
            ]);
            return;
        }

        // Check if employee exists
        $employee_data = $this->common->getdatabytable('employees', [
            'email' => $email,
            'company_id' => $company_id,
            'is_active' => 1
        ]);

        if (empty($employee_data)) {
            $this->logger->warning('Employee not found for forgot password', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 404,
                'message' => 'No account found with this email'
            ]);
            return;
        }

        // Rate limiting - check if there's a recent OTP request (within 1 minute)
        $recent_otp = $this->employees->get_recent_otp($email, $company_id, 'PASSWORD_RESET', 1);

        if ($recent_otp) {
            $this->logger->warning('OTP rate limit exceeded', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 429,
                'message' => 'Please wait before requesting another OTP'
            ]);
            return;
        }

        // Invalidate any existing OTPs for this email
        $this->employees->invalidate_existing_otps($email, $company_id, 'PASSWORD_RESET');

        // Generate 6-digit OTP
        $otp_code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in database
        $otp_data = [
            'email' => $email,
            'company_id' => $company_id,
            'otp_code' => $otp_code,
            'purpose' => 'PASSWORD_RESET',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $otp_id = $this->employees->insert_otp($otp_data);

        if (!$otp_id) {
            $this->logger->error('Failed to store OTP', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 500,
                'message' => 'Failed to process request. Please try again.'
            ]);
            return;
        }

        // Get company name for email
        $company_data = $this->common->getdatabytable('companies', ['id' => $company_id]);
        $company_name = $company_data ? $company_data->name : '';
        $employee_name = $employee_data->first_name;

        // Send OTP email
        $this->load->library('mailer');
        $email_sent = $this->mailer->send_otp($email, $otp_code, $employee_name, $company_name);

        if (!$email_sent) {
            $this->logger->error('Failed to send OTP email', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 500,
                'message' => 'Failed to send OTP. Please try again.'
            ]);
            return;
        }

        $this->logger->info('OTP sent successfully', [
            'email' => $email,
            'company_id' => $company_id,
            'otp_id' => $otp_id
        ], 'auth');

        $this->output([
            'status' => 200,
            'message' => 'OTP sent successfully to your email',
            'data' => [
                'email' => $this->mask_email($email),
                'expires_in' => 600 // 10 minutes in seconds
            ]
        ]);
    }

    /**
     * Verify OTP
     *
     * Verifies the OTP and returns a reset token for password reset.
     *
     * @api POST /api/v1/user/auth/verify_otp
     *
     * @param string email      The employee's email address
     * @param int    company_id The company ID
     * @param string otp        The 6-digit OTP code
     *
     * @return void Outputs JSON response
     *         - 200: OTP verified successfully with reset token
     *         - 400: Missing required fields or invalid OTP format
     *         - 400: OTP expired
     *         - 400: Invalid OTP
     *         - 400: Too many failed attempts
     */
    public function verify_otp()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Verify OTP API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'auth');

        $post_data = $this->input->post(null, true);

        // Validate required fields
        if (empty($post_data['email']) || empty($post_data['company_id']) || empty($post_data['otp'])) {
            $this->logger->warning('Missing required fields for OTP verification', [
                'has_email' => !empty($post_data['email']),
                'has_company_id' => !empty($post_data['company_id']),
                'has_otp' => !empty($post_data['otp'])
            ], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'Email, Company ID and OTP are required'
            ]);
            return;
        }

        $email = trim($post_data['email']);
        $company_id = $post_data['company_id'];
        $otp = trim($post_data['otp']);

        // Validate OTP format (6 digits)
        if (!preg_match('/^\d{6}$/', $otp)) {
            $this->output([
                'status' => 400,
                'message' => 'Invalid OTP format'
            ]);
            return;
        }

        // Get the latest OTP record
        $otp_record = $this->employees->get_otp_record($email, $company_id, 'PASSWORD_RESET');

        if (empty($otp_record)) {
            $this->logger->warning('No OTP record found', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'No OTP request found. Please request a new OTP.'
            ]);
            return;
        }

        // Check if max attempts exceeded
        if ($otp_record->attempts >= $otp_record->max_attempts) {
            $this->logger->warning('Max OTP attempts exceeded', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'Too many failed attempts. Please request a new OTP.'
            ]);
            return;
        }

        // Check if OTP is expired
        if (strtotime($otp_record->expires_at) < time()) {
            $this->logger->warning('OTP expired', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'OTP has expired. Please request a new OTP.'
            ]);
            return;
        }

        // Verify OTP
        if ($otp_record->otp_code !== $otp) {
            // Increment attempts
            $this->employees->increment_otp_attempts($otp_record->id);

            $remaining_attempts = $otp_record->max_attempts - $otp_record->attempts - 1;

            $this->logger->warning('Invalid OTP entered', [
                'email' => $email,
                'company_id' => $company_id,
                'remaining_attempts' => $remaining_attempts
            ], 'auth');

            $this->output([
                'status' => 400,
                'message' => "Invalid OTP. {$remaining_attempts} attempts remaining."
            ]);
            return;
        }

        // OTP is valid - Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $reset_token_expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Update OTP record
        $this->employees->verify_otp($otp_record->id, $reset_token, $reset_token_expires);

        $this->logger->info('OTP verified successfully', [
            'email' => $email,
            'company_id' => $company_id,
            'otp_id' => $otp_record->id
        ], 'auth');

        $this->output([
            'status' => 200,
            'message' => 'OTP verified successfully',
            'data' => [
                'reset_token' => $reset_token,
                'expires_in' => 900 // 15 minutes in seconds
            ]
        ]);
    }

    /**
     * Reset Password
     *
     * Resets the employee's password using the reset token.
     *
     * @api POST /api/v1/user/auth/reset_password
     *
     * @param string email         The employee's email address
     * @param int    company_id    The company ID
     * @param string reset_token   The reset token from verify_otp
     * @param string password      The new password (min 6 characters)
     * @param string confirm_password  Confirmation of new password
     *
     * @return void Outputs JSON response
     *         - 200: Password reset successfully
     *         - 400: Missing required fields or validation errors
     *         - 400: Invalid or expired reset token
     *         - 404: Employee not found
     */
    public function reset_password()
    {
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Reset Password API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'auth');

        $post_data = $this->input->post(null, true);

        // Validate required fields
        if (empty($post_data['email']) || empty($post_data['company_id']) ||
            empty($post_data['reset_token']) || empty($post_data['password'])) {
            $this->logger->warning('Missing required fields for password reset', [
                'has_email' => !empty($post_data['email']),
                'has_company_id' => !empty($post_data['company_id']),
                'has_reset_token' => !empty($post_data['reset_token']),
                'has_password' => !empty($post_data['password'])
            ], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'Email, Company ID, Reset Token and Password are required'
            ]);
            return;
        }

        $email = trim($post_data['email']);
        $company_id = $post_data['company_id'];
        $reset_token = trim($post_data['reset_token']);
        $password = $post_data['password'];
        $confirm_password = isset($post_data['confirm_password']) ? $post_data['confirm_password'] : $password;

        // Validate password length
        if (strlen($password) < 6) {
            $this->output([
                'status' => 400,
                'message' => 'Password must be at least 6 characters'
            ]);
            return;
        }

        // Validate password match
        if ($password !== $confirm_password) {
            $this->output([
                'status' => 400,
                'message' => 'Passwords do not match'
            ]);
            return;
        }

        // Verify reset token
        $otp_record = $this->employees->get_otp_by_reset_token($email, $company_id, $reset_token);

        if (empty($otp_record)) {
            $this->logger->warning('Invalid reset token', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'Invalid reset token. Please request a new OTP.'
            ]);
            return;
        }

        // Check if reset token is expired
        if (strtotime($otp_record->reset_token_expires_at) < time()) {
            $this->logger->warning('Reset token expired', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 400,
                'message' => 'Reset token has expired. Please request a new OTP.'
            ]);
            return;
        }

        // Get employee
        $employee_data = $this->common->getdatabytable('employees', [
            'email' => $email,
            'company_id' => $company_id
        ]);

        if (empty($employee_data)) {
            $this->logger->warning('Employee not found for password reset', [
                'email' => $email,
                'company_id' => $company_id
            ], 'auth');
            $this->output([
                'status' => 404,
                'message' => 'Employee not found'
            ]);
            return;
        }

        // Update password
        $this->employees->update_password($employee_data->id, password_hash($password, PASSWORD_DEFAULT));

        // Invalidate the reset token (delete the OTP record)
        $this->employees->delete_otp($otp_record->id);

        $this->logger->info('Password reset successfully', [
            'employee_id' => $employee_data->id,
            'email' => $email,
            'company_id' => $company_id
        ], 'auth');

        // Send success email
        $this->load->library('mailer');
        $this->mailer->send_password_reset_success($email, $employee_data->first_name);

        $this->output([
            'status' => 200,
            'message' => 'Password reset successfully. You can now login with your new password.'
        ]);
    }

    /**
     * Mask email address for privacy
     *
     * @param string $email The email address
     * @return string The masked email
     */
    private function mask_email($email)
    {
        $parts = explode('@', $email);
        if (count($parts) != 2) return $email;

        $name = $parts[0];
        $domain = $parts[1];

        $name_length = strlen($name);
        if ($name_length <= 2) {
            $masked_name = str_repeat('*', $name_length);
        } else {
            $masked_name = substr($name, 0, 2) . str_repeat('*', $name_length - 2);
        }

        return $masked_name . '@' . $domain;
    }

    /**
     * Logout
     *
     * Logs out the authenticated user.
     * Since JWT is stateless, this just returns a success response.
     * Client should discard the token on their end.
     *
     * POST /api/v1/user/auth/logout
     *
     * @return void JSON response with logout confirmation
     */
    public function logout()
    {
        // Check API key
        if (!$this->check_auth()) {
            return;
        }

        // Get token to log employee_id (optional verification)
        $token = $this->check_bearer_token();
        $employee_id = null;

        if ($token) {
            $decoded = $this->decode_token($token);
            if ($decoded && isset($decoded->employee_id)) {
                $employee_id = $decoded->employee_id;
            }
        }

        $this->logger->info('User logged out', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address(),
        ], 'auth');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Logout successful',
            'data' => null
        ]);
    }

    /**
     * Update FCM Token
     *
     * Stores the Firebase Cloud Messaging token for push notifications.
     *
     * POST /api/v1/user/auth/update_fcm
     *
     * Body Parameters (form-data):
     * - fcm_token (required): The FCM device token
     *
     * @return void JSON response
     */
    public function update_fcm()
    {
        // Check API key
        if (!$this->check_auth()) {
            return;
        }

        // Get Bearer token
        $token = $this->check_bearer_token();

        if (empty($token)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Authorization token is required',
                'data' => null
            ]);
            return;
        }

        // Decode token
        $decoded = $this->decode_token($token);

        if (empty($decoded) || !isset($decoded->employee_id)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid or expired token',
                'data' => null
            ]);
            return;
        }

        $employee_id = $decoded->employee_id;

        // Get form-data input
        $post_data = $this->input->post(null, true);
        $fcm_token = isset($post_data['fcm_token']) ? trim($post_data['fcm_token']) : null;

        if (empty($fcm_token)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'fcm_token is required',
                'data' => null
            ]);
            return;
        }

        // Update fcm_token in employees table
        $this->db->where('id', $employee_id);
        $updated = $this->db->update('employees', [
            'fcm_token' => $fcm_token,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if (!$updated) {
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to update FCM token',
                'data' => null
            ]);
            return;
        }

        $this->logger->info('FCM token updated', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address()
        ], 'auth');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'FCM token updated successfully',
            'data' => null
        ]);
    }
}