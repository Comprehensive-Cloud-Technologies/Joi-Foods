<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Employees Model
 *
 * Handles database operations for employees including
 * registration, authentication, password reset, and OTP management.
 *
 * @category  Models
 * @package   Joy_Foods
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2025-12-31
 */
class Employees_model extends CI_Model
{
    /**
     * Insert new employee record
     *
     * @param array $data Employee data
     * @return int|bool Insert ID on success, false on failure
     */
    public function insert_employee($data)
    {
        $this->db->insert('employees', $data);
        return $this->db->insert_id();
    }

    /**
     * Update employee record
     *
     * @param int   $employee_id Employee ID
     * @param array $data        Data to update
     * @return bool True on success
     */
    public function update_employee($employee_id, $data)
    {
        $this->db->where('id', $employee_id);
        return $this->db->update('employees', $data);
    }

    /**
     * Generate unique employee code for company
     *
     * @param int    $company_id   Company ID
     * @param string $company_code Company code prefix
     * @return string Generated employee code
     */
    public function generate_employee_code($company_id, $company_code)
    {
        $this->db->select('employee_code');
        $this->db->from('employees');
        $this->db->where('company_id', $company_id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get()->row();

        if ($last && preg_match('/(\d+)$/', $last->employee_code, $matches)) {
            $next_num = intval($matches[1]) + 1;
        } else {
            $next_num = 1;
        }

        return $company_code . '-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check for recent OTP request (rate limiting)
     *
     * @param string $email      Employee email
     * @param int    $company_id Company ID
     * @param string $purpose    OTP purpose
     * @param int    $minutes    Minutes to check
     * @return object|null OTP record if found
     */
    public function get_recent_otp($email, $company_id, $purpose = 'PASSWORD_RESET', $minutes = 1)
    {
        $this->db->select('id, created_at');
        $this->db->from('otp_verifications');
        $this->db->where('email', $email);
        $this->db->where('company_id', $company_id);
        $this->db->where('purpose', $purpose);
        $this->db->where('created_at >', date('Y-m-d H:i:s', strtotime("-{$minutes} minute")));
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    /**
     * Invalidate existing OTPs for email
     *
     * @param string $email      Employee email
     * @param int    $company_id Company ID
     * @param string $purpose    OTP purpose
     * @return bool True on success
     */
    public function invalidate_existing_otps($email, $company_id, $purpose = 'PASSWORD_RESET')
    {
        $this->db->where('email', $email);
        $this->db->where('company_id', $company_id);
        $this->db->where('purpose', $purpose);
        $this->db->where('is_verified', 0);

        return $this->db->delete('otp_verifications');
    }

    /**
     * Insert new OTP record
     *
     * @param array $data OTP data
     * @return int|bool Insert ID on success, false on failure
     */
    public function insert_otp($data)
    {
        $this->db->insert('otp_verifications', $data);
        return $this->db->insert_id();
    }

    /**
     * Get latest OTP record for verification
     *
     * @param string $email      Employee email
     * @param int    $company_id Company ID
     * @param string $purpose    OTP purpose
     * @return object|null OTP record if found
     */
    public function get_otp_record($email, $company_id, $purpose = 'PASSWORD_RESET')
    {
        $this->db->select('*');
        $this->db->from('otp_verifications');
        $this->db->where('email', $email);
        $this->db->where('company_id', $company_id);
        $this->db->where('purpose', $purpose);
        $this->db->where('is_verified', 0);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    /**
     * Increment OTP attempt count
     *
     * @param int $otp_id OTP record ID
     * @return bool True on success
     */
    public function increment_otp_attempts($otp_id)
    {
        $this->db->where('id', $otp_id);
        $this->db->set('attempts', 'attempts + 1', false);

        return $this->db->update('otp_verifications');
    }

    /**
     * Mark OTP as verified and set reset token
     *
     * @param int    $otp_id      OTP record ID
     * @param string $reset_token Generated reset token
     * @param string $expires_at  Token expiration datetime
     * @return bool True on success
     */
    public function verify_otp($otp_id, $reset_token, $expires_at)
    {
        $this->db->where('id', $otp_id);

        return $this->db->update('otp_verifications', [
            'is_verified' => 1,
            'verified_at' => date('Y-m-d H:i:s'),
            'reset_token' => $reset_token,
            'reset_token_expires_at' => $expires_at
        ]);
    }

    /**
     * Get OTP record by reset token
     *
     * @param string $email       Employee email
     * @param int    $company_id  Company ID
     * @param string $reset_token Reset token
     * @return object|null OTP record if found
     */
    public function get_otp_by_reset_token($email, $company_id, $reset_token)
    {
        $this->db->select('*');
        $this->db->from('otp_verifications');
        $this->db->where('email', $email);
        $this->db->where('company_id', $company_id);
        $this->db->where('reset_token', $reset_token);
        $this->db->where('is_verified', 1);
        $this->db->where('purpose', 'PASSWORD_RESET');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    /**
     * Delete OTP record
     *
     * @param int $otp_id OTP record ID
     * @return bool True on success
     */
    public function delete_otp($otp_id)
    {
        $this->db->where('id', $otp_id);
        return $this->db->delete('otp_verifications');
    }

    /**
     * Update employee password
     *
     * @param int    $employee_id  Employee ID
     * @param string $password_hash Hashed password
     * @return bool True on success
     */
    public function update_password($employee_id, $password_hash)
    {
        $this->db->where('id', $employee_id);

        return $this->db->update('employees', [
            'password_hash' => $password_hash,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }


    /**
     * Get total wallet credit amount for employee
     *
     * @param int $uid Employee ID
     * @return object Object with 'amount' property containing total credits
     */
    public function GetWalletMoney($uid)
    {
        $this->db->select_sum('amount');
        $this->db->from('transaction');
        $this->db->where('transaction_type =', 1);
        $this->db->where('user_id =', $uid);
        return $this->db->get()->row();
    }

    /**
     * Get total wallet debit amount for employee
     *
     * @param int $uid Employee ID
     * @return object Object with 'amount' property containing total debits
     */
    public function GetWalletMoneydebit($uid)
    {
        $this->db->select_sum('amount');
        $this->db->from('transaction');
        $this->db->where('transaction_type =', 2);
        $this->db->where('user_id =', $uid);
        return $this->db->get()->row();
    }
}
