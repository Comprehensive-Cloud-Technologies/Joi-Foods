<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Policies extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Policies_model', 'policies');
    }

    public function index()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Policy Management || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['policies'] = $this->policies->get_all_by_client($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/policies/index', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/policies');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function add()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Add Policy || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/policies/add');
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/policies_form');
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

            if (!empty($post_data['policy_code']) && !empty($post_data['name']) && !empty($post_data['policy_type'])) {

                // Check if policy code already exists
                $existing = $this->policies->check_policy_code_exists($client_id, $post_data['policy_code']);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Policy code already exists'
                    );
                    echo json_encode($response);
                    return;
                }

                $data = array(
                    'client_id' => $client_id,
                    'policy_code' => $post_data['policy_code'],
                    'name' => $post_data['name'],
                    'description' => $post_data['description'],
                    'policy_type' => $post_data['policy_type'],
                    'company_contribution_type' => $post_data['company_contribution_type'] ?? 'PERCENTAGE',
                    'company_contribution_value' => $post_data['company_contribution_value'] ?? 0,
                    'employee_contribution_type' => $post_data['employee_contribution_type'] ?? 'PERCENTAGE',
                    'employee_contribution_value' => $post_data['employee_contribution_value'] ?? 0,
                    'max_meal_value' => NULL,
                    'daily_meal_limit' => $post_data['daily_meal_limit'] ?: 1,
                    'weekly_meal_limit' => $post_data['weekly_meal_limit'] ?: NULL,
                    'monthly_meal_limit' => $post_data['monthly_meal_limit'] ?: NULL,
                    'monthly_budget_limit' => NULL,
                    'applies_to_qsr' => 0, // QSR does not apply policies
                    'applies_to_premeal' => isset($post_data['applies_to_premeal']) ? 1 : 0,
                    'applies_to_delivery' => isset($post_data['applies_to_delivery']) ? 1 : 0,
                    'breakfast_enabled' => isset($post_data['breakfast_enabled']) ? 1 : 0,
                    'lunch_enabled' => isset($post_data['lunch_enabled']) ? 1 : 0,
                    'dinner_enabled' => isset($post_data['dinner_enabled']) ? 1 : 0,
                    'snacks_enabled' => isset($post_data['snacks_enabled']) ? 1 : 0,
                    'advance_booking_days' => $post_data['advance_booking_days'] ?: 7,
                    'booking_cutoff_hours' => $post_data['booking_cutoff_hours'] ?: 2,
                    'cancellation_cutoff_hours' => $post_data['cancellation_cutoff_hours'] ?: 1,
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'is_default' => isset($post_data['is_default']) ? 1 : 0,
                    'created_by' => get_client_sessiondata('id'),
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'policies');

                if ($result) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Policy added successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to add policy'
                    );
                }
            } else {
                $response = array(
                    'status' => 400,
                    'message' => 'Required fields are missing'
                );
            }
        } else {
            $response = array(
                'status' => 400,
                'message' => 'Please login again'
            );
        }

        echo json_encode($response);
    }

    public function view($id)
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'View Policy || ' . config_item('application_name');

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['policy'] = $this->policies->get_by_id($id, $client_id);

            if (!$data['policy']) {
                $this->session->set_flashdata('error', 'Policy not found');
                redirect('client/policies');
            }

            // Get usage info
            $data['usage'] = $this->policies->get_usage_summary($id);
            $data['attached_companies'] = $this->policies->get_attached_companies($id);
            $data['attached_employees'] = $this->policies->get_attached_employees($id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/policies/view', $data);
            $this->load->view('client/common/footer', $footer_data);
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function edit($id)
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Edit Policy || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['policy'] = $this->policies->get_by_id($id, $client_id);

            if (!$data['policy']) {
                $this->session->set_flashdata('error', 'Policy not found');
                redirect('client/policies');
            }

            // Get usage info for warning
            $data['usage'] = $this->policies->get_usage_summary($id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/policies/edit', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/policies_form');
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

            if (isset($post_data['policy_id']) && $post_data['policy_id'] !== '' && !empty($post_data['policy_code']) && !empty($post_data['name']) && !empty($post_data['policy_type'])) {

                // Verify policy belongs to this client
                $policy = $this->policies->get_by_id($post_data['policy_id'], $client_id);
                if (!$policy) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Policy not found'
                    );
                    echo json_encode($response);
                    return;
                }

                // Check if policy code already exists (excluding current)
                $existing = $this->policies->check_policy_code_exists($client_id, $post_data['policy_code'], $post_data['policy_id']);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Policy code already exists'
                    );
                    echo json_encode($response);
                    return;
                }

                $data = array(
                    'policy_code' => $post_data['policy_code'],
                    'name' => $post_data['name'],
                    'description' => $post_data['description'],
                    'policy_type' => $post_data['policy_type'],
                    'company_contribution_type' => $post_data['company_contribution_type'] ?? 'PERCENTAGE',
                    'company_contribution_value' => $post_data['company_contribution_value'] ?? 0,
                    'employee_contribution_type' => $post_data['employee_contribution_type'] ?? 'PERCENTAGE',
                    'employee_contribution_value' => $post_data['employee_contribution_value'] ?? 0,
                    'max_meal_value' => NULL,
                    'daily_meal_limit' => $post_data['daily_meal_limit'] ?: 1,
                    'weekly_meal_limit' => $post_data['weekly_meal_limit'] ?: NULL,
                    'monthly_meal_limit' => $post_data['monthly_meal_limit'] ?: NULL,
                    'monthly_budget_limit' => NULL,
                    'applies_to_qsr' => 0, // QSR does not apply policies
                    'applies_to_premeal' => isset($post_data['applies_to_premeal']) ? 1 : 0,
                    'applies_to_delivery' => isset($post_data['applies_to_delivery']) ? 1 : 0,
                    'breakfast_enabled' => isset($post_data['breakfast_enabled']) ? 1 : 0,
                    'lunch_enabled' => isset($post_data['lunch_enabled']) ? 1 : 0,
                    'dinner_enabled' => isset($post_data['dinner_enabled']) ? 1 : 0,
                    'snacks_enabled' => isset($post_data['snacks_enabled']) ? 1 : 0,
                    'advance_booking_days' => $post_data['advance_booking_days'] ?: 7,
                    'booking_cutoff_hours' => $post_data['booking_cutoff_hours'] ?: 2,
                    'cancellation_cutoff_hours' => $post_data['cancellation_cutoff_hours'] ?: 1,
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'is_default' => isset($post_data['is_default']) ? 1 : 0,
                    'updated_by' => get_client_sessiondata('id'),
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->update('policies', $data, ['id' => $post_data['policy_id']]);

                if ($result !== false) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Policy updated successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to update policy'
                    );
                }
            } else {
                $response = array(
                    'status' => 400,
                    'message' => 'Required fields are missing'
                );
            }
        } else {
            $response = array(
                'status' => 400,
                'message' => 'Please login again'
            );
        }

        echo json_encode($response);
    }

    public function check_usage()
    {
        if (is_loggedin_client()) {
            $id = $this->input->post('id', true);
            $client_id = get_client_sessiondata('client_id');

            // Verify policy belongs to this client
            $policy = $this->policies->get_by_id($id, $client_id);
            if (!$policy) {
                echo json_encode(['status' => 'error', 'message' => 'Policy not found']);
                return;
            }

            $usage = $this->policies->get_usage_summary($id);
            $attached_companies = $this->policies->get_attached_companies($id);
            $attached_employees = $this->policies->get_attached_employees($id);

            echo json_encode([
                'status' => 'success',
                'usage' => $usage,
                'companies' => $attached_companies,
                'employees' => $attached_employees
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    public function delete()
    {
        if (is_loggedin_client()) {

            $id = $this->input->post('id', true);
            $force_delete = $this->input->post('force_delete', true);
            $client_id = get_client_sessiondata('client_id');

            // Verify policy belongs to this client
            $policy = $this->policies->get_by_id($id, $client_id);
            if (!$policy) {
                echo json_encode(['status' => 'error', 'message' => 'Policy not found']);
                return;
            }

            // Check if policy is attached to companies or employees
            $usage = $this->policies->get_usage_summary($id);

            if (($usage['companies'] > 0 || $usage['employees'] > 0) && !$force_delete) {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'This policy is currently in use',
                    'usage' => $usage,
                    'requires_confirmation' => true
                ]);
                return;
            }

            // If force delete, detach from companies and employees first
            if ($force_delete) {
                $this->policies->detach_from_companies($id);
                $this->policies->detach_from_employees($id);
            }

            // Soft delete
            $data = array(
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_by' => get_client_sessiondata('id')
            );

            $result = $this->common->update('policies', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Policy deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete policy']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }
}
