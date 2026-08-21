<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('ClientDashboard_model', 'dashboard_model');
        $this->load->model('StockReports_model', 'stock_model');
    }

    // --- Sales Report ---

    public function sales()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Sales Report || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['datepicker'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['datepicker'] = true;
            $footer_data['select_2'] = true;

            $data['companies'] = $this->dashboard_model->get_companies_list($client_id);
            $data['stores'] = $this->dashboard_model->get_stores_list($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/reports/sales', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/reports_sales');
        } else {
            redirect('client');
        }
    }

    public function sales_data()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $filters = array(
            'date_from' => $this->input->post('date_from', true),
            'date_to' => $this->input->post('date_to', true),
            'company_id' => $this->input->post('company_id', true),
            'store_id' => $this->input->post('store_id', true),
            'module' => $this->input->post('module', true),
            'status' => $this->input->post('status', true),
            'order_type' => $this->input->post('order_type', true)
        );

        $orders = $this->dashboard_model->get_sales_orders($client_id, $filters);
        $summary = $this->dashboard_model->get_sales_summary($client_id, $filters);

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
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $order_id = $this->input->post('order_id', true);

        if (empty($order_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Order ID required']);
            return;
        }

        $order = $this->dashboard_model->get_order_detail($order_id, $client_id);

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

    // --- Company Billing Report ---

    public function company_billing()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Company Billing Report || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['datepicker'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['datepicker'] = true;
            $footer_data['select_2'] = true;

            $data['companies'] = $this->dashboard_model->get_companies_list($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/reports/company_billing', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/reports_company_billing');
        } else {
            redirect('client');
        }
    }

    public function company_billing_data()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $filters = array(
            'date_from' => $this->input->post('date_from', true),
            'date_to' => $this->input->post('date_to', true),
            'company_id' => $this->input->post('company_id', true),
            'module' => $this->input->post('module', true)
        );

        $rows = $this->dashboard_model->get_company_billing_report($client_id, $filters);
        $summary = $this->dashboard_model->get_company_billing_summary($client_id, $filters);

        echo json_encode([
            'status' => 'success',
            'rows' => $rows,
            'summary' => [
                'total_companies' => (int)($summary->total_companies ?? 0),
                'total_orders' => (int)($summary->total_orders ?? 0),
                'total_amount' => round((float)($summary->total_amount ?? 0), 2),
                'company_contribution' => round((float)($summary->company_contribution ?? 0), 2),
                'employee_contribution' => round((float)($summary->employee_contribution ?? 0), 2)
            ]
        ]);
    }

    // --- Store Performance Report ---

    public function store_performance()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Store Performance Report || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['datepicker'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['datepicker'] = true;
            $footer_data['select_2'] = true;

            $data['stores'] = $this->dashboard_model->get_stores_list($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/reports/store_performance', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/reports_store_performance');
        } else {
            redirect('client');
        }
    }

    public function store_performance_data()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $filters = array(
            'date_from' => $this->input->post('date_from', true),
            'date_to' => $this->input->post('date_to', true),
            'store_id' => $this->input->post('store_id', true),
            'module' => $this->input->post('module', true)
        );

        $rows = $this->dashboard_model->get_store_performance_report($client_id, $filters);
        $summary = $this->dashboard_model->get_store_performance_summary($client_id, $filters);

        echo json_encode([
            'status' => 'success',
            'rows' => $rows,
            'summary' => [
                'total_stores' => (int)($summary->total_stores ?? 0),
                'total_orders' => (int)($summary->total_orders ?? 0),
                'total_amount' => round((float)($summary->total_amount ?? 0), 2),
                'company_contribution' => round((float)($summary->company_contribution ?? 0), 2),
                'employee_contribution' => round((float)($summary->employee_contribution ?? 0), 2)
            ]
        ]);
    }

    // --- Customer Reviews ---

    public function reviews()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Customer Reviews || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['datepicker'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['datepicker'] = true;
            $footer_data['select_2'] = true;

            $data['companies'] = $this->dashboard_model->get_companies_list($client_id);
            $data['stores'] = $this->dashboard_model->get_stores_list($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/reports/reviews', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/reports_reviews');
        } else {
            redirect('client');
        }
    }

    public function reviews_data()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $filters = array(
            'date_from' => $this->input->post('date_from', true),
            'date_to' => $this->input->post('date_to', true),
            'company_id' => $this->input->post('company_id', true),
            'store_id' => $this->input->post('store_id', true),
            'module' => $this->input->post('module', true)
        );

        $reviews = $this->dashboard_model->get_reviews($client_id, $filters);
        $summary = $this->dashboard_model->get_reviews_summary($client_id, $filters);

        echo json_encode([
            'status' => 'success',
            'reviews' => $reviews,
            'summary' => [
                'total_reviews' => (int)($summary->total_reviews ?? 0),
                'stores_reviewed' => (int)($summary->stores_reviewed ?? 0),
                'companies_reviewed' => (int)($summary->companies_reviewed ?? 0)
            ]
        ]);
    }

    // --- Employee Report ---

    public function employees()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Employee Report || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['select_2'] = true;

            $data['companies'] = $this->dashboard_model->get_companies_list($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/reports/employees', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/reports_employees');
        } else {
            redirect('client');
        }
    }

    public function employees_data()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $filters = array(
            'company_id' => $this->input->post('company_id', true),
            'status' => $this->input->post('status', true)
        );

        $employees = $this->dashboard_model->get_employees_report($client_id, $filters);
        $summary = $this->dashboard_model->get_employees_summary($client_id, $filters);

        echo json_encode([
            'status' => 'success',
            'employees' => $employees,
            'summary' => [
                'total_employees' => (int)($summary->total_employees ?? 0),
                'active_employees' => (int)($summary->active_employees ?? 0),
                'inactive_employees' => (int)($summary->inactive_employees ?? 0),
                'total_wallet_balance' => round((float)($summary->total_wallet_balance ?? 0), 2)
            ]
        ]);
    }

    // --- Wallet Transactions Report ---

    public function wallet_transactions()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Wallet Transactions || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['datepicker'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['datepicker'] = true;
            $footer_data['select_2'] = true;

            $data['companies'] = $this->dashboard_model->get_companies_list($client_id);
            $data['employees'] = $this->dashboard_model->get_employees_list($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/reports/wallet_transactions', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/reports_wallet_transactions');
        } else {
            redirect('client');
        }
    }

    public function wallet_transactions_data()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $filters = array(
            'company_id' => $this->input->post('company_id', true),
            'employee_id' => $this->input->post('employee_id', true),
            'type' => $this->input->post('type', true),
            'source' => $this->input->post('source', true),
            'date_from' => $this->input->post('date_from', true),
            'date_to' => $this->input->post('date_to', true)
        );

        $transactions = $this->dashboard_model->get_wallet_transactions($client_id, $filters);
        $summary = $this->dashboard_model->get_wallet_transactions_summary($client_id, $filters);

        echo json_encode([
            'status' => 'success',
            'transactions' => $transactions,
            'summary' => [
                'total_transactions' => (int)($summary->total_transactions ?? 0),
                'total_credits' => round((float)($summary->total_credits ?? 0), 2),
                'total_debits' => round((float)($summary->total_debits ?? 0), 2),
                'company_credits' => round((float)($summary->company_credits ?? 0), 2)
            ]
        ]);
    }

    // --- Inventory Report (Current Stock) ---

    public function inventory()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Inventory Report || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['select_2'] = true;

            $data['companies'] = $this->dashboard_model->get_companies_list($client_id);
            $data['stores'] = $this->dashboard_model->get_stores_list($client_id);
            $data['categories'] = $this->stock_model->get_categories_list($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/reports/inventory', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/reports_inventory');
        } else {
            redirect('client');
        }
    }

    public function inventory_data()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $filters = array(
            'company_id' => $this->input->post('company_id', true),
            'store_id' => $this->input->post('store_id', true),
            'category_id' => $this->input->post('category_id', true),
            'stock_status' => $this->input->post('stock_status', true)
        );

        $rows = $this->stock_model->get_current_stock($client_id, $filters);
        $summary = $this->stock_model->get_current_stock_summary($client_id, $filters);

        echo json_encode([
            'status' => 'success',
            'rows' => $rows,
            'summary' => [
                'total_products' => (int)($summary->total_products ?? 0),
                'in_stock' => (int)($summary->in_stock ?? 0),
                'out_of_stock' => (int)($summary->out_of_stock ?? 0),
                'unlimited_products' => (int)($summary->unlimited_products ?? 0),
                'total_units' => (int)($summary->total_units ?? 0)
            ]
        ]);
    }

    // --- Inventory Transactions Report (Stock In/Out Ledger) ---

    public function inventory_transactions()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Inventory Transactions || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['datepicker'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['datepicker'] = true;
            $footer_data['select_2'] = true;

            $data['companies'] = $this->dashboard_model->get_companies_list($client_id);
            $data['stores'] = $this->dashboard_model->get_stores_list($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/reports/inventory_transactions', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/reports_inventory_transactions');
        } else {
            redirect('client');
        }
    }

    public function inventory_transactions_data()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $filters = array(
            'company_id' => $this->input->post('company_id', true),
            'store_id' => $this->input->post('store_id', true),
            'transaction_type' => $this->input->post('transaction_type', true),
            'source' => $this->input->post('source', true),
            'date_from' => $this->input->post('date_from', true),
            'date_to' => $this->input->post('date_to', true)
        );

        $transactions = $this->stock_model->get_transactions($client_id, $filters);
        $summary = $this->stock_model->get_transactions_summary($client_id, $filters);

        echo json_encode([
            'status' => 'success',
            'transactions' => $transactions,
            'summary' => [
                'total_transactions' => (int)($summary->total_transactions ?? 0),
                'total_in' => (int)($summary->total_in ?? 0),
                'total_out' => (int)($summary->total_out ?? 0),
                'total_adjustments' => (int)($summary->total_adjustments ?? 0)
            ]
        ]);
    }

    // --- Tax Report (GST / Accounting) ---

    public function tax()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Tax Report || ' . config_item('application_name');
            $header_data['datatable'] = true;
            $header_data['datatable_buttons'] = true;
            $header_data['datepicker'] = true;
            $header_data['select_2'] = true;

            $footer_data['datatable'] = true;
            $footer_data['datatable_buttons'] = true;
            $footer_data['datepicker'] = true;
            $footer_data['select_2'] = true;

            $data['companies'] = $this->dashboard_model->get_companies_list($client_id);
            $data['stores'] = $this->dashboard_model->get_stores_list($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/reports/tax', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/reports_tax');
        } else {
            redirect('client');
        }
    }

    public function tax_data()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $filters = array(
            'date_from' => $this->input->post('date_from', true),
            'date_to' => $this->input->post('date_to', true),
            'company_id' => $this->input->post('company_id', true),
            'store_id' => $this->input->post('store_id', true),
            'module' => $this->input->post('module', true),
            'status' => $this->input->post('status', true)
        );

        $summary = $this->dashboard_model->get_tax_summary($client_id, $filters);
        $slabs = $this->dashboard_model->get_tax_by_slab($client_id, $filters);
        $orders = $this->dashboard_model->get_tax_orders($client_id, $filters);

        echo json_encode([
            'status' => 'success',
            'summary' => [
                'total_orders' => (int)($summary->total_orders ?? 0),
                'taxable_value' => round((float)($summary->taxable_value ?? 0), 2),
                'total_tax' => round((float)($summary->total_tax ?? 0), 2),
                'gross_total' => round((float)($summary->gross_total ?? 0), 2)
            ],
            'slabs' => $slabs,
            'orders' => $orders
        ]);
    }

    public function review_detail()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $review_id = $this->input->post('review_id', true);

        if (empty($review_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Review ID required']);
            return;
        }

        $review = $this->dashboard_model->get_review_detail($review_id, $client_id);

        if (!$review) {
            echo json_encode(['status' => 'error', 'message' => 'Review not found']);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'review' => $review
        ]);
    }
}
