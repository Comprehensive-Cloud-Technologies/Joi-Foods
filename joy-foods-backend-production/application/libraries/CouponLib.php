<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CouponLib - Coupon Validation and Application Library
 *
 * Handles coupon validation, discount calculation, and usage tracking
 * for QSR, KOT, and PREMEAL order modules.
 */
class CouponLib
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
    }

    /**
     * Validate a coupon and calculate discount
     *
     * @param array $params Required keys:
     *   - coupon_code: string
     *   - order_amount: float (amount before discount)
     *   - employee_id: int
     *   - company_id: int
     *   - client_id: int
     *   - module: string (QSR|KOT|PREMEAL)
     *
     * @return array [
     *   'valid' => bool,
     *   'message' => string,
     *   'coupon' => object|null,
     *   'discount_amount' => float,
     *   'final_amount' => float
     * ]
     */
    public function validateCoupon($params)
    {
        $response = [
            'valid' => false,
            'message' => '',
            'coupon' => null,
            'discount_amount' => 0,
            'final_amount' => isset($params['order_amount']) ? $params['order_amount'] : 0
        ];

        // Required parameters check
        $required = ['coupon_code', 'order_amount', 'employee_id', 'company_id', 'client_id', 'module'];
        foreach ($required as $field) {
            if (!isset($params[$field]) || $params[$field] === '') {
                $response['message'] = 'Missing required parameter: ' . $field;
                return $response;
            }
        }

        $coupon_code = strtoupper(trim($params['coupon_code']));
        $order_amount = floatval($params['order_amount']);
        $employee_id = intval($params['employee_id']);
        $company_id = intval($params['company_id']);
        $client_id = intval($params['client_id']);
        $module = strtoupper($params['module']);

        // Validate module
        if (!in_array($module, ['QSR', 'KOT', 'PREMEAL'])) {
            $response['message'] = 'Invalid module type';
            return $response;
        }

        // Fetch coupon
        $coupon = $this->CI->db
            ->where('code', $coupon_code)
            ->where('client_id', $client_id)
            ->where('deleted_at IS NULL')
            ->get('coupons')
            ->row();

        if (!$coupon) {
            $response['message'] = 'Invalid coupon code';
            return $response;
        }

        // Check if coupon is active
        if ($coupon->is_active != 1) {
            $response['message'] = 'This coupon is no longer active';
            return $response;
        }

        // Check company restriction
        if (!empty($coupon->company_id) && $coupon->company_id != $company_id) {
            $response['message'] = 'This coupon is not valid for your company';
            return $response;
        }

        // Check validity period
        $now = date('Y-m-d H:i:s');
        if ($now < $coupon->valid_from) {
            $response['message'] = 'This coupon is not yet valid. Valid from: ' . date('d M Y', strtotime($coupon->valid_from));
            return $response;
        }

        if (!empty($coupon->valid_until) && $now > $coupon->valid_until) {
            $response['message'] = 'This coupon has expired';
            return $response;
        }

        // Check module applicability
        $module_field = 'applies_to_' . strtolower($module);
        if (!$coupon->$module_field) {
            $response['message'] = 'This coupon is not applicable for ' . $module . ' orders';
            return $response;
        }

        // Check minimum order amount
        if ($order_amount < floatval($coupon->min_order_amount)) {
            $response['message'] = 'Minimum order amount of ₹' . number_format($coupon->min_order_amount, 2) . ' required for this coupon';
            return $response;
        }

        // Check total usage limit
        if (!empty($coupon->usage_limit) && $coupon->usage_count >= $coupon->usage_limit) {
            $response['message'] = 'This coupon has reached its usage limit';
            return $response;
        }

        // Check per-user usage limit
        if (!empty($coupon->per_user_limit)) {
            $user_usage = $this->getUserUsageCount($coupon->id, $employee_id);
            if ($user_usage >= $coupon->per_user_limit) {
                $response['message'] = 'You have already used this coupon the maximum number of times';
                return $response;
            }
        }

        // Calculate discount
        $discount_amount = $this->calculateDiscount($coupon, $order_amount);
        $final_amount = $order_amount - $discount_amount;

        // Ensure final amount is not negative
        if ($final_amount < 0) {
            $final_amount = 0;
            $discount_amount = $order_amount;
        }

        $response['valid'] = true;
        $response['message'] = 'Coupon applied successfully';
        $response['coupon'] = $coupon;
        $response['discount_amount'] = round($discount_amount, 2);
        $response['final_amount'] = round($final_amount, 2);

        return $response;
    }

    /**
     * Calculate discount amount based on coupon type
     *
     * @param object $coupon
     * @param float $order_amount
     * @return float
     */
    public function calculateDiscount($coupon, $order_amount)
    {
        $discount = 0;

        if ($coupon->discount_type == 'PERCENTAGE') {
            $discount = ($order_amount * floatval($coupon->discount_value)) / 100;

            // Apply max discount cap if set
            if (!empty($coupon->max_discount_amount) && $discount > floatval($coupon->max_discount_amount)) {
                $discount = floatval($coupon->max_discount_amount);
            }
        } else {
            // FIXED discount
            $discount = floatval($coupon->discount_value);
        }

        // Discount cannot exceed order amount
        if ($discount > $order_amount) {
            $discount = $order_amount;
        }

        return $discount;
    }

    /**
     * Get user's usage count for a specific coupon
     *
     * @param int $coupon_id
     * @param int $employee_id
     * @return int
     */
    public function getUserUsageCount($coupon_id, $employee_id)
    {
        return $this->CI->db
            ->where('coupon_id', $coupon_id)
            ->where('employee_id', $employee_id)
            ->count_all_results('coupon_usage');
    }

    /**
     * Apply coupon - Record usage after successful order
     *
     * @param int $coupon_id
     * @param int $employee_id
     * @param int $order_id
     * @param float $discount_amount
     * @return bool
     */
    public function applyCoupon($coupon_id, $employee_id, $order_id, $discount_amount)
    {
        $this->CI->db->trans_start();

        // Record coupon usage
        $usage_data = [
            'coupon_id' => $coupon_id,
            'employee_id' => $employee_id,
            'order_id' => $order_id,
            'discount_amount' => $discount_amount,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->CI->db->insert('coupon_usage', $usage_data);

        // Increment usage count on coupon
        $this->CI->db
            ->set('usage_count', 'usage_count + 1', false)
            ->where('id', $coupon_id)
            ->update('coupons');

        $this->CI->db->trans_complete();

        return $this->CI->db->trans_status();
    }

    /**
     * Reverse coupon usage - For order cancellation/refund
     *
     * @param int $order_id
     * @return bool
     */
    public function reverseCouponUsage($order_id)
    {
        // Get usage record
        $usage = $this->CI->db
            ->where('order_id', $order_id)
            ->get('coupon_usage')
            ->row();

        if (!$usage) {
            return true; // No coupon was used
        }

        $this->CI->db->trans_start();

        // Decrement usage count on coupon
        $this->CI->db
            ->set('usage_count', 'GREATEST(usage_count - 1, 0)', false)
            ->where('id', $usage->coupon_id)
            ->update('coupons');

        // Delete usage record
        $this->CI->db
            ->where('id', $usage->id)
            ->delete('coupon_usage');

        $this->CI->db->trans_complete();

        return $this->CI->db->trans_status();
    }

    /**
     * Get available coupons for an employee
     *
     * @param array $params Required keys:
     *   - employee_id: int
     *   - company_id: int
     *   - client_id: int
     *   - module: string (QSR|KOT|PREMEAL)
     *   - order_amount: float (optional - to filter by min_order_amount)
     *
     * @return array List of available coupons
     */
    public function getAvailableCoupons($params)
    {
        $employee_id = intval($params['employee_id']);
        $company_id = intval($params['company_id']);
        $client_id = intval($params['client_id']);
        $module = strtoupper($params['module']);
        $order_amount = isset($params['order_amount']) ? floatval($params['order_amount']) : null;

        $module_field = 'applies_to_' . strtolower($module);
        $now = date('Y-m-d H:i:s');

        $this->CI->db->select('c.*');
        $this->CI->db->from('coupons c');
        $this->CI->db->where('c.client_id', $client_id);
        $this->CI->db->where('c.is_active', 1);
        $this->CI->db->where('c.deleted_at IS NULL');
        $this->CI->db->where('c.' . $module_field, 1);
        $this->CI->db->where('c.valid_from <=', $now);
        $this->CI->db->group_start();
        $this->CI->db->where('c.valid_until IS NULL');
        $this->CI->db->or_where('c.valid_until >=', $now);
        $this->CI->db->group_end();

        // Company filter - null means all companies
        $this->CI->db->group_start();
        $this->CI->db->where('c.company_id IS NULL');
        $this->CI->db->or_where('c.company_id', $company_id);
        $this->CI->db->group_end();

        // Filter by order amount if provided
        if ($order_amount !== null) {
            $this->CI->db->where('c.min_order_amount <=', $order_amount);
        }

        // Check total usage limit
        $this->CI->db->group_start();
        $this->CI->db->where('c.usage_limit IS NULL');
        $this->CI->db->or_where('c.usage_count < c.usage_limit', null, false);
        $this->CI->db->group_end();

        $this->CI->db->order_by('c.discount_type', 'ASC'); // Percentage first
        $this->CI->db->order_by('c.discount_value', 'DESC'); // Higher value first

        $coupons = $this->CI->db->get()->result();

        // Filter by per-user limit
        $available_coupons = [];
        foreach ($coupons as $coupon) {
            if (!empty($coupon->per_user_limit)) {
                $user_usage = $this->getUserUsageCount($coupon->id, $employee_id);
                if ($user_usage >= $coupon->per_user_limit) {
                    continue; // Skip - user has used this coupon max times
                }
            }

            // Calculate potential savings if order amount provided
            if ($order_amount !== null) {
                $coupon->potential_discount = $this->calculateDiscount($coupon, $order_amount);
            }

            $available_coupons[] = $coupon;
        }

        return $available_coupons;
    }

    /**
     * Get coupon by ID with full details
     *
     * @param int $coupon_id
     * @param int $client_id
     * @return object|null
     */
    public function getCouponById($coupon_id, $client_id)
    {
        return $this->CI->db
            ->where('id', $coupon_id)
            ->where('client_id', $client_id)
            ->where('deleted_at IS NULL')
            ->get('coupons')
            ->row();
    }

    /**
     * Get coupon by code
     *
     * @param string $coupon_code
     * @param int $client_id
     * @return object|null
     */
    public function getCouponByCode($coupon_code, $client_id)
    {
        return $this->CI->db
            ->where('code', strtoupper(trim($coupon_code)))
            ->where('client_id', $client_id)
            ->where('deleted_at IS NULL')
            ->get('coupons')
            ->row();
    }

    /**
     * Format coupon for API response
     *
     * @param object $coupon
     * @param float|null $order_amount Optional - to include potential discount
     * @return array
     */
    public function formatCouponForApi($coupon, $order_amount = null)
    {
        $formatted = [
            'id' => (int) $coupon->id,
            'code' => $coupon->code,
            'name' => $coupon->name,
            'description' => $coupon->description,
            'discount_type' => $coupon->discount_type,
            'discount_value' => (float) $coupon->discount_value,
            'max_discount_amount' => $coupon->max_discount_amount ? (float) $coupon->max_discount_amount : null,
            'min_order_amount' => (float) $coupon->min_order_amount,
            'valid_from' => $coupon->valid_from,
            'valid_until' => $coupon->valid_until,
            'applies_to_qsr' => (bool) $coupon->applies_to_qsr,
            'applies_to_kot' => (bool) $coupon->applies_to_kot,
            'applies_to_premeal' => (bool) $coupon->applies_to_premeal
        ];

        // Add discount summary text
        if ($coupon->discount_type == 'PERCENTAGE') {
            $formatted['discount_text'] = $coupon->discount_value . '% off';
            if (!empty($coupon->max_discount_amount)) {
                $formatted['discount_text'] .= ' (up to ₹' . number_format($coupon->max_discount_amount, 0) . ')';
            }
        } else {
            $formatted['discount_text'] = '₹' . number_format($coupon->discount_value, 0) . ' off';
        }

        // Add potential savings if order amount provided
        if ($order_amount !== null) {
            $formatted['potential_discount'] = round($this->calculateDiscount($coupon, $order_amount), 2);
        }

        return $formatted;
    }
}
