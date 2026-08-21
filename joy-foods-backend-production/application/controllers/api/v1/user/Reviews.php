<?php
//Jai Sree Ram
defined('BASEPATH') OR exit('No direct script access allowed');

class Reviews extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Reviews_model', 'reviews');
        $this->tokenHandler = new TokenHandler();
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
            return substr($authHeader, 7);
        }
        return null;
    }

    private function decode_token($token)
    {
        try {
            return $this->tokenHandler->DecodeToken($token);
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
                'message' => 'Authorization token is required'
            ]);
            return false;
        }

        $decoded = $this->decode_token($token);

        if (empty($decoded)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid or expired token'
            ]);
            return false;
        }

        if (isset($decoded->exp) && $decoded->exp < time()) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Token has expired'
            ]);
            return false;
        }

        if (!isset($decoded->role) || $decoded->role !== 'employee') {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Access denied. Invalid role'
            ]);
            return false;
        }

        if (!isset($decoded->employee_id) || !isset($decoded->company_id)) {
            $this->output([
                'status' => 401,
                'success' => false,
                'message' => 'Invalid token data'
            ]);
            return false;
        }

        return $decoded;
    }

    /**
     * Submit Review
     *
     * Submit a review for a completed order. One review per order only.
     *
     * @api POST /api/v1/user/reviews/submit_review
     *
     * @header Authorization Bearer {token}
     * @param int    order_id       - Order ID to review
     * @param string food_review    - Review text for food quality
     * @param string service_review - Review text for service quality
     * @param string extra_comments - Any additional comments (optional)
     *
     * @return void JSON response
     */
    public function submit_review()
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

        $order_id = $this->input->post('order_id', true);
        $food_review = $this->input->post('food_review', true);
        $service_review = $this->input->post('service_review', true);
        $extra_comments = $this->input->post('extra_comments', true);

        // Validate required fields
        if (empty($order_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Order ID is required'
            ]);
            return;
        }

        if (empty($food_review) && empty($service_review)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'At least food review or service review is required'
            ]);
            return;
        }

        // Get order and validate ownership
        $order = $this->common->getdatabytable('orders', [
            'id' => $order_id,
            'employee_id' => $employee_id,
            'company_id' => $company_id
        ]);

        if (empty($order)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Order not found'
            ]);
            return;
        }

        // Check order status - only completed/delivered orders can be reviewed
        $reviewable_statuses = ['COMPLETED', 'DELIVERED'];
        // if (!in_array($order->status, $reviewable_statuses)) {
        //     $this->output([
        //         'status' => 400,
        //         'success' => false,
        //         'message' => 'Only completed or delivered orders can be reviewed'
        //     ]);
        //     return;
        // }

        // Check if review is required for this order
        if (isset($order->is_review_required) && $order->is_review_required == 0) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Review is not required for this order'
            ]);
            return;
        }

        // Check if already reviewed
        if ($order->is_reviewed == 1) {
            $this->output([
                'status' => 409,
                'success' => false,
                'message' => 'Review already submitted for this order'
            ]);
            return;
        }

        // Double-check in reviews table
        if ($this->reviews->review_exists($order_id)) {
            // Sync the flag if out of sync
            $this->reviews->mark_order_reviewed($order_id);
            $this->output([
                'status' => 409,
                'success' => false,
                'message' => 'Review already submitted for this order'
            ]);
            return;
        }

        // Insert review
        $review_data = [
            'order_id' => $order_id,
            'employee_id' => $employee_id,
            'store_id' => $order->store_id,
            'module' => $order->module,
            'food_review' => $food_review,
            'service_review' => $service_review,
            'extra_comments' => $extra_comments
        ];

        $review_id = $this->reviews->create_review($review_data);

        if (!$review_id) {
            $this->logger->error('Failed to create review', [
                'order_id' => $order_id,
                'employee_id' => $employee_id
            ], 'reviews');
            $this->output([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to submit review'
            ]);
            return;
        }

        // Mark order as reviewed
        $this->reviews->mark_order_reviewed($order_id);

        $this->logger->info('Review submitted', [
            'review_id' => $review_id,
            'order_id' => $order_id,
            'employee_id' => $employee_id,
            'module' => $order->module
        ], 'reviews');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => [
                'review_id' => (int)$review_id
            ]
        ]);
    }
}
