<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Companyusers extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Companies_model', 'companies');
        $this->load->model('CompanyUsers_model', 'company_users');
    }

    public function index($company_id)
    {
        if (is_loggedin_client()) {

            $client_id = get_client_sessiondata('client_id');
            $company = $this->companies->get_by_id($company_id, $client_id);

            if (!$company) {
                $this->session->set_flashdata('error', 'Company not found');
                redirect('client/companies');
            }

            $header_data['title'] = 'Company Users - ' . $company->name . ' || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['company'] = $company;
            $data['users'] = $this->company_users->get_all_by_company($company_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/company_users/index', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/company_users');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function add($company_id)
    {
        if (is_loggedin_client()) {

            $client_id = get_client_sessiondata('client_id');
            $company = $this->companies->get_by_id($company_id, $client_id);

            if (!$company) {
                $this->session->set_flashdata('error', 'Company not found');
                redirect('client/companies');
            }

            $header_data['title'] = 'Add User - ' . $company->name . ' || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $data['company'] = $company;

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/company_users/add', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/company_users_form');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function store()
    {
        if (is_loggedin_client()) {
            $post_data = html_escape($this->input->post());
            $client_id = get_client_sessiondata('client_id');

            if (isset($post_data['company_id']) && $post_data['company_id'] !== '' && !empty($post_data['email']) && !empty($post_data['first_name']) && !empty($post_data['password'])) {

                // Verify company belongs to this client
                $company = $this->companies->get_by_id($post_data['company_id'], $client_id);
                if (!$company) {
                    echo json_encode(['status' => 400, 'message' => 'Company not found']);
                    return;
                }

                // Check if email already exists
                if ($this->company_users->check_email_exists($post_data['company_id'], $post_data['email'])) {
                    echo json_encode(['status' => 400, 'message' => 'Email already exists for this company']);
                    return;
                }

                $data = array(
                    'company_id' => $post_data['company_id'],
                    'email' => $post_data['email'],
                    'password_hash' => password_hash($post_data['password'], PASSWORD_DEFAULT),
                    'first_name' => $post_data['first_name'],
                    'last_name' => $post_data['last_name'],
                    'phone' => $post_data['phone'],
                    'designation' => $post_data['designation'],
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'can_manual_credit' => isset($post_data['can_manual_credit']) ? 1 : 0,
                    'can_manual_debit' => isset($post_data['can_manual_debit']) ? 1 : 0,
                    'can_razorpay_recharge' => isset($post_data['can_razorpay_recharge']) ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'company_users');

                if ($result) {
                    echo json_encode(['status' => 200, 'message' => 'User added successfully']);
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Failed to add user']);
                }
            } else {
                echo json_encode(['status' => 400, 'message' => 'Required fields are missing']);
            }
        } else {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
        }
    }

    public function edit($id)
    {
        if (is_loggedin_client()) {

            $client_id = get_client_sessiondata('client_id');
            $user = $this->company_users->get_by_id($id);

            if (!$user) {
                $this->session->set_flashdata('error', 'User not found');
                redirect('client/companies');
            }

            // Verify company belongs to this client
            $company = $this->companies->get_by_id($user->company_id, $client_id);
            if (!$company) {
                $this->session->set_flashdata('error', 'Company not found');
                redirect('client/companies');
            }

            $header_data['title'] = 'Edit User - ' . $company->name . ' || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $data['company'] = $company;
            $data['user'] = $user;

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/company_users/edit', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/company_users_form');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function update()
    {
        if (is_loggedin_client()) {
            $post_data = html_escape($this->input->post());
            $client_id = get_client_sessiondata('client_id');

            if (isset($post_data['user_id']) && $post_data['user_id'] !== '' && !empty($post_data['email']) && !empty($post_data['first_name'])) {

                $user = $this->company_users->get_by_id($post_data['user_id']);
                if (!$user) {
                    echo json_encode(['status' => 400, 'message' => 'User not found']);
                    return;
                }

                // Verify company belongs to this client
                $company = $this->companies->get_by_id($user->company_id, $client_id);
                if (!$company) {
                    echo json_encode(['status' => 400, 'message' => 'Company not found']);
                    return;
                }

                // Check if email already exists (excluding current)
                if ($this->company_users->check_email_exists($user->company_id, $post_data['email'], $post_data['user_id'])) {
                    echo json_encode(['status' => 400, 'message' => 'Email already exists for this company']);
                    return;
                }

                $data = array(
                    'email' => $post_data['email'],
                    'first_name' => $post_data['first_name'],
                    'last_name' => $post_data['last_name'],
                    'phone' => $post_data['phone'],
                    'designation' => $post_data['designation'],
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'can_manual_credit' => isset($post_data['can_manual_credit']) ? 1 : 0,
                    'can_manual_debit' => isset($post_data['can_manual_debit']) ? 1 : 0,
                    'can_razorpay_recharge' => isset($post_data['can_razorpay_recharge']) ? 1 : 0,
                    'updated_at' => date('Y-m-d H:i:s')
                );

                // Update password only if provided
                if (!empty($post_data['password'])) {
                    $data['password_hash'] = password_hash($post_data['password'], PASSWORD_DEFAULT);
                }

                $result = $this->common->update('company_users', $data, ['id' => $post_data['user_id']]);

                if ($result !== false) {
                    echo json_encode(['status' => 200, 'message' => 'User updated successfully']);
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Failed to update user']);
                }
            } else {
                echo json_encode(['status' => 400, 'message' => 'Required fields are missing']);
            }
        } else {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
        }
    }

    public function delete()
    {
        if (is_loggedin_client()) {

            $id = $this->input->post('id', true);
            $client_id = get_client_sessiondata('client_id');

            $user = $this->company_users->get_by_id($id);
            if (!$user) {
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
                return;
            }

            // Verify company belongs to this client
            $company = $this->companies->get_by_id($user->company_id, $client_id);
            if (!$company) {
                echo json_encode(['status' => 'error', 'message' => 'Company not found']);
                return;
            }

            // Soft delete
            $data = array(
                'deleted_at' => date('Y-m-d H:i:s')
            );

            $result = $this->common->update('company_users', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete user']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }
}
