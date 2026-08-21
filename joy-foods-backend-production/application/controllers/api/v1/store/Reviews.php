<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Store Reviews Controller
 *
 * Feedback/review reports API for store staff.
 * Returns reviews scoped to the authenticated store only.
 */
class Reviews extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('StoreReviews_model', 'reviews_model');
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
            return substr($headers_of_page['Authorization'], 7);
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
     * Get feedback/reviews list with date filters and pagination
     *
     * POST /api/v1/store/reviews/list_reviews
     * Input: date_from (required), date_to (required), page (opt), per_page (opt)
     * Max date range: 1 month
     */
    public function list_reviews()
    {
        if (!$this->check_auth()) return;
        $auth = $this->authenticate();
        if (!$auth) return;

        $store_id = $auth->store_id;

        $this->logger->info('Store Reviews List API called', [
            'staff_id' => $auth->staff_id,
            'store_id' => $store_id,
            'ip' => $this->input->ip_address()
        ], 'store_reviews');

        $date_from = $this->input->post('date_from', true);
        $date_to = $this->input->post('date_to', true);

        if (empty($date_from) || empty($date_to)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'date_from and date_to are required',
                'data' => null
            ]);
            return;
        }

        $from = strtotime($date_from);
        $to = strtotime($date_to);

        if (!$from || !$to || $to < $from) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid date range. date_to must be after date_from',
                'data' => null
            ]);
            return;
        }

        if (($to - $from) > (31 * 86400)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Date range cannot exceed 1 month',
                'data' => null
            ]);
            return;
        }

        $page = (int)$this->input->post('page', true) ?: 1;
        $per_page = (int)$this->input->post('per_page', true) ?: 20;

        if ($per_page > 100) $per_page = 100;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $per_page;

        $filters = [
            'date_from' => $date_from,
            'date_to' => $date_to
        ];

        $total_count = $this->reviews_model->get_reviews_count($store_id, $filters);
        $total_pages = $per_page > 0 ? ceil($total_count / $per_page) : 1;

        $filters['limit'] = $per_page;
        $filters['offset'] = $offset;
        $reviews = $this->reviews_model->get_reviews($store_id, $filters);

        $reviews_data = [];
        foreach ($reviews as $review) {
            $customer_name = null;
            if (!empty($review->is_guest_order)) {
                $customer_name = $review->guest_name;
            } else if (!empty($review->first_name)) {
                $customer_name = trim($review->first_name . ' ' . $review->last_name);
            }

            $reviews_data[] = [
                'id' => (int)$review->id,
                'order_id' => (int)$review->order_id,
                'order_number' => $review->order_number,
                'module' => $review->module,
                'company_name' => $review->company_name,
                'customer_name' => $customer_name,
                'employee_code' => $review->employee_code,
                'is_guest_order' => !empty($review->is_guest_order),
                'food_review' => $review->food_review,
                'service_review' => $review->service_review,
                'extra_comments' => $review->extra_comments,
                'order_amount' => (float)$review->total_amount,
                'order_status' => $review->order_status,
                'created_at' => $review->created_at
            ];
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($reviews_data) ? 'No reviews found' : 'Reviews fetched successfully',
            'data' => [
                'reviews' => $reviews_data,
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
     * Get reviews summary stats
     *
     * POST /api/v1/store/reviews/summary
     * Input: date_from (required), date_to (required)
     * Max date range: 1 month
     */
    public function summary()
    {
        if (!$this->check_auth()) return;
        $auth = $this->authenticate();
        if (!$auth) return;

        $store_id = $auth->store_id;

        $date_from = $this->input->post('date_from', true);
        $date_to = $this->input->post('date_to', true);

        if (empty($date_from) || empty($date_to)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'date_from and date_to are required',
                'data' => null
            ]);
            return;
        }

        $from = strtotime($date_from);
        $to = strtotime($date_to);

        if (!$from || !$to || $to < $from) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid date range. date_to must be after date_from',
                'data' => null
            ]);
            return;
        }

        if (($to - $from) > (31 * 86400)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Date range cannot exceed 1 month',
                'data' => null
            ]);
            return;
        }

        $filters = [
            'date_from' => $date_from,
            'date_to' => $date_to
        ];

        $summary = $this->reviews_model->get_reviews_summary($store_id, $filters);

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Summary fetched successfully',
            'data' => [
                'total_reviews' => (int)$summary->total_reviews
            ]
        ]);
    }

    /**
     * Get single review detail
     *
     * POST /api/v1/store/reviews/detail
     * Input: review_id
     */
    public function detail()
    {
        if (!$this->check_auth()) return;
        $auth = $this->authenticate();
        if (!$auth) return;

        $store_id = $auth->store_id;
        $review_id = $this->input->post('review_id', true);

        if (empty($review_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'review_id is required',
                'data' => null
            ]);
            return;
        }

        $review = $this->reviews_model->get_review_detail($review_id, $store_id);

        if (empty($review)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Review not found',
                'data' => null
            ]);
            return;
        }

        $customer_name = null;
        $customer_phone = null;
        $customer_email = null;
        if (!empty($review->is_guest_order)) {
            $customer_name = $review->guest_name;
            $customer_phone = $review->guest_phone;
        } else if (!empty($review->first_name)) {
            $customer_name = trim($review->first_name . ' ' . $review->last_name);
            $customer_phone = $review->employee_phone;
            $customer_email = $review->employee_email;
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Review detail fetched successfully',
            'data' => [
                'review' => [
                    'id' => (int)$review->id,
                    'order_id' => (int)$review->order_id,
                    'order_number' => $review->order_number,
                    'module' => $review->order_module,
                    'order_amount' => (float)$review->total_amount,
                    'order_status' => $review->order_status,
                    'order_date' => $review->order_date,
                    'company_name' => $review->company_name,
                    'company_code' => $review->company_code,
                    'is_guest_order' => !empty($review->is_guest_order),
                    'customer' => [
                        'name' => $customer_name,
                        'employee_code' => $review->employee_code,
                        'phone' => $customer_phone,
                        'email' => $customer_email
                    ],
                    'food_review' => $review->food_review,
                    'service_review' => $review->service_review,
                    'extra_comments' => $review->extra_comments,
                    'created_at' => $review->created_at
                ]
            ]
        ]);
    }
}
