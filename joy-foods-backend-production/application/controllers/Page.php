<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Page extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('AccountDeletionRequests_model', 'deletion_requests');
        $this->load->library('monolog');
        $this->logger = new Monolog();
    }

    /**
     * Public account deletion request form
     *
     * GET /page/delete_account
     */
    public function delete_account()
    {
        $this->load->view('page/delete_account');
    }

    /**
     * Handle account deletion request submission
     *
     * POST /page/submit_delete_account
     * Params: company_code, email
     */
    public function submit_delete_account()
    {
        $company_code = trim((string) $this->input->post('company_code', true));
        $email = trim((string) $this->input->post('email', true));

        // Validate inputs
        if ($company_code === '' || $email === '') {
            $this->json_response([
                'status' => 400,
                'success' => false,
                'message' => 'Company code and email are required.'
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json_response([
                'status' => 400,
                'success' => false,
                'message' => 'Please enter a valid email address.'
            ]);
            return;
        }

        // Resolve company (and its client) from the company code
        $company = $this->deletion_requests->get_company_by_code($company_code);

        if (empty($company)) {
            $this->json_response([
                'status' => 404,
                'success' => false,
                'message' => 'No company found for this company code. Please check and try again.'
            ]);
            return;
        }

        // Resolve employee by email within the company (optional match)
        $employee = $this->deletion_requests->get_employee_by_email($company->id, $email);

        $request_data = [
            'client_id' => $company->client_id,
            'company_id' => $company->id,
            'employee_id' => $employee ? $employee->id : null,
            'company_code' => $company_code,
            'email' => $email,
            'status' => 'PENDING',
            'ip_address' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $request_id = $this->deletion_requests->insert_request($request_data);

        if (!$request_id) {
            $this->logger->error('Failed to store account deletion request', [
                'company_code' => $company_code,
                'email' => $email
            ], 'page');

            $this->json_response([
                'status' => 500,
                'success' => false,
                'message' => 'Could not submit your request. Please try again later.'
            ]);
            return;
        }

        $this->logger->info('Account deletion request submitted', [
            'request_id' => $request_id,
            'client_id' => $company->client_id,
            'company_id' => $company->id,
            'employee_matched' => $employee ? 1 : 0
        ], 'page');

        $this->json_response([
            'status' => 200,
            'success' => true,
            'message' => 'Your account deletion request has been submitted successfully.',
            'data' => [
                'request_id' => (int) $request_id
            ]
        ]);
    }

    /**
     * Emit a JSON response (web controller, no API base class)
     */
    private function json_response($payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }
}
