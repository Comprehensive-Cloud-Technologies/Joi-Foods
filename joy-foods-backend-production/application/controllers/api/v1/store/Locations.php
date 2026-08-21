<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Store Delivery Locations Controller
 *
 * Handles CRUD operations for delivery locations by store staff.
 *
 * @category  Controllers
 * @package   Joy_Foods_API
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-03-11
 */
class Locations extends CI_Controller
{
    private $tokenHandler;
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('DeliveryLocations_model', 'locations_model');
        $this->tokenHandler = new TokenHandler();

        $this->load->library('monolog');
        $this->logger = new Monolog();
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

    private function check_bearer_token()
    {
        $headers_of_page = $this->input->request_headers();
        if (isset($headers_of_page['Authorization']) && strpos($headers_of_page['Authorization'], 'Bearer ') === 0) {
            return substr($headers_of_page['Authorization'], 7);
        }
        return null;
    }

    private function decode_token($token)
    {
        try {
            return $this->tokenHandler->DecodeToken($token);
        } catch (Exception $e) {
            log_message('error', 'Token decoding failed: ' . $e->getMessage());
            return null;
        }
    }

    private function authenticate()
    {
        $token = $this->check_bearer_token();

        if (empty($token)) {
            $this->output(['status' => 401, 'success' => false, 'message' => 'Authorization token is required']);
            return false;
        }

        $decoded = $this->decode_token($token);

        if (empty($decoded)) {
            $this->output(['status' => 401, 'success' => false, 'message' => 'Invalid or expired token']);
            return false;
        }

        if (isset($decoded->exp) && $decoded->exp < time()) {
            $this->output(['status' => 401, 'success' => false, 'message' => 'Token has expired']);
            return false;
        }

        if (!isset($decoded->role) || $decoded->role !== 'store_staff') {
            $this->output(['status' => 403, 'success' => false, 'message' => 'Access denied. Invalid role']);
            return false;
        }

        if (!isset($decoded->staff_id) || !isset($decoded->store_id)) {
            $this->output(['status' => 401, 'success' => false, 'message' => 'Invalid token data']);
            return false;
        }

        return $decoded;
    }

    // ------------------------------------------------------------------
    // Endpoints
    // ------------------------------------------------------------------

    /**
     * List all delivery locations for the store
     *
     * POST /api/v1/store/locations/list
     */
    public function list()
    {
        if (!$this->check_auth()) return;
        $decoded = $this->authenticate();
        if (!$decoded) return;

        $store_id = $decoded->store_id;
        $locations = $this->locations_model->get_all_by_store($store_id);

        $result = [];
        foreach ($locations as $loc) {
            $result[] = [
                'id' => (int)$loc->id,
                'location_code' => $loc->location_code,
                'name' => $loc->name,
                'short_name' => $loc->short_name,
                'description' => $loc->description,
                'floor' => $loc->floor,
                'building' => $loc->building,
                'landmark' => $loc->landmark,
                'display_order' => (int)$loc->display_order,
                'is_active' => (bool)$loc->is_active,
                'created_at' => $loc->created_at
            ];
        }

        $this->logger->info('Delivery locations listed', [
            'store_id' => $store_id,
            'count' => count($result)
        ], 'store_locations');

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Delivery locations fetched successfully',
            'data' => [
                'locations' => $result,
                'total' => count($result)
            ]
        ]);
    }

    /**
     * Get single location detail
     *
     * POST /api/v1/store/locations/detail
     */
    public function detail()
    {
        if (!$this->check_auth()) return;
        $decoded = $this->authenticate();
        if (!$decoded) return;

        $id = $this->input->post('id', true);
        if (empty($id)) {
            $this->output(['status' => 400, 'success' => false, 'message' => 'Location ID is required']);
            return;
        }

        $location = $this->locations_model->get_by_id($id, $decoded->store_id);
        if (!$location) {
            $this->output(['status' => 404, 'success' => false, 'message' => 'Location not found']);
            return;
        }

        $this->output([
            'status' => 200,
            'success' => true,
            'message' => 'Location details fetched',
            'data' => [
                'location' => [
                    'id' => (int)$location->id,
                    'location_code' => $location->location_code,
                    'name' => $location->name,
                    'short_name' => $location->short_name,
                    'description' => $location->description,
                    'floor' => $location->floor,
                    'building' => $location->building,
                    'landmark' => $location->landmark,
                    'display_order' => (int)$location->display_order,
                    'is_active' => (bool)$location->is_active,
                    'created_at' => $location->created_at,
                    'updated_at' => $location->updated_at
                ]
            ]
        ]);
    }

    /**
     * Add new delivery location
     *
     * POST /api/v1/store/locations/add
     */
    public function add()
    {
        if (!$this->check_auth()) return;
        $decoded = $this->authenticate();
        if (!$decoded) return;

        $name = $this->input->post('name', true);
        if (empty($name)) {
            $this->output(['status' => 400, 'success' => false, 'message' => 'Location name is required']);
            return;
        }

        $store_id = $decoded->store_id;
        $location_code = $this->locations_model->generate_location_code();

        $data = [
            'store_id' => $store_id,
            'location_code' => $location_code,
            'name' => $name,
            'short_name' => $this->input->post('short_name', true),
            'description' => $this->input->post('description', true),
            'floor' => $this->input->post('floor', true),
            'building' => $this->input->post('building', true),
            'landmark' => $this->input->post('landmark', true),
            'display_order' => $this->input->post('display_order', true) ?: 0,
            'is_active' => 1,
            'created_by' => $decoded->staff_id,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->common->insert($data, 'delivery_locations');

        if ($id) {
            $this->logger->info('Delivery location created', [
                'store_id' => $store_id,
                'location_id' => $id,
                'name' => $name
            ], 'store_locations');

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'Location added successfully',
                'data' => [
                    'id' => (int)$id,
                    'location_code' => $location_code,
                    'name' => $name
                ]
            ]);
        } else {
            $this->output(['status' => 400, 'success' => false, 'message' => 'Failed to add location']);
        }
    }

    /**
     * Update delivery location
     *
     * POST /api/v1/store/locations/update
     */
    public function update()
    {
        if (!$this->check_auth()) return;
        $decoded = $this->authenticate();
        if (!$decoded) return;

        $id = $this->input->post('id', true);
        if (empty($id)) {
            $this->output(['status' => 400, 'success' => false, 'message' => 'Location ID is required']);
            return;
        }

        $location = $this->locations_model->get_by_id($id, $decoded->store_id);
        if (!$location) {
            $this->output(['status' => 404, 'success' => false, 'message' => 'Location not found']);
            return;
        }

        $data = [
            'updated_by' => $decoded->staff_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Only update provided fields
        $fields = ['name', 'short_name', 'description', 'floor', 'building', 'landmark', 'display_order', 'is_active'];
        foreach ($fields as $field) {
            $value = $this->input->post($field);
            if ($value !== null) {
                $data[$field] = $value;
            }
        }

        $result = $this->common->update('delivery_locations', $data, ['id' => $id]);

        if ($result !== false) {
            $this->logger->info('Delivery location updated', [
                'store_id' => $decoded->store_id,
                'location_id' => $id
            ], 'store_locations');

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'Location updated successfully'
            ]);
        } else {
            $this->output(['status' => 400, 'success' => false, 'message' => 'Failed to update location']);
        }
    }

    /**
     * Soft delete delivery location
     *
     * POST /api/v1/store/locations/delete
     */
    public function delete()
    {
        if (!$this->check_auth()) return;
        $decoded = $this->authenticate();
        if (!$decoded) return;

        $id = $this->input->post('id', true);
        if (empty($id)) {
            $this->output(['status' => 400, 'success' => false, 'message' => 'Location ID is required']);
            return;
        }

        $location = $this->locations_model->get_by_id($id, $decoded->store_id);
        if (!$location) {
            $this->output(['status' => 404, 'success' => false, 'message' => 'Location not found']);
            return;
        }

        $data = [
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_by' => $decoded->staff_id
        ];

        $result = $this->common->update('delivery_locations', $data, ['id' => $id]);

        if ($result !== false) {
            $this->logger->info('Delivery location deleted', [
                'store_id' => $decoded->store_id,
                'location_id' => $id,
                'name' => $location->name
            ], 'store_locations');

            $this->output([
                'status' => 200,
                'success' => true,
                'message' => 'Location deleted successfully'
            ]);
        } else {
            $this->output(['status' => 400, 'success' => false, 'message' => 'Failed to delete location']);
        }
    }
}
