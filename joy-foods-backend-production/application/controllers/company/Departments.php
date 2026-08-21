<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Departments extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Companies_model', 'companies');
        $this->load->model('Departments_model', 'departments');
    }

    public function index()
    {
        if (is_loggedin_company()) {

            $company_id = get_company_sessiondata('company_id');
            $company = get_company_details();
            $client_id = $company->client_id;


            $company = $this->companies->get_by_id($company_id, $client_id);

            if (!$company) {
                $this->session->set_flashdata('error', 'Company not found');
                redirect('client/companies');
            }

            $header_data['title'] = 'Departments - ' . $company->name . ' || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['company'] = $company;
            $data['departments'] = $this->departments->get_all_with_counts($company_id);

            $this->load->view('company/common/header', $header_data);
            $this->load->view('company/common/sidebar');
            $this->load->view('company/departments/index', $data);
            $this->load->view('company/common/footer', $footer_data);

            $this->load->view('company/validation/departments');
        } else {
            $data['title'] = 'Company Login || ' . config_item('application_name');
            $this->load->view('company/auth/login', $data);
        }
    }


    public function add()
    {
        if (is_loggedin_company()) {

            $company_id = get_company_sessiondata('company_id');
            $company = get_company_details();
            $client_id = $company->client_id;

            if (!$company) {
                $this->session->set_flashdata('error', 'Company not found');
                redirect('company/companies');
            }

            $header_data['title'] = 'Add Department - ' . $company->name . ' || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $data['company'] = $company;

            $this->load->view('company/common/header', $header_data);
            $this->load->view('company/common/sidebar');
            $this->load->view('company/departments/add', $data);
            $this->load->view('company/common/footer', $footer_data);

            $this->load->view('company/validation/departments_form');
        } else {
            $data['title'] = 'Company Login || ' . config_item('application_name');
            $this->load->view('company/auth/login', $data);
        }
    }


    public function store()
    {
        if (is_loggedin_company()) {
            $post_data = $this->input->post(null, true);
            $company_id = get_company_sessiondata('company_id');
            $company = get_company_details();
            $client_id = $company->client_id;


            if (!empty($post_data['name'])) {

                // Check if name already exists
                if ($this->departments->check_name_exists($company_id, $post_data['name'])) {
                    echo json_encode(['status' => 400, 'message' => 'Department name already exists']);
                    return;
                }

                // Check if code already exists (if provided)
                if (!empty($post_data['code']) && $this->departments->check_code_exists($company_id, $post_data['code'])) {
                    echo json_encode(['status' => 400, 'message' => 'Department code already exists']);
                    return;
                }

                $data = array(
                    'company_id' => $company_id,
                    'name' => $post_data['name'],
                    'code' => $post_data['code'] ? strtoupper($post_data['code']) : NULL,
                    'description' => $post_data['description'],
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'company_departments');

                if ($result) {
                    echo json_encode(['status' => 200, 'message' => 'Department added successfully']);
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Failed to add department']);
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
        if (is_loggedin_company()) {

            $id = $this->input->post('id', true);
            
            $company = get_company_details();
            $client_id = $company->client_id;

            $department = $this->departments->get_by_id($id);
            if (!$department) {
                echo json_encode(['status' => 'error', 'message' => 'Department not found']);
                return;
            }


            // Check if department has employees
            $employee_count = $this->departments->get_employee_count($id);
            if ($employee_count > 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Cannot delete department. It has ' . $employee_count . ' employee(s) assigned.'
                ]);
                return;
            }

            // Soft delete
            $data = array(
                'deleted_at' => date('Y-m-d H:i:s')
            );

            $result = $this->common->update('company_departments', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Department deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete department']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    public function edit($id)
    {
        if (is_loggedin_company()) {

            $company_id = get_company_sessiondata('company_id');
            $company = get_company_details();
            $client_id = $company->client_id;


            $department = $this->departments->get_by_id($id);

            if (!$department) {
                $this->session->set_flashdata('error', 'Department not found');
                redirect('company/departments');
            }


            $header_data['title'] = 'Edit Department - ' . $company->name . ' || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $data['company'] = $company;
            $data['department'] = $department;
            $data['employee_count'] = $this->departments->get_employee_count($id);

            $this->load->view('company/common/header', $header_data);
            $this->load->view('company/common/sidebar');
            $this->load->view('company/departments/edit', $data);
            $this->load->view('company/common/footer', $footer_data);

            $this->load->view('company/validation/departments_form');
        } else {
            $data['title'] = 'Company Login || ' . config_item('application_name');
            $this->load->view('company/auth/login', $data);
        }
    }

    public function update()
    {
        if (is_loggedin_company()) {
            $post_data = $this->input->post(null, true);
            $company_id = get_company_sessiondata('company_id');
            $company = get_company_details();
            $client_id = $company->client_id;

            if (isset($post_data['department_id']) && $post_data['department_id'] !== '' && !empty($post_data['name'])) {

                $department = $this->departments->get_by_id($post_data['department_id']);
                if (!$department) {
                    echo json_encode(['status' => 400, 'message' => 'Department not found']);
                    return;
                }

                // Check if name already exists (excluding current)
                if ($this->departments->check_name_exists($company_id, $post_data['name'], $post_data['department_id'])) {
                    echo json_encode(['status' => 400, 'message' => 'Department name already exists']);
                    return;
                }

                // Check if code already exists (if provided)
                if (!empty($post_data['code']) && $this->departments->check_code_exists($company_id, $post_data['code'], $post_data['department_id'])) {
                    echo json_encode(['status' => 400, 'message' => 'Department code already exists']);
                    return;
                }

                $data = array(
                    'name' => $post_data['name'],
                    'code' => $post_data['code'] ? strtoupper($post_data['code']) : NULL,
                    'description' => $post_data['description'],
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->update('company_departments', $data, ['id' => $post_data['department_id']]);

                if ($result !== false) {
                    echo json_encode(['status' => 200, 'message' => 'Department updated successfully']);
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Failed to update department']);
                }
            } else {
                echo json_encode(['status' => 400, 'message' => 'Required fields are missing']);
            }
        } else {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
        }
    }
}