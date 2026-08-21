<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class CompanyDashboard_model extends CI_Model
{
    private $excluded_statuses = array('CANCELLED', 'REJECTED');

    public function get_departments_count($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('company_departments');
    }

    public function get_today_orders_count($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        $this->db->where_not_in('status', $this->excluded_statuses);
        return $this->db->count_all_results('orders');
    }

    public function get_month_orders_count($company_id, $month_start, $month_end)
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('DATE(created_at) >=', $month_start);
        $this->db->where('DATE(created_at) <=', $month_end);
        $this->db->where_not_in('status', $this->excluded_statuses);
        return $this->db->count_all_results('orders');
    }

    public function get_contribution_summary($company_id, $month_start, $month_end)
    {
        $this->db->select_sum('company_contribution');
        $this->db->select_sum('employee_contribution');
        $this->db->select_sum('total_amount');
        $this->db->where('company_id', $company_id);
        $this->db->where('DATE(created_at) >=', $month_start);
        $this->db->where('DATE(created_at) <=', $month_end);
        $this->db->where_not_in('status', $this->excluded_statuses);
        $row = $this->db->get('orders')->row();

        return array(
            'company_contribution' => round((float)($row->company_contribution ?? 0), 2),
            'employee_contribution' => round((float)($row->employee_contribution ?? 0), 2),
            'total' => round((float)($row->total_amount ?? 0), 2)
        );
    }

    public function get_module_stats($company_id, $month_start, $month_end)
    {
        $query = $this->db
            ->select('module, COUNT(*) as order_count, COALESCE(SUM(total_amount),0) as total, COALESCE(SUM(company_contribution),0) as company_share, COALESCE(SUM(employee_contribution),0) as employee_share')
            ->where('company_id', $company_id)
            ->where('DATE(created_at) >=', $month_start)
            ->where('DATE(created_at) <=', $month_end)
            ->where_not_in('status', $this->excluded_statuses)
            ->group_by('module')
            ->get('orders');

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

    public function get_department_kot_billing($company_id, $month_start, $month_end)
    {
        return $this->db
            ->select('cd.name as department_name, cd.code as department_code, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total, COALESCE(SUM(o.company_contribution),0) as company_share, COALESCE(SUM(o.employee_contribution),0) as employee_share')
            ->from('orders o')
            ->join('company_departments cd', 'cd.id = o.department_id', 'left')
            ->where('o.company_id', $company_id)
            ->where('o.module', 'KOT')
            ->where('DATE(o.created_at) >=', $month_start)
            ->where('DATE(o.created_at) <=', $month_end)
            ->where_not_in('o.status', $this->excluded_statuses)
            ->group_by('o.department_id')
            ->order_by('company_share', 'DESC')
            ->get()
            ->result();
    }

    public function get_monthly_contribution_trend($company_id, $months_back = 6)
    {
        $trend = array();

        for ($i = $months_back - 1; $i >= 0; $i--) {
            $month_start = date('Y-m-01', strtotime("-{$i} months"));
            $month_end = date('Y-m-t', strtotime("-{$i} months"));
            $label = date('M', strtotime("-{$i} months"));

            $this->db->select('COALESCE(SUM(company_contribution),0) as company_share, COALESCE(SUM(employee_contribution),0) as employee_share');
            $this->db->where('company_id', $company_id);
            $this->db->where('DATE(created_at) >=', $month_start);
            $this->db->where('DATE(created_at) <=', $month_end);
            $this->db->where_not_in('status', $this->excluded_statuses);
            $row = $this->db->get('orders')->row();

            $trend[] = array(
                'label' => $label,
                'company_share' => round((float)($row->company_share ?? 0), 2),
                'employee_share' => round((float)($row->employee_share ?? 0), 2)
            );
        }

        return $trend;
    }

    // --- Report Methods ---

    public function get_departments_list($company_id)
    {
        return $this->db
            ->select('id, name, code')
            ->where('company_id', $company_id)
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->get('company_departments')
            ->result();
    }

    // --- QSR Report ---

    public function get_qsr_orders($company_id, $filters = array())
    {
        $this->db->select('o.id, o.order_number, o.total_amount, o.company_contribution, o.employee_contribution, o.wallet_deducted, o.discount_amount, o.status, o.created_at, o.payment_method, o.is_guest_order, o.guest_name, o.guest_phone, s.name as store_name, e.first_name, e.last_name, e.employee_code');
        $this->db->from('orders o');
        $this->db->join('stores s', 's.id = o.store_id', 'left');
        $this->db->join('employees e', 'e.id = o.employee_id', 'left');
        $this->db->where('o.company_id', $company_id);
        $this->db->where('o.module', 'QSR');
        $this->db->where_not_in('o.status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(o.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(o.created_at) <=', $filters['date_to']);
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

    public function get_qsr_summary($company_id, $filters = array())
    {
        $this->db->select('COUNT(*) as total_orders, COALESCE(SUM(total_amount),0) as total_amount, COALESCE(SUM(company_contribution),0) as company_contribution, COALESCE(SUM(employee_contribution),0) as employee_contribution');
        $this->db->from('orders');
        $this->db->where('company_id', $company_id);
        $this->db->where('module', 'QSR');
        $this->db->where_not_in('status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (isset($filters['order_type']) && $filters['order_type'] !== '') {
            if ($filters['order_type'] === 'guest') {
                $this->db->where('is_guest_order', 1);
            } else if ($filters['order_type'] === 'employee') {
                $this->db->where('is_guest_order', 0);
            }
        }

        return $this->db->get()->row();
    }

    // --- KOT Report ---

    public function get_kot_orders($company_id, $filters = array())
    {
        $this->db->select('o.id, o.order_number, o.total_amount, o.company_contribution, o.employee_contribution, o.wallet_deducted, o.discount_amount, o.status, o.created_at, o.payment_method, o.is_guest_order, o.guest_name, o.guest_phone, e.first_name, e.last_name, e.employee_code, cd.name as department_name, cd.code as department_code, dl.name as location_name');
        $this->db->from('orders o');
        $this->db->join('employees e', 'e.id = o.employee_id', 'left');
        $this->db->join('company_departments cd', 'cd.id = o.department_id', 'left');
        $this->db->join('delivery_locations dl', 'dl.id = o.delivery_location_id', 'left');
        $this->db->where('o.company_id', $company_id);
        $this->db->where('o.module', 'KOT');
        $this->db->where_not_in('o.status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(o.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(o.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['department_id'])) {
            $this->db->where('o.department_id', $filters['department_id']);
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

    public function get_kot_summary($company_id, $filters = array())
    {
        $this->db->select('COUNT(*) as total_orders, COALESCE(SUM(total_amount),0) as total_amount, COALESCE(SUM(company_contribution),0) as company_contribution, COALESCE(SUM(employee_contribution),0) as employee_contribution');
        $this->db->from('orders');
        $this->db->where('company_id', $company_id);
        $this->db->where('module', 'KOT');
        $this->db->where_not_in('status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['department_id'])) {
            $this->db->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (isset($filters['order_type']) && $filters['order_type'] !== '') {
            if ($filters['order_type'] === 'guest') {
                $this->db->where('is_guest_order', 1);
            } else if ($filters['order_type'] === 'employee') {
                $this->db->where('is_guest_order', 0);
            }
        }

        return $this->db->get()->row();
    }

    public function get_premeal_orders($company_id, $filters = array())
    {
        $this->db->select('o.id, o.order_number, o.total_amount, o.company_contribution, o.employee_contribution, o.wallet_deducted, o.discount_amount, o.status, o.created_at, o.meal_type, o.scheduled_date, o.payment_method, e.first_name, e.last_name, e.employee_code, cd.name as department_name');
        $this->db->from('orders o');
        $this->db->join('employees e', 'e.id = o.employee_id', 'left');
        $this->db->join('company_departments cd', 'cd.id = o.department_id', 'left');
        $this->db->where('o.company_id', $company_id);
        $this->db->where('o.module', 'PREMEAL');
        $this->db->where_not_in('o.status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('o.scheduled_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('o.scheduled_date <=', $filters['date_to']);
        }
        if (!empty($filters['meal_type'])) {
            $this->db->where('o.meal_type', $filters['meal_type']);
        }
        if (!empty($filters['department_id'])) {
            $this->db->where('o.department_id', $filters['department_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('o.status', $filters['status']);
        }

        $this->db->order_by('o.scheduled_date', 'DESC');
        return $this->db->get()->result();
    }

    public function get_premeal_summary($company_id, $filters = array())
    {
        $this->db->select('COUNT(*) as total_orders, COALESCE(SUM(total_amount),0) as total_amount, COALESCE(SUM(company_contribution),0) as company_contribution, COALESCE(SUM(employee_contribution),0) as employee_contribution');
        $this->db->from('orders');
        $this->db->where('company_id', $company_id);
        $this->db->where('module', 'PREMEAL');
        $this->db->where_not_in('status', $this->excluded_statuses);

        if (!empty($filters['date_from'])) {
            $this->db->where('scheduled_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('scheduled_date <=', $filters['date_to']);
        }
        if (!empty($filters['meal_type'])) {
            $this->db->where('meal_type', $filters['meal_type']);
        }
        if (!empty($filters['department_id'])) {
            $this->db->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }

        return $this->db->get()->row();
    }

    // --- Order Detail ---

    public function get_order_detail($order_id, $company_id)
    {
        $this->db->select('o.*, s.name as store_name, s.store_code, s.store_type, e.first_name, e.last_name, e.employee_code, e.email as employee_email, e.phone as employee_phone, cd.name as department_name, dl.name as location_name, p.name as policy_name');
        $this->db->from('orders o');
        $this->db->join('stores s', 's.id = o.store_id', 'left');
        $this->db->join('employees e', 'e.id = o.employee_id', 'left');
        $this->db->join('company_departments cd', 'cd.id = o.department_id', 'left');
        $this->db->join('delivery_locations dl', 'dl.id = o.delivery_location_id', 'left');
        $this->db->join('policies p', 'p.id = o.policy_id', 'left');
        $this->db->where('o.id', $order_id);
        $this->db->where('o.company_id', $company_id);
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
}
