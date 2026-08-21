<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('ClientDashboard_model', 'dashboard_model');
    }

    public function index()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Dashboard || ' . config_item('application_name');
            $footer_data['apex_chart'] = true;

            $client_id = get_client_sessiondata('client_id');

            $this_month_start = date('Y-m-01');
            $this_month_end = date('Y-m-t');
            $last_month_start = date('Y-m-01', strtotime('-1 month'));
            $last_month_end = date('Y-m-t', strtotime('-1 month'));

            // Quick stats
            $data['stats'] = array(
                'companies' => $this->dashboard_model->get_companies_count($client_id),
                'stores' => $this->dashboard_model->get_stores_count($client_id),
                'employees' => $this->dashboard_model->get_employees_count($client_id),
                'products' => $this->dashboard_model->get_products_count($client_id),
                'today_orders' => $this->dashboard_model->get_today_orders_count($client_id),
                'month_orders' => $this->dashboard_model->get_month_orders_count($client_id, $this_month_start, $this_month_end)
            );

            // Revenue
            $this_month_rev = $this->dashboard_model->get_revenue_summary($client_id, $this_month_start, $this_month_end);
            $last_month_rev = $this->dashboard_model->get_revenue_summary($client_id, $last_month_start, $last_month_end);
            $data['revenue'] = array(
                'this_month' => array_merge($this_month_rev, array('label' => date('M Y'))),
                'last_month' => array_merge($last_month_rev, array('label' => date('M Y', strtotime('-1 month'))))
            );

            // Breakdowns
            $data['module_stats'] = $this->dashboard_model->get_module_stats($client_id, $this_month_start, $this_month_end);
            $data['company_billing'] = $this->dashboard_model->get_company_billing($client_id, $this_month_start, $this_month_end);
            $data['store_orders'] = $this->dashboard_model->get_store_orders($client_id, $this_month_start, $this_month_end);
            $data['monthly_trend'] = $this->dashboard_model->get_monthly_revenue_trend($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/home/index', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/home', $data);
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }
}
