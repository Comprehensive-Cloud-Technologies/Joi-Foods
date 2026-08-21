<?php
//Jai Sree Ram
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Coupons API Controller
 *
 * Handles coupon verification for QSR, KOT, and PREMEAL modules.
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
class Coupons extends CI_Controller
{
    private $tokenHandler;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->library('CouponLib');
        $this->tokenHandler = new TokenHandler();
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
     * Verify Coupon
     *
     * Validates a coupon code and returns discount details.
     *
     * POST /api/v1/user/coupons/verify
     *
     * Required parameters (form-data):
     * - coupon_code: Coupon code to verify
     * - module: Module type (QSR, KOT, PREMEAL)
     * - store_id: Store ID
     * - amount: Total payable amount before discount
     *
     * @return void JSON response with coupon validity and pricing summary
     */
    public function verify()
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

        // Get form-data input
        $post_data = $this->input->post(null, true);

        $coupon_code = isset($post_data['coupon_code']) ? trim($post_data['coupon_code']) : null;
        $module = isset($post_data['module']) ? strtoupper(trim($post_data['module'])) : null;
        $store_id = isset($post_data['store_id']) ? (int) $post_data['store_id'] : null;
        $amount = isset($post_data['amount']) ? (float) $post_data['amount'] : 0;

        // Validate required fields
        if (empty($coupon_code)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'coupon_code is required',
                'data' => null
            ]);
            return;
        }

        if (empty($module) || !in_array($module, ['QSR', 'KOT', 'PREMEAL'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Valid module is required (QSR, KOT, PREMEAL)',
                'data' => null
            ]);
            return;
        }

        if (empty($store_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'store_id is required',
                'data' => null
            ]);
            return;
        }

        if ($amount <= 0) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Valid amount is required',
                'data' => null
            ]);
            return;
        }

        // Validate store belongs to company
        $store = $this->common->getdatabytable('stores', [
            'id' => $store_id,
            'company_id' => $company_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($store)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Invalid store or store not accessible',
                'data' => null
            ]);
            return;
        }

        // Get client_id from company
        $company = $this->common->getdatabytable('companies', ['id' => $company_id]);
        $client_id = $company ? $company->client_id : 0;

        // Validate coupon
        $coupon_result = $this->couponlib->validateCoupon([
            'coupon_code' => $coupon_code,
            'order_amount' => $amount,
            'employee_id' => $employee_id,
            'company_id' => $company_id,
            'client_id' => $client_id,
            'module' => $module
        ]);

        if (!$coupon_result['valid']) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => $coupon_result['message'],
                'data' => [
                    'valid' => false
                ]
            ]);
            return;
        }

        // Coupon is valid - prepare pricing summary
        $coupon = $coupon_result['coupon'];
        $discount_amount = $coupon_result['discount_amount'];
        $total_after_discount = round($amount - $discount_amount, 2);

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Coupon is valid',
            'data' => [
                'valid' => true,
                'coupon' => [
                    'id' => (int) $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => (float) $coupon->discount_value,
                    'max_discount_amount' => $coupon->max_discount_amount ? (float) $coupon->max_discount_amount : null,
                    'min_order_amount' => (float) $coupon->min_order_amount
                ],
                'pricing' => [
                    'original_amount' => round($amount, 2),
                    'discount_amount' => round($discount_amount, 2),
                    'total_after_discount' => $total_after_discount
                ],
                'message' => 'You save ₹' . number_format($discount_amount, 2) . ' with this coupon!'
            ]
        ]);
    }
}
