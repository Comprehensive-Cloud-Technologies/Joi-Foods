<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Guest Catalog API Controller
 *
 * Handles product browsing, categories, search for guest QSR ordering.
 * No JWT authentication required — only API key header.
 */
class Catalog extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Stores_model', 'stores_model');
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

        $company = $this->common->getdatabytable('companies', [
            'id' => $store->company_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($company) || empty($company->allow_guest_booking)) {
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

        $store->client_id = $company->client_id;
        return $store;
    }

    /**
     * Get guest session ID from header
     */
    private function get_session_id()
    {
        $headers = $this->input->request_headers();
        return isset($headers['X-Guest-Session']) ? $headers['X-Guest-Session'] : null;
    }

    /**
     * Get QSR categories for store
     *
     * POST /api/v1/guest/catalog/categories
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
     * Get QSR products by category (paginated)
     *
     * POST /api/v1/guest/catalog/products
     * Input: store_code, category_id, page (opt), per_page (opt)
     */
    public function products()
    {
        if (!$this->check_auth()) return;

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $category_id = $this->input->post('category_id', true);
        $page = (int)$this->input->post('page', true) ?: 1;
        $per_page = (int)$this->input->post('per_page', true) ?: 20;

        if (empty($category_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Category ID is required',
                'data' => null
            ]);
            return;
        }

        if ($per_page > 50) $per_page = 50;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $per_page;

        $total_count = $this->products->get_products_count_by_store($store->id, $category_id, $store->store_type);
        $total_pages = ceil($total_count / $per_page);
        $products = $this->products->get_products_by_store($store->id, $category_id, $store->store_type, $per_page, $offset);

        $session_id = $this->get_session_id();

        $products_data = [];
        foreach ($products as $product) {
            $price = !empty($product->store_price) ? $product->store_price : $product->base_price;
            $is_in_stock = ($product->available_stock === null || $product->available_stock > 0);

            $is_in_cart = false;
            $cart_quantity = 0;
            $cart_id = null;
            if ($session_id) {
                $existing = $this->guest_model->get_existing_cart($session_id, $store->id, $product->id);
                if ($existing) {
                    $is_in_cart = true;
                    $cart_quantity = (int)$existing->quantity;
                    $cart_id = (int)$existing->id;
                }
            }

            $images = [];
            if (!empty($product->images)) {
                $images_array = json_decode($product->images, true);
                if (is_array($images_array)) {
                    foreach ($images_array as $img_path) {
                        $images[] = base_url($img_path);
                    }
                }
            }

            $products_data[] = [
                'id' => (int)$product->id,
                'name' => $product->name,
                'short_name' => $product->short_name,
                'description' => $product->description,
                'ingredients' => $product->ingredients,
                'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                'images' => $images,
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

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($products_data) ? 'No products found' : 'Products fetched successfully',
            'data' => [
                'products' => $products_data,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total_count' => $total_count,
                    'total_pages' => $total_pages,
                    'has_next' => $page < $total_pages,
                    'has_previous' => $page > 1
                ]
            ]
        ]);
    }

    /**
     * Get single product detail
     *
     * POST /api/v1/guest/catalog/product_detail
     * Input: store_code, product_id
     */
    public function product_detail()
    {
        if (!$this->check_auth()) return;

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $product_id = $this->input->post('product_id', true);
        if (empty($product_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product ID is required',
                'data' => null
            ]);
            return;
        }

        $product = $this->products->get_product_detail($product_id, $store->id, $store->store_type);

        if (empty($product)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Product not found or not available in this store',
                'data' => null
            ]);
            return;
        }

        $price = !empty($product->store_price) ? $product->store_price : $product->base_price;
        $is_in_stock = ($product->available_stock === null || $product->available_stock > 0);

        $session_id = $this->get_session_id();
        $is_in_cart = false;
        $cart_quantity = 0;
        $cart_id = null;
        if ($session_id) {
            $existing = $this->guest_model->get_existing_cart($session_id, $store->id, $product->id);
            if ($existing) {
                $is_in_cart = true;
                $cart_quantity = (int)$existing->quantity;
                $cart_id = (int)$existing->id;
            }
        }

        $images = [];
        if (!empty($product->images)) {
            $images_array = json_decode($product->images, true);
            if (is_array($images_array)) {
                foreach ($images_array as $img_path) {
                    $images[] = base_url($img_path);
                }
            }
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Product detail fetched successfully',
            'data' => [
                'product' => [
                    'id' => (int)$product->id,
                    'name' => $product->name,
                    'short_name' => $product->short_name,
                    'description' => $product->description,
                    'ingredients' => $product->ingredients,
                    'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                    'images' => $images,
                    'price' => (float)$price,
                    'base_price' => (float)$product->base_price,
                    'discount_price' => $product->discount_price ? (float)$product->discount_price : null,
                    'tax_percentage' => (float)$product->tax_percentage,
                    'is_vegetarian' => (bool)$product->is_vegetarian,
                    'is_vegan' => (bool)$product->is_vegan,
                    'calories' => $product->calories ? (int)$product->calories : null,
                    'meal_times' => [
                        'breakfast' => (bool)$product->breakfast,
                        'lunch' => (bool)$product->lunch,
                        'dinner' => (bool)$product->dinner
                    ],
                    'is_featured' => (bool)$product->is_featured,
                    'is_popular' => (bool)$product->is_popular,
                    'is_in_stock' => $is_in_stock,
                    'available_stock' => $product->available_stock === null ? null : (int)$product->available_stock,
                    'is_in_cart' => $is_in_cart,
                    'cart_id' => $cart_id,
                    'cart_quantity' => $cart_quantity,
                    'category' => [
                        'id' => $product->category_id ? (int)$product->category_id : null,
                        'name' => $product->category_name
                    ]
                ]
            ]
        ]);
    }

    /**
     * Search QSR products in store
     *
     * POST /api/v1/guest/catalog/search
     * Input: store_code, keyword, page (opt), per_page (opt)
     */
    public function search()
    {
        if (!$this->check_auth()) return;

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $keyword = $this->input->post('keyword', true);
        $page = (int)$this->input->post('page', true) ?: 1;
        $per_page = (int)$this->input->post('per_page', true) ?: 20;

        if (empty($keyword) || strlen(trim($keyword)) < 2) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Search keyword must be at least 2 characters',
                'data' => null
            ]);
            return;
        }

        if ($per_page > 50) $per_page = 50;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $per_page;

        $products = $this->products->search_products($store->id, $keyword, $store->store_type, $per_page, $offset);

        $session_id = $this->get_session_id();

        $products_data = [];
        foreach ($products as $product) {
            $price = !empty($product->store_price) ? $product->store_price : $product->base_price;
            $is_in_stock = ($product->available_stock === null || $product->available_stock > 0);

            $is_in_cart = false;
            $cart_quantity = 0;
            $cart_id = null;
            if ($session_id) {
                $existing = $this->guest_model->get_existing_cart($session_id, $store->id, $product->id);
                if ($existing) {
                    $is_in_cart = true;
                    $cart_quantity = (int)$existing->quantity;
                    $cart_id = (int)$existing->id;
                }
            }

            $products_data[] = [
                'id' => (int)$product->id,
                'name' => $product->name,
                'short_name' => $product->short_name,
                'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                'price' => (float)$price,
                'tax_percentage' => (float)$product->tax_percentage,
                'is_vegetarian' => (bool)$product->is_vegetarian,
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

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($products_data) ? 'No products found' : 'Search results fetched',
            'data' => [
                'keyword' => $keyword,
                'products' => $products_data,
                'total_count' => count($products_data)
            ]
        ]);
    }
}
