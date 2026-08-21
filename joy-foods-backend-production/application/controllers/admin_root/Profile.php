<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
    }

    /**
     * Change password form
     */
    public function change_password()
    {
        if (is_loggedin_admin()) {

            $header_data['title'] = 'Change Password || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/profile/change_password');
            $this->load->view('admin/common/footer', $footer_data);
            $this->load->view('admin/validation/profile');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('admin/auth/login', $data);
        }
    }

    /**
     * Update password (AJAX)
     */
    public function update_password()
    {
        if (!is_loggedin_admin()) {
            echo json_encode(['status' => 400, 'message' => 'Please login again']);
            return;
        }

        $post_data = $this->input->post();
        $current = isset($post_data['current_password']) ? $post_data['current_password'] : '';
        $new = isset($post_data['new_password']) ? $post_data['new_password'] : '';
        $confirm = isset($post_data['confirm_password']) ? $post_data['confirm_password'] : '';

        if (empty($current) || empty($new) || empty($confirm)) {
            echo json_encode(['status' => 400, 'message' => 'All fields are required']);
            return;
        }

        if (strlen($new) < 6) {
            echo json_encode(['status' => 400, 'message' => 'New password must be at least 6 characters']);
            return;
        }

        if ($new !== $confirm) {
            echo json_encode(['status' => 400, 'message' => 'New password and confirm password do not match']);
            return;
        }

        $admin_id = get_admin_sessiondata('id');
        $admin = $this->common->getdatabytable('admin', ['id' => $admin_id]);

        if (empty($admin)) {
            echo json_encode(['status' => 400, 'message' => 'Account not found']);
            return;
        }

        // Admin passwords use the legacy md5 scheme (see Common_model::check_admin)
        if ($admin->password !== md5($current)) {
            echo json_encode(['status' => 400, 'message' => 'Current password is incorrect']);
            return;
        }

        if (md5($new) === $admin->password) {
            echo json_encode(['status' => 400, 'message' => 'New password must be different from the current password']);
            return;
        }

        $this->common->update('admin', ['password' => md5($new)], ['id' => $admin_id]);

        echo json_encode(['status' => 200, 'message' => 'Password changed successfully']);
    }
}
