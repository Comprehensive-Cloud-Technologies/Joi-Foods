<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('CompanyDashboard_model', 'dashboard_model');
        $this->load->model('Common_model', 'common');
    }

    /**
     * Check if a module is enabled for the company
     */
    private function is_module_enabled($module)
    {
        $company = get_company_details();
        if (!$company) return false;

        switch ($module) {
            case 'QSR': return !empty($company->qsr_enabled);
            case 'KOT': return !empty($company->delivery_enabled);
            case 'PREMEAL': return !empty($company->premeal_enabled);
            default: return false;
        }
    }

    public function qsr()
    {
        if (is_loggedin_company()) {
            if (!$this->is_module_enabled('QSR')) {
                redirect('company');
                return;
            }

            $company_id = get_company_sessiondata('company_id');

            $header_data['title'] = 'QSR Report || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['datepicker'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['datepicker'] = true;
            $footer_data['select_2'] = true;

            $this->load->view('company/common/header', $header_data);
            $this->load->view('company/common/sidebar');
            $this->load->view('company/reports/qsr');
            $this->load->view('company/common/footer', $footer_data);
            $this->load->view('company/validation/reports_qsr');
        } else {
            redirect('company');
        }
    }

    public function qsr_data()
    {
        if (!is_loggedin_company()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        if (!$this->is_module_enabled('QSR')) {
            echo json_encode(['status' => 'error', 'message' => 'QSR module is not enabled']);
            return;
        }

        $company_id = get_company_sessiondata('company_id');
        $filters = array(
            'date_from' => $this->input->post('date_from', true),
            'date_to' => $this->input->post('date_to', true),
            'status' => $this->input->post('status', true),
            'order_type' => $this->input->post('order_type', true)
        );

        $orders = $this->dashboard_model->get_qsr_orders($company_id, $filters);
        $summary = $this->dashboard_model->get_qsr_summary($company_id, $filters);

        echo json_encode([
            'status' => 'success',
            'orders' => $orders,
            'summary' => [
                'total_orders' => (int)($summary->total_orders ?? 0),
                'total_amount' => round((float)($summary->total_amount ?? 0), 2),
                'company_contribution' => round((float)($summary->company_contribution ?? 0), 2),
                'employee_contribution' => round((float)($summary->employee_contribution ?? 0), 2)
            ]
        ]);
    }

    public function kot()
    {
        if (is_loggedin_company()) {
            if (!$this->is_module_enabled('KOT')) {
                redirect('company');
                return;
            }

            $company_id = get_company_sessiondata('company_id');

            $header_data['title'] = 'KOT Report || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['datepicker'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['datepicker'] = true;
            $footer_data['select_2'] = true;

            $data['departments'] = $this->dashboard_model->get_departments_list($company_id);

            $this->load->view('company/common/header', $header_data);
            $this->load->view('company/common/sidebar');
            $this->load->view('company/reports/kot', $data);
            $this->load->view('company/common/footer', $footer_data);
            $this->load->view('company/validation/reports_kot');
        } else {
            redirect('company');
        }
    }

    public function kot_data()
    {
        if (!is_loggedin_company()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        if (!$this->is_module_enabled('KOT')) {
            echo json_encode(['status' => 'error', 'message' => 'KOT module is not enabled']);
            return;
        }

        $company_id = get_company_sessiondata('company_id');
        $filters = array(
            'date_from' => $this->input->post('date_from', true),
            'date_to' => $this->input->post('date_to', true),
            'department_id' => $this->input->post('department_id', true),
            'status' => $this->input->post('status', true),
            'order_type' => $this->input->post('order_type', true)
        );

        $orders = $this->dashboard_model->get_kot_orders($company_id, $filters);
        $summary = $this->dashboard_model->get_kot_summary($company_id, $filters);

        echo json_encode([
            'status' => 'success',
            'orders' => $orders,
            'summary' => [
                'total_orders' => (int)($summary->total_orders ?? 0),
                'total_amount' => round((float)($summary->total_amount ?? 0), 2),
                'company_contribution' => round((float)($summary->company_contribution ?? 0), 2),
                'employee_contribution' => round((float)($summary->employee_contribution ?? 0), 2)
            ]
        ]);
    }

    public function order_detail()
    {
        if (!is_loggedin_company()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $company_id = get_company_sessiondata('company_id');
        $order_id = $this->input->post('order_id', true);

        if (empty($order_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Order ID required']);
            return;
        }

        $order = $this->dashboard_model->get_order_detail($order_id, $company_id);

        if (!$order) {
            echo json_encode(['status' => 'error', 'message' => 'Order not found']);
            return;
        }

        $items = $this->dashboard_model->get_order_items($order_id);
        $payments = $this->dashboard_model->get_order_payments($order_id);

        echo json_encode([
            'status' => 'success',
            'order' => $order,
            'items' => $items,
            'payments' => $payments
        ]);
    }

    public function premeal()
    {
        if (is_loggedin_company()) {
            if (!$this->is_module_enabled('PREMEAL')) {
                redirect('company');
                return;
            }

            $company_id = get_company_sessiondata('company_id');

            $header_data['title'] = 'Premeal Report || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['datepicker'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['datepicker'] = true;
            $footer_data['select_2'] = true;

            $data['departments'] = $this->dashboard_model->get_departments_list($company_id);

            $this->load->view('company/common/header', $header_data);
            $this->load->view('company/common/sidebar');
            $this->load->view('company/reports/premeal', $data);
            $this->load->view('company/common/footer', $footer_data);
            $this->load->view('company/validation/reports_premeal');
        } else {
            redirect('company');
        }
    }

    public function premeal_data()
    {
        if (!is_loggedin_company()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        if (!$this->is_module_enabled('PREMEAL')) {
            echo json_encode(['status' => 'error', 'message' => 'Premeal module is not enabled']);
            return;
        }

        $company_id = get_company_sessiondata('company_id');
        $filters = array(
            'date_from' => $this->input->post('date_from', true),
            'date_to' => $this->input->post('date_to', true),
            'meal_type' => $this->input->post('meal_type', true),
            'department_id' => $this->input->post('department_id', true),
            'status' => $this->input->post('status', true)
        );

        $orders = $this->dashboard_model->get_premeal_orders($company_id, $filters);
        $summary = $this->dashboard_model->get_premeal_summary($company_id, $filters);

        echo json_encode([
            'status' => 'success',
            'orders' => $orders,
            'summary' => [
                'total_orders' => (int)($summary->total_orders ?? 0),
                'total_amount' => round((float)($summary->total_amount ?? 0), 2),
                'company_contribution' => round((float)($summary->company_contribution ?? 0), 2),
                'employee_contribution' => round((float)($summary->employee_contribution ?? 0), 2)
            ]
        ]);
    }
}
