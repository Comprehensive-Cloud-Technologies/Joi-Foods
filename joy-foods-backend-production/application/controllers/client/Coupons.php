<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Coupons extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Coupons_model', 'coupons');
    }

    public function index()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Coupon Management || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['coupons'] = $this->coupons->get_all_by_client($client_id);
            $data['stats'] = $this->coupons->get_stats($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/coupons/index', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/coupons');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function add()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Add Coupon || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $header_data['datepicker'] = true;
            $footer_data['datepicker'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['companies'] = $this->coupons->get_companies_dropdown($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/coupons/add', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/coupons_form');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function store()
    {
        if (is_loggedin_client()) {
            $post_data = html_escape($this->input->post());
            $client_id = get_client_sessiondata('client_id');

            if (!empty($post_data['code']) && !empty($post_data['name']) && !empty($post_data['discount_type']) && !empty($post_data['discount_value'])) {

                // Check if coupon code already exists
                $existing = $this->coupons->check_code_exists($client_id, $post_data['code']);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Coupon code already exists. Please use a unique code.'
                    );
                    echo json_encode($response);
                    return;
                }

                // Prepare data
                $data = array(
                    'client_id' => $client_id,
                    'company_id' => !empty($post_data['company_id']) ? $post_data['company_id'] : null,
                    'code' => strtoupper($post_data['code']),
                    'name' => $post_data['name'],
                    'description' => !empty($post_data['description']) ? $post_data['description'] : null,
                    'discount_type' => $post_data['discount_type'],
                    'discount_value' => $post_data['discount_value'],
                    'max_discount_amount' => !empty($post_data['max_discount_amount']) ? $post_data['max_discount_amount'] : null,
                    'min_order_amount' => !empty($post_data['min_order_amount']) ? $post_data['min_order_amount'] : 0,
                    'usage_limit' => !empty($post_data['usage_limit']) ? $post_data['usage_limit'] : null,
                    'per_user_limit' => !empty($post_data['per_user_limit']) ? $post_data['per_user_limit'] : 1,
                    'applies_to_qsr' => isset($post_data['applies_to_qsr']) ? 1 : 0,
                    'applies_to_kot' => isset($post_data['applies_to_kot']) ? 1 : 0,
                    'applies_to_premeal' => isset($post_data['applies_to_premeal']) ? 1 : 0,
                    'valid_from' => $post_data['valid_from'],
                    'valid_until' => !empty($post_data['valid_until']) ? $post_data['valid_until'] : null,
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'created_by' => get_client_sessiondata('id'),
                    'created_at' => date('Y-m-d H:i:s')
                );

                $insert = $this->coupons->create($data);

                if ($insert) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Coupon created successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Something went wrong. Please try again.'
                    );
                }
            } else {
                $response = array(
                    'status' => 400,
                    'message' => 'Required fields are missing'
                );
            }
        } else {
            $response = array(
                'status' => 400,
                'message' => 'Please login'
            );
        }
        echo json_encode($response);
    }

    public function edit($id = null)
    {
        if (is_loggedin_client()) {

            if (empty($id)) {
                $this->session->set_flashdata('error', 'Coupon not found');
                redirect('client/coupons');
            }

            $header_data['title'] = 'Edit Coupon || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $header_data['datepicker'] = true;
            $footer_data['datepicker'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['coupon'] = $this->coupons->get_by_id($id, $client_id);

            if (!$data['coupon']) {
                $this->session->set_flashdata('error', 'Coupon not found');
                redirect('client/coupons');
            }

            $data['companies'] = $this->coupons->get_companies_dropdown($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/coupons/edit', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/coupons_form');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function update()
    {
        if (is_loggedin_client()) {
            $post_data = html_escape($this->input->post());
            $client_id = get_client_sessiondata('client_id');

            if (!empty($post_data['coupon_id']) && !empty($post_data['code']) && !empty($post_data['name']) && !empty($post_data['discount_type']) && !empty($post_data['discount_value'])) {

                // Verify coupon belongs to client
                $coupon = $this->coupons->get_by_id($post_data['coupon_id'], $client_id);
                if (!$coupon) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Coupon not found'
                    );
                    echo json_encode($response);
                    return;
                }

                // Check if coupon code already exists (excluding current)
                $existing = $this->coupons->check_code_exists($client_id, $post_data['code'], $post_data['coupon_id']);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Coupon code already exists. Please use a unique code.'
                    );
                    echo json_encode($response);
                    return;
                }

                // Prepare data
                $data = array(
                    'company_id' => !empty($post_data['company_id']) ? $post_data['company_id'] : null,
                    'code' => strtoupper($post_data['code']),
                    'name' => $post_data['name'],
                    'description' => !empty($post_data['description']) ? $post_data['description'] : null,
                    'discount_type' => $post_data['discount_type'],
                    'discount_value' => $post_data['discount_value'],
                    'max_discount_amount' => !empty($post_data['max_discount_amount']) ? $post_data['max_discount_amount'] : null,
                    'min_order_amount' => !empty($post_data['min_order_amount']) ? $post_data['min_order_amount'] : 0,
                    'usage_limit' => !empty($post_data['usage_limit']) ? $post_data['usage_limit'] : null,
                    'per_user_limit' => !empty($post_data['per_user_limit']) ? $post_data['per_user_limit'] : 1,
                    'applies_to_qsr' => isset($post_data['applies_to_qsr']) ? 1 : 0,
                    'applies_to_kot' => isset($post_data['applies_to_kot']) ? 1 : 0,
                    'applies_to_premeal' => isset($post_data['applies_to_premeal']) ? 1 : 0,
                    'valid_from' => $post_data['valid_from'],
                    'valid_until' => !empty($post_data['valid_until']) ? $post_data['valid_until'] : null,
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'updated_by' => get_client_sessiondata('id'),
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $update = $this->coupons->update($post_data['coupon_id'], $data);

                if ($update) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Coupon updated successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Something went wrong. Please try again.'
                    );
                }
            } else {
                $response = array(
                    'status' => 400,
                    'message' => 'Required fields are missing'
                );
            }
        } else {
            $response = array(
                'status' => 400,
                'message' => 'Please login'
            );
        }
        echo json_encode($response);
    }

    public function delete()
    {
        if (is_loggedin_client()) {
            $id = $this->input->post('id', true);
            $client_id = get_client_sessiondata('client_id');

            // Verify coupon belongs to client
            $coupon = $this->coupons->get_by_id($id, $client_id);
            if (!$coupon) {
                echo json_encode(['status' => 'error', 'message' => 'Coupon not found']);
                return;
            }

            $delete = $this->coupons->delete($id);

            if ($delete) {
                echo json_encode(['status' => 'success', 'message' => 'Coupon deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete coupon']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    public function toggle_status()
    {
        if (is_loggedin_client()) {
            $id = $this->input->post('id', true);
            $status = $this->input->post('status', true);
            $client_id = get_client_sessiondata('client_id');

            // Verify coupon belongs to client
            $coupon = $this->coupons->get_by_id($id, $client_id);
            if (!$coupon) {
                echo json_encode(['status' => 'error', 'message' => 'Coupon not found']);
                return;
            }

            $update = $this->coupons->update($id, [
                'is_active' => $status,
                'updated_by' => get_client_sessiondata('id'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($update) {
                $msg = $status == 1 ? 'Coupon activated' : 'Coupon deactivated';
                echo json_encode(['status' => 'success', 'message' => $msg]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }
}
