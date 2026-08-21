<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Zbit_error extends CI_Controller
{
    public function page_not_found()
    {
        $data404['heading'] = '404 Page Not Found';
        $data404['message'] = '<p>The page you requested was not found.</p>';

        $this->output->set_status_header('404');
        $uri_segments = $this->uri->segments;
        if (@$uri_segments[1] == 'api') {
            header("Content-Type: application/json; charset=UTF-8");
            $response = array(
                'status'    => 404,
                'message'   => 'API not found'
            );
            echo json_encode($response);
            die;
        } else {
            $this->load->view('errors/html/error_404', $data404);
        }
    }
}
