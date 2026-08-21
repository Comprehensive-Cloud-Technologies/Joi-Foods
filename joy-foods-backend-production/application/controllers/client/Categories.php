<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Categories extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Categories_model', 'categories');
    }

    public function index()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Category Management || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['categories'] = $this->categories->get_all_by_client($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/categories/index', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/categories');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function add()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Add Category || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['parent_categories'] = $this->categories->get_top_level_categories($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/categories/add', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/categories_form');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function store()
    {
        if (is_loggedin_client()) {
            $post_data = $this->input->post(null, true);
            $client_id = get_client_sessiondata('client_id');

            if (!empty($post_data['name'])) {

                // Check if category name already exists
                $existing = $this->categories->check_name_exists($post_data['name'], $client_id);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Category name already exists. Please use a unique name.'
                    );
                    echo json_encode($response);
                    return;
                }

                // Handle icon upload
                $icon_path = null;
                if (!empty($_FILES['icon']['name'])) {
                    $config['upload_path'] = './uploads/categories/';
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = TRUE;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('icon')) {
                        $upload_data = $this->upload->data();
                        $icon_path = 'uploads/categories/' . $upload_data['file_name'];
                    }
                }

                // Handle thumbnail upload
                $thumbnail_path = null;
                if (!empty($_FILES['thumbnail']['name'])) {
                    $config['upload_path'] = './uploads/categories/';
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = TRUE;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);

                    if ($this->upload->do_upload('thumbnail')) {
                        $upload_data = $this->upload->data();
                        $thumbnail_path = 'uploads/categories/' . $upload_data['file_name'];
                    }
                }

                $data = array(
                    'client_id' => $client_id,
                    'parent_id' => NULL,
                    'name' => $post_data['name'],
                    'description' => $post_data['description'],
                    'icon' => $icon_path,
                    'thumbnail' => $thumbnail_path,
                    'qsr_enabled' => isset($post_data['qsr_enabled']) ? 1 : 0,
                    'kot_enabled' => isset($post_data['kot_enabled']) ? 1 : 0,
                    'premeal_enabled' => isset($post_data['premeal_enabled']) ? 1 : 0,
                    'is_primary' => isset($post_data['is_primary']) ? 1 : 0,
                    'display_order' => $post_data['display_order'] ?: 0,
                    'is_active' => isset($post_data['is_active']) ? $post_data['is_active'] : 1,
                    'created_by' => get_client_sessiondata('id'),
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'categories');

                if ($result) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Category added successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to add category'
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
                'message' => 'Please login again'
            );
        }

        echo json_encode($response);
    }

    public function edit($id)
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Edit Category || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['category'] = $this->categories->get_by_id($id, $client_id);

            if (!$data['category']) {
                show_404();
                return;
            }

            // Get parent categories excluding current category
            $data['parent_categories'] = $this->categories->get_top_level_categories($client_id, $id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/categories/edit', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/categories_form');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function update()
    {
        if (is_loggedin_client()) {
            $post_data = $this->input->post(null, true);
            $client_id = get_client_sessiondata('client_id');

            if (isset($post_data['category_id']) && $post_data['category_id'] !== '' && !empty($post_data['name'])) {

                // Verify category belongs to this client
                $category = $this->categories->get_by_id($post_data['category_id'], $client_id);
                if (!$category) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Category not found'
                    );
                    echo json_encode($response);
                    return;
                }

                // Check if category name already exists (excluding current)
                $existing = $this->categories->check_name_exists($post_data['name'], $client_id, $post_data['category_id']);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Category name already exists. Please use a unique name.'
                    );
                    echo json_encode($response);
                    return;
                }

                // Handle icon upload
                $icon_path = $post_data['existing_icon']; // Keep existing by default
                if (!empty($_FILES['icon']['name'])) {
                    $config['upload_path'] = './uploads/categories/';
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = TRUE;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('icon')) {
                        $upload_data = $this->upload->data();
                        $icon_path = 'uploads/categories/' . $upload_data['file_name'];

                        // Delete old icon file if it exists
                        if (!empty($post_data['existing_icon']) && file_exists('./' . $post_data['existing_icon'])) {
                            unlink('./' . $post_data['existing_icon']);
                        }
                    }
                }

                // Handle thumbnail upload
                $thumbnail_path = $post_data['existing_thumbnail']; // Keep existing by default
                if (!empty($_FILES['thumbnail']['name'])) {
                    $config['upload_path'] = './uploads/categories/';
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = TRUE;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);

                    if ($this->upload->do_upload('thumbnail')) {
                        $upload_data = $this->upload->data();
                        $thumbnail_path = 'uploads/categories/' . $upload_data['file_name'];

                        // Delete old thumbnail file if it exists
                        if (!empty($post_data['existing_thumbnail']) && file_exists('./' . $post_data['existing_thumbnail'])) {
                            unlink('./' . $post_data['existing_thumbnail']);
                        }
                    }
                }

                $data = array(
                    'parent_id' => NULL,
                    'name' => $post_data['name'],
                    'description' => $post_data['description'],
                    'icon' => $icon_path,
                    'thumbnail' => $thumbnail_path,
                    'qsr_enabled' => isset($post_data['qsr_enabled']) ? 1 : 0,
                    'kot_enabled' => isset($post_data['kot_enabled']) ? 1 : 0,
                    'premeal_enabled' => isset($post_data['premeal_enabled']) ? 1 : 0,
                    'is_primary' => isset($post_data['is_primary']) ? 1 : 0,
                    'display_order' => $post_data['display_order'] ?: 0,
                    'updated_by' => get_client_sessiondata('id'),
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->update('categories', $data, ['id' => $post_data['category_id']]);

                if ($result !== false) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Category updated successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to update category'
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
                'message' => 'Please login again'
            );
        }

        echo json_encode($response);
    }

    public function check_usage()
    {
        if (is_loggedin_client()) {
            $id = $this->input->post('id', true);
            $client_id = get_client_sessiondata('client_id');

            // Verify category belongs to this client
            $category = $this->categories->get_by_id($id, $client_id);
            if (!$category) {
                echo json_encode(['status' => 'error', 'message' => 'Category not found']);
                return;
            }

            $usage = $this->categories->get_usage_summary($id);

            echo json_encode([
                'status' => 'success',
                'usage' => $usage
            ]);
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

            // Verify category belongs to this client
            $category = $this->categories->get_by_id($id, $client_id);
            if (!$category) {
                echo json_encode(['status' => 'error', 'message' => 'Category not found']);
                return;
            }

            $data = array(
                'is_active' => $status ? 1 : 0,
                'updated_by' => get_client_sessiondata('id'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            $result = $this->common->update('categories', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Category status updated successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update category status'
                ]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    public function delete()
    {
        if (is_loggedin_client()) {

            $id = $this->input->post('id', true);
            $force_delete = $this->input->post('force_delete', true);
            $client_id = get_client_sessiondata('client_id');

            // Verify category belongs to this client
            $category = $this->categories->get_by_id($id, $client_id);
            if (!$category) {
                echo json_encode(['status' => 'error', 'message' => 'Category not found']);
                return;
            }

            // Check if category has related data
            $usage = $this->categories->get_usage_summary($id);
            $total_usage = $usage['products'] + $usage['subcategories'];

            if ($total_usage > 0 && !$force_delete) {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'This category has related data',
                    'usage' => $usage,
                    'requires_confirmation' => true
                ]);
                return;
            }

            // If force delete, remove all related data first
            if ($force_delete) {
                $this->categories->delete_subcategories($id);
            }

            // Soft delete
            $data = array(
                'is_active' => 0,
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_by' => get_client_sessiondata('id')
            );

            $result = $this->common->update('categories', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete category']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }
}
