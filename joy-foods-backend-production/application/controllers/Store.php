<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Store extends CI_Controller
{

    public function index($store_id = null)
    {
        if($store_id == null){
            show_404();
        }

        redirect(config_item('guest_booking_web').$store_id);
    }
}
