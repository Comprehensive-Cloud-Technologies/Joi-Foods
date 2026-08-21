<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Stores extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Stores_model', 'stores');
        $this->load->model('Storedocuments_model', 'storedocuments');
    }

    public function index()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Store Management || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['stores'] = $this->stores->get_all_by_client($client_id);

            // Get stats for each store
            foreach ($data['stores'] as &$store) {
                $store->staff_count = $this->stores->get_staff_count($store->id);
            }

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/stores/index', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/stores');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function add()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Add Store || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['companies'] = $this->stores->get_companies_dropdown($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/stores/add', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/stores_form');
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

            if (!empty($post_data['store_code']) && !empty($post_data['name']) && !empty($post_data['store_type']) && isset($post_data['company_id']) && $post_data['company_id'] !== '') {

                // Check if store code already exists (globally unique)
                $existing = $this->stores->check_store_code_exists($post_data['store_code']);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Store code already exists. Please use a unique code.'
                    );
                    echo json_encode($response);
                    return;
                }

                // Handle thumbnail upload
                $thumbnail_path = null;
                if (!empty($_FILES['thumbnail']['name'])) {
                    $config['upload_path'] = './uploads/stores/';
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = TRUE;

                    // Create directory if not exists
                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('thumbnail')) {
                        $upload_data = $this->upload->data();
                        $thumbnail_path = 'uploads/stores/' . $upload_data['file_name'];
                    } else {
                        $response = array('status' => 400, 'message' => $this->upload->display_errors('', ''));
                        echo json_encode($response);
                        return;
                    }
                }

                $data = array(
                    'client_id' => $client_id,
                    'company_id' => $post_data['company_id'],
                    'store_code' => strtoupper($post_data['store_code']),
                    'thumbnail' => $thumbnail_path,
                    'name' => $post_data['name'],
                    'short_name' => $post_data['short_name'],
                    'description' => $post_data['description'],
                    'primary_email' => $post_data['primary_email'],
                    'secondary_email' => $post_data['secondary_email'],
                    'primary_phone' => $post_data['primary_phone'],
                    'secondary_phone' => $post_data['secondary_phone'],
                    'contact_person_name' => $post_data['contact_person_name'],
                    'contact_person_phone' => $post_data['contact_person_phone'],
                    'address_line1' => $post_data['address_line1'],
                    'address_line2' => $post_data['address_line2'],
                    'city' => $post_data['city'],
                    'state' => $post_data['state'],
                    'country' => $post_data['country'] ?: 'India',
                    'pincode' => $post_data['pincode'],
                    'landmark' => $post_data['landmark'],
                    'latitude' => $post_data['latitude'] ?: NULL,
                    'longitude' => $post_data['longitude'] ?: NULL,
                    'gst_number' => $post_data['gst_number'],
                    'fssai_license' => $post_data['fssai_license'],
                    'trade_license_number' => $post_data['trade_license_number'],
                    'store_type' => $post_data['store_type'],
                    'breakfast_time' => !empty($post_data['breakfast_time']) ? $post_data['breakfast_time'] : NULL,
                    'lunch_time' => !empty($post_data['lunch_time']) ? $post_data['lunch_time'] : NULL,
                    'dinner_time' => !empty($post_data['dinner_time']) ? $post_data['dinner_time'] : NULL,
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'is_operational' => isset($post_data['is_operational']) ? 1 : 0,
                    'created_by' => get_client_sessiondata('id'),
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'stores');

                if ($result) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Store added successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to add store'
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

            $header_data['title'] = 'View Store || ' . config_item('application_name');

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['store'] = $this->stores->get_by_id($id, $client_id);

            if (!$data['store']) {
                $this->session->set_flashdata('error', 'Store not found');
                redirect('client/stores');
            }

            // Get related data
            $data['usage'] = $this->stores->get_usage_summary($id);
            $data['staff'] = $this->stores->get_store_staff($id);
            $data['documents'] = $this->storedocuments->get_all($id);

            $validation_data['qr_data'] = base_url('store/'.$data['store']->store_code);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/stores/view', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/store_view', $validation_data);
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function edit($id)
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Edit Store || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['store'] = $this->stores->get_by_id($id, $client_id);

            if (!$data['store']) {
                $this->session->set_flashdata('error', 'Store not found');
                redirect('client/stores');
            }

            // Get usage info for warning
            $data['usage'] = $this->stores->get_usage_summary($id);
            $data['companies'] = $this->stores->get_companies_dropdown($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/stores/edit', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/stores_form');
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

            if (isset($post_data['store_id']) && $post_data['store_id'] !== '' && !empty($post_data['store_code']) && !empty($post_data['name']) && !empty($post_data['store_type']) && isset($post_data['company_id']) && $post_data['company_id'] !== '') {

                // Verify store belongs to this client
                $store = $this->stores->get_by_id($post_data['store_id'], $client_id);
                if (!$store) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Store not found'
                    );
                    echo json_encode($response);
                    return;
                }

                // Check if store code already exists (excluding current)
                $existing = $this->stores->check_store_code_exists($post_data['store_code'], $post_data['store_id']);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Store code already exists. Please use a unique code.'
                    );
                    echo json_encode($response);
                    return;
                }

                // Handle thumbnail upload
                $thumbnail_path = $post_data['existing_thumbnail']; // Keep existing by default
                if (!empty($_FILES['thumbnail']['name'])) {
                    $config['upload_path'] = './uploads/stores/';
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = TRUE;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('thumbnail')) {
                        $upload_data = $this->upload->data();
                        $thumbnail_path = 'uploads/stores/' . $upload_data['file_name'];

                        // Delete old thumbnail file if it exists
                        if (!empty($post_data['existing_thumbnail']) && file_exists('./' . $post_data['existing_thumbnail'])) {
                            unlink('./' . $post_data['existing_thumbnail']);
                        }
                    }
                }

                $data = array(
                    'company_id' => $post_data['company_id'],
                    'store_code' => strtoupper($post_data['store_code']),
                    'name' => $post_data['name'],
                    'short_name' => $post_data['short_name'],
                    'thumbnail' => $thumbnail_path,
                    'description' => $post_data['description'],
                    'primary_email' => $post_data['primary_email'],
                    'secondary_email' => $post_data['secondary_email'],
                    'primary_phone' => $post_data['primary_phone'],
                    'secondary_phone' => $post_data['secondary_phone'],
                    'contact_person_name' => $post_data['contact_person_name'],
                    'contact_person_phone' => $post_data['contact_person_phone'],
                    'address_line1' => $post_data['address_line1'],
                    'address_line2' => $post_data['address_line2'],
                    'city' => $post_data['city'],
                    'state' => $post_data['state'],
                    'country' => $post_data['country'] ?: 'India',
                    'pincode' => $post_data['pincode'],
                    'landmark' => $post_data['landmark'],
                    'latitude' => $post_data['latitude'] ?: NULL,
                    'longitude' => $post_data['longitude'] ?: NULL,
                    'gst_number' => $post_data['gst_number'],
                    'fssai_license' => $post_data['fssai_license'],
                    'trade_license_number' => $post_data['trade_license_number'],
                    'store_type' => $post_data['store_type'],
                    'breakfast_time' => !empty($post_data['breakfast_time']) ? $post_data['breakfast_time'] : NULL,
                    'lunch_time' => !empty($post_data['lunch_time']) ? $post_data['lunch_time'] : NULL,
                    'dinner_time' => !empty($post_data['dinner_time']) ? $post_data['dinner_time'] : NULL,
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'is_operational' => isset($post_data['is_operational']) ? 1 : 0,
                    'updated_by' => get_client_sessiondata('id'),
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->update('stores', $data, ['id' => $post_data['store_id']]);

                if ($result !== false) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Store updated successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to update store'
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

            // Verify store belongs to this client
            $store = $this->stores->get_by_id($id, $client_id);
            if (!$store) {
                echo json_encode(['status' => 'error', 'message' => 'Store not found']);
                return;
            }

            $usage = $this->stores->get_usage_summary($id);

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
            $force_delete = $this->input->post('force_delete', true);
            $client_id = get_client_sessiondata('client_id');

            // Verify store belongs to this client
            $store = $this->stores->get_by_id($id, $client_id);
            if (!$store) {
                echo json_encode(['status' => 'error', 'message' => 'Store not found']);
                return;
            }

            // Check if store has related data
            $usage = $this->stores->get_usage_summary($id);
            $total_usage = $usage['staff'];

            if ($total_usage > 0 && !$force_delete) {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'This store has related data',
                    'usage' => $usage,
                    'requires_confirmation' => true
                ]);
                return;
            }

            // If force delete, remove all related data first
            if ($force_delete) {
                $this->stores->delete_store_staff($id);
            }

            // Soft delete
            $data = array(
                'is_active' => 0,
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_by' => get_client_sessiondata('id')
            );

            $result = $this->common->update('stores', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Store deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete store']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    // ==================== STAFF MANAGEMENT ====================

    public function staff($store_id)
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Store Staff || ' . config_item('application_name');

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');

            // Verify store belongs to this client
            $data['store'] = $this->stores->get_by_id($store_id, $client_id);
            if (!$data['store']) {
                $this->session->set_flashdata('error', 'Store not found');
                redirect('client/stores');
            }

            $data['staff'] = $this->stores->get_store_staff($store_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/stores/staff/index', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/store_staff');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function add_staff($store_id)
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Add Staff || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');

            // Verify store belongs to this client
            $data['store'] = $this->stores->get_by_id($store_id, $client_id);
            if (!$data['store']) {
                $this->session->set_flashdata('error', 'Store not found');
                redirect('client/stores');
            }

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/stores/staff/add', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/store_staff_form');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function store_staff()
    {
        if (is_loggedin_client()) {
            $post_data = html_escape($this->input->post());
            $client_id = get_client_sessiondata('client_id');

            if (isset($post_data['store_id']) && $post_data['store_id'] !== '' && !empty($post_data['first_name']) && !empty($post_data['email']) && !empty($post_data['password'])) {

                // Verify store belongs to this client
                $store = $this->stores->get_by_id($post_data['store_id'], $client_id);
                if (!$store) {
                    $response = array('status' => 400, 'message' => 'Store not found');
                    echo json_encode($response);
                    return;
                }

                // Validate password
                if (strlen($post_data['password']) < 6) {
                    $response = array('status' => 400, 'message' => 'Password must be at least 6 characters');
                    echo json_encode($response);
                    return;
                }

                if ($post_data['password'] !== $post_data['confirm_password']) {
                    $response = array('status' => 400, 'message' => 'Passwords do not match');
                    echo json_encode($response);
                    return;
                }

                // Check for duplicate email across entire client (all stores)
                if ($this->stores->check_staff_email_exists($client_id, $post_data['email'])) {
                    $response = array('status' => 400, 'message' => 'Email already exists. Please use a different email.');
                    echo json_encode($response);
                    return;
                }

                // Auto-generate staff code
                $staff_code = $this->stores->generate_staff_code($post_data['store_id'], $store->store_code);

                $data = array(
                    'store_id' => $post_data['store_id'],
                    'staff_code' => $staff_code,
                    'first_name' => $post_data['first_name'],
                    'last_name' => $post_data['last_name'],
                    'email' => $post_data['email'],
                    'phone' => $post_data['phone'],
                    'id_number' => !empty($post_data['id_number']) ? $post_data['id_number'] : null,
                    'password_hash' => password_hash($post_data['password'], PASSWORD_DEFAULT),
                    'role' => 'BILLER',
                    'is_active' => isset($post_data['is_active']) ? 1 : 0,
                    'created_by' => get_client_sessiondata('id'),
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'store_staff');

                if ($result) {
                    $response = array('status' => 200, 'message' => 'Staff added successfully');
                } else {
                    $response = array('status' => 400, 'message' => 'Failed to add staff');
                }
            } else {
                $response = array('status' => 400, 'message' => 'Required fields are missing');
            }
        } else {
            $response = array('status' => 400, 'message' => 'Please login again');
        }

        echo json_encode($response);
    }

    public function edit_staff($staff_id)
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Edit Staff || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');

            // Get staff and verify through store ownership
            $data['staff'] = $this->stores->get_staff_by_id($staff_id, $client_id);

            if (!$data['staff']) {
                $this->session->set_flashdata('error', 'Staff not found');
                redirect('client/stores');
            }

            $data['store'] = $this->stores->get_by_id($data['staff']->store_id, $client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/stores/staff/edit', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/store_staff_form');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function update_staff()
    {
        if (is_loggedin_client()) {
            $post_data = html_escape($this->input->post());
            $client_id = get_client_sessiondata('client_id');

            if (!empty($post_data['staff_id']) && !empty($post_data['first_name']) && !empty($post_data['email'])) {

                // Get staff and verify through store ownership
                $staff = $this->stores->get_staff_for_update($post_data['staff_id']);

                if (!$staff || $staff->client_id != $client_id) {
                    $response = array('status' => 400, 'message' => 'Staff not found');
                    echo json_encode($response);
                    return;
                }

                // Validate password if provided
                if (!empty($post_data['password'])) {
                    if (strlen($post_data['password']) < 6) {
                        $response = array('status' => 400, 'message' => 'Password must be at least 6 characters');
                        echo json_encode($response);
                        return;
                    }

                    if ($post_data['password'] !== $post_data['confirm_password']) {
                        $response = array('status' => 400, 'message' => 'Passwords do not match');
                        echo json_encode($response);
                        return;
                    }
                }

                // Check for duplicate email across entire client (excluding current staff)
                if ($this->stores->check_staff_email_exists($client_id, $post_data['email'], $post_data['staff_id'])) {
                    $response = array('status' => 400, 'message' => 'Email already exists. Please use a different email.');
                    echo json_encode($response);
                    return;
                }

                $data = array(
                    'first_name' => $post_data['first_name'],
                    'last_name' => $post_data['last_name'],
                    'email' => $post_data['email'],
                    'phone' => $post_data['phone'],
                    'id_number' => !empty($post_data['id_number']) ? $post_data['id_number'] : null,
                    'is_active' => isset($post_data['is_active']) ? 1 : 0,
                    'updated_by' => get_client_sessiondata('id'),
                    'updated_at' => date('Y-m-d H:i:s')
                );

                // Update password if provided
                if (!empty($post_data['password'])) {
                    $data['password_hash'] = password_hash($post_data['password'], PASSWORD_DEFAULT);
                }

                $result = $this->common->update('store_staff', $data, ['id' => $post_data['staff_id']]);

                if ($result !== false) {
                    $response = array('status' => 200, 'message' => 'Staff updated successfully');
                } else {
                    $response = array('status' => 400, 'message' => 'Failed to update staff');
                }
            } else {
                $response = array('status' => 400, 'message' => 'Required fields are missing');
            }
        } else {
            $response = array('status' => 400, 'message' => 'Please login again');
        }

        echo json_encode($response);
    }

    public function delete_staff()
    {
        if (is_loggedin_client()) {
            $id = $this->input->post('id', true);
            $client_id = get_client_sessiondata('client_id');

            // Get staff and verify through store ownership
            $staff = $this->stores->get_staff_for_update($id);

            if (!$staff || $staff->client_id != $client_id) {
                echo json_encode(['status' => 'error', 'message' => 'Staff not found']);
                return;
            }

            // Soft delete
            $data = array(
                'is_active' => 0,
                'deleted_at' => date('Y-m-d H:i:s')
            );

            $result = $this->common->update('store_staff', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Staff deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete staff']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    // ------------------------------------------------------------------
    // Store Documents Methods
    // ------------------------------------------------------------------

    /**
     * List all documents for a store
     *
     * GET /client/stores/documents/{store_id}
     */
    public function documents($store_id)
    {
        if (is_loggedin_client()) {

            $client_id = get_client_sessiondata('client_id');
            $store = $this->stores->get_by_id($store_id, $client_id);

            if (!$store) {
                $this->session->set_flashdata('error', 'Store not found');
                redirect('client/stores');
            }

            $header_data['title'] = 'Documents - ' . $store->name . ' || ' . config_item('application_name');

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['store'] = $store;
            $data['documents'] = $this->storedocuments->get_all($store_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/store_documents/index', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/store_documents');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    /**
     * Show upload document form
     *
     * GET /client/stores/add_document/{store_id}
     */
    public function add_document($store_id)
    {
        if (is_loggedin_client()) {

            $client_id = get_client_sessiondata('client_id');
            $store = $this->stores->get_by_id($store_id, $client_id);

            if (!$store) {
                $this->session->set_flashdata('error', 'Store not found');
                redirect('client/stores');
            }

            $header_data['title'] = 'Upload Document - ' . $store->name . ' || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['store'] = $store;

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/store_documents/add', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/store_documents_form');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    /**
     * Store uploaded document
     *
     * POST /client/stores/store_document
     */
    public function store_document()
    {
        if (is_loggedin_client()) {
            $post_data = html_escape($this->input->post());
            $client_id = get_client_sessiondata('client_id');

            if (isset($post_data['store_id']) && $post_data['store_id'] !== '' && !empty($post_data['label'])) {

                // Verify store belongs to this client
                $store = $this->stores->get_by_id($post_data['store_id'], $client_id);
                if (!$store) {
                    echo json_encode(['status' => 400, 'message' => 'Store not found']);
                    return;
                }

                // Check if file was uploaded
                if (empty($_FILES['document']['name'])) {
                    echo json_encode(['status' => 400, 'message' => 'Please select a file to upload']);
                    return;
                }

                // Upload file
                $upload_path = './uploads/stores/documents/';
                $allowed_types = 'pdf|doc|docx|jpg|jpeg|png|xls|xlsx';
                $upload_data = upload_file('document', $upload_path, $allowed_types, 10240);

                if (!$upload_data) {
                    echo json_encode(['status' => 400, 'message' => 'Failed to upload file. Please check file type and size (max 10MB).']);
                    return;
                }

                $data = array(
                    'store_id' => $post_data['store_id'],
                    'client_id' => $client_id,
                    'label' => $post_data['label'],
                    'original_filename' => $upload_data['orig_name'],
                    'file_path' => 'uploads/stores/documents/' . $upload_data['file_name'],
                    'file_size' => $upload_data['file_size'] * 1024,
                    'file_extension' => $upload_data['file_ext'],
                    'mime_type' => $upload_data['file_type'],
                    'uploaded_by' => get_client_sessiondata('id'),
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'store_documents');

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
     * POST /client/stores/delete_document
     */
    public function delete_document()
    {
        if (is_loggedin_client()) {

            $id = $this->input->post('id', true);
            $client_id = get_client_sessiondata('client_id');

            $document = $this->storedocuments->get_by_id($id, $client_id);
            if (!$document) {
                echo json_encode(['status' => 'error', 'message' => 'Document not found']);
                return;
            }

            // Soft delete
            $data = array(
                'deleted_at' => date('Y-m-d H:i:s')
            );

            $result = $this->common->update('store_documents', $data, ['id' => $id]);

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
     * GET /client/stores/download_document/{id}
     */
    public function download_document($id)
    {
        if (is_loggedin_client()) {

            $client_id = get_client_sessiondata('client_id');
            $document = $this->storedocuments->get_by_id($id, $client_id);

            if (!$document) {
                $this->session->set_flashdata('error', 'Document not found');
                redirect('client/stores');
                return;
            }

            $file_path = './' . $document->file_path;

            if (!file_exists($file_path)) {
                $this->session->set_flashdata('error', 'File not found on server');
                redirect('client/stores/documents/' . $document->store_id);
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
