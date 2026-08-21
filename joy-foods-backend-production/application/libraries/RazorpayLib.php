<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

/**
 * RazorpayLib - Razorpay Payment Integration Library
 *
 * Handles Razorpay order creation, payment verification,
 * and refund operations for Joy Foods.
 * Uses official Razorpay PHP SDK (razorpay/razorpay)
 * Keys are fetched from clients table based on company_id
 *
 * @category  Libraries
 * @package   Joy_Foods
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.1.0
 * @since     2026-01-03
 */
class RazorpayLib
{
    protected $CI;
    protected $key_id;
    protected $key_secret;
    protected $api;
    protected $client_id;
    protected $initialized = false;
    protected $logger;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('Common_model', 'common');
        $this->CI->load->library('monolog');
        $this->logger = new Monolog();
    }

    /**
     * Initialize Razorpay with company's client credentials
     *
     * @param int $company_id Company ID to fetch client credentials
     * @return bool True if initialized successfully
     */
    public function init($company_id)
    {
        if (empty($company_id)) {
            $this->logger->error('RazorpayLib: company_id is required for initialization', [], 'razorpay');
            return false;
        }

        // Get company to find client_id
        $company = $this->CI->common->getdatabytable('companies', [
            'id' => $company_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($company)) {
            $this->logger->error('RazorpayLib: Company not found', [
                'company_id' => $company_id
            ], 'razorpay');
            return false;
        }

        $this->client_id = $company->client_id;

        // Get client's Razorpay credentials
        $client = $this->CI->common->getdatabytable('clients', [
            'id' => $this->client_id,
            'is_active' => 1,
            'deleted_at' => NULL
        ]);

        if (empty($client)) {
            $this->logger->error('RazorpayLib: Client not found', [
                'client_id' => $this->client_id
            ], 'razorpay');
            return false;
        }

        if (empty($client->razorpay_key_id) || empty($client->razorpay_key_secret)) {
            $this->logger->error('RazorpayLib: Razorpay credentials not configured', [
                'client_id' => $this->client_id
            ], 'razorpay');
            return false;
        }

        $this->key_id = $client->razorpay_key_id;
        $this->key_secret = $client->razorpay_key_secret;

        // Initialize Razorpay API
        $this->api = new Api($this->key_id, $this->key_secret);
        $this->initialized = true;

        $this->logger->info('RazorpayLib initialized successfully', [
            'company_id' => $company_id,
            'client_id' => $this->client_id
        ], 'razorpay');

        return true;
    }

    /**
     * Check if library is initialized
     *
     * @return bool
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * Ensure library is initialized before API calls
     *
     * @return bool
     */
    protected function ensureInitialized()
    {
        if (!$this->initialized) {
            $this->logger->error('RazorpayLib: Not initialized. Call init($company_id) first.', [], 'razorpay');
            return false;
        }
        return true;
    }

    /**
     * Create Razorpay Order
     *
     * @param array $params Required keys:
     *   - amount: float (in rupees, will be converted to paise)
     *   - receipt: string (unique receipt ID)
     *   - notes: array (optional additional notes)
     *
     * @return array [
     *   'success' => bool,
     *   'order' => array|null (Razorpay order data),
     *   'message' => string
     * ]
     */
    public function createOrder($params)
    {
        $response = [
            'success' => false,
            'order' => null,
            'message' => ''
        ];

        if (!$this->ensureInitialized()) {
            $response['message'] = 'Razorpay not initialized. Call init($company_id) first.';
            return $response;
        }

        if (empty($params['amount']) || $params['amount'] <= 0) {
            $response['message'] = 'Invalid amount';
            return $response;
        }

        if (empty($params['receipt'])) {
            $response['message'] = 'Receipt ID is required';
            return $response;
        }

        try {
            // Convert amount to paise (Razorpay accepts amount in smallest currency unit)
            $amount_in_paise = (int) round($params['amount'] * 100);

            $order_data = [
                'amount' => $amount_in_paise,
                'currency' => 'INR',
                'receipt' => $params['receipt'],
                'payment_capture' => 1, // Auto-capture payment
                'notes' => isset($params['notes']) ? $params['notes'] : []
            ];

            $order = $this->api->order->create($order_data);

            $response['success'] = true;
            $response['order'] = $order->toArray();
            $response['message'] = 'Order created successfully';

            $this->logger->info('Razorpay order created', [
                'order_id' => $order->id,
                'amount' => $params['amount'],
                'receipt' => $params['receipt'],
                'client_id' => $this->client_id
            ], 'razorpay');

        } catch (\Exception $e) {
            $this->logger->error('Razorpay createOrder error', [
                'error' => $e->getMessage(),
                'amount' => $params['amount'],
                'receipt' => $params['receipt'],
                'client_id' => $this->client_id
            ], 'razorpay');
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Verify Payment Signature
     *
     * Validates the payment signature from Razorpay callback
     *
     * @param string $razorpay_order_id   Razorpay order ID
     * @param string $razorpay_payment_id Razorpay payment ID
     * @param string $razorpay_signature  Razorpay signature
     *
     * @return bool True if signature is valid
     */
    public function verifyPaymentSignature($razorpay_order_id, $razorpay_payment_id, $razorpay_signature)
    {
        if (!$this->ensureInitialized()) {
            return false;
        }

        try {
            $attributes = [
                'razorpay_order_id' => $razorpay_order_id,
                'razorpay_payment_id' => $razorpay_payment_id,
                'razorpay_signature' => $razorpay_signature
            ];

            $this->api->utility->verifyPaymentSignature($attributes);

            $this->logger->info('Razorpay signature verified', [
                'order_id' => $razorpay_order_id,
                'payment_id' => $razorpay_payment_id,
                'client_id' => $this->client_id
            ], 'razorpay');

            return true;

        } catch (SignatureVerificationError $e) {
            $this->logger->error('Razorpay signature verification failed', [
                'error' => $e->getMessage(),
                'order_id' => $razorpay_order_id,
                'payment_id' => $razorpay_payment_id,
                'client_id' => $this->client_id
            ], 'razorpay');
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Razorpay verification error', [
                'error' => $e->getMessage(),
                'order_id' => $razorpay_order_id,
                'payment_id' => $razorpay_payment_id,
                'client_id' => $this->client_id
            ], 'razorpay');
            return false;
        }
    }

    /**
     * Fetch Payment Details
     *
     * @param string $payment_id Razorpay payment ID
     *
     * @return array [
     *   'success' => bool,
     *   'payment' => array|null,
     *   'message' => string
     * ]
     */
    public function fetchPayment($payment_id)
    {
        $response = [
            'success' => false,
            'payment' => null,
            'message' => ''
        ];

        if (!$this->ensureInitialized()) {
            $response['message'] = 'Razorpay not initialized. Call init($company_id) first.';
            return $response;
        }

        if (empty($payment_id)) {
            $response['message'] = 'Payment ID is required';
            return $response;
        }

        try {
            $payment = $this->api->payment->fetch($payment_id);

            $response['success'] = true;
            $response['payment'] = $payment->toArray();
            $response['message'] = 'Payment fetched successfully';

        } catch (\Exception $e) {
            $this->logger->error('Razorpay fetchPayment error', [
                'error' => $e->getMessage(),
                'payment_id' => $payment_id,
                'client_id' => $this->client_id
            ], 'razorpay');
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Fetch Order Details
     *
     * @param string $order_id Razorpay order ID
     *
     * @return array [
     *   'success' => bool,
     *   'order' => array|null,
     *   'message' => string
     * ]
     */
    public function fetchOrder($order_id)
    {
        $response = [
            'success' => false,
            'order' => null,
            'message' => ''
        ];

        if (!$this->ensureInitialized()) {
            $response['message'] = 'Razorpay not initialized. Call init($company_id) first.';
            return $response;
        }

        if (empty($order_id)) {
            $response['message'] = 'Order ID is required';
            return $response;
        }

        try {
            $order = $this->api->order->fetch($order_id);

            $response['success'] = true;
            $response['order'] = $order->toArray();
            $response['message'] = 'Order fetched successfully';

        } catch (\Exception $e) {
            $this->logger->error('Razorpay fetchOrder error', [
                'error' => $e->getMessage(),
                'order_id' => $order_id,
                'client_id' => $this->client_id
            ], 'razorpay');
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Create Refund
     *
     * @param string $payment_id Razorpay payment ID
     * @param float  $amount     Refund amount in rupees (optional, full refund if not provided)
     * @param array  $notes      Optional notes
     *
     * @return array [
     *   'success' => bool,
     *   'refund' => array|null,
     *   'message' => string
     * ]
     */
    public function createRefund($payment_id, $amount = null, $notes = [])
    {
        $response = [
            'success' => false,
            'refund' => null,
            'message' => ''
        ];

        if (!$this->ensureInitialized()) {
            $response['message'] = 'Razorpay not initialized. Call init($company_id) first.';
            return $response;
        }

        if (empty($payment_id)) {
            $response['message'] = 'Payment ID is required';
            return $response;
        }

        try {
            $refund_data = [];

            if ($amount !== null && $amount > 0) {
                $refund_data['amount'] = (int) round($amount * 100); // Convert to paise
            }

            if (!empty($notes)) {
                $refund_data['notes'] = $notes;
            }

            $refund = $this->api->payment->fetch($payment_id)->refund($refund_data);

            $response['success'] = true;
            $response['refund'] = $refund->toArray();
            $response['message'] = 'Refund created successfully';

            $this->logger->info('Razorpay refund created', [
                'refund_id' => $refund->id,
                'payment_id' => $payment_id,
                'amount' => $amount,
                'client_id' => $this->client_id
            ], 'razorpay');

        } catch (\Exception $e) {
            $this->logger->error('Razorpay createRefund error', [
                'error' => $e->getMessage(),
                'payment_id' => $payment_id,
                'amount' => $amount,
                'client_id' => $this->client_id
            ], 'razorpay');
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Capture Payment (for manual capture mode)
     *
     * @param string $payment_id Razorpay payment ID
     * @param float  $amount     Amount to capture in rupees
     *
     * @return array [
     *   'success' => bool,
     *   'payment' => array|null,
     *   'message' => string
     * ]
     */
    public function capturePayment($payment_id, $amount)
    {
        $response = [
            'success' => false,
            'payment' => null,
            'message' => ''
        ];

        if (!$this->ensureInitialized()) {
            $response['message'] = 'Razorpay not initialized. Call init($company_id) first.';
            return $response;
        }

        if (empty($payment_id)) {
            $response['message'] = 'Payment ID is required';
            return $response;
        }

        try {
            $amount_in_paise = (int) round($amount * 100);

            $payment = $this->api->payment->fetch($payment_id)->capture([
                'amount' => $amount_in_paise,
                'currency' => 'INR'
            ]);

            $response['success'] = true;
            $response['payment'] = $payment->toArray();
            $response['message'] = 'Payment captured successfully';

            $this->logger->info('Razorpay payment captured', [
                'payment_id' => $payment_id,
                'amount' => $amount,
                'client_id' => $this->client_id
            ], 'razorpay');

        } catch (\Exception $e) {
            $this->logger->error('Razorpay capturePayment error', [
                'error' => $e->getMessage(),
                'payment_id' => $payment_id,
                'amount' => $amount,
                'client_id' => $this->client_id
            ], 'razorpay');
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Get Razorpay Key ID for frontend
     *
     * @return string Razorpay Key ID
     */
    public function getKeyId()
    {
        return $this->key_id;
    }

    /**
     * Get Client ID
     *
     * @return int|null Client ID
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Get Razorpay API instance
     *
     * @return Api Razorpay API instance
     */
    public function getApi()
    {
        return $this->api;
    }
}
