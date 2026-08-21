<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (is_loggedin_admin()) {

            $header_data['title'] = 'Dashboard || ' . config_item('application_name');
            $footer_data['apex_chart'] = true;

            $data = array();
            
            $this->load->view('admin/common/header', $header_data);
            $this->load->view('admin/common/sidebar');
            $this->load->view('admin/home/index', $data);
            $this->load->view('admin/common/footer', $footer_data);
            $this->load->view('admin/validation/home', $data);
        } else {
            //load login files..
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('admin/auth/login', $data);
        }
    }

}
