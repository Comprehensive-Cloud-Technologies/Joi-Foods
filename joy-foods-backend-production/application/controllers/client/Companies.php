<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Companies extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Companies_model', 'companies');
        $this->load->model('Companydocuments_model', 'companydocuments');
        $this->load->helper('upload');
    }

    public function index()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Company Management || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['companies'] = $this->companies->get_all_by_client($client_id);

            // Get stats for each company
            foreach ($data['companies'] as &$company) {
                $company->employee_count = $this->companies->get_employee_count($company->id);
                $company->department_count = $this->companies->get_department_count($company->id);
            }

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/companies/index', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/companies');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function add()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Add Company || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['datepicker'] = true;
            $footer_data['datepicker'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/companies/add');
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/companies_form');
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

            if (!empty($post_data['company_code']) && !empty($post_data['name']) && !empty($post_data['primary_email'])) {

                // Check if company code already exists (globally unique)
                $existing = $this->companies->check_company_code_exists($post_data['company_code']);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Company code already exists. Please use a unique code.'
                    );
                    echo json_encode($response);
                    return;
                }

                $data = array(
                    'client_id' => $client_id,
                    'company_code' => strtoupper($post_data['company_code']),
                    'name' => $post_data['name'],
                    'legal_name' => $post_data['legal_name'],
                    'short_name' => $post_data['short_name'],
                    'primary_email' => $post_data['primary_email'],
                    'secondary_email' => $post_data['secondary_email'],
                    'primary_phone' => $post_data['primary_phone'],
                    'secondary_phone' => $post_data['secondary_phone'],
                    'address_line1' => $post_data['address_line1'],
                    'address_line2' => $post_data['address_line2'],
                    'city' => $post_data['city'],
                    'state' => $post_data['state'],
                    'country' => $post_data['country'] ?: 'India',
                    'pincode' => $post_data['pincode'],
                    'gst_number' => $post_data['gst_number'],
                    'pan_number' => $post_data['pan_number'],
                    'cin_number' => $post_data['cin_number'],
                    'contract_start_date' => $post_data['contract_start_date'] ?: NULL,
                    'contract_end_date' => $post_data['contract_end_date'] ?: NULL,
                    'billing_cycle' => $post_data['billing_cycle'] ?: 'MONTHLY',
                    'payment_terms_days' => $post_data['payment_terms_days'] ?: 30,
                    'max_employees' => $post_data['max_employees'] ?: NULL,
                    'allow_guest_booking' => isset($post_data['allow_guest_booking']) ? 1 : 0,
                    'guest_booking_requires_approval' => 0,
                    'qsr_enabled' => isset($post_data['qsr_enabled']) ? 1 : 0,
                    'premeal_enabled' => isset($post_data['premeal_enabled']) ? 1 : 0,
                    'delivery_enabled' => isset($post_data['delivery_enabled']) ? 1 : 0,
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'created_by' => get_client_sessiondata('id'),
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'companies');

                if ($result) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Company added successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to add company'
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

            $header_data['title'] = 'View Company || ' . config_item('application_name');

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['company'] = $this->companies->get_by_id($id, $client_id);

            if (!$data['company']) {
                $this->session->set_flashdata('error', 'Company not found');
                redirect('client/companies');
            }

            // Get related data
            $data['usage'] = $this->companies->get_usage_summary($id);
            $data['usage']['documents'] = $this->companydocuments->get_count($id);
            $data['attached_policies'] = $this->companies->get_attached_policies($id);
            $data['departments'] = $this->companies->get_departments($id);
            $data['company_users'] = $this->companies->get_company_users($id);
            $data['documents'] = $this->companydocuments->get_all($id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/companies/view', $data);
            $this->load->view('client/common/footer', $footer_data);
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function edit($id)
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Edit Company || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['datepicker'] = true;
            $footer_data['datepicker'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['company'] = $this->companies->get_by_id($id, $client_id);

            if (!$data['company']) {
                $this->session->set_flashdata('error', 'Company not found');
                redirect('client/companies');
            }

            // Get usage info for warning
            $data['usage'] = $this->companies->get_usage_summary($id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/companies/edit', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/companies_form');
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

            if (isset($post_data['company_id']) && $post_data['company_id'] !== '' && !empty($post_data['company_code']) && !empty($post_data['name']) && !empty($post_data['primary_email'])) {

                // Verify company belongs to this client
                $company = $this->companies->get_by_id($post_data['company_id'], $client_id);
                if (!$company) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Company not found'
                    );
                    echo json_encode($response);
                    return;
                }

                // Check if company code already exists (excluding current)
                $existing = $this->companies->check_company_code_exists($post_data['company_code'], $post_data['company_id']);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Company code already exists. Please use a unique code.'
                    );
                    echo json_encode($response);
                    return;
                }

                $data = array(
                    'company_code' => strtoupper($post_data['company_code']),
                    'name' => $post_data['name'],
                    'legal_name' => $post_data['legal_name'],
                    'short_name' => $post_data['short_name'],
                    'primary_email' => $post_data['primary_email'],
                    'secondary_email' => $post_data['secondary_email'],
                    'primary_phone' => $post_data['primary_phone'],
                    'secondary_phone' => $post_data['secondary_phone'],
                    'address_line1' => $post_data['address_line1'],
                    'address_line2' => $post_data['address_line2'],
                    'city' => $post_data['city'],
                    'state' => $post_data['state'],
                    'country' => $post_data['country'] ?: 'India',
                    'pincode' => $post_data['pincode'],
                    'gst_number' => $post_data['gst_number'],
                    'pan_number' => $post_data['pan_number'],
                    'cin_number' => $post_data['cin_number'],
                    'contract_start_date' => $post_data['contract_start_date'] ?: NULL,
                    'contract_end_date' => $post_data['contract_end_date'] ?: NULL,
                    'billing_cycle' => $post_data['billing_cycle'] ?: 'MONTHLY',
                    'payment_terms_days' => $post_data['payment_terms_days'] ?: 30,
                    'max_employees' => $post_data['max_employees'] ?: NULL,
                    'allow_guest_booking' => isset($post_data['allow_guest_booking']) ? 1 : 0,
                    'guest_booking_requires_approval' => 0,
                    'qsr_enabled' => isset($post_data['qsr_enabled']) ? 1 : 0,
                    'premeal_enabled' => isset($post_data['premeal_enabled']) ? 1 : 0,
                    'delivery_enabled' => isset($post_data['delivery_enabled']) ? 1 : 0,
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'updated_by' => get_client_sessiondata('id'),
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->update('companies', $data, ['id' => $post_data['company_id']]);

                if ($result !== false) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Company updated successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to update company'
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

            // Verify company belongs to this client
            $company = $this->companies->get_by_id($id, $client_id);
            if (!$company) {
                echo json_encode(['status' => 'error', 'message' => 'Company not found']);
                return;
            }

            $usage = $this->companies->get_usage_summary($id);

            echo json_encode([
                'status' => 'success',
                'usage' => $usage
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    public function delete()
    {
        if (is_loggedin_client()) {

            $id = $this->input->post('id', true);
            $force_delete = 0; // Default to false
            $client_id = get_client_sessiondata('client_id');

            // Verify company belongs to this client
            $company = $this->companies->get_by_id($id, $client_id);
            if (!$company) {
                echo json_encode(['status' => 'error', 'message' => 'Company not found']);
                return;
            }

            // Check if company has related data
            $usage = $this->companies->get_usage_summary($id);
            $total_usage = $usage['departments'] + $usage['employees'] + $usage['users'] + $usage['policies'] + $usage['stores'] + $usage['orders'];

            if ($total_usage > 0 && !$force_delete) {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'This company has related data',
                    'usage' => $usage,
                    'requires_confirmation' => true
                ]);
                return;
            }

            // If force delete, remove all related data first
            if ($force_delete) {
                $this->companies->delete_departments($id);
                $this->companies->detach_policies($id);
                $this->companies->delete_company_users($id);
                $this->companies->delete_employees($id);
            }

            // Soft delete
            $data = array(
                'is_active' => 0,
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_by' => get_client_sessiondata('id')
            );

            $result = $this->common->update('companies', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Company deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete company']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    // ------------------------------------------------------------------
    // Company Documents Methods
    // ------------------------------------------------------------------

    /**
     * List all documents for a company
     *
     * GET /client/companies/documents/{company_id}
     */
    public function documents($company_id)
    {
        if (is_loggedin_client()) {

            $client_id = get_client_sessiondata('client_id');
            $company = $this->companies->get_by_id($company_id, $client_id);

            if (!$company) {
                $this->session->set_flashdata('error', 'Company not found');
                redirect('client/companies');
            }

            $header_data['title'] = 'Documents - ' . $company->name . ' || ' . config_item('application_name');

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['company'] = $company;
            $data['documents'] = $this->companydocuments->get_all($company_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/company_documents/index', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/company_documents');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    /**
     * Show upload document form
     *
     * GET /client/companies/add_document/{company_id}
     */
    public function add_document($company_id)
    {
        if (is_loggedin_client()) {

            $client_id = get_client_sessiondata('client_id');
            $company = $this->companies->get_by_id($company_id, $client_id);

            if (!$company) {
                $this->session->set_flashdata('error', 'Company not found');
                redirect('client/companies');
            }

            $header_data['title'] = 'Upload Document - ' . $company->name . ' || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['company'] = $company;

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/company_documents/add', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/company_documents_form');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    /**
     * Store uploaded document
     *
     * POST /client/companies/store_document
     */
    public function store_document()
    {
        if (is_loggedin_client()) {
            $post_data = html_escape($this->input->post());
            $client_id = get_client_sessiondata('client_id');

            if (isset($post_data['company_id']) && $post_data['company_id'] !== '' && !empty($post_data['label'])) {

                // Verify company belongs to this client
                $company = $this->companies->get_by_id($post_data['company_id'], $client_id);
                if (!$company) {
                    echo json_encode(['status' => 400, 'message' => 'Company not found']);
                    return;
                }

                // Check if file was uploaded
                if (empty($_FILES['document']['name'])) {
                    echo json_encode(['status' => 400, 'message' => 'Please select a file to upload']);
                    return;
                }

                // Upload file
                $upload_path = './uploads/companies/documents/';
                $allowed_types = 'pdf|doc|docx|jpg|jpeg|png|xls|xlsx';
                $upload_data = upload_file('document', $upload_path, $allowed_types, 10240);

                if (!$upload_data) {
                    echo json_encode(['status' => 400, 'message' => 'Failed to upload file. Please check file type and size (max 10MB).']);
                    return;
                }

                $data = array(
                    'company_id' => $post_data['company_id'],
                    'client_id' => $client_id,
                    'label' => $post_data['label'],
                    'original_filename' => $upload_data['orig_name'],
                    'file_path' => 'uploads/companies/documents/' . $upload_data['file_name'],
                    'file_size' => $upload_data['file_size'] * 1024,
                    'file_extension' => $upload_data['file_ext'],
                    'mime_type' => $upload_data['file_type'],
                    'uploaded_by' => get_client_sessiondata('id'),
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'company_documents');

                if ($result) {
                    echo json_encode(['status' => 200, 'message' => 'Document uploaded successfully']);
                } else {
                    delete_uploaded_file($upload_path . $upload_data['file_name']);
                    echo json_encode(['status' => 400, 'message' => 'Failed to save document']);
                }
            } else {
                echo json_encode(['status' => 400, 'message' => 'Required fields are missing']);
            }
        } else {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
        }
    }

    /**
     * Delete a document (soft delete + remove file)
     *
     * POST /client/companies/delete_document
     */
    public function delete_document()
    {
        if (is_loggedin_client()) {

            $id = $this->input->post('id', true);
            $client_id = get_client_sessiondata('client_id');

            $document = $this->companydocuments->get_by_id($id, $client_id);
            if (!$document) {
                echo json_encode(['status' => 'error', 'message' => 'Document not found']);
                return;
            }

            // Soft delete
            $data = array(
                'deleted_at' => date('Y-m-d H:i:s')
            );

            $result = $this->common->update('company_documents', $data, ['id' => $id]);

            if ($result !== false) {
                // Delete physical file
                delete_uploaded_file('./' . $document->file_path);

                echo json_encode(['status' => 'success', 'message' => 'Document deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete document']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    /**
     * Download a document
     *
     * GET /client/companies/download_document/{id}
     */
    public function download_document($id)
    {
        if (is_loggedin_client()) {

            $client_id = get_client_sessiondata('client_id');
            $document = $this->companydocuments->get_by_id($id, $client_id);

            if (!$document) {
                $this->session->set_flashdata('error', 'Document not found');
                redirect('client/companies');
                return;
            }

            $file_path = './' . $document->file_path;

            if (!file_exists($file_path)) {
                $this->session->set_flashdata('error', 'File not found on server');
                redirect('client/companies/documents/' . $document->company_id);
                return;
            }

            $this->load->helper('download');
            $data = file_get_contents($file_path);
            force_download($document->original_filename, $data);
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }
}
