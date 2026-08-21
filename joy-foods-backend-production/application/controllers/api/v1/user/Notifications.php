<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Notifications API Controller
 *
 * Handles in-app notification operations for employees.
 * Provides listing, unread count, and mark-as-read endpoints.
 *
 * @category  Controllers
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-02-24
 */
class Notifications extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Notifications_model', 'notifications_model');
        $this->tokenHandler = new TokenHandler();

        // Load Monolog library for logging
        $this->load->library('monolog');
        $this->logger = new Monolog();
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

        if (!isset($decoded->role) || $decoded->role !== 'employee') {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Access denied. Invalid role',
                'data' => null
            ]);
            return false;
        }

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
     * Calculate relative time ago string
     *
     * @param string $datetime DateTime string
     * @return string Relative time
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
     * Get Notifications List
     *
     * Returns paginated list of notifications for the authenticated employee.
     *
     * GET /api/v1/user/notifications/list
     * GET /api/v1/user/notifications/list?page=1&per_page=20
     *
     * @return void JSON response with notifications list
     */
    public function list()
    {
        if (!$this->check_auth()) {
            return;
        }

        $auth = $this->authenticate();
        if (!$auth) {
            return;
        }

        $employee_id = $auth->employee_id;

        $this->logger->info('Notifications List API called', [
            'employee_id' => $employee_id,
            'ip' => $this->input->ip_address()
        ], 'notifications');

        // Pagination
        $page = (int)$this->input->get('page', true) ?: 1;
        $per_page = (int)$this->input->get('per_page', true) ?: 20;

        if ($page < 1) $page = 1;
        if ($per_page < 1) $per_page = 20;
        if ($per_page > 50) $per_page = 50;

        $offset = ($page - 1) * $per_page;

        // Get data
        $total_count = $this->notifications_model->get_count($employee_id);
        $notifications = $this->notifications_model->get_list($employee_id, $per_page, $offset);

        $total_pages = $total_count > 0 ? ceil($total_count / $per_page) : 0;

        // Format notifications
        $notifications_data = [];
        foreach ($notifications as $notification) {
            $notifications_data[] = [
                'id' => (int)$notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'order_id' => $notification->order_id ? (int)$notification->order_id : null,
                'order_number' => $notification->order_number,
                'module' => $notification->module,
                'time_ago' => $this->time_ago($notification->created_at),
                'created_at' => $notification->created_at
            ];
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($notifications_data) ? 'No notifications' : 'Notifications fetched successfully',
            'data' => [
                'notifications' => $notifications_data,
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

}
