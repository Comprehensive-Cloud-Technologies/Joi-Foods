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
        if (is_loggedin_company()) {

            $header_data['title'] = 'My Profile || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $data['user'] = get_company_user_details();
            $data['company'] = get_company_details();

            $this->load->view('company/common/header', $header_data);
            $this->load->view('company/common/sidebar');
            $this->load->view('company/profile/index', $data);
            $this->load->view('company/common/footer', $footer_data);
            $this->load->view('company/validation/profile');
        } else {
            $data['title'] = 'Company Login || ' . config_item('application_name');
            $this->load->view('company/auth/login', $data);
        }
    }

    public function update()
    {
        if (is_loggedin_company()) {
            $post = html_escape($this->input->post());
            $user_id = get_company_sessiondata('id');

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

            $this->common->update('company_users', $update_data, ['id' => $user_id]);

            // Refresh session data (re-run login query to get joined fields)
            $updated_user = $this->db->query(
                "SELECT cu.*, c.name as company_name, c.company_code, c.is_active as company_active
                 FROM company_users cu
                 INNER JOIN companies c ON cu.company_id = c.id
                 WHERE cu.id = ?",
                [$user_id]
            )->row();
            if ($updated_user) {
                $this->session->set_userdata('user_details_company', $updated_user);
            }

            echo json_encode(['status' => 200, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
        }
    }

    public function change_password()
    {
        if (is_loggedin_company()) {
            $post = $this->input->post();
            $user_id = get_company_sessiondata('id');

            if (empty($post['current_password']) || empty($post['new_password']) || empty($post['confirm_password'])) {
                echo json_encode(['status' => 400, 'message' => 'All password fields are required']);
                return;
            }

            // Verify current password
            $user = $this->common->getdatabytable('company_users', ['id' => $user_id]);
            if (!password_verify($post['current_password'], $user->password_hash)) {
                echo json_encode(['status' => 400, 'message' => 'Current password is incorrect']);
                return;
            }

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

            $this->common->update('company_users', $update_data, ['id' => $user_id]);

            echo json_encode(['status' => 200, 'message' => 'Password changed successfully']);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
        }
    }
}
