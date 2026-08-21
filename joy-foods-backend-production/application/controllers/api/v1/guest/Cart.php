<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Guest Cart API Controller
 *
 * Handles cart operations for guest QSR ordering.
 * No JWT authentication required — only API key header.
 * Cart tracked via session_id (UUID) in X-Guest-Session header.
 */
class Cart extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Stores_model', 'stores_model');
        $this->load->model('Products_model', 'products');
        $this->load->model('Guest_model', 'guest_model');
        $this->load->helper('common');
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
     * Validate product is available in store
     */
    private function validate_product($store_id, $product_id, $module = 'QSR')
    {
        return $this->products->get_product_detail($product_id, $store_id, $module);
    }

    /**
     * Add item to guest cart
     *
     * POST /api/v1/guest/cart/add
     * Input: store_code, product_id, quantity (opt, default 1), note (opt)
     * Header: X-Guest-Session (optional — generated if not provided)
     */
    public function add()
    {
        if (!$this->check_auth()) return;

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $product_id = $this->input->post('product_id', true);
        $quantity = (int)$this->input->post('quantity', true) ?: 1;
        $note = $this->input->post('note', true);

        if (empty($product_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Product ID is required',
                'data' => null
            ]);
            return;
        }

        if ($quantity < 1) $quantity = 1;
        if ($quantity > 50) $quantity = 50;

        // Validate product exists and is available in store
        $product = $this->validate_product($store->id, $product_id, $store->store_type);
        if (empty($product)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Product not found or not available in this store',
                'data' => null
            ]);
            return;
        }

        // Check stock
        if ($product->available_stock !== null && (int)$product->available_stock < $quantity) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Insufficient stock. Available: ' . (int)$product->available_stock,
                'data' => null
            ]);
            return;
        }

        // Get or generate session ID
        $session_id = $this->get_session_id();
        $is_new_session = false;
        if (empty($session_id)) {
            $session_id = generate_uuid();
            $is_new_session = true;
        }

        // Check if product already in cart
        $existing = $this->guest_model->get_existing_cart($session_id, $store->id, $product_id);

        if ($existing) {
            // Update quantity
            $new_quantity = (int)$existing->quantity + $quantity;

            // Check stock for new total
            if ($product->available_stock !== null && (int)$product->available_stock < $new_quantity) {
                $this->output([
                    'status' => 400,
                    'success' => false,
                    'message' => 'Insufficient stock for total quantity. Available: ' . (int)$product->available_stock,
                    'data' => null
                ]);
                return;
            }

            $this->guest_model->update_quantity($existing->id, $new_quantity, $note);
            $cart_id = (int)$existing->id;
            $final_quantity = $new_quantity;
        } else {
            // Add new item
            $cart_data = [
                'session_id' => $session_id,
                'store_id' => $store->id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'note' => $note,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $cart_id = $this->guest_model->add_to_cart($cart_data);
            $final_quantity = $quantity;
        }

        $price = !empty($product->store_price) ? $product->store_price : $product->base_price;
        $cart_count = $this->guest_model->get_cart_count($session_id, $store->id);

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => $existing ? 'Cart updated successfully' : 'Item added to cart',
            'data' => [
                'session_id' => $session_id,
                'is_new_session' => $is_new_session,
                'cart_id' => $cart_id,
                'product_id' => (int)$product_id,
                'product_name' => $product->name,
                'thumbnail' => $product->thumbnail ? base_url($product->thumbnail) : null,
                'quantity' => $final_quantity,
                'price' => (float)$price,
                'cart_count' => $cart_count
            ]
        ]);
    }

    /**
     * Increment cart item quantity by 1
     *
     * POST /api/v1/guest/cart/increment
     * Input: cart_id
     * Header: X-Guest-Session (required)
     */
    public function increment()
    {
        if (!$this->check_auth()) return;

        $session_id = $this->get_session_id();
        if (empty($session_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'X-Guest-Session header is required',
                'data' => null
            ]);
            return;
        }

        $cart_id = $this->input->post('cart_id', true);
        if (empty($cart_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'cart_id is required',
                'data' => null
            ]);
            return;
        }

        $cart_item = $this->guest_model->get_cart_item($cart_id, $session_id);
        if (empty($cart_item)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Cart item not found',
                'data' => null
            ]);
            return;
        }

        $new_quantity = (int)$cart_item->quantity + 1;

        // Check stock
        $store = $this->common->getdatabytable('stores', ['id' => $cart_item->store_id]);
        $module = $store ? $store->store_type : 'QSR';
        $product = $this->validate_product($cart_item->store_id, $cart_item->product_id, $module);
        if ($product && $product->available_stock !== null && (int)$product->available_stock < $new_quantity) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'Insufficient stock. Available: ' . (int)$product->available_stock,
                'data' => null
            ]);
            return;
        }

        $this->guest_model->update_quantity($cart_id, $new_quantity);

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Quantity updated',
            'data' => [
                'cart_id' => (int)$cart_id,
                'quantity' => $new_quantity
            ]
        ]);
    }

    /**
     * Decrement cart item quantity by 1 (removes if reaches 0)
     *
     * POST /api/v1/guest/cart/decrement
     * Input: cart_id
     * Header: X-Guest-Session (required)
     */
    public function decrement()
    {
        if (!$this->check_auth()) return;

        $session_id = $this->get_session_id();
        if (empty($session_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'X-Guest-Session header is required',
                'data' => null
            ]);
            return;
        }

        $cart_id = $this->input->post('cart_id', true);
        if (empty($cart_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'cart_id is required',
                'data' => null
            ]);
            return;
        }

        $cart_item = $this->guest_model->get_cart_item($cart_id, $session_id);
        if (empty($cart_item)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Cart item not found',
                'data' => null
            ]);
            return;
        }

        $new_quantity = (int)$cart_item->quantity - 1;

        if ($new_quantity <= 0) {
            $this->guest_model->remove_from_cart($cart_id);
            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'Item removed from cart',
                'data' => [
                    'cart_id' => (int)$cart_id,
                    'removed' => true,
                    'quantity' => 0
                ]
            ]);
            return;
        }

        $this->guest_model->update_quantity($cart_id, $new_quantity);

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Quantity updated',
            'data' => [
                'cart_id' => (int)$cart_id,
                'quantity' => $new_quantity
            ]
        ]);
    }

    /**
     * Remove item from cart
     *
     * POST /api/v1/guest/cart/remove
     * Input: cart_id
     * Header: X-Guest-Session (required)
     */
    public function remove()
    {
        if (!$this->check_auth()) return;

        $session_id = $this->get_session_id();
        if (empty($session_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'X-Guest-Session header is required',
                'data' => null
            ]);
            return;
        }

        $cart_id = $this->input->post('cart_id', true);
        if (empty($cart_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'cart_id is required',
                'data' => null
            ]);
            return;
        }

        $cart_item = $this->guest_model->get_cart_item($cart_id, $session_id);
        if (empty($cart_item)) {
            $this->output([
                'status' => 404,
                'success' => false,
                'message' => 'Cart item not found',
                'data' => null
            ]);
            return;
        }

        $this->guest_model->remove_from_cart($cart_id);

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Item removed from cart',
            'data' => [
                'cart_id' => (int)$cart_id,
                'removed' => true
            ]
        ]);
    }

    /**
     * Get cart items with pricing summary
     *
     * POST /api/v1/guest/cart/list
     * Input: store_code
     * Header: X-Guest-Session (required)
     */
    public function list_items()
    {
        if (!$this->check_auth()) return;

        $session_id = $this->get_session_id();
        if (empty($session_id)) {
            $this->output([
                'status' => 400,
                'success' => false,
                'message' => 'X-Guest-Session header is required',
                'data' => null
            ]);
            return;
        }

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $cart_items = $this->guest_model->get_cart_items($session_id, $store->id);

        $items_data = [];
        $subtotal = 0;
        $total_tax = 0;
        $total_items = 0;
        $has_stock_issue = false;

        foreach ($cart_items as $item) {
            $quantity = (int)$item->quantity;
            $inclusive_price = !empty($item->store_price) ? (float)$item->store_price : (float)$item->base_price;
            $tax_percentage = (float)$item->tax_percentage;

            $unit_base_price = round(calculate_base_price($inclusive_price, $tax_percentage), 2);
            $unit_gst = round(calculate_gst_amount($inclusive_price, $tax_percentage), 2);

            $item_subtotal = round($unit_base_price * $quantity, 2);
            $item_tax = round($unit_gst * $quantity, 2);
            $item_total = round($inclusive_price * $quantity, 2);

            $is_in_stock = ($item->available_stock === null || (int)$item->available_stock >= $quantity);
            if (!$is_in_stock) $has_stock_issue = true;

            $subtotal += $item_subtotal;
            $total_tax += $item_tax;
            $total_items += $quantity;

            $items_data[] = [
                'cart_id' => (int)$item->cart_id,
                'product_id' => (int)$item->product_id,
                'product_name' => $item->product_name,
                'short_name' => $item->short_name,
                'thumbnail' => $item->thumbnail ? base_url($item->thumbnail) : null,
                'quantity' => $quantity,
                'unit_price' => $inclusive_price,
                'tax_percentage' => $tax_percentage,
                'base_price' => $unit_base_price,
                'tax_amount' => $item_tax,
                'subtotal' => $item_subtotal,
                'total' => $item_total,
                'note' => $item->note,
                'is_vegetarian' => (bool)$item->is_vegetarian,
                'is_vegan' => (bool)$item->is_vegan,
                'is_in_stock' => $is_in_stock,
                'available_stock' => $item->available_stock === null ? null : (int)$item->available_stock
            ];
        }

        $grand_total = round($subtotal + $total_tax, 2);

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => empty($items_data) ? 'Cart is empty' : 'Cart fetched successfully',
            'data' => [
                'items' => $items_data,
                'summary' => [
                    'total_items' => $total_items,
                    'item_count' => count($items_data),
                    'subtotal' => $subtotal,
                    'tax_amount' => $total_tax,
                    'total_amount' => $grand_total,
                    'amount_payable' => $grand_total,
                    'formatted' => [
                        'subtotal' => number_format($subtotal, 2),
                        'tax_amount' => number_format($total_tax, 2),
                        'total_amount' => number_format($grand_total, 2),
                        'amount_payable' => number_format($grand_total, 2)
                    ]
                ],
                'has_stock_issue' => $has_stock_issue
            ]
        ]);
    }

    /**
     * Get cart item count
     *
     * POST /api/v1/guest/cart/count
     * Input: store_code
     * Header: X-Guest-Session (required)
     */
    public function count()
    {
        if (!$this->check_auth()) return;

        $session_id = $this->get_session_id();
        if (empty($session_id)) {
            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'No session',
                'data' => ['count' => 0]
            ]);
            return;
        }

        $store_code = $this->input->post('store_code', true);
        $store = $this->resolve_store($store_code);
        if (!$store) return;

        $count = $this->guest_model->get_cart_count($session_id, $store->id);

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Cart count fetched',
            'data' => ['count' => (int)$count]
        ]);
    }
}
