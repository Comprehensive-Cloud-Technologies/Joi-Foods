<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Products_model', 'products');
    }

    public function index()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Product Management || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['products'] = $this->products->get_all_by_client($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/products/index', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/products');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    public function add()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Add Product || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $header_data['js_tree'] = true;
            $footer_data['js_tree'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['categories'] = $this->products->get_active_categories($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/products/add', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/products_form');
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

                // Check if product name already exists
                $existing = $this->products->check_name_exists($post_data['name'], $client_id);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Product name already exists. Please use a unique name.'
                    );
                    echo json_encode($response);
                    return;
                }

                // Handle thumbnail upload
                $thumbnail_path = null;
                if (!empty($_FILES['thumbnail']['name'])) {
                    $config['upload_path'] = './uploads/products/';
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = TRUE;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload');
                    $this->upload->initialize($config);

                    if ($this->upload->do_upload('thumbnail')) {
                        $upload_data = $this->upload->data();
                        $thumbnail_path = 'uploads/products/' . $upload_data['file_name'];
                    }
                }

                // Handle multiple product images upload
                $images_array = [];
                if (!empty($_FILES['product_images']['name'][0])) {
                    $config['upload_path'] = './uploads/products/';
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = TRUE;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload');
                    $files_count = count($_FILES['product_images']['name']);

                    for ($i = 0; $i < $files_count; $i++) {
                        if (!empty($_FILES['product_images']['name'][$i])) {
                            $_FILES['product_image']['name'] = $_FILES['product_images']['name'][$i];
                            $_FILES['product_image']['type'] = $_FILES['product_images']['type'][$i];
                            $_FILES['product_image']['tmp_name'] = $_FILES['product_images']['tmp_name'][$i];
                            $_FILES['product_image']['error'] = $_FILES['product_images']['error'][$i];
                            $_FILES['product_image']['size'] = $_FILES['product_images']['size'][$i];

                            $this->upload->initialize($config);

                            if ($this->upload->do_upload('product_image')) {
                                $upload_data = $this->upload->data();
                                $images_array[] = 'uploads/products/' . $upload_data['file_name'];
                            }
                        }
                    }
                }

                $data = array(
                    'client_id' => $client_id,
                    'category_id' => !empty($post_data['category_id']) ? $post_data['category_id'] : NULL,
                    'name' => $post_data['name'],
                    'description' => $post_data['description'],
                    'ingredients' => $post_data['ingredients'],
                    'thumbnail' => $thumbnail_path,
                    'images' => !empty($images_array) ? json_encode($images_array) : NULL,
                    'base_price' => $post_data['base_price'] ?: 0,
                    'tax_percentage' => $post_data['tax_percentage'] ?: 0,
                    'calories' => !empty($post_data['calories']) ? $post_data['calories'] : NULL,
                    'is_vegetarian' => isset($post_data['is_vegetarian']) ? 1 : 0,
                    'qsr_enabled' => isset($post_data['qsr_enabled']) ? 1 : 0,
                    'kot_enabled' => isset($post_data['kot_enabled']) ? 1 : 0,
                    'premeal_enabled' => isset($post_data['premeal_enabled']) ? 1 : 0,
                    'breakfast' => (isset($post_data['premeal_enabled']) && isset($post_data['meal_type']) && $post_data['meal_type'] == 'BREAKFAST') ? 1 : 0,
                    'lunch' => (isset($post_data['premeal_enabled']) && isset($post_data['meal_type']) && $post_data['meal_type'] == 'LUNCH') ? 1 : 0,
                    'dinner' => (isset($post_data['premeal_enabled']) && isset($post_data['meal_type']) && $post_data['meal_type'] == 'DINNER') ? 1 : 0,
                    'is_featured' => isset($post_data['is_featured']) ? 1 : 0,
                    'display_order' => $post_data['display_order'] ?: 0,
                    'is_active' => 1,
                    'created_by' => get_client_sessiondata('id'),
                    'created_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->insert($data, 'products');

                if ($result) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Product added successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to add product'
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

            $header_data['title'] = 'Edit Product || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $header_data['js_tree'] = true;
            $footer_data['js_tree'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['product'] = $this->products->get_by_id($id, $client_id);

            if (!$data['product']) {
                show_404();
                return;
            }

            $data['categories'] = $this->products->get_active_categories($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/products/edit', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/products_form');
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

            if (isset($post_data['product_id']) && $post_data['product_id'] !== '' && !empty($post_data['name'])) {

                // Verify product belongs to this client
                $product = $this->products->get_by_id($post_data['product_id'], $client_id);
                if (!$product) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Product not found'
                    );
                    echo json_encode($response);
                    return;
                }

                // Check if product name already exists (excluding current)
                $existing = $this->products->check_name_exists($post_data['name'], $client_id, $post_data['product_id']);

                if ($existing) {
                    $response = array(
                        'status' => 400,
                        'message' => 'Product name already exists. Please use a unique name.'
                    );
                    echo json_encode($response);
                    return;
                }

                // Handle thumbnail upload
                $thumbnail_path = $post_data['existing_thumbnail']; // Keep existing by default
                if (!empty($_FILES['thumbnail']['name'])) {
                    $config['upload_path'] = './uploads/products/';
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = TRUE;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload');
                    $this->upload->initialize($config);

                    if ($this->upload->do_upload('thumbnail')) {
                        $upload_data = $this->upload->data();
                        $thumbnail_path = 'uploads/products/' . $upload_data['file_name'];

                        // Delete old thumbnail file if it exists
                        if (!empty($post_data['existing_thumbnail']) && file_exists('./' . $post_data['existing_thumbnail'])) {
                            unlink('./' . $post_data['existing_thumbnail']);
                        }
                    }
                }

                // Handle images - combine existing (retained) with new uploads
                $existing_images = [];
                if (!empty($post_data['existing_images'])) {
                    $existing_images = json_decode($post_data['existing_images'], true);
                    if (!is_array($existing_images)) {
                        $existing_images = [];
                    }
                }

                // Get old images to delete removed ones
                $old_images = [];
                if (!empty($product->images)) {
                    $old_images = json_decode($product->images, true);
                    if (!is_array($old_images)) {
                        $old_images = [];
                    }
                }

                // Delete images that were removed
                foreach ($old_images as $old_img) {
                    if (!in_array($old_img, $existing_images) && file_exists('./' . $old_img)) {
                        unlink('./' . $old_img);
                    }
                }

                // Handle new product images upload
                $new_images = [];
                if (!empty($_FILES['product_images']['name'][0])) {
                    $config['upload_path'] = './uploads/products/';
                    $config['allowed_types'] = 'jpg|jpeg|png|gif';
                    $config['max_size'] = 2048; // 2MB
                    $config['encrypt_name'] = TRUE;

                    if (!is_dir($config['upload_path'])) {
                        mkdir($config['upload_path'], 0777, true);
                    }

                    $this->load->library('upload');
                    $files_count = count($_FILES['product_images']['name']);

                    for ($i = 0; $i < $files_count; $i++) {
                        if (!empty($_FILES['product_images']['name'][$i])) {
                            $_FILES['product_image']['name'] = $_FILES['product_images']['name'][$i];
                            $_FILES['product_image']['type'] = $_FILES['product_images']['type'][$i];
                            $_FILES['product_image']['tmp_name'] = $_FILES['product_images']['tmp_name'][$i];
                            $_FILES['product_image']['error'] = $_FILES['product_images']['error'][$i];
                            $_FILES['product_image']['size'] = $_FILES['product_images']['size'][$i];

                            $this->upload->initialize($config);

                            if ($this->upload->do_upload('product_image')) {
                                $upload_data = $this->upload->data();
                                $new_images[] = 'uploads/products/' . $upload_data['file_name'];
                            }
                        }
                    }
                }

                // Merge existing and new images
                $final_images = array_merge($existing_images, $new_images);

                $data = array(
                    'category_id' => !empty($post_data['category_id']) ? $post_data['category_id'] : NULL,
                    'name' => $post_data['name'],
                    'description' => $post_data['description'],
                    'ingredients' => $post_data['ingredients'],
                    'thumbnail' => $thumbnail_path,
                    'images' => !empty($final_images) ? json_encode($final_images) : NULL,
                    'base_price' => $post_data['base_price'] ?: 0,
                    'tax_percentage' => $post_data['tax_percentage'] ?: 0,
                    'calories' => !empty($post_data['calories']) ? $post_data['calories'] : NULL,
                    'is_vegetarian' => isset($post_data['is_vegetarian']) ? 1 : 0,
                    'qsr_enabled' => isset($post_data['qsr_enabled']) ? 1 : 0,
                    'kot_enabled' => isset($post_data['kot_enabled']) ? 1 : 0,
                    'premeal_enabled' => isset($post_data['premeal_enabled']) ? 1 : 0,
                    'breakfast' => (isset($post_data['premeal_enabled']) && isset($post_data['meal_type']) && $post_data['meal_type'] == 'BREAKFAST') ? 1 : 0,
                    'lunch' => (isset($post_data['premeal_enabled']) && isset($post_data['meal_type']) && $post_data['meal_type'] == 'LUNCH') ? 1 : 0,
                    'dinner' => (isset($post_data['premeal_enabled']) && isset($post_data['meal_type']) && $post_data['meal_type'] == 'DINNER') ? 1 : 0,
                    'is_featured' => isset($post_data['is_featured']) ? 1 : 0,
                    'display_order' => $post_data['display_order'] ?: 0,
                    'updated_by' => get_client_sessiondata('id'),
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $result = $this->common->update('products', $data, ['id' => $post_data['product_id']]);

                if ($result !== false) {
                    $response = array(
                        'status' => 200,
                        'message' => 'Product updated successfully'
                    );
                } else {
                    $response = array(
                        'status' => 400,
                        'message' => 'Failed to update product'
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

    public function toggle_status()
    {
        if (is_loggedin_client()) {
            $id = $this->input->post('id', true);
            $status = $this->input->post('status', true);
            $client_id = get_client_sessiondata('client_id');

            // Verify product belongs to this client
            $product = $this->products->get_by_id($id, $client_id);
            if (!$product) {
                echo json_encode(['status' => 'error', 'message' => 'Product not found']);
                return;
            }

            $data = array(
                'is_active' => $status ? 1 : 0,
                'updated_by' => get_client_sessiondata('id'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            $result = $this->common->update('products', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Product status updated successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update product status'
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
            $client_id = get_client_sessiondata('client_id');

            // Verify product belongs to this client
            $product = $this->products->get_by_id($id, $client_id);
            if (!$product) {
                echo json_encode(['status' => 'error', 'message' => 'Product not found']);
                return;
            }

            // Soft delete
            $data = array(
                'is_active' => 0,
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_by' => get_client_sessiondata('id')
            );

            $result = $this->common->update('products', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete product']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    // ----------------------------------------------------------------
    // Bulk Import
    // ----------------------------------------------------------------

    /**
     * Bulk import landing page.
     */
    public function bulk_import()
    {
        if (!is_loggedin_client()) {
            redirect('client');
            return;
        }

        $header_data['title'] = 'Bulk Import Products || ' . config_item('application_name');
        $header_data['sweet_alert'] = true;
        $footer_data['sweet_alert'] = true;

        // Clear any stale preview from the session
        $this->session->unset_userdata('product_import_preview');

        $this->load->view('client/common/header', $header_data);
        $this->load->view('client/common/sidebar');
        $this->load->view('client/products/bulk_import');
        $this->load->view('client/common/footer', $footer_data);
        $this->load->view('client/validation/bulk_import');
    }

    /**
     * Stream the import template (xlsx).
     */
    public function download_template()
    {
        if (!is_loggedin_client()) {
            redirect('client');
            return;
        }

        $this->load->library('ProductImporter', null, 'importer');
        $this->importer->generate_template('product_import_template.xlsx');
    }

    /**
     * Parse + validate the uploaded file and store the preview in the session.
     * Returns a JSON summary the UI uses to render the preview screen.
     */
    public function bulk_import_preview()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $user_id   = get_client_sessiondata('id');

        if (empty($_FILES['import_file']['name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Please choose a file to upload']);
            return;
        }

        if ($_FILES['import_file']['size'] > 5 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'File too large. Max 5 MB.']);
            return;
        }

        $upload_dir = './uploads/product_imports/' . $client_id . '/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $config = [
            'upload_path'   => $upload_dir,
            'allowed_types' => 'xlsx|xls|csv',
            'max_size'      => 5120, // 5MB
            'encrypt_name'  => true
        ];

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('import_file')) {
            echo json_encode(['status' => 'error', 'message' => strip_tags($this->upload->display_errors())]);
            return;
        }

        $upload_data = $this->upload->data();
        $stored_path = $upload_dir . $upload_data['file_name'];
        $relative_path = 'uploads/product_imports/' . $client_id . '/' . $upload_data['file_name'];

        $options = [
            'duplicate_strategy'     => $this->input->post('duplicate_strategy', true) === 'UPDATE' ? 'UPDATE' : 'SKIP',
            'auto_create_categories' => $this->input->post('auto_create_categories', true) ? true : false
        ];

        $this->load->library('ProductImporter', null, 'importer');

        try {
            $preview = $this->importer->parse_and_validate($stored_path, $client_id, $options);
        } catch (Exception $e) {
            // Cleanup uploaded file on parse failure
            if (file_exists($stored_path)) {
                @unlink($stored_path);
            }
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            return;
        }

        // Insert a PREVIEW row in product_imports for audit
        $this->db->insert('product_imports', [
            'client_id'              => $client_id,
            'file_name'              => $upload_data['orig_name'],
            'stored_path'            => $relative_path,
            'total_rows'             => $preview['summary']['total_rows'],
            'duplicate_strategy'     => $options['duplicate_strategy'],
            'auto_create_categories' => $options['auto_create_categories'] ? 1 : 0,
            'status'                 => 'PREVIEW',
            'imported_by'            => $user_id,
            'created_at'             => date('Y-m-d H:i:s')
        ]);
        $import_id = $this->db->insert_id();

        // Persist the preview in session so commit() can use it
        $this->session->set_userdata('product_import_preview', [
            'import_id' => $import_id,
            'data'      => $preview
        ]);

        echo json_encode([
            'status'             => 'success',
            'import_id'          => (int)$import_id,
            'file_name'          => $upload_data['orig_name'],
            'summary'            => $preview['summary'],
            'rows'               => $preview['rows'],
            'new_category_names' => $preview['new_category_names'],
            'options'            => $preview['options']
        ]);
    }

    /**
     * Commit the previewed import. Reads preview from session, applies it.
     */
    public function bulk_import_commit()
    {
        if (!is_loggedin_client()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $client_id = get_client_sessiondata('client_id');
        $user_id   = get_client_sessiondata('id');

        $session_preview = $this->session->userdata('product_import_preview');
        if (empty($session_preview) || empty($session_preview['data'])) {
            echo json_encode(['status' => 'error', 'message' => 'No preview found. Please upload the file again.']);
            return;
        }

        $import_id = (int)$session_preview['import_id'];
        $preview   = $session_preview['data'];

        $this->load->library('ProductImporter', null, 'importer');

        try {
            $result = $this->importer->commit($preview, $client_id, $user_id);
        } catch (Exception $e) {
            // Update audit row to FAILED
            $this->db->update('product_imports', [
                'status'        => 'FAILED',
                'error_summary' => json_encode(['error' => $e->getMessage()])
            ], ['id' => $import_id]);

            $this->session->unset_userdata('product_import_preview');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            return;
        }

        // Collect error rows for audit
        $error_rows = [];
        foreach ($preview['rows'] as $r) {
            if ($r['status'] === 'ERROR') {
                $error_rows[] = [
                    'row'    => $r['row_number'],
                    'errors' => $r['errors']
                ];
            }
        }

        $this->db->update('product_imports', [
            'success_count'          => $result['success_count'],
            'skip_count'             => $result['skip_count'],
            'fail_count'             => $result['fail_count'],
            'new_categories_created' => $result['created_categories'],
            'status'                 => 'COMMITTED',
            'error_summary'          => !empty($error_rows) ? json_encode($error_rows) : null
        ], ['id' => $import_id]);

        $this->session->unset_userdata('product_import_preview');

        echo json_encode([
            'status'             => 'success',
            'import_id'          => $import_id,
            'success_count'      => $result['success_count'],
            'skip_count'         => $result['skip_count'],
            'fail_count'         => $result['fail_count'],
            'created_categories' => $result['created_categories']
        ]);
    }

    /**
     * Past import history page.
     */
    public function bulk_import_history()
    {
        if (!is_loggedin_client()) {
            redirect('client');
            return;
        }

        $client_id = get_client_sessiondata('client_id');

        $header_data['title'] = 'Import History || ' . config_item('application_name');
        $header_data['datatable'] = true;
        $footer_data['datatable'] = true;

        $imports = $this->db
            ->select('pi.*, cu.first_name, cu.last_name')
            ->from('product_imports pi')
            ->join('client_users cu', 'cu.id = pi.imported_by', 'left')
            ->where('pi.client_id', $client_id)
            ->order_by('pi.created_at', 'DESC')
            ->limit(200)
            ->get()
            ->result();

        $data['imports'] = $imports;

        $this->load->view('client/common/header', $header_data);
        $this->load->view('client/common/sidebar');
        $this->load->view('client/products/bulk_import_history', $data);
        $this->load->view('client/common/footer', $footer_data);
    }
}
