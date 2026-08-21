<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Common extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
    }


    public function check_login()
    {
        if (!is_loggedin_company()) {

            $post_data = html_escape($this->input->post());
            if (!empty($post_data)) {
                $user_details = $this->common->check_company($post_data);
                if (!empty($user_details)) {
                    $this->session->set_userdata('user_details_company', $user_details);

                    $message = array(
                        "status" => 200,
                        "message" => "Successfully logged in..."
                    );
                } else {
                    $message = array(
                        "status" => 400,
                        "message" => "Incorrect Email/Phone or Password. Please check your credentials."
                    );
                }
            } else {
                $message = array(
                    "status" => 400,
                    "message" => "Please enter your credentials."
                );
            }
        } else {
            $message = array(
                'status'  => 400,
                'message' => 'You are already logged in.'
            );
        }
        echo json_encode(@$message);
    }

    public function logout()
    {
        $this->session->unset_userdata('user_details_company');
        redirect("company");
    }
}
