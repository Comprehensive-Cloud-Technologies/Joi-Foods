<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Storedocuments_model extends CI_Model
{
    /**
     * Get all active documents for a store
     */
    public function get_all($store_id)
    {
        return $this->db
            ->select('*')
            ->from('store_documents')
            ->where('store_id', $store_id)
            ->where('deleted_at IS NULL', NULL, FALSE)
            ->order_by('created_at', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Get a single document by ID with client verification
     */
    public function get_by_id($id, $client_id)
    {
        return $this->db
            ->select('*')
            ->from('store_documents')
            ->where('id', $id)
            ->where('client_id', $client_id)
            ->where('deleted_at IS NULL', NULL, FALSE)
            ->get()
            ->row();
    }

    /**
     * Get active document count for a store
     */
    public function get_count($store_id)
    {
        return $this->db
            ->where('store_id', $store_id)
            ->where('deleted_at IS NULL', NULL, FALSE)
            ->count_all_results('store_documents');
    }
}
