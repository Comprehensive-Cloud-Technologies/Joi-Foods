<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Support API Controller
 *
 * Handles support inquiry operations for employees.
 * Provides topic listing and inquiry submission.
 *
 * @category  Controllers
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-13
 */
class Support extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    /**
     * Predefined support topics
     */
    private $topics = [
        'Order Issue',
        'Payment Issue',
        'Account Issue',
        'App Feedback',
        'General Inquiry'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
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
     * Get Support Topics
     *
     * Returns list of predefined support topics with suggested subjects.
     *
     * GET /api/v1/user/support/topics
     *
     * @return void JSON response with topics list
     */
    public function topics()
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

        $this->logger->info('Support Topics API called', [
            'employee_id' => $auth->employee_id,
            'ip' => $this->input->ip_address()
        ], 'support');

        // Get client contact info via company
        $company = $this->common->getdatabytable('companies', ['id' => $auth->company_id]);
        $client = null;
        if ($company) {
            $client = $this->common->getdatabytable('clients', ['id' => $company->client_id]);
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Topics retrieved successfully',
            'data' => [
                'topics' => $this->topics,
                'support_email' => $client->email ?? null,
                'support_phone' => $client->phone ?? null
            ]
        ]);
    }

    /**
     * Submit Support Inquiry
     *
     * Submits a new support inquiry from the employee.
     *
     * POST /api/v1/user/support/submit
     *
     * Required parameters (form-data):
     * - topic: Topic key (e.g., ORDER_ISSUE, PAYMENT_ISSUE)
     * - subject: Subject line
     * - message: Detailed message
     *
     * @return void JSON response with submission confirmation
     */
    public function submit()
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

        $this->logger->info('Support Submit API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address()
        ], 'support');

        // Get form-data input
        $post_data = $this->input->post(null, true);

        $topic = isset($post_data['topic']) ? trim($post_data['topic']) : null;
        $subject = isset($post_data['subject']) ? trim($post_data['subject']) : null;
        $message = isset($post_data['message']) ? trim($post_data['message']) : null;

        // Validate required fields
        if (empty($topic)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Topic is required',
                'data' => null
            ]);
            return;
        }

        if (empty($subject)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Subject is required',
                'data' => null
            ]);
            return;
        }

        if (empty($message)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Message is required',
                'data' => null
            ]);
            return;
        }

        // Validate topic exists
        if (!in_array($topic, $this->topics)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid topic. Please select a valid topic.',
                'data' => null
            ]);
            return;
        }

        // Validate message length
        if (strlen($message) < 10) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Message must be at least 10 characters long',
                'data' => null
            ]);
            return;
        }

        if (strlen($message) > 5000) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Message cannot exceed 5000 characters',
                'data' => null
            ]);
            return;
        }

        // Get client_id from company
        $company = $this->common->getdatabytable('companies', ['id' => $company_id]);
        if (empty($company)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Company not found',
                'data' => null
            ]);
            return;
        }

        $client_id = $company->client_id;

        // Get employee details
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

        // Prepare inquiry data
        $inquiry_data = [
            'client_id' => $client_id,
            'company_id' => $company_id,
            'employee_id' => $employee_id,
            'topic' => $topic,
            'subject' => $subject,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Insert inquiry
        $this->db->insert('support_inquiries', $inquiry_data);
        $inquiry_id = $this->db->insert_id();

        if (!$inquiry_id) {
            $this->logger->error('Failed to submit support inquiry', [
                'employee_id' => $employee_id,
                'topic' => $topic
            ], 'support');

            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to submit inquiry. Please try again.',
                'data' => null
            ]);
            return;
        }

        $this->logger->info('Support inquiry submitted successfully', [
            'inquiry_id' => $inquiry_id,
            'employee_id' => $employee_id,
            'topic' => $topic,
            'subject' => $subject
        ], 'support');

        // Send email notification to client
        $client = $this->common->getdatabytable('clients', ['id' => $client_id]);
        if ($client && !empty($client->email)) {
            try {
                $this->load->library('Mailer', null, 'mailer');
                $employee_name = trim($employee->first_name . ' ' . ($employee->last_name ?? ''));
                $this->mailer->send_support_inquiry($client->email, [
                    'inquiry_id'     => $inquiry_id,
                    'topic'          => $topic,
                    'subject'        => $subject,
                    'message'        => $message,
                    'employee_name'  => $employee_name,
                    'employee_email' => $employee->email ?? '',
                    'employee_phone' => $employee->phone ?? '',
                    'company_name'   => $company->name ?? ''
                ]);
            } catch (Exception $e) {
                $this->logger->error('Failed to send support inquiry email', [
                    'inquiry_id' => $inquiry_id,
                    'client_email' => $client->email,
                    'error' => $e->getMessage()
                ], 'support');
            }
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Your inquiry has been submitted successfully. We will get back to you soon.',
            'data' => [
                'inquiry_id' => (int) $inquiry_id,
                'topic' => $topic,
                'subject' => $subject,
                'submitted_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
