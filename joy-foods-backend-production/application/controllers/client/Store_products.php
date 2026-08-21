<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Store_products extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Store_products_model', 'store_products');
    }

    /**
     * List store products with filter
     */
    public function index()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Store Items || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['datatable'] = true;
            $footer_data['datatable'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['stores'] = $this->store_products->get_stores_dropdown($client_id);
            $data['categories'] = $this->store_products->get_categories_dropdown($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/store_products/index', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/store_products');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    /**
     * Add items to store page
     */
    public function add()
    {
        if (is_loggedin_client()) {

            $header_data['title'] = 'Add Items to Store || ' . config_item('application_name');

            $header_data['form_validation'] = true;
            $footer_data['form_validation'] = true;

            $header_data['sweet_alert'] = true;
            $footer_data['sweet_alert'] = true;

            $header_data['select_2'] = true;
            $footer_data['select_2'] = true;

            $header_data['typeahead'] = true;
            $footer_data['typeahead'] = true;

            $client_id = get_client_sessiondata('client_id');
            $data['stores'] = $this->store_products->get_stores_dropdown($client_id);
            $data['categories'] = $this->store_products->get_categories_dropdown($client_id);

            $this->load->view('client/common/header', $header_data);
            $this->load->view('client/common/sidebar');
            $this->load->view('client/store_products/add', $data);
            $this->load->view('client/common/footer', $footer_data);

            $this->load->view('client/validation/store_products_form');
        } else {
            $data['title'] = 'Login || ' . config_item('application_name');
            $this->load->view('client/auth/login', $data);
        }
    }

    /**
     * AJAX: Get store products by filter
     */
    public function get_store_products()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');
            $store_id = $this->input->post('store_id', true);
            $category_id = $this->input->post('category_id', true);

            $store_products = $this->store_products->get_store_products_by_filter($client_id, $store_id, $category_id);

            if (!empty($store_products)) {
                $response = array(
                    'status' => 200,
                    'data' => $store_products,
                    'message' => 'Store products retrieved successfully'
                );
            } else {
                $response = array(
                    'status' => 200,
                    'data' => [],
                    'message' => 'No store products found'
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

    /**
     * AJAX: Get products for autocomplete search
     */
    public function get_products_autocomplete()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');
            $query = $this->input->post('query', true);

            $products = $this->store_products->get_products_autocomplete($client_id, $query);

            echo json_encode($products);
        } else {
            echo json_encode([]);
        }
    }

    /**
     * AJAX: Get product by ID for adding to table
     */
    public function get_product_by_id()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');
            $product_id = $this->input->post('pid', true);
            $store_id = $this->input->post('store_id', true);

            if ($store_id === null || $store_id === '' || $store_id === false) {
                $response = array(
                    'status' => 400,
                    'message' => 'Please select a store first'
                );
                echo json_encode($response);
                return;
            }

            $product = $this->store_products->get_product_for_store($product_id, $store_id, $client_id);

            if ($product) {
                // Use store price if exists, else base price
                $price = !empty($product->store_price) ? $product->store_price : $product->base_price;

                $response = array(
                    'status' => 200,
                    'data' => array(
                        'product_id' => $product->product_id,
                        'product_name' => $product->product_name,
                        'base_price' => $product->base_price,
                        'store_price' => $price,
                        'category_name' => $product->category_name,
                        'store_product_id' => $product->store_product_id,
                        'is_existing' => !empty($product->store_product_id) ? true : false
                    ),
                    'message' => 'Product retrieved successfully'
                );
            } else {
                $response = array(
                    'status' => 400,
                    'message' => 'Product not found'
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

    /**
     * AJAX: Get products by category filter for adding to store
     */
    public function get_products_by_filter()
    {
        if (is_loggedin_client()) {
            $client_id = get_client_sessiondata('client_id');
            $category_id = $this->input->post('category_id', true);
            $store_id = $this->input->post('store_id', true);

            if ($store_id === null || $store_id === '' || $store_id === false) {
                $response = array(
                    'status' => 400,
                    'message' => 'Please select a store first'
                );
                echo json_encode($response);
                return;
            }

            $products = $this->store_products->get_products_by_category($client_id, $category_id, $store_id);

            if (!empty($products)) {
                $data = [];
                foreach ($products as $product) {
                    // Use store price if exists, else base price
                    $price = !empty($product->store_price) ? $product->store_price : $product->base_price;

                    $data[] = array(
                        'product_id' => $product->product_id,
                        'product_name' => $product->product_name,
                        'base_price' => $product->base_price,
                        'store_price' => $price,
                        'category_name' => $product->category_name,
                        'store_product_id' => $product->store_product_id,
                        'is_existing' => !empty($product->store_product_id) ? true : false
                    );
                }

                $response = array(
                    'status' => 200,
                    'data' => $data,
                    'message' => 'Products retrieved successfully'
                );
            } else {
                $response = array(
                    'status' => 400,
                    'message' => 'No products found in this category'
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

    /**
     * Store/Update store products (batch)
     */
    public function store()
    {
        if (is_loggedin_client()) {
            $post_data = $this->input->post();
            $client_id = get_client_sessiondata('client_id');
            $user_id = get_client_sessiondata('id');

            if (!isset($post_data['store_id']) || $post_data['store_id'] === '') {
                $response = array(
                    'status' => 400,
                    'message' => 'Please select a store'
                );
                echo json_encode($response);
                return;
            }

            if (empty($post_data['product_id']) || !is_array($post_data['product_id'])) {
                $response = array(
                    'status' => 400,
                    'message' => 'Please add at least one product'
                );
                echo json_encode($response);
                return;
            }

            $store_id = $post_data['store_id'];
            $product_ids = $post_data['product_id'];
            $prices = $post_data['store_price'];

            $insert_data = [];
            $update_count = 0;
            $insert_count = 0;

            for ($i = 0; $i < count($product_ids); $i++) {
                $product_id = $product_ids[$i];
                $price = isset($prices[$i]) ? $prices[$i] : 0;

                // Check if product already exists in store
                $existing = $this->store_products->check_store_product_exists($store_id, $product_id, $client_id);

                if ($existing) {
                    // Update existing record
                    $update_data = array(
                        'price' => $price,
                        'updated_by' => $user_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                    $this->store_products->update_store_product($existing->id, $update_data, $client_id);
                    $update_count++;
                } else {
                    // Prepare for insert
                    $insert_data[] = array(
                        'client_id' => $client_id,
                        'store_id' => $store_id,
                        'product_id' => $product_id,
                        'price' => $price,
                        'is_active' => 1,
                        'created_by' => $user_id,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    $insert_count++;
                }
            }

            // Batch insert new records
            if (!empty($insert_data)) {
                $this->store_products->batch_insert_store_products($insert_data);
            }

            $response = array(
                'status' => 200,
                'message' => "Store items saved successfully. Added: $insert_count, Updated: $update_count"
            );
        } else {
            $response = array(
                'status' => 400,
                'message' => 'Please login again'
            );
        }

        echo json_encode($response);
    }

    /**
     * Toggle store product status
     */
    public function toggle_status()
    {
        if (is_loggedin_client()) {
            $id = $this->input->post('id', true);
            $status = $this->input->post('status', true);
            $client_id = get_client_sessiondata('client_id');

            // Verify store product belongs to this client
            $store_product = $this->store_products->get_by_id($id, $client_id);
            if (!$store_product) {
                echo json_encode(['status' => 'error', 'message' => 'Store item not found']);
                return;
            }

            $data = array(
                'is_active' => $status ? 1 : 0,
                'updated_by' => get_client_sessiondata('id'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            $result = $this->common->update('store_products', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Store item status updated successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update store item status'
                ]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    /**
     * Delete store product (soft delete)
     */
    public function delete()
    {
        if (is_loggedin_client()) {

            $id = $this->input->post('id', true);
            $client_id = get_client_sessiondata('client_id');

            // Verify store product belongs to this client
            $store_product = $this->store_products->get_by_id($id, $client_id);
            if (!$store_product) {
                echo json_encode(['status' => 'error', 'message' => 'Store item not found']);
                return;
            }

            // Soft delete
            $data = array(
                'is_active' => 0,
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_by' => get_client_sessiondata('id')
            );

            $result = $this->common->update('store_products', $data, ['id' => $id]);

            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Store item deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete store item']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    /**
     * Bulk delete store products (soft delete)
     */
    public function bulk_delete()
    {
        if (is_loggedin_client()) {
            $ids = $this->input->post('ids', true);
            $client_id = get_client_sessiondata('client_id');
            $user_id = get_client_sessiondata('id');

            if (empty($ids) || !is_array($ids)) {
                echo json_encode(['status' => 'error', 'message' => 'Please select at least one item']);
                return;
            }

            $deleted = 0;
            foreach ($ids as $id) {
                // Verify each store product belongs to this client
                $store_product = $this->store_products->get_by_id($id, $client_id);
                if (!$store_product) {
                    continue;
                }

                $data = array(
                    'is_active' => 0,
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $user_id
                );

                $result = $this->common->update('store_products', $data, ['id' => $id]);
                if ($result !== false) {
                    $deleted++;
                }
            }

            if ($deleted > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => $deleted . ' store item(s) deleted successfully',
                    'deleted' => $deleted
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No items were deleted']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }

    /**
     * Update store product price (inline edit)
     */
    public function update_price()
    {
        if (is_loggedin_client()) {
            $id = $this->input->post('id', true);
            $price = $this->input->post('price', true);
            $client_id = get_client_sessiondata('client_id');

            // Verify store product belongs to this client
            $store_product = $this->store_products->get_by_id($id, $client_id);
            if (!$store_product) {
                echo json_encode(['status' => 'error', 'message' => 'Store item not found']);
                return;
            }

            $data = array(
                'price' => $price,
                'updated_by' => get_client_sessiondata('id'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            $result = $this->store_products->update_store_product($id, $data, $client_id);

            if ($result !== false) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Price updated successfully'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update price'
                ]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        }
    }
}
