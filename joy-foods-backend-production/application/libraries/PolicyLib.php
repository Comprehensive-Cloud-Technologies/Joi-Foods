<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PolicyLib - Policy Calculation and Validation Library
 *
 * Handles policy retrieval, contribution calculations, meal limits,
 * and booking validations for PREMEAL ordering system.
 *
 * @category  Libraries
 * @package   Joy_Foods
 * @author    ZooBit Infotech
 * @version   1.0.0
 */
class PolicyLib
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->database();
    }

    /**
     * Get effective policy for an employee
     *
     * Priority: employee_policies > company default policy
     *
     * @param int $employee_id
     * @param string $module (QSR|KOT|PREMEAL)
     * @return object|null Policy object with all settings
     */
    public function get_employee_policy($employee_id, $module = 'PREMEAL')
    {
        // Determine module-specific filter field
        $module = strtoupper($module);
        $applies_field = $this->get_applies_to_field($module);

        // First check employee_policies table
        $query = $this->CI->db
            ->select('ep.*, p.*')
            ->from('employee_policies ep')
            ->join('policies p', 'p.id = ep.policy_id')
            ->where('ep.employee_id', $employee_id)
            ->where('ep.is_active', 1)
            ->where('p.is_active', 1)
            ->where('p.deleted_at IS NULL')
            ->where('ep.effective_from <=', date('Y-m-d'))
            ->group_start()
                ->where('ep.effective_until IS NULL')
                ->or_where('ep.effective_until >=', date('Y-m-d'))
            ->group_end();

        if ($applies_field) {
            $query->where('p.' . $applies_field, 1);
        }

        $employee_policy = $query
            ->order_by('ep.priority', 'DESC')
            ->get()
            ->row();

        if ($employee_policy) {
            // Apply custom overrides from employee_policies
            if (!empty($employee_policy->custom_daily_meal_limit)) {
                $employee_policy->daily_meal_limit = $employee_policy->custom_daily_meal_limit;
            }
            if (!empty($employee_policy->custom_monthly_budget_limit)) {
                $employee_policy->monthly_budget_limit = $employee_policy->custom_monthly_budget_limit;
            }
            return $employee_policy;
        }

        // Fallback to company's default policy
        $employee = $this->CI->db
            ->select('company_id')
            ->where('id', $employee_id)
            ->get('employees')
            ->row();

        if (!$employee) {
            return null;
        }

        $cp_query = $this->CI->db
            ->select('cp.*, p.*')
            ->from('company_policies cp')
            ->join('policies p', 'p.id = cp.policy_id')
            ->where('cp.company_id', $employee->company_id)
            ->where('cp.is_default', 1)
            ->where('cp.is_active', 1)
            ->where('p.is_active', 1)
            ->where('p.deleted_at IS NULL')
            ->where('cp.effective_from <=', date('Y-m-d'))
            ->group_start()
                ->where('cp.effective_until IS NULL')
                ->or_where('cp.effective_until >=', date('Y-m-d'))
            ->group_end();

        if ($applies_field) {
            $cp_query->where('p.' . $applies_field, 1);
        }

        $company_policy = $cp_query->get()->row();

        if ($company_policy) {
            // Apply custom overrides from company_policies
            if (!empty($company_policy->custom_daily_meal_limit)) {
                $company_policy->daily_meal_limit = $company_policy->custom_daily_meal_limit;
            }
            if (!empty($company_policy->custom_monthly_budget_limit)) {
                $company_policy->monthly_budget_limit = $company_policy->custom_monthly_budget_limit;
            }
        }

        return $company_policy;
    }

    /**
     * Get the applies_to field name for a module
     *
     * @param string $module
     * @return string|null
     */
    private function get_applies_to_field($module)
    {
        switch ($module) {
            case 'KOT':
                return 'applies_to_delivery';
            case 'PREMEAL':
                return 'applies_to_premeal';
            case 'QSR':
                return 'applies_to_qsr';
            default:
                return null;
        }
    }

    /**
     * Check if employee can book for a specific date and meal type
     *
     * Rules:
     * 1. Only 1 order per meal_type per day (can't book LUNCH twice)
     * 2. daily_meal_limit controls total meals per day across all types
     *
     * @param int $employee_id
     * @param string $date (Y-m-d format)
     * @param string $meal_type (BREAKFAST|LUNCH|DINNER)
     * @param object|null $policy Pre-fetched policy (optional)
     * @return array [available, daily_limit, used, remaining, reason]
     */
    public function check_daily_limit($employee_id, $date, $meal_type, $policy = null, $module = 'PREMEAL')
    {
        $module = strtoupper($module);

        if (!$policy) {
            $policy = $this->get_employee_policy($employee_id, $module);
        }

        $daily_limit = ($policy && $policy->daily_meal_limit) ? (int)$policy->daily_meal_limit : 1;

        // KOT module: count by created_at date (no scheduled_date), independent from PREMEAL
        if ($module === 'KOT') {
            $total_orders_today = $this->CI->db
                ->where('employee_id', $employee_id)
                ->where('DATE(created_at)', $date)
                ->where('module', 'KOT')
                ->where_not_in('status', ['CANCELLED', 'REJECTED'])
                ->count_all_results('orders');

            $remaining = $daily_limit - $total_orders_today;

            if ($remaining <= 0) {
                return [
                    'available' => false,
                    'daily_limit' => $daily_limit,
                    'used' => (int)$total_orders_today,
                    'remaining' => 0,
                    'reason' => 'Daily KOT order limit (' . $daily_limit . ') reached for today'
                ];
            }

            return [
                'available' => true,
                'daily_limit' => $daily_limit,
                'used' => (int)$total_orders_today,
                'remaining' => (int)$remaining
            ];
        }

        // PREMEAL module: existing logic with scheduled_date and meal_type
        // Rule 1: Check if already has order for this meal_type on this date (max 1 per meal type)
        $meal_type_count = $this->CI->db
            ->where('employee_id', $employee_id)
            ->where('scheduled_date', $date)
            ->where('meal_type', $meal_type)
            ->where('module', 'PREMEAL')
            ->where_not_in('status', ['CANCELLED', 'REJECTED'])
            ->count_all_results('orders');

        if ($meal_type_count > 0) {
            return [
                'available' => false,
                'daily_limit' => $daily_limit,
                'used' => (int)$meal_type_count,
                'remaining' => 0,
                'reason' => $meal_type . ' already booked for this date'
            ];
        }

        // Rule 2: Check total meals for the day against daily_meal_limit
        $total_meals_today = $this->CI->db
            ->where('employee_id', $employee_id)
            ->where('scheduled_date', $date)
            ->where('module', 'PREMEAL')
            ->where_not_in('status', ['CANCELLED', 'REJECTED'])
            ->count_all_results('orders');

        $remaining = $daily_limit - $total_meals_today;

        if ($remaining <= 0) {
            return [
                'available' => false,
                'daily_limit' => $daily_limit,
                'used' => (int)$total_meals_today,
                'remaining' => 0,
                'reason' => 'Daily meal limit (' . $daily_limit . ') reached for this date'
            ];
        }

        return [
            'available' => true,
            'daily_limit' => $daily_limit,
            'used' => (int)$total_meals_today,
            'remaining' => (int)$remaining
        ];
    }

    /**
     * Check weekly meal limit
     *
     * @param int $employee_id
     * @param string $date (Y-m-d format)
     * @param object|null $policy
     * @return array [available, weekly_limit, used, remaining]
     */
    public function check_weekly_limit($employee_id, $date, $policy = null)
    {
        if (!$policy) {
            $policy = $this->get_employee_policy($employee_id);
        }

        if (!$policy || empty($policy->weekly_meal_limit)) {
            return [
                'available' => true,
                'weekly_limit' => null,
                'used' => 0,
                'remaining' => null
            ];
        }

        // Get week start and end (Monday to Sunday)
        $week_start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
        $week_end = date('Y-m-d', strtotime('sunday this week', strtotime($date)));

        $existing_count = $this->CI->db
            ->where('employee_id', $employee_id)
            ->where('scheduled_date >=', $week_start)
            ->where('scheduled_date <=', $week_end)
            ->where('module', 'PREMEAL')
            ->where_not_in('status', ['CANCELLED', 'REJECTED'])
            ->count_all_results('orders');

        $remaining = $policy->weekly_meal_limit - $existing_count;

        return [
            'available' => $remaining > 0,
            'weekly_limit' => (int)$policy->weekly_meal_limit,
            'used' => (int)$existing_count,
            'remaining' => max(0, (int)$remaining)
        ];
    }

    /**
     * Check monthly meal limit
     *
     * @param int $employee_id
     * @param string $date (Y-m-d format)
     * @param object|null $policy
     * @return array [available, monthly_limit, used, remaining]
     */
    public function check_monthly_limit($employee_id, $date, $policy = null)
    {
        if (!$policy) {
            $policy = $this->get_employee_policy($employee_id);
        }

        if (!$policy || empty($policy->monthly_meal_limit)) {
            return [
                'available' => true,
                'monthly_limit' => null,
                'used' => 0,
                'remaining' => null
            ];
        }

        $month_start = date('Y-m-01', strtotime($date));
        $month_end = date('Y-m-t', strtotime($date));

        $existing_count = $this->CI->db
            ->where('employee_id', $employee_id)
            ->where('scheduled_date >=', $month_start)
            ->where('scheduled_date <=', $month_end)
            ->where('module', 'PREMEAL')
            ->where_not_in('status', ['CANCELLED', 'REJECTED'])
            ->count_all_results('orders');

        $remaining = $policy->monthly_meal_limit - $existing_count;

        return [
            'available' => $remaining > 0,
            'monthly_limit' => (int)$policy->monthly_meal_limit,
            'used' => (int)$existing_count,
            'remaining' => max(0, (int)$remaining)
        ];
    }

    /**
     * Check monthly budget limit
     *
     * @param int $employee_id
     * @param string $date (Y-m-d format)
     * @param float $new_amount Amount to be added
     * @param object|null $policy
     * @return array [available, budget_limit, used, remaining]
     */
    public function check_monthly_budget($employee_id, $date, $new_amount = 0, $policy = null)
    {
        if (!$policy) {
            $policy = $this->get_employee_policy($employee_id);
        }

        if (!$policy || empty($policy->monthly_budget_limit)) {
            return [
                'available' => true,
                'budget_limit' => null,
                'used' => 0,
                'remaining' => null
            ];
        }

        $month_start = date('Y-m-01', strtotime($date));
        $month_end = date('Y-m-t', strtotime($date));

        // Sum company_contribution for this month (exclude cancelled/rejected only)
        $used = $this->CI->db
            ->select_sum('company_contribution')
            ->where('employee_id', $employee_id)
            ->where('scheduled_date >=', $month_start)
            ->where('scheduled_date <=', $month_end)
            ->where('module', 'PREMEAL')
            ->where_not_in('status', ['CANCELLED', 'REJECTED'])
            ->get('orders')
            ->row();

        $used_amount = $used->company_contribution ?: 0;
        $remaining = $policy->monthly_budget_limit - $used_amount;

        return [
            'available' => ($remaining - $new_amount) >= 0,
            'budget_limit' => (float)$policy->monthly_budget_limit,
            'used' => (float)$used_amount,
            'remaining' => max(0, (float)$remaining)
        ];
    }

    /**
     * Calculate company and employee contribution
     *
     * @param object $policy
     * @param float $meal_value Total meal value
     * @return array [company_contribution, employee_contribution, policy_type]
     */
    public function calculate_contribution($policy, $meal_value)
    {
        if (!$policy) {
            // No policy - employee pays full amount
            return [
                'company_contribution' => 0,
                'employee_contribution' => $meal_value,
                'policy_type' => 'PAID'
            ];
        }

        $company_contribution = 0;
        $employee_contribution = $meal_value;

        switch ($policy->policy_type) {
            case 'FREE':
                // Company pays 100%
                $company_contribution = $meal_value;
                $employee_contribution = 0;
                break;

            case 'PARTIAL':
                // Calculate company share
                if ($policy->company_contribution_type == 'PERCENTAGE') {
                    $company_contribution = ($meal_value * $policy->company_contribution_value) / 100;
                } else { // FIXED_AMOUNT
                    $company_contribution = min($policy->company_contribution_value, $meal_value);
                }

                // Apply max meal value cap if set
                if (!empty($policy->max_meal_value) && $meal_value > $policy->max_meal_value) {
                    // Recalculate based on max meal value
                    if ($policy->company_contribution_type == 'PERCENTAGE') {
                        $company_contribution = ($policy->max_meal_value * $policy->company_contribution_value) / 100;
                    } else {
                        $company_contribution = min($policy->company_contribution_value, $policy->max_meal_value);
                    }
                }

                $employee_contribution = $meal_value - $company_contribution;
                break;

            case 'PAID':
            default:
                // Employee pays 100%
                $company_contribution = 0;
                $employee_contribution = $meal_value;
                break;
        }

        return [
            'company_contribution' => round($company_contribution, 2),
            'employee_contribution' => round($employee_contribution, 2),
            'policy_type' => $policy->policy_type
        ];
    }

    /**
     * Check if date is within advance booking limit
     *
     * @param object $policy
     * @param string $date (Y-m-d format)
     * @return array [bookable, max_date, days_ahead]
     */
    public function is_date_bookable($policy, $date)
    {
        $today = date('Y-m-d');
        $advance_days = $policy ? ($policy->advance_booking_days ?: 7) : 7;
        $max_date = date('Y-m-d', strtotime("+{$advance_days} days"));

        // Check if date is in the past
        if ($date < $today) {
            return [
                'bookable' => false,
                'reason' => 'Cannot book for past dates',
                'max_date' => $max_date,
                'advance_booking_days' => $advance_days
            ];
        }

        // Check if date is beyond allowed advance booking
        if ($date > $max_date) {
            return [
                'bookable' => false,
                'reason' => "Cannot book more than {$advance_days} days in advance",
                'max_date' => $max_date,
                'advance_booking_days' => $advance_days
            ];
        }

        return [
            'bookable' => true,
            'reason' => null,
            'max_date' => $max_date,
            'advance_booking_days' => $advance_days
        ];
    }

    /**
     * Check if cutoff time has passed for a meal
     *
     * @param object $store Store with meal times
     * @param string $meal_type (BREAKFAST|LUNCH|DINNER)
     * @param string $date (Y-m-d format)
     * @param object|null $policy Policy with booking_cutoff_hours
     * @return array [passed, cutoff_time, serving_time, reason]
     */
    public function is_cutoff_passed($store, $meal_type, $date, $policy = null)
    {
        $today = date('Y-m-d');
        $now = date('H:i:s');

        // Get serving time based on meal type
        $serving_time = null;
        switch ($meal_type) {
            case 'BREAKFAST':
                $serving_time = $store->breakfast_time;
                break;
            case 'LUNCH':
                $serving_time = $store->lunch_time;
                break;
            case 'DINNER':
                $serving_time = $store->dinner_time;
                break;
        }

        if (!$serving_time) {
            return [
                'passed' => true,
                'cutoff_time' => null,
                'serving_time' => null,
                'reason' => 'Meal time not configured for this store'
            ];
        }

        // Get cutoff hours from policy (default 2 hours)
        $cutoff_hours = $policy ? ($policy->booking_cutoff_hours ?: 2) : 2;

        // Calculate cutoff time
        $cutoff_time = date('H:i:s', strtotime("-{$cutoff_hours} hours", strtotime($serving_time)));

        // If booking is for today, check if cutoff has passed
        if ($date == $today) {
            if ($now >= $cutoff_time) {
                return [
                    'passed' => true,
                    'cutoff_time' => substr($cutoff_time, 0, 5),
                    'serving_time' => substr($serving_time, 0, 5),
                    'reason' => 'Booking cutoff time has passed for today'
                ];
            }
        }

        // For future dates, cutoff hasn't passed
        return [
            'passed' => false,
            'cutoff_time' => substr($cutoff_time, 0, 5),
            'serving_time' => substr($serving_time, 0, 5),
            'reason' => null
        ];
    }

    /**
     * Check if meal type is enabled in policy
     *
     * @param object $policy
     * @param string $meal_type (BREAKFAST|LUNCH|DINNER)
     * @return bool
     */
    public function is_meal_type_enabled($policy, $meal_type)
    {
        if (!$policy) {
            return true; // No policy means no restrictions
        }

        switch ($meal_type) {
            case 'BREAKFAST':
                return (bool)$policy->breakfast_enabled;
            case 'LUNCH':
                return (bool)$policy->lunch_enabled;
            case 'DINNER':
                return (bool)$policy->dinner_enabled;
            case 'SNACKS':
                return (bool)$policy->snacks_enabled;
            default:
                return false;
        }
    }

    /**
     * Check if PREMEAL is enabled in policy
     *
     * @param object $policy
     * @return bool
     */
    public function is_premeal_enabled($policy)
    {
        if (!$policy) {
            return true; // No policy means no restrictions
        }

        return (bool)$policy->applies_to_premeal;
    }

    /**
     * Check if KOT/delivery is enabled in policy
     *
     * @param object $policy
     * @return bool
     */
    public function is_kot_enabled($policy)
    {
        if (!$policy) {
            return false;
        }

        return (bool)$policy->applies_to_delivery && (bool)$policy->snacks_enabled;
    }

    /**
     * Validate date for PREMEAL booking
     * Combines all date-related checks
     *
     * @param int $employee_id
     * @param string $date
     * @param string $meal_type
     * @param object $store
     * @param object|null $policy
     * @return array Complete validation result
     */
    public function validate_date_for_booking($employee_id, $date, $meal_type, $store, $policy = null)
    {
        if (!$policy) {
            $policy = $this->get_employee_policy($employee_id);
        }

        $result = [
            'date' => $date,
            'day_name' => date('l', strtotime($date)),
            'is_available' => true,
            'reason' => null
        ];

        // Check if date is bookable (within advance booking limit)
        $bookable = $this->is_date_bookable($policy, $date);
        if (!$bookable['bookable']) {
            $result['is_available'] = false;
            $result['reason'] = $bookable['reason'];
            return $result;
        }

        // Check cutoff time
        $cutoff = $this->is_cutoff_passed($store, $meal_type, $date, $policy);
        $result['cutoff_passed'] = $cutoff['passed'];
        $result['cutoff_time'] = $cutoff['cutoff_time'];
        $result['serving_time'] = $cutoff['serving_time'];

        if ($cutoff['passed']) {
            $result['is_available'] = false;
            $result['reason'] = $cutoff['reason'];
            return $result;
        }

        // Check meal type is enabled
        if (!$this->is_meal_type_enabled($policy, $meal_type)) {
            $result['is_available'] = false;
            $result['reason'] = "{$meal_type} is not enabled in your policy";
            return $result;
        }

        // Check daily limit
        $daily_limit = $this->check_daily_limit($employee_id, $date, $meal_type, $policy);
        $result['meal_limit'] = $daily_limit;

        if (!$daily_limit['available']) {
            $result['is_available'] = false;
            $result['reason'] = $daily_limit['reason'] ?? 'Daily meal limit already used for this date';
            return $result;
        }

        // Check weekly limit
        $weekly_limit = $this->check_weekly_limit($employee_id, $date, $policy);
        if ($weekly_limit['weekly_limit'] !== null && !$weekly_limit['available']) {
            $result['is_available'] = false;
            $result['reason'] = 'Weekly meal limit exceeded';
            $result['weekly_limit'] = $weekly_limit;
            return $result;
        }

        // Check monthly limit
        $monthly_limit = $this->check_monthly_limit($employee_id, $date, $policy);
        if ($monthly_limit['monthly_limit'] !== null && !$monthly_limit['available']) {
            $result['is_available'] = false;
            $result['reason'] = 'Monthly meal limit exceeded';
            $result['monthly_limit'] = $monthly_limit;
            return $result;
        }

        return $result;
    }

    /**
     * Get policy summary for API response
     *
     * @param object $policy
     * @return array
     */
    public function get_policy_summary($policy)
    {
        if (!$policy) {
            return null;
        }

        $company_contribution_display = '';
        if ($policy->company_contribution_type == 'PERCENTAGE') {
            $company_contribution_display = $policy->company_contribution_value . '%';
        } else {
            $company_contribution_display = '₹' . $policy->company_contribution_value;
        }

        return [
            'id' => (int)$policy->id,
            'name' => $policy->name,
            'type' => $policy->policy_type,
            'company_contribution' => $company_contribution_display,
            'daily_meal_limit' => (int)($policy->daily_meal_limit ?: 1),
            'weekly_meal_limit' => $policy->weekly_meal_limit ? (int)$policy->weekly_meal_limit : null,
            'monthly_meal_limit' => $policy->monthly_meal_limit ? (int)$policy->monthly_meal_limit : null,
            'monthly_budget_limit' => $policy->monthly_budget_limit ? (float)$policy->monthly_budget_limit : null,
            'max_meal_value' => $policy->max_meal_value ? (float)$policy->max_meal_value : null,
            'advance_booking_days' => (int)($policy->advance_booking_days ?: 7),
            'booking_cutoff_hours' => (int)($policy->booking_cutoff_hours ?: 2),
            'cancellation_cutoff_hours' => (int)($policy->cancellation_cutoff_hours ?: 1),
            'meals_enabled' => [
                'breakfast' => (bool)$policy->breakfast_enabled,
                'lunch' => (bool)$policy->lunch_enabled,
                'dinner' => (bool)$policy->dinner_enabled,
                'snacks' => (bool)$policy->snacks_enabled
            ]
        ];
    }
}
