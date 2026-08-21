<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Banners extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Banners_model', 'banners');
        $this->load->model('Categories_model', 'categories');
        $this->load->model('Products_model', 'products');
        $this->load->model('Companies_model', 'companies');
        $this->load->model('Common_model', 'common');
    }

    /**
     * List all banners
     */
    public function index()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            $header_data['title'] = 'Banners';
            $header_data['datatable'] = true;
            $header_data['sweet_alert'] = true;
            $header_data['select_2'] = true;
            $header_data['form_validation'] = true;

            $footer_data['datatable'] = true;
            $footer_data['sweet_alert'] = true;
            $footer_data['select_2'] = true;
            $footer_data['form_validation'] = true;

            $data['banners'] = $this->banners->get_all_by_client($client_id);
            $data['companies'] = $this->companies->get_all_by_client($client_id);
            $data['categories'] = $this->categories->get_all_by_client($client_id);
            $data['products'] = $this->products->get_all_by_client($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/banners/index', $data);
            $this->load->view('client/common/footer', $footer_data);
            $this->load->view('client/validation/banners');
        } else {
            redirect(base_url('client'));
        }
    }

    /**
     * Store new banner
     */
    public function store()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');

            // Validate company_id
            $company_id = $this->input->post('company_id', true);
            if (empty($company_id)) {
                echo json_encode(['status' => 400, 'message' => 'Company is required']);
                return;
            }

            // Verify company belongs to this client
            $company = $this->companies->get_by_id($company_id, $client_id);
            if (!$company) {
                echo json_encode(['status' => 400, 'message' => 'Invalid company']);
                return;
            }

            // Handle image upload
            $image_path = '';
            if (!empty($_FILES['image']['name'])) {
                $upload_result = $this->upload_image();
                if ($upload_result['status'] == 'error') {
                    echo json_encode(['status' => 400, 'message' => $upload_result['message']]);
                    return;
                }
                $image_path = $upload_result['path'];
            } else {
                echo json_encode(['status' => 400, 'message' => 'Banner image is required']);
                return;
            }

            // Prepare action payload
            $action_type = $this->input->post('action_type', true);
            $action_payload = null;

            if ($action_type == 'PRODUCT') {
                $action_payload = $this->input->post('product_id', true);
            } elseif ($action_type == 'CATEGORY') {
                $action_payload = $this->input->post('category_id', true);
            } elseif ($action_type == 'URL') {
                $action_payload = $this->input->post('url', true);
            }

            $data = [
                'client_id' => $client_id,
                'company_id' => $company_id,
                'title' => $this->input->post('title', true),
                'description' => $this->input->post('description', true),
                'image_path' => $image_path,
                'action_type' => $action_type,
                'action_payload' => $action_payload,
                'display_order' => $this->input->post('display_order', true) ?: 0,
                'is_active' => 1,
                'created_by' => get_client_sessiondata('id'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->common->insert($data, 'banners');

            if ($result) {
                echo json_encode(['status' => 200, 'message' => 'Banner added successfully']);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Failed to add banner']);
            }
        } else {
            echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
        }
    }

    /**
     * Get banner by ID for editing
     */
    public function get_by_id()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');
            $id = $this->input->post('id', true);

            $banner = $this->banners->get_by_id($id, $client_id);

            if ($banner) {
                echo json_encode(['status' => 200, 'data' => $banner]);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Banner not found']);
            }
        } else {
            echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
        }
    }

    /**
     * Update banner
     */
    public function update()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');
            $banner_id = $this->input->post('banner_id', true);

            // Validate company_id
            $company_id = $this->input->post('company_id', true);
            if (empty($company_id)) {
                echo json_encode(['status' => 400, 'message' => 'Company is required']);
                return;
            }

            // Verify company belongs to this client
            $company = $this->companies->get_by_id($company_id, $client_id);
            if (!$company) {
                echo json_encode(['status' => 400, 'message' => 'Invalid company']);
                return;
            }

            // Check if banner exists
            $banner = $this->banners->get_by_id($banner_id, $client_id);
            if (!$banner) {
                echo json_encode(['status' => 400, 'message' => 'Banner not found']);
                return;
            }

            // Handle image upload
            $image_path = $this->input->post('existing_image', true);
            if (!empty($_FILES['image']['name'])) {
                $upload_result = $this->upload_image();
                if ($upload_result['status'] == 'error') {
                    echo json_encode(['status' => 400, 'message' => $upload_result['message']]);
                    return;
                }
                $image_path = $upload_result['path'];

                // Delete old image if exists
                if (!empty($banner->image_path) && file_exists(FCPATH . $banner->image_path)) {
                    unlink(FCPATH . $banner->image_path);
                }
            }

            // Prepare action payload
            $action_type = $this->input->post('action_type', true);
            $action_payload = null;

            if ($action_type == 'PRODUCT') {
                $action_payload = $this->input->post('product_id', true);
            } elseif ($action_type == 'CATEGORY') {
                $action_payload = $this->input->post('category_id', true);
            } elseif ($action_type == 'URL') {
                $action_payload = $this->input->post('url', true);
            }

            $data = [
                'company_id' => $company_id,
                'title' => $this->input->post('title', true),
                'description' => $this->input->post('description', true),
                'image_path' => $image_path,
                'action_type' => $action_type,
                'action_payload' => $action_payload,
                'display_order' => $this->input->post('display_order', true) ?: 0,
                'updated_by' => get_client_sessiondata('id'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->common->update('banners', $data, ['id' => $banner_id, 'client_id' => $client_id]);

            if ($result !== false) {
                echo json_encode(['status' => 200, 'message' => 'Banner updated successfully']);
            } else {
                echo json_encode(['status' => 400, 'message' => 'Failed to update banner']);
            }
        } else {
            echo json_encode(['status' => 401, 'message' => 'Unauthorized']);
        }
    }

    /**
     * Toggle banner status
     */
    public function toggle_status()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');
            $id = $this->input->post('id', true);
            $status = $this->input->post('status', true);

            $data = [
                'is_active' => $status,
                'updated_by' => get_client_sessiondata('id'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->common->update('banners', $data, ['id' => $id, 'client_id' => $client_id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    /**
     * Delete banner
     */
    public function delete()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');
            $id = $this->input->post('id', true);

            // Soft delete
            $data = [
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_by' => get_client_sessiondata('id')
            ];

            $result = $this->common->update('banners', $data, ['id' => $id, 'client_id' => $client_id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Banner deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete banner']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    /**
     * Upload banner image
     */
    private function upload_image()
    {
        $config['upload_path'] = './uploads/banners/';
        $config['allowed_types'] = 'gif|jpg|jpeg|png|webp';
        $config['max_size'] = 2048; // 2MB
        $config['encrypt_name'] = TRUE;

        // Create directory if not exists
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, TRUE);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('image')) {
            return ['status' => 'error', 'message' => $this->upload->display_errors('', '')];
        } else {
            $upload_data = $this->upload->data();
            return ['status' => 'success', 'path' => 'uploads/banners/' . $upload_data['file_name']];
        }
    }
}
