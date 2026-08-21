<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Accountdeletionrequests extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('AccountDeletionRequests_model', 'deletion_requests');
        $this->load->model('Companies_model', 'companies');
        $this->load->model('Common_model', 'common');
    }

    /**
     * List all account deletion requests with filters
     */
    public function index()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Account Deletion Requests';
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

            // Default date range - current month
            $data['default_date_from'] = date('Y-m-01');
            $data['default_date_to'] = date('Y-m-d');

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/account_deletion_requests/index', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/account_deletion_requests');
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
                'status' => $this->input->post('status', true),
                'date_from' => $this->input->post('date_from', true),
                'date_to' => $this->input->post('date_to', true)
            ];

            $requests = $this->deletion_requests->get_all_by_client($client_id, $filters);

            $status_badges = [
                'PENDING' => 'bg-soft-warning text-warning',
                'PROCESSED' => 'bg-soft-success text-success',
                'REJECTED' => 'bg-soft-danger text-danger'
            ];

            $data = [];
            foreach ($requests as $request) {
                $employee_name = trim($request->first_name . ' ' . $request->last_name);
                if ($employee_name === '') {
                    $employee_name = '<span class="text-muted">No match</span>';
                } else {
                    $employee_name = htmlspecialchars($employee_name);
                }

                $badge_class = isset($status_badges[$request->status]) ? $status_badges[$request->status] : 'bg-soft-secondary text-secondary';

                $data[] = [
                    $request->id,
                    date('d M Y', strtotime($request->created_at)),
                    date('h:i A', strtotime($request->created_at)),
                    '<span class="badge bg-soft-primary text-primary">' . htmlspecialchars($request->company_name ?: $request->company_code) . '</span>',
                    htmlspecialchars($request->email),
                    $employee_name,
                    '<span class="badge ' . $badge_class . '">' . htmlspecialchars($request->status) . '</span>',
                    '<button type="button" class="btn btn-soft-primary btn-sm" onclick="viewRequest(' . $request->id . ')" data-bs-toggle="tooltip" title="View Details"><i class="uil uil-eye"></i></button>'
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
     * Get request details for modal
     */
    public function get_by_id()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');
            $id = $this->input->post('id', true);

            $request = $this->deletion_requests->get_by_id($id, $client_id);

            if ($request) {
                $employee_name = trim($request->first_name . ' ' . $request->last_name);

                echo json_encode([
                    'status' => 200,
                    'data' => [
                        'id' => $request->id,
                        'company_name' => $request->company_name,
                        'company_code' => $request->company_code,
                        'email' => $request->email,
                        'employee_name' => $employee_name !== '' ? $employee_name : null,
                        'employee_code' => $request->employee_code,
                        'employee_email' => $request->employee_email,
                        'employee_phone' => $request->employee_phone,
                        'status' => $request->status,
                        'note' => $request->note,
                        'ip_address' => $request->ip_address,
                        'created_at' => date('d M Y, h:i A', strtotime($request->created_at)),
                        'processed_at' => $request->processed_at ? date('d M Y, h:i A', strtotime($request->processed_at)) : null
                    ]
                ]);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Request not found']);
            }
        } else {
            echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
        }
    }

    /**
     * Update the status of a deletion request (mark processed / rejected)
     */
    public function update_status()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');
            $id = $this->input->post('id', true);
            $status = $this->input->post('status', true);
            $note = $this->input->post('note', true);

            if (!in_array($status, ['PROCESSED', 'REJECTED'], true)) {
                echo json_encode(['status' => 400, 'message' => 'Invalid status']);
                return;
            }

            // Ensure the request belongs to this client before updating
            $request = $this->deletion_requests->get_by_id($id, $client_id);
            if (!$request) {
                echo json_encode(['status' => 400, 'message' => 'Request not found']);
                return;
            }

            $this->deletion_requests->update_status($id, $client_id, [
                'status' => $status,
                'note' => $note,
                'processed_by' => get_client_sessiondata('id'),
                'processed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            echo json_encode([
                'status' => 200,
                'message' => 'Request updated successfully'
            ]);
        } else {
            echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
        }
    }
}
