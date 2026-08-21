<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Profile Model
 *
 * Handles database operations for employee profile and wallet transactions.
 *
 * @category  Models
 * @package   Joy_Foods
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-01-08
 */
class Profile_model extends CI_Model
{
    /**
     * Get employee profile with company details
     *
     * @param int $employee_id Employee ID
     * @return object|null Employee object with company details or null
     */
    public function get_employee_profile($employee_id)
    {
        $this->db->select('e.*, c.name as company_name, c.company_code');
        $this->db->from('employees e');
        $this->db->join('companies c', 'c.id = e.company_id', 'left');
        $this->db->where('e.id', $employee_id);
        $this->db->where('e.is_active', 1);
        $this->db->where('e.deleted_at', NULL);

        return $this->db->get()->row();
    }

    /**
     * Update employee profile
     *
     * @param int   $employee_id Employee ID
     * @param array $data        Data to update
     * @return bool True on success
     */
    public function update_profile($employee_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $employee_id);
        return $this->db->update('employees', $data);
    }

    /**
     * Check if phone number exists for another employee in the same company
     *
     * @param string $phone       Phone number
     * @param int    $company_id  Company ID
     * @param int    $exclude_id  Employee ID to exclude
     * @return bool True if phone exists for another employee
     */
    public function phone_exists($phone, $company_id, $exclude_id)
    {
        $this->db->where('phone', $phone);
        $this->db->where('company_id', $company_id);
        $this->db->where('id !=', $exclude_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('employees') > 0;
    }

    /**
     * Get wallet transactions with pagination
     *
     * @param int $employee_id Employee ID
     * @param int $limit       Number of transactions per page
     * @param int $offset      Offset for pagination
     * @return array List of transactions
     */
    public function get_wallet_transactions($employee_id, $limit = 20, $offset = 0)
    {
        $this->db->select('t.transaction_id, t.transaction_uuid, t.transaction_type, t.amount,
                          t.transaction_label, t.transaction_date, t.transaction_time, t.order_id,
                          o.order_number, s.name as store_name');
        $this->db->from('transaction t');
        $this->db->join('orders o', 'o.id = t.order_id', 'left');
        $this->db->join('stores s', 's.id = o.store_id', 'left');
        $this->db->where('t.user_id', $employee_id);
        $this->db->order_by('t.transaction_time', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    /**
     * Get total count of wallet transactions
     *
     * @param int $employee_id Employee ID
     * @return int Count of transactions
     */
    public function get_wallet_transactions_count($employee_id)
    {
        $this->db->where('user_id', $employee_id);
        return $this->db->count_all_results('transaction');
    }

    /**
     * Get wallet summary (total credits, debits, balance)
     *
     * @param int $employee_id Employee ID
     * @return array Wallet summary with credits, debits, and balance
     */
    public function get_wallet_summary($employee_id)
    {
        // Get total credits (transaction_type = 1)
        $this->db->select_sum('amount');
        $this->db->from('transaction');
        $this->db->where('user_id', $employee_id);
        $this->db->where('transaction_type', 1);
        $credit_result = $this->db->get()->row();
        $total_credits = $credit_result->amount ? (float)$credit_result->amount : 0;

        // Get total debits (transaction_type = 2)
        $this->db->select_sum('amount');
        $this->db->from('transaction');
        $this->db->where('user_id', $employee_id);
        $this->db->where('transaction_type', 2);
        $debit_result = $this->db->get()->row();
        $total_debits = $debit_result->amount ? (float)$debit_result->amount : 0;

        return [
            'total_credits' => round($total_credits, 2),
            'total_debits' => round($total_debits, 2),
            'available_balance' => round($total_credits - $total_debits, 2)
        ];
    }
}
