<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Supportinquiries extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('SupportInquiries_model', 'support_inquiries');
        $this->load->model('Companies_model', 'companies');
        $this->load->model('Common_model', 'common');
    }

    /**
     * List all support inquiries with filters
     */
    public function index()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Support Inquiries';
            $header_data['datatable'] = true;
            $header_data['sweet_alert'] = true;
            $header_data['select_2'] = true;
            $header_data['datepicker'] = true;

            $footer_data['datatable'] = true;
            $footer_data['sweet_alert'] = true;
            $footer_data['select_2'] = true;
            $footer_data['datepicker'] = true;

            // Get companies for filter dropdown
            $data['companies'] = $this->companies->get_all_by_client($client_id);

            // Get topics for filter dropdown
            $data['topics'] = $this->support_inquiries->get_topics_by_client($client_id);

            // Default date range - current month
            $data['default_date_from'] = date('Y-m-01');
            $data['default_date_to'] = date('Y-m-d');

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/support_inquiries/index', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/support_inquiries');
        } else {
            redirect(base_url('client'));
        }
    }

    /**
     * Get filtered data via AJAX
     */
    public function get_data()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $filters = [
                'company_id' => $this->input->post('company_id', true),
                'topic' => $this->input->post('topic', true),
                'date_from' => $this->input->post('date_from', true),
                'date_to' => $this->input->post('date_to', true)
            ];

            $inquiries = $this->support_inquiries->get_all_by_client($client_id, $filters);

            $data = [];
            foreach ($inquiries as $inquiry) {
                $employee_name = trim($inquiry->first_name . ' ' . $inquiry->last_name);

                $data[] = [
                    $inquiry->id,
                    date('d M Y', strtotime($inquiry->created_at)),
                    date('h:i A', strtotime($inquiry->created_at)),
                    '<span class="badge bg-soft-primary text-primary">' . htmlspecialchars($inquiry->company_name ?: 'N/A') . '</span>',
                    htmlspecialchars($employee_name),
                    '<span class="badge bg-soft-info text-info">' . htmlspecialchars($inquiry->topic) . '</span>',
                    htmlspecialchars($inquiry->subject),
                    '<button type="button" class="btn btn-soft-primary btn-sm" onclick="viewInquiry(' . $inquiry->id . ')" data-bs-toggle="tooltip" title="View Details"><i class="uil uil-eye"></i></button>'
                ];
            }

            echo json_encode([
                'status' => 200,
                'data' => $data
            ]);
        } else {
            echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
        }
    }

    /**
     * Get inquiry details for modal
     */
    public function get_by_id()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');
            $id = $this->input->post('id', true);

            $inquiry = $this->support_inquiries->get_by_id($id, $client_id);

            if ($inquiry) {
                echo json_encode([
                    'status' => 200,
                    'data' => [
                        'id' => $inquiry->id,
                        'topic' => $inquiry->topic,
                        'subject' => $inquiry->subject,
                        'message' => $inquiry->message,
                        'company_name' => $inquiry->company_name,
                        'employee_name' => trim($inquiry->first_name . ' ' . $inquiry->last_name),
                        'employee_code' => $inquiry->employee_code,
                        'employee_email' => $inquiry->employee_email,
                        'employee_phone' => $inquiry->employee_phone,
                        'created_at' => date('d M Y, h:i A', strtotime($inquiry->created_at))
                    ]
                ]);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Inquiry not found']);
            }
        } else {
            echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
        }
    }
}
