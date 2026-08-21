<?php
//Jai Sree Ram
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Miscellaneous Controller
 * 
 * Handles miscellaneous operations for the Joy Foods application.
 *
 * @category  Controllers
 * @package   Joy_Foods_Auth
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2025-12-01
 */
class Misc extends CI_Controller
{
    private $tokenHandler;
    private $logger;


    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model', 'common');
        $this->load->model('Employees_model', 'employees');
        $this->tokenHandler = new TokenHandler();

        // Load Monolog library for logging
        $this->load->library('monolog');
        $this->logger = new Monolog();

    }


    private function output($data)
    {
        header("Content-Type: application/json; charset=UTF-8");
        if(isset($data['status'])){
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



    public function config(){
        // Check API authorization
        if (!$this->check_auth()) {
            return;
        }

        $this->logger->info('Config API called', [
            'ip' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ], 'misc_config');


        //latest ios version code.
        $data['ios_version'] = 1;
        $data['ios_version_code'] = 1;
        $data['ios_version_name'] = '1.0.0';
        $data['ios_version_url'] =  config_item('store_links')['ios'];

        //latest android version code.
        $data['android_version'] = 1;
        $data['android_version_code'] = 1;
        $data['android_version_name'] = '1.0.0';
        $data['android_version_url'] = config_item('store_links')['android'];


        //need to check force ios and android update.
        $data['force_ios_update'] = true;
        $data['force_android_update'] = true;


        //legal pages links
        $data['privacy_policy'] = base_url('legal/privacy_policy');
        $data['terms_and_conditions'] = base_url('legal/terms_and_conditions');
        $data['refunds_cancellations_policy'] = base_url('legal/refunds_cancellations_policy');
        $data['about_us'] = base_url('legal/about_us');


        $response = array(
            'status'    => 200,
            'message'   => 'Success.',
            'data'      => $data
        );

        $this->output($response);
    }
}