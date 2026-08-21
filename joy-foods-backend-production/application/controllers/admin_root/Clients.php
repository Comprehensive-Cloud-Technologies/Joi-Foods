<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Clients extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Clients_model', 'clients');
    }

    public function index()
    {
        if (is_loggedin_admin()) {

            $header_data['title'] = 'Client Management || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['clients'] = $this->common->getdatabytableall('clients', ['deleted_at' => NULL]);

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/clients/index', $data);
            $this->load->view('admin/common/footer', $footer_data);

            $this->load->view('admin/validation/clients');
        } else {
            //load login files..
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('admin/auth/login', $data);
        }
    }

    public function add()
    {
        if (is_loggedin_admin()) {

            $header_data['title'] = 'Add Client || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/clients/add');
            $this->load->view('admin/common/footer', $footer_data);

            $this->load->view('admin/validation/clients_form');
        } else {
            //load login files..
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('admin/auth/login', $data);
        }
    }

    public function store()
    {
        if (is_loggedin_admin()) {
            $post_data = $this->input->post(null, true);

            if (!empty($post_data['client_code']) && !empty($post_data['name']) && !empty($post_data['email'])) {

                $data = array(
                    'client_code' => $post_data['client_code'],
                    'name' => $post_data['name'],
                    'legal_name' => $post_data['legal_name'],
                    'email' => $post_data['email'],
                    'phone' => $post_data['phone'],
                    'alternate_phone' => $post_data['alternate_phone'],
                    'address_line1' => $post_data['address_line1'],
                    'address_line2' => $post_data['address_line2'],
                    'city' => $post_data['city'],
                    'state' => $post_data['state'],
                    'country' => $post_data['country'],
                    'pincode' => $post_data['pincode'],
                    'gst_number' => $post_data['gst_number'],
                    'pan_number' => $post_data['pan_number'],
                    'fssai_license' => $post_data['fssai_license'],
                    'razorpay_key_id' => $post_data['razorpay_key_id'],
                    'razorpay_key_secret' => $post_data['razorpay_key_secret'],
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'created_by' => $this->session->userdata('admin_id'),
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'clients');

                if ($result) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Client added successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to add client'
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
        if (is_loggedin_admin()) {

            $header_data['title'] = 'View Client || ' . config_item('application_name');

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['client'] = $this->common->getdatabytable('clients', ['id' => $id]);

            if (!$data['client']) {
                $this->session->set_flashdata('error', 'Client not found');
                redirect('admin_root/clients');
            }

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/clients/view', $data);
            $this->load->view('admin/common/footer', $footer_data);

            $this->load->view('admin/validation/clients');
        } else {
            //load login files..
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('admin/auth/login', $data);
        }
    }

    public function edit($id)
    {
        if (is_loggedin_admin()) {

            $header_data['title'] = 'Edit Client || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['client'] = $this->common->getdatabytable('clients', ['id' => $id]);

            if (!$data['client']) {
                $this->session->set_flashdata('error', 'Client not found');
                redirect('admin_root/clients');
            }

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/clients/edit', $data);
            $this->load->view('admin/common/footer', $footer_data);

            $this->load->view('admin/validation/clients_form');
        } else {
            //load login files..
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('admin/auth/login', $data);
        }
    }

    public function update()
    {
        if (is_loggedin_admin()) {
            $post_data = $this->input->post(null, true);

            if (isset($post_data['client_id']) && $post_data['client_id'] !== '' && !empty($post_data['client_code']) && !empty($post_data['name']) && !empty($post_data['email'])) {

                $data = array(
                    'client_code' => $post_data['client_code'],
                    'name' => $post_data['name'],
                    'legal_name' => $post_data['legal_name'],
                    'email' => $post_data['email'],
                    'phone' => $post_data['phone'],
                    'alternate_phone' => $post_data['alternate_phone'],
                    'address_line1' => $post_data['address_line1'],
                    'address_line2' => $post_data['address_line2'],
                    'city' => $post_data['city'],
                    'state' => $post_data['state'],
                    'country' => $post_data['country'],
                    'pincode' => $post_data['pincode'],
                    'gst_number' => $post_data['gst_number'],
                    'pan_number' => $post_data['pan_number'],
                    'fssai_license' => $post_data['fssai_license'],
                    'razorpay_key_id' => $post_data['razorpay_key_id'],
                    'razorpay_key_secret' => $post_data['razorpay_key_secret'],
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'updated_by' => $this->session->userdata('admin_id'),
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->update('clients', $data, ['id' => $post_data['client_id']]);

                if ($result !== false) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Client updated successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to update client'
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

    public function delete()
    {
        if (is_loggedin_admin()) {

            $id = $this->input->post('id', true);

            // Soft delete
            $data = array(
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('admin_id')
            );

            $result = $this->common->update('clients', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Client deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete client']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    // =====================================================
    // CLIENT USERS METHODS
    // =====================================================

    public function users()
    {
        if (is_loggedin_admin()) {

            $header_data['title'] = 'Client Users || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            // Get all client users with client names using join
            $data['users'] = $this->clients->users();

            $data['clients'] = $this->common->getdatabytableall('clients', ['deleted_at' => NULL, 'is_active' => 1]);

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/client_users/index', $data);
            $this->load->view('admin/common/footer', $footer_data);

            $this->load->view('admin/validation/client_users');
        } else {
            //load login files..
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('admin/auth/login', $data);
        }
    }

    public function add_user()
    {
        if (is_loggedin_admin()) {

            $header_data['title'] = 'Add Client User || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $data['clients'] = $this->common->getdatabytableall('clients', ['deleted_at' => NULL, 'is_active' => 1]);

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/client_users/add', $data);
            $this->load->view('admin/common/footer', $footer_data);

            $this->load->view('admin/validation/client_users_form');
        } else {
            //load login files..
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('admin/auth/login', $data);
        }
    }

    public function store_user()
    {
        if (is_loggedin_admin()) {
            $post_data = $this->input->post(null, true);

            if (isset($post_data['client_id']) && $post_data['client_id'] !== '' && !empty($post_data['email']) && !empty($post_data['first_name']) && !empty($post_data['password'])) {

                // Check if email already exists for this client
                $existing = $this->common->getdatabytable('client_users', [
                    'client_id' => $post_data['client_id'],
                    'email' => $post_data['email'],
                    'deleted_at' => NULL
                ]);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Email already exists for this client'
                    );
                    echo json_encode($response);
                    return;
                }

                $data = array(
                    'client_id' => $post_data['client_id'],
                    'email' => $post_data['email'],
                    'password_hash' => password_hash($post_data['password'], PASSWORD_DEFAULT),
                    'first_name' => $post_data['first_name'],
                    'last_name' => $post_data['last_name'],
                    'phone' => $post_data['phone'],
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'client_users');

                if ($result) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Client user added successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to add client user'
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

    public function edit_user($id)
    {
        if (is_loggedin_admin()) {

            $header_data['title'] = 'Edit Client User || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $data['user'] = $this->common->getdatabytable('client_users', ['id' => $id]);

            if (!$data['user']) {
                $this->session->set_flashdata('error', 'User not found');
                redirect('admin_root/clients/users');
            }

            $data['clients'] = $this->common->getdatabytableall('clients', ['deleted_at' => NULL, 'is_active' => 1]);

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/client_users/edit', $data);
            $this->load->view('admin/common/footer', $footer_data);

            $this->load->view('admin/validation/client_users_form');
        } else {
            //load login files..
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('admin/auth/login', $data);
        }
    }

    public function update_user()
    {
        if (is_loggedin_admin()) {
            $post_data = $this->input->post(null, true);

            if (isset($post_data['user_id']) && $post_data['user_id'] !== '' && isset($post_data['client_id']) && $post_data['client_id'] !== '' && !empty($post_data['email']) && !empty($post_data['first_name'])) {

                // Check if email already exists for this client (excluding current user)
                $existing = $this->clients->check_email_exists($post_data['client_id'], $post_data['email'], $post_data['user_id']);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Email already exists for this client'
                    );
                    echo json_encode($response);
                    return;
                }

                $data = array(
                    'client_id' => $post_data['client_id'],
                    'email' => $post_data['email'],
                    'first_name' => $post_data['first_name'],
                    'last_name' => $post_data['last_name'],
                    'phone' => $post_data['phone'],
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'updated_at' => date('Y-m-d H:i:s')
                );

                // Update password only if provided
                if (!empty($post_data['password'])) {
                    $data['password_hash'] = password_hash($post_data['password'], PASSWORD_DEFAULT);
                }

                $result = $this->common->update('client_users', $data, ['id' => $post_data['user_id']]);

                if ($result !== false) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Client user updated successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to update client user'
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

    public function delete_user()
    {
        if (is_loggedin_admin()) {

            $id = $this->input->post('id', true);

            // Soft delete
            $data = array(
                'deleted_at' => date('Y-m-d H:i:s')
            );

            $result = $this->common->update('client_users', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Client user deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete client user']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }
}
