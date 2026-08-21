<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employees extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Company_employees_model', 'employees_model');
        $this->load->model('Common_model', 'common');
        $this->load->library('RazorpayLib', null, 'razorpaylib');
    }

    /**
     * Fetch the currently logged-in company user's permission flags.
     * Returns an object with can_manual_recharge / can_razorpay_recharge,
     * or null when not logged in.
     */
    private function current_user_permissions()
    {
        $user_id = get_company_sessiondata('id');
        if (empty($user_id)) {
            return null;
        }
        return $this->common->getdatabytable('company_users', [
            'id' => $user_id,
            'deleted_at' => NULL
        ]);
    }

    public function index()
    {
        if (is_loggedin_company()) {

            $company_id = get_company_sessiondata('company_id');
            $company = get_company_details();

            $header_data['title'] = 'Employees || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $header_data['datepicker'] = true;
            $footer_data['datepicker'] = true;

            $data['employees'] = $this->employees_model->get_all_by_company($company_id);
            $data['departments'] = $this->employees_model->get_departments_dropdown($company_id);
            $data['policies'] = $this->employees_model->get_company_policies($company_id);
            $data['company'] = $company;
            $data['current_user_permissions'] = $this->current_user_permissions();

            $this->load->view('company/common/header', $header_data);
            $this->load->view('company/common/sidebar');
            $this->load->view('company/employees/index', $data);
            $this->load->view('company/common/footer', $footer_data);
            $this->load->view('company/validation/employees', $data);
        } else {
            $data['title'] = 'Company Login || ' . config_item('application_name');
            $this->load->view('company/auth/login', $data);
        }
    }

    /**
     * Get employee data for editing (AJAX)
     */
    public function get($id)
    {
        if (is_loggedin_company()) {
            $company_id = get_company_sessiondata('company_id');
            $employee = $this->employees_model->get_by_id($id, $company_id);

            if ($employee) {
                $employee->policy_id = $this->employees_model->get_employee_policy_id($id);
                echo json_encode(['status' => 'success', 'data' => $employee]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Please login again']);
        }
    }

    /**
     * Generate unique employee code (AJAX)
     */
    public function generate_code()
    {
        if (is_loggedin_company()) {
            $company_id = get_company_sessiondata('company_id');
            $company = get_company_details();
            $code = $this->employees_model->generate_employee_code($company_id, $company->company_code);
            echo json_encode(['status' => 'success', 'code' => $code]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Please login again']);
        }
    }

    /**
     * Store new employee
     */
    public function store()
    {
        if (is_loggedin_company()) {
            $post = html_escape($this->input->post());
            $company_id = get_company_sessiondata('company_id');
            $user_id = get_company_sessiondata('id');

            if (empty($post['employee_code']) || empty($post['first_name']) || empty($post['email']) || empty($post['password'])) {
                echo json_encode(['status' => 400, 'message' => 'Required fields are missing']);
                return;
            }

            // Check max employees limit
            $company = get_company_details();
            if (!empty($company->max_employees)) {
                $current_count = $this->employees_model->get_all_by_company_count($company_id);
                if ($current_count >= (int)$company->max_employees) {
                    echo json_encode(['status' => 400, 'message' => 'Maximum employee limit (' . $company->max_employees . ') reached']);
                    return;
                }
            }

            // Validate module access against company settings
            if (isset($post['qsr_access']) && empty($company->qsr_enabled)) {
                echo json_encode(['status' => 400, 'message' => 'QSR module is not enabled for your company']);
                return;
            }
            if (isset($post['premeal_access']) && empty($company->premeal_enabled)) {
                echo json_encode(['status' => 400, 'message' => 'Premeal module is not enabled for your company']);
                return;
            }
            if (isset($post['kot_permission']) && empty($company->delivery_enabled)) {
                echo json_encode(['status' => 400, 'message' => 'KOT/Delivery module is not enabled for your company']);
                return;
            }

            // Validate password
            if (strlen($post['password']) < 6) {
                echo json_encode(['status' => 400, 'message' => 'Password must be at least 6 characters']);
                return;
            }

            if ($post['password'] !== $post['confirm_password']) {
                echo json_encode(['status' => 400, 'message' => 'Passwords do not match']);
                return;
            }

            // Validate email
            if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['status' => 400, 'message' => 'Please enter a valid email address']);
                return;
            }

            // Check for duplicates
            if ($this->employees_model->check_code_exists($company_id, $post['employee_code'])) {
                echo json_encode(['status' => 400, 'message' => 'Employee code already exists']);
                return;
            }

            if ($this->employees_model->check_email_exists($company_id, $post['email'])) {
                echo json_encode(['status' => 400, 'message' => 'Email already exists']);
                return;
            }

            if (!empty($post['phone']) && $this->employees_model->check_phone_exists($company_id, $post['phone'])) {
                echo json_encode(['status' => 400, 'message' => 'Phone number already exists']);
                return;
            }

            // Check RFID card uniqueness within the company
            if (!empty($post['rfid_card_number'])) {
                $existing_rfid = $this->common->getdatabytable('employees', [
                    'company_id' => $company_id,
                    'rfid_card_number' => trim($post['rfid_card_number']),
                    'deleted_at' => NULL
                ]);
                if ($existing_rfid) {
                    echo json_encode(['status' => 400, 'message' => 'This RFID card is already assigned to another employee']);
                    return;
                }
            }

            // Prepare employee data
            $employee_data = [
                'company_id' => $company_id,
                'employee_code' => $post['employee_code'],
                'first_name' => $post['first_name'],
                'last_name' => isset($post['last_name']) ? $post['last_name'] : null,
                'email' => $post['email'],
                'phone' => isset($post['phone']) ? $post['phone'] : null,
                'password_hash' => password_hash($post['password'], PASSWORD_DEFAULT),
                'department_id' => !empty($post['department_id']) ? $post['department_id'] : null,
                'designation' => isset($post['designation']) ? $post['designation'] : null,
                'date_of_joining' => !empty($post['date_of_joining']) ? $post['date_of_joining'] : null,
                'employment_type' => isset($post['employment_type']) ? $post['employment_type'] : 'FULL_TIME',
                'gender' => !empty($post['gender']) ? $post['gender'] : null,
                // Auto-verify email and phone when added by company portal
                'email_verified_at' => date('Y-m-d H:i:s'),
                'phone_verified_at' => !empty($post['phone']) ? date('Y-m-d H:i:s') : null,
                'kot_permission' => isset($post['kot_permission']) ? 1 : 0,
                'qsr_access' => isset($post['qsr_access']) ? 1 : 0,
                'premeal_access' => isset($post['premeal_access']) ? 1 : 0,
                'rfid_card_number' => !empty($post['rfid_card_number']) ? trim($post['rfid_card_number']) : null,
                'rfid_card_issued_at' => !empty($post['rfid_card_issued_at']) ? $post['rfid_card_issued_at'] : null,
                'is_active' => isset($post['is_active']) ? 1 : 0,
                'is_registered' => 1,
                'created_by' => $user_id,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert employee
            $employee_id = $this->common->insert($employee_data, 'employees');

            if ($employee_id) {
                // Save policy
                $policy_id = isset($post['policy_id']) && !empty($post['policy_id']) ? $post['policy_id'] : null;
                $this->employees_model->save_employee_policy($employee_id, $policy_id, $user_id);

                echo json_encode(['status' => 200, 'message' => 'Employee added successfully']);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Failed to add employee']);
            }
        } else {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
        }
    }

    /**
     * Update employee
     */
    public function update()
    {
        if (is_loggedin_company()) {
            $post = html_escape($this->input->post());
            $company_id = get_company_sessiondata('company_id');
            $user_id = get_company_sessiondata('id');

            if (!isset($post['id']) || $post['id'] === '' || empty($post['employee_code']) || empty($post['first_name']) || empty($post['email'])) {
                echo json_encode(['status' => 400, 'message' => 'Required fields are missing']);
                return;
            }

            $id = $post['id'];

            // Validate password if provided
            if (!empty($post['password'])) {
                if (strlen($post['password']) < 6) {
                    echo json_encode(['status' => 400, 'message' => 'Password must be at least 6 characters']);
                    return;
                }

                if ($post['password'] !== $post['confirm_password']) {
                    echo json_encode(['status' => 400, 'message' => 'Passwords do not match']);
                    return;
                }
            }

            // Validate email
            if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['status' => 400, 'message' => 'Please enter a valid email address']);
                return;
            }

            // Check if employee exists and belongs to company
            $employee = $this->employees_model->get_by_id($id, $company_id);
            if (!$employee) {
                echo json_encode(['status' => 400, 'message' => 'Employee not found']);
                return;
            }

            // Validate module access against company settings
            $company = get_company_details();
            if (isset($post['qsr_access']) && empty($company->qsr_enabled)) {
                echo json_encode(['status' => 400, 'message' => 'QSR module is not enabled for your company']);
                return;
            }
            if (isset($post['premeal_access']) && empty($company->premeal_enabled)) {
                echo json_encode(['status' => 400, 'message' => 'Premeal module is not enabled for your company']);
                return;
            }
            if (isset($post['kot_permission']) && empty($company->delivery_enabled)) {
                echo json_encode(['status' => 400, 'message' => 'KOT/Delivery module is not enabled for your company']);
                return;
            }

            // Check for duplicates (excluding current)
            if ($this->employees_model->check_code_exists($company_id, $post['employee_code'], $id)) {
                echo json_encode(['status' => 400, 'message' => 'Employee code already exists']);
                return;
            }

            if ($this->employees_model->check_email_exists($company_id, $post['email'], $id)) {
                echo json_encode(['status' => 400, 'message' => 'Email already exists']);
                return;
            }

            if (!empty($post['phone']) && $this->employees_model->check_phone_exists($company_id, $post['phone'], $id)) {
                echo json_encode(['status' => 400, 'message' => 'Phone number already exists']);
                return;
            }

            // Check RFID card uniqueness within the company (excluding current employee)
            if (!empty($post['rfid_card_number'])) {
                $rfid_clash = $this->db
                    ->where('company_id', $company_id)
                    ->where('rfid_card_number', trim($post['rfid_card_number']))
                    ->where('id !=', $id)
                    ->where('deleted_at IS NULL', NULL, FALSE)
                    ->get('employees')
                    ->row();
                if ($rfid_clash) {
                    echo json_encode(['status' => 400, 'message' => 'This RFID card is already assigned to another employee']);
                    return;
                }
            }

            // Prepare update data
            $update_data = [
                'employee_code' => $post['employee_code'],
                'first_name' => $post['first_name'],
                'last_name' => isset($post['last_name']) ? $post['last_name'] : null,
                'email' => $post['email'],
                'phone' => isset($post['phone']) ? $post['phone'] : null,
                'department_id' => !empty($post['department_id']) ? $post['department_id'] : null,
                'designation' => isset($post['designation']) ? $post['designation'] : null,
                'date_of_joining' => !empty($post['date_of_joining']) ? $post['date_of_joining'] : null,
                'employment_type' => isset($post['employment_type']) ? $post['employment_type'] : 'FULL_TIME',
                'gender' => !empty($post['gender']) ? $post['gender'] : null,
                'kot_permission' => isset($post['kot_permission']) ? 1 : 0,
                'qsr_access' => isset($post['qsr_access']) ? 1 : 0,
                'premeal_access' => isset($post['premeal_access']) ? 1 : 0,
                'rfid_card_number' => !empty($post['rfid_card_number']) ? trim($post['rfid_card_number']) : null,
                'rfid_card_issued_at' => !empty($post['rfid_card_issued_at']) ? $post['rfid_card_issued_at'] : null,
                'is_active' => isset($post['is_active']) ? 1 : 0,
                'updated_by' => $user_id,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update password if provided
            if (!empty($post['password'])) {
                $update_data['password_hash'] = password_hash($post['password'], PASSWORD_DEFAULT);
            }

            // If email changed, verify now (added by company portal)
            if ($post['email'] != $employee->email) {
                $update_data['email_verified_at'] = date('Y-m-d H:i:s');
            }

            // If phone changed, verify now (added by company portal)
            if (!empty($post['phone']) && $post['phone'] != $employee->phone) {
                $update_data['phone_verified_at'] = date('Y-m-d H:i:s');
            }

            $this->common->update('employees', $update_data, ['id' => $id]);

            // Save policy
            $policy_id = isset($post['policy_id']) && !empty($post['policy_id']) ? $post['policy_id'] : null;
            $this->employees_model->save_employee_policy($id, $policy_id, $user_id);

            echo json_encode(['status' => 200, 'message' => 'Employee updated successfully']);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
        }
    }

    /**
     * Delete employee (soft delete)
     */
    public function delete()
    {
        if (is_loggedin_company()) {
            $company_id = get_company_sessiondata('company_id');
            $id = $this->input->post('id', true);

            $employee = $this->employees_model->get_by_id($id, $company_id);
            if (!$employee) {
                echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
                return;
            }

            $update_data = [
                'is_active' => 0,
                'deleted_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->common->update('employees', $update_data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Employee deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete employee']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    /**
     * Adjust wallet - add or deduct money from employee wallet
     */
    public function credit_wallet()
    {
        if (!is_loggedin_company()) {
            echo json_encode(['status' => 'error', 'message' => 'Please login again']);
            return;
        }

        $company_id = get_company_sessiondata('company_id');
        $user_id = get_company_sessiondata('id');
        $user_name = get_company_sessiondata('first_name') . ' ' . get_company_sessiondata('last_name');

        $employee_id = $this->input->post('employee_id', true);
        $amount = $this->input->post('amount', true);
        $reason = $this->input->post('reason', true);
        $action = $this->input->post('action', true); // credit or debit

        // Permission check — depends on action
        $perms = $this->current_user_permissions();
        if ($action === 'debit') {
            if (empty($perms) || empty($perms->can_manual_debit)) {
                echo json_encode(['status' => 'error', 'message' => 'You do not have permission to deduct from employee wallets']);
                return;
            }
        } else {
            if (empty($perms) || empty($perms->can_manual_credit)) {
                echo json_encode(['status' => 'error', 'message' => 'You do not have permission to credit employee wallets']);
                return;
            }
        }

        // Validate
        if (empty($employee_id) || empty($amount)) {
            echo json_encode(['status' => 'error', 'message' => 'Employee and amount are required']);
            return;
        }

        $is_debit = ($action === 'debit');
        $amount = round((float)$amount, 2);

        if ($amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Amount must be greater than 0']);
            return;
        }

        if ($amount > 50000) {
            echo json_encode(['status' => 'error', 'message' => 'Maximum amount is ₹50,000']);
            return;
        }

        // Verify employee belongs to this company
        $employee = $this->employees_model->get_by_id($employee_id, $company_id);
        if (!$employee) {
            echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
            return;
        }

        if (!$employee->is_active) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot adjust inactive employee wallet']);
            return;
        }

        $this->load->helper('common');

        // For debit, check sufficient balance
        if ($is_debit) {
            $wallet_before = getWalletMoneyArray($employee_id);
            if ($amount > round($wallet_before['available'], 2)) {
                echo json_encode(['status' => 'error', 'message' => 'Insufficient wallet balance. Available: ₹' . number_format($wallet_before['available'], 2)]);
                return;
            }
        }

        $transaction_type = $is_debit ? 2 : 1;
        $label_prefix = $is_debit ? 'Company deduction' : 'Company credit';

        // Start DB transaction
        $this->db->trans_start();

        // 1. Insert into transaction table — distinct source for credit vs debit
        $this->db->insert('transaction', [
            'transaction_uuid' => generate_uuid(),
            'user_id' => $employee_id,
            'amount' => $amount,
            'transaction_type' => $transaction_type,
            'transaction_label' => $label_prefix . (!empty($reason) ? ': ' . $reason : ''),
            'source' => $is_debit ? 'COMPANY_DEBIT' : 'COMPANY_CREDIT',
            'transaction_date' => date('Y-m-d')
        ]);
        $transaction_id = $this->db->insert_id();

        // 2. Insert into wallet_credits log (negative amount for debit)
        $this->db->insert('wallet_credits', [
            'employee_id' => $employee_id,
            'company_id' => $company_id,
            'amount' => $is_debit ? -$amount : $amount,
            'reason' => !empty($reason) ? $reason : null,
            'transaction_id' => $transaction_id,
            'credited_by' => $user_id,
            'credited_by_name' => $user_name,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update wallet. Please try again.']);
            return;
        }

        // Get updated wallet balance
        $wallet = getWalletMoneyArray($employee_id);
        $action_word = $is_debit ? 'deducted from' : 'credited to';

        echo json_encode([
            'status' => 'success',
            'message' => '₹' . number_format($amount, 2) . ' ' . $action_word . ' ' . $employee->first_name . '\'s wallet',
            'new_balance' => round($wallet['available'], 2)
        ]);
    }

    /**
     * Razorpay recharge — Step 1: Initiate
     *
     * Validates permission + employee, creates a Razorpay order for the
     * requested amount, and returns the order_id + key so the frontend can
     * open Razorpay checkout. No wallet credit happens here — that's in
     * razorpay_recharge_complete after the customer pays.
     */
    public function razorpay_recharge_initiate()
    {
        if (!is_loggedin_company()) {
            echo json_encode(['status' => 'error', 'message' => 'Please login again']);
            return;
        }

        // Permission check
        $perms = $this->current_user_permissions();
        if (empty($perms) || empty($perms->can_razorpay_recharge)) {
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to recharge via Razorpay']);
            return;
        }

        $company_id = get_company_sessiondata('company_id');
        $user_id = get_company_sessiondata('id');

        $employee_id = $this->input->post('employee_id', true);
        $amount = $this->input->post('amount', true);

        if (empty($employee_id) || empty($amount)) {
            echo json_encode(['status' => 'error', 'message' => 'Employee and amount are required']);
            return;
        }

        $amount = round((float)$amount, 2);
        if ($amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Amount must be greater than 0']);
            return;
        }
        if ($amount > 50000) {
            echo json_encode(['status' => 'error', 'message' => 'Maximum amount is ₹50,000']);
            return;
        }

        // Verify employee belongs to this company and is active
        $employee = $this->employees_model->get_by_id($employee_id, $company_id);
        if (!$employee) {
            echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
            return;
        }
        if (!$employee->is_active) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot recharge inactive employee']);
            return;
        }

        // Initialise Razorpay with this company's client credentials
        if (!$this->razorpaylib->init($company_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Payment gateway configuration error']);
            return;
        }

        $receipt_id = 'CR_' . $company_id . '_' . $employee_id . '_' . time();

        $result = $this->razorpaylib->createOrder([
            'amount' => $amount,
            'receipt' => $receipt_id,
            'notes' => [
                'purpose'      => 'company_wallet_recharge',
                'company_id'   => $company_id,
                'employee_id'  => $employee_id,
                'initiated_by' => $user_id
            ]
        ]);

        if (empty($result['success'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create payment order: ' . ($result['message'] ?? 'Unknown error')
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'razorpay_order_id' => $result['order']['id'],
                'razorpay_key'      => $this->razorpaylib->getKeyId(),
                'amount'            => $amount,
                'currency'          => 'INR',
                'employee_id'       => (int)$employee_id,
                'employee_name'     => trim($employee->first_name . ' ' . ($employee->last_name ?? '')),
                'employee_email'    => $employee->email,
                'employee_phone'    => $employee->phone
            ]
        ]);
    }

    /**
     * Razorpay recharge — Step 2: Complete
     *
     * Called by the frontend after Razorpay checkout succeeds. Verifies the
     * payment signature, then credits the employee wallet (transaction +
     * wallet_credits log) with source COMPANY_RAZORPAY_RECHARGE.
     */
    public function razorpay_recharge_complete()
    {
        if (!is_loggedin_company()) {
            echo json_encode(['status' => 'error', 'message' => 'Please login again']);
            return;
        }

        $perms = $this->current_user_permissions();
        if (empty($perms) || empty($perms->can_razorpay_recharge)) {
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission to recharge via Razorpay']);
            return;
        }

        $company_id = get_company_sessiondata('company_id');
        $user_id = get_company_sessiondata('id');
        $user_name = get_company_sessiondata('first_name') . ' ' . get_company_sessiondata('last_name');

        $employee_id = $this->input->post('employee_id', true);
        $amount = $this->input->post('amount', true);
        $razorpay_order_id = $this->input->post('razorpay_order_id', true);
        $razorpay_payment_id = $this->input->post('razorpay_payment_id', true);
        $razorpay_signature = $this->input->post('razorpay_signature', true);

        if (empty($employee_id) || empty($amount) || empty($razorpay_order_id) || empty($razorpay_payment_id) || empty($razorpay_signature)) {
            echo json_encode(['status' => 'error', 'message' => 'Required payment details are missing']);
            return;
        }

        $amount = round((float)$amount, 2);
        if ($amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid amount']);
            return;
        }

        // Verify employee belongs to this company
        $employee = $this->employees_model->get_by_id($employee_id, $company_id);
        if (!$employee) {
            echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
            return;
        }

        // Verify Razorpay signature
        if (!$this->razorpaylib->init($company_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Payment gateway configuration error']);
            return;
        }

        $is_valid = $this->razorpaylib->verifyPaymentSignature(
            $razorpay_order_id,
            $razorpay_payment_id,
            $razorpay_signature
        );

        if (!$is_valid) {
            echo json_encode(['status' => 'error', 'message' => 'Payment verification failed. Invalid signature.']);
            return;
        }

        $this->load->helper('common');

        // Credit wallet inside a DB transaction
        $this->db->trans_start();

        // 1. Transaction ledger entry
        $this->db->insert('transaction', [
            'transaction_uuid'  => generate_uuid(),
            'user_id'           => $employee_id,
            'amount'            => $amount,
            'transaction_type'  => 1, // credit
            'transaction_label' => 'Razorpay recharge by company',
            'source'            => 'COMPANY_RAZORPAY_RECHARGE',
            'transaction_date'  => date('Y-m-d')
        ]);
        $transaction_id = $this->db->insert_id();

        // 2. Audit log in wallet_credits (positive amount; includes RP payment refs)
        $this->db->insert('wallet_credits', [
            'employee_id'         => $employee_id,
            'company_id'          => $company_id,
            'amount'              => $amount,
            'reason'              => 'Razorpay recharge by company user',
            'transaction_id'      => $transaction_id,
            'credited_by'         => $user_id,
            'credited_by_name'    => $user_name,
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_order_id'   => $razorpay_order_id,
            'created_at'          => date('Y-m-d H:i:s')
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to credit wallet after payment. Please contact support.']);
            return;
        }

        $wallet = getWalletMoneyArray($employee_id);

        echo json_encode([
            'status' => 'success',
            'message' => '₹' . number_format($amount, 2) . ' credited to ' . $employee->first_name . '\'s wallet via Razorpay',
            'new_balance' => round($wallet['available'], 2),
            'razorpay_payment_id' => $razorpay_payment_id
        ]);
    }

    /**
     * Toggle employee status
     */
    public function toggle_status()
    {
        if (is_loggedin_company()) {
            $company_id = get_company_sessiondata('company_id');
            $id = $this->input->post('id', true);

            $employee = $this->employees_model->get_by_id($id, $company_id);
            if (!$employee) {
                echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
                return;
            }

            $new_status = $employee->is_active ? 0 : 1;
            $result = $this->common->update('employees', ['is_active' => $new_status], ['id' => $id]);

            if ($result !== false) {
                $status_text = $new_status ? 'activated' : 'deactivated';
                echo json_encode(['status' => 'success', 'message' => "Employee {$status_text} successfully"]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }
}
