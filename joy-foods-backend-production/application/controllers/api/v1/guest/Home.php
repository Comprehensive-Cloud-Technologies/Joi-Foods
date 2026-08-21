<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Guest Home API Controller
 *
 * Handles store info, banners, categories, and featured products
 * for guest QSR ordering via QR code scan (web app).
 * No JWT authentication required — only API key header.
 */
class Home extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Stores_model', 'stores_model');
        $this->load->model('Banners_model', 'banners');
        $this->load->model('Categories_model', 'categories');
        $this->load->model('Products_model', 'products');
        $this->load->model('Guest_model', 'guest_model');
    }

    private function output($data)
    {
        header("Content-Type: application/json; charset=UTF-8");
        if (isset($data['status'])) {
            http_response_code($data['status']);
        }
        echo json_encode($data);
    }

    private function check_auth()
    {
        $headers_of_page = $this->input->request_headers();
        if (isset($headers_of_page['Auth']) && $headers_of_page['Auth'] == config_item('api_authorization')) {
            return true;
        }
        $this->output([
            'status' => 401,
            'success' => false,
            'message' => 'Unauthorized. Invalid API key.'
        ]);
        return false;
    }

    /**
     * Resolve store from store_code and validate guest booking
     *
     * @param string $store_code Store code from QR
     * @return object|bool Store object with company data if valid
     */
    private function resolve_store($store_code)
    {
        if (empty($store_code)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'store_code is required',
                'data' => null
            ]);
            return false;
        }

        $store = $this->stores_model->get_store_by_code($store_code);

        if (empty($store) || !$store->is_active) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Store not found or inactive',
                'data' => null
            ]);
            return false;
        }

        if (!$store->is_operational) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store is currently not operational',
                'data' => null
            ]);
            return false;
        }

        // Check company allows guest booking
        $company = $this->common->getdatabytable('companies', [
            'id' => $store->company_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($company)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Store is not available',
                'data' => null
            ]);
            return false;
        }

        if (empty($company->allow_guest_booking)) {
            $this->output([
                'status' => 403,
                'success' => false,
                'message' => 'Guest ordering is not available for this store',
                'data' => null
            ]);
            return false;
        }

        // Only QSR and KOT supported for guest ordering
        if (!in_array($store->store_type, ['QSR', 'KOT'])) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Guest ordering is not supported for this store type',
                'data' => null
            ]);
            return false;
        }

        $store->company_name = $company->name;
        $store->client_id = $company->client_id;

        return $store;
    }

    /**
     * Get store info by store_code
     *
     * POST /api/v1/guest/home/store_info
     * Input: store_code
     */
    public function store_info()
    {
        if (!$this->check_auth()) return;

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Store info fetched successfully',
            'data' => [
                'store' => [
                    'id' => (int)$store->id,
                    'store_code' => $store->store_code,
                    'name' => $store->name,
                    'short_name' => $store->short_name,
                    'thumbnail' => $store->thumbnail ? base_url($store->thumbnail) : null,
                    'address' => trim(implode(', ', array_filter([
                        $store->address_line1,
                        $store->city,
                        $store->state
                    ]))),
                    'phone' => $store->primary_phone,
                    'company_name' => $store->company_name,
                    'store_type' => $store->store_type,
                    'is_operational' => (bool)$store->is_operational
                ]
            ]
        ]);
    }

    /**
     * Get banners for store's company
     *
     * POST /api/v1/guest/home/banners
     * Input: store_code
     */
    public function banners()
    {
        if (!$this->check_auth()) return;

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $banners = $this->banners->get_active_banners_by_company($store->company_id);

        $banners_data = [];
        if (!empty($banners)) {
            foreach ($banners as $banner) {
                $banners_data[] = [
                    'id' => (int)$banner->id,
                    'title' => $banner->title,
                    'description' => $banner->description,
                    'image_url' => $banner->image_path ? base_url($banner->image_path) : null,
                    'action' => [
                        'type' => $banner->action_type,
                        'payload' => $banner->action_payload
                    ],
                    'display_order' => (int)$banner->display_order
                ];
            }
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($banners_data) ? 'No banners found' : 'Banners fetched successfully',
            'data' => [
                'banners' => $banners_data,
                'total_count' => count($banners_data)
            ]
        ]);
    }

    /**
     * Get QSR categories for store
     *
     * POST /api/v1/guest/home/categories
     * Input: store_code
     */
    public function categories()
    {
        if (!$this->check_auth()) return;

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $categories = $this->categories->get_categories_by_store($store->id, $store->store_type);

        $categories_data = [];
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $categories_data[] = [
                    'id' => (int)$category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'icon' => $category->icon ? base_url($category->icon) : null,
                    'thumbnail' => $category->thumbnail ? base_url($category->thumbnail) : null,
                    'is_primary' => (bool)$category->is_primary,
                    'display_order' => (int)$category->display_order
                ];
            }
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($categories_data) ? 'No categories found' : 'Categories fetched successfully',
            'data' => [
                'categories' => $categories_data,
                'total_count' => count($categories_data)
            ]
        ]);
    }

    /**
     * Get featured QSR products for store
     *
     * POST /api/v1/guest/home/featured
     * Input: store_code, limit (optional, default 10)
     */
    public function featured()
    {
        if (!$this->check_auth()) return;

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $limit = (int)$this->input->post('limit', true) ?: 10;
        if ($limit > 50) $limit = 50;

        $products = $this->products->get_featured_products_by_store($store->id, $store->store_type, $limit);

        // Get guest session for cart status
        $headers = $this->input->request_headers();
        $session_id = isset($headers['X-Guest-Session']) ? $headers['X-Guest-Session'] : null;

        $products_data = [];
        if (!empty($products)) {
            foreach ($products as $product) {
                $price = !empty($product->store_price) ? $product->store_price : $product->base_price;
                $is_in_stock = ($product->available_stock === null || $product->available_stock > 0);

                $is_in_cart = false;
                $cart_quantity = 0;
                $cart_id = null;
                if ($session_id) {
                    $existing_cart = $this->guest_model->get_existing_cart($session_id, $store->id, $product->id);
                    if ($existing_cart) {
                        $is_in_cart = true;
                        $cart_quantity = (int)$existing_cart->quantity;
                        $cart_id = (int)$existing_cart->id;
                    }
                }

                $products_data[] = [
                    'id' => (int)$product->id,
                    'name' => $product->name,
                    'short_name' => $product->short_name,
                    'description' => $product->description,
                    'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                    'price' => (float)$price,
                    'base_price' => (float)$product->base_price,
                    'discount_price' => $product->discount_price ? (float)$product->discount_price : null,
                    'tax_percentage' => (float)$product->tax_percentage,
                    'is_vegetarian' => (bool)$product->is_vegetarian,
                    'is_vegan' => (bool)$product->is_vegan,
                    'calories' => $product->calories ? (int)$product->calories : null,
                    'is_featured' => (bool)$product->is_featured,
                    'is_popular' => (bool)$product->is_popular,
                    'is_in_stock' => $is_in_stock,
                    'is_in_cart' => $is_in_cart,
                    'cart_id' => $cart_id,
                    'cart_quantity' => $cart_quantity,
                    'category' => [
                        'id' => $product->category_id ? (int)$product->category_id : null,
                        'name' => $product->category_name
                    ]
                ];
            }
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($products_data) ? 'No featured products found' : 'Featured products fetched successfully',
            'data' => [
                'products' => $products_data,
                'total_count' => count($products_data)
            ]
        ]);
    }
}
