<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Legal extends CI_Controller
{

    public function privacy_policy()
    {
      $this->load->view('legal/privacy_policy');
    }


    public function terms_and_conditions()
    {
        $this->load->view('legal/terms_and_conditions');
    }

    public function refunds_cancellations_policy()
    {
        $this->load->view('legal/refund_policy');
    }

    public function about_us()
    {
        $this->load->view('legal/about_us');
    }
}
