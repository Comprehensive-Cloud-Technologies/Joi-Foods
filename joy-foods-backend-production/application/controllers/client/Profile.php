<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
    }

    public function index()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'My Profile || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['user'] = get_client_user_details();
            $data['client'] = $this->common->getdatabytable('clients', ['id' => get_client_sessiondata('client_id')]);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/profile/index', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/profile');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function update()
    {
        if (is_loggedin_client()) {
            $post = html_escape($this->input->post());
            $user_id = get_client_sessiondata('id');

            if (empty($post['first_name'])) {
                echo json_encode(['status' => 400, 'message' => 'First name is required']);
                return;
            }

            $update_data = [
                'first_name' => $post['first_name'],
                'last_name' => isset($post['last_name']) ? $post['last_name'] : null,
                'phone' => isset($post['phone']) ? $post['phone'] : null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->common->update('client_users', $update_data, ['id' => $user_id]);

            // Refresh session data
            $updated_user = $this->common->getdatabytable('client_users', ['id' => $user_id]);
            $this->session->set_userdata('user_details_client', $updated_user);

            echo json_encode(['status' => 200, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
        }
    }

    public function change_password()
    {
        if (is_loggedin_client()) {
            $post = $this->input->post();
            $user_id = get_client_sessiondata('id');

            if (empty($post['current_password']) || empty($post['new_password']) || empty($post['confirm_password'])) {
                echo json_encode(['status' => 400, 'message' => 'All password fields are required']);
                return;
            }

            // Verify current password
            $user = $this->common->getdatabytable('client_users', ['id' => $user_id]);
            if (!password_verify($post['current_password'], $user->password_hash)) {
                echo json_encode(['status' => 400, 'message' => 'Current password is incorrect']);
                return;
            }

            // Validate new password
            if (strlen($post['new_password']) < 6) {
                echo json_encode(['status' => 400, 'message' => 'New password must be at least 6 characters']);
                return;
            }

            if ($post['new_password'] !== $post['confirm_password']) {
                echo json_encode(['status' => 400, 'message' => 'New passwords do not match']);
                return;
            }

            $update_data = [
                'password_hash' => password_hash($post['new_password'], PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->common->update('client_users', $update_data, ['id' => $user_id]);

            echo json_encode(['status' => 200, 'message' => 'Password changed successfully']);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
        }
    }
}
