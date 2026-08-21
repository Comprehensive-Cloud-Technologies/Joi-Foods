<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Clients_model extends CI_Model
{
    public function users(){
        $this->db->select('client_users.*, clients.name as client_name, clients.client_code');
        $this->db->from('client_users');
        $this->db->join('clients', 'clients.id = client_users.client_id', 'left');
        $this->db->where('client_users.deleted_at', NULL);
        $this->db->order_by('client_users.id', 'DESC');
        return $this->db->get()->result();
    }


    public function check_email_exists($client_id, $email, $exclude_user_id = null){
        $this->db->where('client_id', $client_id);
        $this->db->where('email', $email);
        if($exclude_user_id){
            $this->db->where('id !=', $exclude_user_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->get('client_users')->row();
    }
}