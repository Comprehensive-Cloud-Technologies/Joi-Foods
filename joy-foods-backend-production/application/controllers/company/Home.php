<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Company_employees_model', 'employees_model');
        $this->load->model('CompanyDashboard_model', 'dashboard_model');
        $this->load->model('Common_model', 'common');
    }

    public function index()
    {
        if (is_loggedin_company()) {

            $header_data['title'] = 'Dashboard || ' . config_item('application_name');
            $footer_data['apex_chart'] = true;

            $company = get_company_details();
            $company_id = get_company_sessiondata('company_id');

            $this_month_start = date('Y-m-01');
            $this_month_end = date('Y-m-t');
            $last_month_start = date('Y-m-01', strtotime('-1 month'));
            $last_month_end = date('Y-m-t', strtotime('-1 month'));

            // Stats
            $data['company'] = $company;
            $data['stats'] = array(
                'employees' => $this->employees_model->get_all_by_company_count($company_id),
                'departments' => $this->dashboard_model->get_departments_count($company_id),
                'today_orders' => $this->dashboard_model->get_today_orders_count($company_id),
                'month_orders' => $this->dashboard_model->get_month_orders_count($company_id, $this_month_start, $this_month_end)
            );

            // Contributions
            $this_month_contrib = $this->dashboard_model->get_contribution_summary($company_id, $this_month_start, $this_month_end);
            $last_month_contrib = $this->dashboard_model->get_contribution_summary($company_id, $last_month_start, $last_month_end);
            $data['contributions'] = array(
                'this_month' => array_merge($this_month_contrib, array('label' => date('M Y'))),
                'last_month' => array_merge($last_month_contrib, array('label' => date('M Y', strtotime('-1 month'))))
            );

            // Module & department breakdown
            $data['module_stats'] = $this->dashboard_model->get_module_stats($company_id, $this_month_start, $this_month_end);
            $data['dept_billing'] = $this->dashboard_model->get_department_kot_billing($company_id, $this_month_start, $this_month_end);
            $data['monthly_trend'] = $this->dashboard_model->get_monthly_contribution_trend($company_id);

            $this->load->view('company/common/header', $header_data);
            $this->load->view('company/common/sidebar');
            $this->load->view('company/home/index', $data);
            $this->load->view('company/common/footer', $footer_data);
            $this->load->view('company/validation/home', $data);
        } else {
            $data['title'] = 'Company Login || ' . config_item('application_name');
            $this->load->view('company/auth/login', $data);
        }
    }
}
