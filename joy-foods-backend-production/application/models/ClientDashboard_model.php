<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class ClientDashboard_model extends CI_Model
{
    private $excluded_statuses = array('CANCELLED', 'REJECTED');

    // --- Quick Stats ---

    public function get_companies_count($client_id)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('companies');
    }

    public function get_stores_count($client_id)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('stores');
    }

    public function get_employees_count($client_id)
    {
        $this->db->from('employees e');
        $this->db->join('companies c', 'c.id = e.company_id');
        $this->db->where('c.client_id', $client_id);
        $this->db->where('e.deleted_at', NULL);
        return $this->db->count_all_results();
    }

    public function get_products_count($client_id)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('products');
    }

    public function get_today_orders_count($client_id)
    {
        $this->db->from('orders o');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->where('c.client_id', $client_id);
        $this->db->where('DATE(o.created_at)', date('Y-m-d'));
        $this->db->where_not_in('o.status', $this->excluded_statuses);
        return $this->db->count_all_results();
    }

    public function get_month_orders_count($client_id, $month_start, $month_end)
    {
        $this->db->from('orders o');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->where('c.client_id', $client_id);
        $this->db->where('DATE(o.created_at) >=', $month_start);
        $this->db->where('DATE(o.created_at) <=', $month_end);
        $this->db->where_not_in('o.status', $this->excluded_statuses);
        return $this->db->count_all_results();
    }

    // --- Revenue ---

    public function get_revenue_summary($client_id, $month_start, $month_end)
    {
        $this->db->select('COALESCE(SUM(o.company_contribution),0) as company_contribution, COALESCE(SUM(o.employee_contribution),0) as employee_contribution, COALESCE(SUM(o.total_amount),0) as total_amount');
        $this->db->from('orders o');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->where('c.client_id', $client_id);
        $this->db->where('DATE(o.created_at) >=', $month_start);
        $this->db->where('DATE(o.created_at) <=', $month_end);
        $this->db->where_not_in('o.status', $this->excluded_statuses);
        $row = $this->db->get()->row();

        return array(
            'company_contribution' => round((float)($row->company_contribution ?? 0), 2),
            'employee_contribution' => round((float)($row->employee_contribution ?? 0), 2),
            'total' => round((float)($row->total_amount ?? 0), 2)
        );
    }

    // --- Module Breakdown ---

    public function get_module_stats($client_id, $month_start, $month_end)
    {
        $query = $this->db
            ->select('o.module, COUNT(*) as order_count, COALESCE(SUM(o.total_amount),0) as total, COALESCE(SUM(o.company_contribution),0) as company_share, COALESCE(SUM(o.employee_contribution),0) as employee_share')
            ->from('orders o')
            ->join('companies c', 'c.id = o.company_id')
            ->where('c.client_id', $client_id)
            ->where('DATE(o.created_at) >=', $month_start)
            ->where('DATE(o.created_at) <=', $month_end)
            ->where_not_in('o.status', $this->excluded_statuses)
            ->group_by('o.module')
            ->get();

        $results = array(
            'QSR' => array('order_count' => 0, 'total' => 0, 'company_share' => 0, 'employee_share' => 0),
            'KOT' => array('order_count' => 0, 'total' => 0, 'company_share' => 0, 'employee_share' => 0),
            'PREMEAL' => array('order_count' => 0, 'total' => 0, 'company_share' => 0, 'employee_share' => 0)
        );

        foreach ($query->result() as $row) {
            $results[$row->module] = array(
                'order_count' => (int)$row->order_count,
                'total' => round((float)$row->total, 2),
                'company_share' => round((float)$row->company_share, 2),
                'employee_share' => round((float)$row->employee_share, 2)
            );
        }

        return $results;
    }

    // --- Company-wise Billing ---

    public function get_company_billing($client_id, $month_start, $month_end)
    {
        return $this->db
            ->select('c.name as company_name, c.company_code, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total, COALESCE(SUM(o.company_contribution),0) as company_share, COALESCE(SUM(o.employee_contribution),0) as employee_share')
            ->from('orders o')
            ->join('companies c', 'c.id = o.company_id')
            ->where('c.client_id', $client_id)
            ->where('DATE(o.created_at) >=', $month_start)
            ->where('DATE(o.created_at) <=', $month_end)
            ->where_not_in('o.status', $this->excluded_statuses)
            ->group_by('o.company_id')
            ->order_by('total', 'DESC')
            ->get()
            ->result();
    }

    // --- Store-wise Orders ---

    public function get_store_orders($client_id, $month_start, $month_end)
    {
        return $this->db
            ->select('s.name as store_name, s.store_code, s.store_type, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total')
            ->from('orders o')
            ->join('stores s', 's.id = o.store_id')
            ->where('s.client_id', $client_id)
            ->where('DATE(o.created_at) >=', $month_start)
            ->where('DATE(o.created_at) <=', $month_end)
            ->where_not_in('o.status', $this->excluded_statuses)
            ->group_by('o.store_id')
            ->order_by('total', 'DESC')
            ->get()
            ->result();
    }

    // --- Filter Dropdowns ---

    public function get_companies_list($client_id)
    {
        return $this->db
            ->select('id, name, company_code')
            ->where('client_id', $client_id)
            ->where('is_active', 1)
            ->where('deleted_at', NULL)
            ->order_by('name', 'ASC')
            ->get('companies')
            ->result();
    }

    public function get_stores_list($client_id)
    {
        return $this->db
            ->select('id, name, store_code, store_type')
            ->where('client_id', $client_id)
            ->where('deleted_at', NULL)
            ->order_by('name', 'ASC')
            ->get('stores')
            ->result();
    }

    // --- Sales Report ---

    public function get_sales_orders($client_id, $filters = array())
    {
        $this->db->select('o.id, o.order_number, o.module, o.total_amount, o.company_contribution, o.employee_contribution, o.status, o.created_at, o.meal_type, o.scheduled_date, o.is_guest_order, o.guest_name, o.guest_phone, c.name as company_name, c.company_code, s.name as store_name, e.first_name, e.last_name, e.employee_code');
        $this->db->from('orders o');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->join('stores s', 's.id = o.store_id', 'left');
        $this->db->join('employees e', 'e.id = o.employee_id', 'left');
        $this->db->where('c.client_id', $client_id);
        $this->db->where_not_in('o.status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(o.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(o.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['company_id'])) {
            $this->db->where('o.company_id', $filters['company_id']);
        }
        if (!empty($filters['store_id'])) {
            $this->db->where('o.store_id', $filters['store_id']);
        }
        if (!empty($filters['module'])) {
            $this->db->where('o.module', $filters['module']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('o.status', $filters['status']);
        }
        if (isset($filters['order_type']) && $filters['order_type'] !== '') {
            if ($filters['order_type'] === 'guest') {
                $this->db->where('o.is_guest_order', 1);
            } else if ($filters['order_type'] === 'employee') {
                $this->db->where('o.is_guest_order', 0);
            }
        }

        $this->db->order_by('o.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_sales_summary($client_id, $filters = array())
    {
        $this->db->select('COUNT(*) as total_orders, COALESCE(SUM(o.total_amount),0) as total_amount, COALESCE(SUM(o.company_contribution),0) as company_contribution, COALESCE(SUM(o.employee_contribution),0) as employee_contribution');
        $this->db->from('orders o');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->where('c.client_id', $client_id);
        $this->db->where_not_in('o.status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(o.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(o.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['company_id'])) {
            $this->db->where('o.company_id', $filters['company_id']);
        }
        if (!empty($filters['store_id'])) {
            $this->db->where('o.store_id', $filters['store_id']);
        }
        if (!empty($filters['module'])) {
            $this->db->where('o.module', $filters['module']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('o.status', $filters['status']);
        }
        if (isset($filters['order_type']) && $filters['order_type'] !== '') {
            if ($filters['order_type'] === 'guest') {
                $this->db->where('o.is_guest_order', 1);
            } else if ($filters['order_type'] === 'employee') {
                $this->db->where('o.is_guest_order', 0);
            }
        }

        return $this->db->get()->row();
    }

    // --- Tax Report (GST / Accounting) ---

    /**
     * Apply common order-level filters for tax report queries.
     * Joined alias: o = orders, c = companies
     */
    private function _apply_tax_filters($client_id, $filters)
    {
        $this->db->where('c.client_id', $client_id);

        // Cancelled/rejected orders are not taxable sales — exclude unless a
        // specific status is requested.
        if (!empty($filters['status'])) {
            $this->db->where('o.status', $filters['status']);
        } else {
            $this->db->where_not_in('o.status', $this->excluded_statuses);
        }

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(o.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(o.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['company_id'])) {
            $this->db->where('o.company_id', $filters['company_id']);
        }
        if (!empty($filters['store_id'])) {
            $this->db->where('o.store_id', $filters['store_id']);
        }
        if (!empty($filters['module'])) {
            $this->db->where('o.module', $filters['module']);
        }
    }

    /**
     * Overall tax totals for the report header.
     */
    public function get_tax_summary($client_id, $filters = array())
    {
        $this->db->select('COUNT(*) as total_orders,
                           COALESCE(SUM(o.subtotal),0) as taxable_value,
                           COALESCE(SUM(o.tax_amount),0) as total_tax,
                           COALESCE(SUM(o.total_amount),0) as gross_total');
        $this->db->from('orders o');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->_apply_tax_filters($client_id, $filters);

        return $this->db->get()->row();
    }

    /**
     * Tax grouped by GST slab (order_items.tax_percentage).
     * This is the GSTR-style breakdown needed for filing.
     */
    public function get_tax_by_slab($client_id, $filters = array())
    {
        $this->db->select('oi.tax_percentage,
                           COUNT(DISTINCT o.id) as order_count,
                           COALESCE(SUM(oi.subtotal),0) as taxable_value,
                           COALESCE(SUM(oi.tax_amount),0) as tax_amount,
                           COALESCE(SUM(oi.total_amount),0) as gross_total');
        $this->db->from('order_items oi');
        $this->db->join('orders o', 'o.id = oi.order_id');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->_apply_tax_filters($client_id, $filters);
        $this->db->group_by('oi.tax_percentage');
        $this->db->order_by('oi.tax_percentage', 'ASC');

        return $this->db->get()->result();
    }

    /**
     * Order-wise tax detail listing.
     */
    public function get_tax_orders($client_id, $filters = array())
    {
        $this->db->select('o.id, o.order_number, o.module, o.status, o.created_at,
                           o.subtotal, o.tax_amount, o.discount_amount, o.total_amount,
                           o.is_guest_order, o.guest_name,
                           c.name as company_name, c.company_code,
                           s.name as store_name, s.gst_number as store_gst,
                           e.first_name, e.last_name, e.employee_code');
        $this->db->from('orders o');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->join('stores s', 's.id = o.store_id', 'left');
        $this->db->join('employees e', 'e.id = o.employee_id', 'left');
        $this->_apply_tax_filters($client_id, $filters);
        $this->db->order_by('o.created_at', 'DESC');

        return $this->db->get()->result();
    }

    // --- Company Billing Report ---

    public function get_company_billing_report($client_id, $filters = array())
    {
        $this->db->select('c.id as company_id, c.name as company_name, c.company_code, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total, COALESCE(SUM(o.company_contribution),0) as company_share, COALESCE(SUM(o.employee_contribution),0) as employee_share');
        $this->db->from('orders o');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->where('c.client_id', $client_id);
        $this->db->where_not_in('o.status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(o.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(o.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['company_id'])) {
            $this->db->where('o.company_id', $filters['company_id']);
        }
        if (!empty($filters['module'])) {
            $this->db->where('o.module', $filters['module']);
        }

        $this->db->group_by('o.company_id');
        $this->db->order_by('company_share', 'DESC');
        return $this->db->get()->result();
    }

    public function get_company_billing_summary($client_id, $filters = array())
    {
        $this->db->select('COUNT(DISTINCT o.company_id) as total_companies, COUNT(o.id) as total_orders, COALESCE(SUM(o.total_amount),0) as total_amount, COALESCE(SUM(o.company_contribution),0) as company_contribution, COALESCE(SUM(o.employee_contribution),0) as employee_contribution');
        $this->db->from('orders o');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->where('c.client_id', $client_id);
        $this->db->where_not_in('o.status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(o.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(o.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['company_id'])) {
            $this->db->where('o.company_id', $filters['company_id']);
        }
        if (!empty($filters['module'])) {
            $this->db->where('o.module', $filters['module']);
        }

        return $this->db->get()->row();
    }

    // --- Store Performance Report ---

    public function get_store_performance_report($client_id, $filters = array())
    {
        $this->db->select('s.id as store_id, s.name as store_name, s.store_code, s.store_type, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total, COALESCE(SUM(o.company_contribution),0) as company_share, COALESCE(SUM(o.employee_contribution),0) as employee_share');
        $this->db->from('orders o');
        $this->db->join('stores s', 's.id = o.store_id');
        $this->db->where('s.client_id', $client_id);
        $this->db->where_not_in('o.status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(o.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(o.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['store_id'])) {
            $this->db->where('o.store_id', $filters['store_id']);
        }
        if (!empty($filters['module'])) {
            $this->db->where('o.module', $filters['module']);
        }

        $this->db->group_by('o.store_id');
        $this->db->order_by('total', 'DESC');
        return $this->db->get()->result();
    }

    public function get_store_performance_summary($client_id, $filters = array())
    {
        $this->db->select('COUNT(DISTINCT o.store_id) as total_stores, COUNT(o.id) as total_orders, COALESCE(SUM(o.total_amount),0) as total_amount, COALESCE(SUM(o.company_contribution),0) as company_contribution, COALESCE(SUM(o.employee_contribution),0) as employee_contribution');
        $this->db->from('orders o');
        $this->db->join('stores s', 's.id = o.store_id');
        $this->db->where('s.client_id', $client_id);
        $this->db->where_not_in('o.status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(o.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(o.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['store_id'])) {
            $this->db->where('o.store_id', $filters['store_id']);
        }
        if (!empty($filters['module'])) {
            $this->db->where('o.module', $filters['module']);
        }

        return $this->db->get()->row();
    }

    // --- Order Detail ---

    public function get_order_detail($order_id, $client_id)
    {
        $this->db->select('o.*, c.name as company_name, c.company_code, s.name as store_name, s.store_code, s.store_type, e.first_name, e.last_name, e.employee_code, e.email as employee_email, e.phone as employee_phone, cd.name as department_name, dl.name as location_name, p.name as policy_name');
        $this->db->from('orders o');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->join('stores s', 's.id = o.store_id', 'left');
        $this->db->join('employees e', 'e.id = o.employee_id', 'left');
        $this->db->join('company_departments cd', 'cd.id = o.department_id', 'left');
        $this->db->join('delivery_locations dl', 'dl.id = o.delivery_location_id', 'left');
        $this->db->join('policies p', 'p.id = o.policy_id', 'left');
        $this->db->where('o.id', $order_id);
        $this->db->where('c.client_id', $client_id);
        return $this->db->get()->row();
    }

    public function get_order_items($order_id)
    {
        $this->db->select('oi.product_name, oi.quantity, oi.unit_price, oi.base_price, oi.tax_percentage, oi.tax_amount, oi.subtotal, oi.total_amount, oi.note');
        $this->db->from('order_items oi');
        $this->db->where('oi.order_id', $order_id);
        $this->db->order_by('oi.id', 'ASC');
        return $this->db->get()->result();
    }

    public function get_order_payments($order_id)
    {
        $this->db->select('payment_type, amount, status, razorpay_payment_id, created_at');
        $this->db->from('order_payments');
        $this->db->where('order_id', $order_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result();
    }

    // --- Monthly Trend ---

    public function get_monthly_revenue_trend($client_id, $months_back = 6)
    {
        $trend = array();

        for ($i = $months_back - 1; $i >= 0; $i--) {
            $month_start = date('Y-m-01', strtotime("-{$i} months"));
            $month_end = date('Y-m-t', strtotime("-{$i} months"));
            $label = date('M', strtotime("-{$i} months"));

            $this->db->select('COALESCE(SUM(o.total_amount),0) as total, COALESCE(SUM(o.company_contribution),0) as company_share, COALESCE(SUM(o.employee_contribution),0) as employee_share');
            $this->db->from('orders o');
            $this->db->join('companies c', 'c.id = o.company_id');
            $this->db->where('c.client_id', $client_id);
            $this->db->where('DATE(o.created_at) >=', $month_start);
            $this->db->where('DATE(o.created_at) <=', $month_end);
            $this->db->where_not_in('o.status', $this->excluded_statuses);
            $row = $this->db->get()->row();

            $trend[] = array(
                'label' => $label,
                'total' => round((float)($row->total ?? 0), 2),
                'company_share' => round((float)($row->company_share ?? 0), 2),
                'employee_share' => round((float)($row->employee_share ?? 0), 2)
            );
        }

        return $trend;
    }

    // --- Customer Reviews ---

    public function get_reviews($client_id, $filters = array())
    {
        $this->db->select('r.id, r.order_id, r.module, r.food_review, r.service_review, r.extra_comments, r.created_at,
                          o.order_number, o.total_amount, o.status as order_status,
                          s.name as store_name, s.store_code, s.store_type,
                          c.name as company_name, c.company_code,
                          e.first_name, e.last_name, e.employee_code');
        $this->db->from('order_reviews r');
        $this->db->join('orders o', 'o.id = r.order_id');
        $this->db->join('stores s', 's.id = r.store_id', 'left');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->join('employees e', 'e.id = r.employee_id', 'left');
        $this->db->where('c.client_id', $client_id);

        if (!empty($filters['company_id'])) {
            $this->db->where('o.company_id', $filters['company_id']);
        }
        if (!empty($filters['store_id'])) {
            $this->db->where('r.store_id', $filters['store_id']);
        }
        if (!empty($filters['module'])) {
            $this->db->where('r.module', $filters['module']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(r.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(r.created_at) <=', $filters['date_to']);
        }

        $this->db->order_by('r.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_review_detail($review_id, $client_id)
    {
        $this->db->select('r.*, o.order_number, o.total_amount, o.status as order_status, o.module as order_module, o.created_at as order_date,
                          s.name as store_name, s.store_code, s.store_type,
                          c.name as company_name, c.company_code,
                          e.first_name, e.last_name, e.employee_code, e.email as employee_email, e.phone as employee_phone');
        $this->db->from('order_reviews r');
        $this->db->join('orders o', 'o.id = r.order_id');
        $this->db->join('stores s', 's.id = r.store_id', 'left');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->join('employees e', 'e.id = r.employee_id', 'left');
        $this->db->where('r.id', $review_id);
        $this->db->where('c.client_id', $client_id);
        return $this->db->get()->row();
    }

    public function get_reviews_summary($client_id, $filters = array())
    {
        $this->db->select('COUNT(*) as total_reviews,
                          COUNT(DISTINCT r.store_id) as stores_reviewed,
                          COUNT(DISTINCT o.company_id) as companies_reviewed');
        $this->db->from('order_reviews r');
        $this->db->join('orders o', 'o.id = r.order_id');
        $this->db->join('companies c', 'c.id = o.company_id');
        $this->db->where('c.client_id', $client_id);

        if (!empty($filters['company_id'])) {
            $this->db->where('o.company_id', $filters['company_id']);
        }
        if (!empty($filters['store_id'])) {
            $this->db->where('r.store_id', $filters['store_id']);
        }
        if (!empty($filters['module'])) {
            $this->db->where('r.module', $filters['module']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(r.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(r.created_at) <=', $filters['date_to']);
        }

        return $this->db->get()->row();
    }

    // --- Employee Report ---

    public function get_employees_report($client_id, $filters = [])
    {
        $this->db->select('e.id, e.employee_code, e.first_name, e.last_name, e.email, e.phone,
            e.is_active, e.qsr_access, e.premeal_access, e.kot_permission, e.created_at,
            c.name as company_name, c.company_code,
            cd.name as department_name,
            COALESCE((SELECT SUM(t.amount) FROM transaction t WHERE t.user_id = e.id AND t.transaction_type = 1), 0)
            - COALESCE((SELECT SUM(t.amount) FROM transaction t WHERE t.user_id = e.id AND t.transaction_type = 2), 0)
            as wallet_balance', FALSE);
        $this->db->from('employees e');
        $this->db->join('companies c', 'c.id = e.company_id');
        $this->db->join('company_departments cd', 'cd.id = e.department_id', 'left');
        $this->db->where('c.client_id', $client_id);
        $this->db->where('e.deleted_at', NULL);

        if (!empty($filters['company_id'])) {
            $this->db->where('e.company_id', $filters['company_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('e.is_active', $filters['status'] == 'active' ? 1 : 0);
        }

        $this->db->order_by('e.id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_employees_summary($client_id, $filters = [])
    {
        $this->db->select('COUNT(e.id) as total_employees,
            SUM(CASE WHEN e.is_active = 1 THEN 1 ELSE 0 END) as active_employees,
            SUM(CASE WHEN e.is_active = 0 THEN 1 ELSE 0 END) as inactive_employees,
            COALESCE(SUM(
                COALESCE((SELECT SUM(t.amount) FROM transaction t WHERE t.user_id = e.id AND t.transaction_type = 1), 0)
                - COALESCE((SELECT SUM(t.amount) FROM transaction t WHERE t.user_id = e.id AND t.transaction_type = 2), 0)
            ), 0) as total_wallet_balance', FALSE);
        $this->db->from('employees e');
        $this->db->join('companies c', 'c.id = e.company_id');
        $this->db->where('c.client_id', $client_id);
        $this->db->where('e.deleted_at', NULL);

        if (!empty($filters['company_id'])) {
            $this->db->where('e.company_id', $filters['company_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('e.is_active', $filters['status'] == 'active' ? 1 : 0);
        }

        return $this->db->get()->row();
    }

    // --- Wallet Transactions Report ---

    public function get_wallet_transactions($client_id, $filters = [])
    {
        $this->db->select('t.transaction_id, t.transaction_uuid, t.user_id, t.order_id, t.amount,
            t.transaction_type, t.transaction_label, t.source, t.transaction_date, t.transaction_time,
            e.first_name, e.last_name, e.employee_code,
            c.name as company_name, c.company_code,
            wc.credited_by_name, wc.reason as credit_reason', FALSE);
        $this->db->from('transaction t');
        $this->db->join('employees e', 'e.id = t.user_id');
        $this->db->join('companies c', 'c.id = e.company_id');
        $this->db->join('wallet_credits wc', 'wc.transaction_id = t.transaction_id', 'left');
        $this->db->where('c.client_id', $client_id);

        if (!empty($filters['company_id'])) {
            $this->db->where('e.company_id', $filters['company_id']);
        }
        if (!empty($filters['employee_id'])) {
            $this->db->where('t.user_id', $filters['employee_id']);
        }
        if (!empty($filters['type'])) {
            $this->db->where('t.transaction_type', $filters['type'] == 'credit' ? 1 : 2);
        }
        if (!empty($filters['source'])) {
            $this->db->where('t.source', $filters['source']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('t.transaction_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('t.transaction_date <=', $filters['date_to']);
        }

        $this->db->order_by('t.transaction_id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_wallet_transactions_summary($client_id, $filters = [])
    {
        $this->db->select('COUNT(t.transaction_id) as total_transactions,
            COALESCE(SUM(CASE WHEN t.transaction_type = 1 THEN t.amount ELSE 0 END), 0) as total_credits,
            COALESCE(SUM(CASE WHEN t.transaction_type = 2 THEN t.amount ELSE 0 END), 0) as total_debits,
            COALESCE(SUM(CASE WHEN t.source = "COMPANY_CREDIT" THEN t.amount ELSE 0 END), 0) as company_credits', FALSE);
        $this->db->from('transaction t');
        $this->db->join('employees e', 'e.id = t.user_id');
        $this->db->join('companies c', 'c.id = e.company_id');
        $this->db->where('c.client_id', $client_id);

        if (!empty($filters['company_id'])) {
            $this->db->where('e.company_id', $filters['company_id']);
        }
        if (!empty($filters['employee_id'])) {
            $this->db->where('t.user_id', $filters['employee_id']);
        }
        if (!empty($filters['type'])) {
            $this->db->where('t.transaction_type', $filters['type'] == 'credit' ? 1 : 2);
        }
        if (!empty($filters['source'])) {
            $this->db->where('t.source', $filters['source']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('t.transaction_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('t.transaction_date <=', $filters['date_to']);
        }

        return $this->db->get()->row();
    }

    public function get_employees_list($client_id)
    {
        $this->db->select('e.id, e.first_name, e.last_name, e.employee_code, c.name as company_name');
        $this->db->from('employees e');
        $this->db->join('companies c', 'c.id = e.company_id');
        $this->db->where('c.client_id', $client_id);
        $this->db->where('e.deleted_at', NULL);
        $this->db->order_by('e.first_name', 'ASC');
        return $this->db->get()->result();
    }
}
