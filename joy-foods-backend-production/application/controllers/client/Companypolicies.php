<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Companypolicies extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Companies_model', 'companies');
        $this->load->model('CompanyPolicies_model', 'company_policies');
        $this->load->model('Policies_model', 'policies');
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

            $header_data['title'] = 'Company Policies - ' . $company->name . ' || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['company'] = $company;
            $data['attached_policies'] = $this->company_policies->get_all_by_company($company_id);
            $data['available_policies'] = $this->company_policies->get_available_policies($client_id, $company_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/company_policies/index', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/company_policies');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function attach($company_id)
    {
        if (is_loggedin_client()) {

            $client_id = get_client_sessiondata('client_id');
            $company = $this->companies->get_by_id($company_id, $client_id);

            if (!$company) {
                $this->session->set_flashdata('error', 'Company not found');
                redirect('client/companies');
            }

            $header_data['title'] = 'Attach Policy - ' . $company->name . ' || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $header_data['datepicker'] = true;
            $footer_data['datepicker'] = true;

            $data['company'] = $company;
            $data['available_policies'] = $this->company_policies->get_available_policies($client_id, $company_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/company_policies/attach', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/company_policies_form');
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

            if (isset($post_data['company_id']) && $post_data['company_id'] !== '' && isset($post_data['policy_id']) && $post_data['policy_id'] !== '') {

                // Verify company belongs to this client
                $company = $this->companies->get_by_id($post_data['company_id'], $client_id);
                if (!$company) {
                    echo json_encode(['status' => 400, 'message' => 'Company not found']);
                    return;
                }

                // Verify policy belongs to this client
                $policy = $this->policies->get_by_id($post_data['policy_id'], $client_id);
                if (!$policy) {
                    echo json_encode(['status' => 400, 'message' => 'Policy not found']);
                    return;
                }

                // Check if policy is already attached
                if ($this->company_policies->check_policy_attached($post_data['company_id'], $post_data['policy_id'])) {
                    echo json_encode(['status' => 400, 'message' => 'This policy is already attached to the company']);
                    return;
                }

                $data = array(
                    'company_id' => $post_data['company_id'],
                    'policy_id' => $post_data['policy_id'],
                    'custom_daily_meal_limit' => NULL,
                    'custom_monthly_budget_limit' => NULL,
                    'apply_to_qsr' => 1,
                    'apply_to_premeal' => 1,
                    'apply_to_delivery' => 1,
                    'is_default' => 0,
                    'priority' => 0,
                    'effective_from' => date('Y-m-d'),
                    'effective_until' => NULL,
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'created_by' => get_client_sessiondata('id'),
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'company_policies');

                if ($result) {
                    echo json_encode(['status' => 200, 'message' => 'Policy attached successfully']);
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Failed to attach policy']);
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
            $company_policy = $this->company_policies->get_by_id($id);

            if (!$company_policy) {
                $this->session->set_flashdata('error', 'Company policy not found');
                redirect('client/companies');
            }

            // Verify company belongs to this client
            $company = $this->companies->get_by_id($company_policy->company_id, $client_id);
            if (!$company) {
                $this->session->set_flashdata('error', 'Company not found');
                redirect('client/companies');
            }

            $header_data['title'] = 'Edit Company Policy - ' . $company->name . ' || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $header_data['datepicker'] = true;
            $footer_data['datepicker'] = true;

            $data['company'] = $company;
            $data['company_policy'] = $company_policy;

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/company_policies/edit', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/company_policies_form');
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

            if (!empty($post_data['company_policy_id'])) {

                $company_policy = $this->company_policies->get_by_id($post_data['company_policy_id']);
                if (!$company_policy) {
                    echo json_encode(['status' => 400, 'message' => 'Company policy not found']);
                    return;
                }

                // Verify company belongs to this client
                $company = $this->companies->get_by_id($company_policy->company_id, $client_id);
                if (!$company) {
                    echo json_encode(['status' => 400, 'message' => 'Company not found']);
                    return;
                }

                $data = array(
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->update('company_policies', $data, ['id' => $post_data['company_policy_id']]);

                if ($result !== false) {
                    echo json_encode(['status' => 200, 'message' => 'Company policy updated successfully']);
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Failed to update company policy']);
                }
            } else {
                echo json_encode(['status' => 400, 'message' => 'Required fields are missing']);
            }
        } else {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
        }
    }

    public function detach()
    {
        if (is_loggedin_client()) {

            $id = $this->input->post('id', true);
            $client_id = get_client_sessiondata('client_id');

            $company_policy = $this->company_policies->get_by_id($id);
            if (!$company_policy) {
                echo json_encode(['status' => 'error', 'message' => 'Company policy not found']);
                return;
            }

            // Verify company belongs to this client
            $company = $this->companies->get_by_id($company_policy->company_id, $client_id);
            if (!$company) {
                echo json_encode(['status' => 'error', 'message' => 'Company not found']);
                return;
            }

            // Check if any employee in this company has this policy assigned
            $employee_count = $this->company_policies->get_policy_employee_count($company_policy->company_id, $company_policy->policy_id);

            if ($employee_count > 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => $employee_count . ' employee(s) currently have this policy assigned. Please remove the policy from all employees first.'
                ]);
                return;
            }

            // Delete the company policy attachment
            $result = $this->company_policies->delete($id);

            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Policy detached successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to detach policy']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }
}
