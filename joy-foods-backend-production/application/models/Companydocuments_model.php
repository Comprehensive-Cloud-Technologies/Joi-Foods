<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Company Documents Model
 *
 * Handles database operations for company documents.
 *
 * @category  Models
 * @package   Joy_Foods
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-03-03
 */
class Companydocuments_model extends CI_Model
{
    /**
     * Get all active documents for a company
     *
     * @param int $company_id Company ID
     * @return array List of documents
     */
    public function get_all($company_id)
    {
        return $this->db
            ->select('*')
            ->from('company_documents')
            ->where('company_id', $company_id)
            ->where('deleted_at IS NULL', NULL, FALSE)
            ->order_by('created_at', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Get a single document by ID with client verification
     *
     * @param int $id        Document ID
     * @param int $client_id Client ID
     * @return object|null Document object or null
     */
    public function get_by_id($id, $client_id)
    {
        return $this->db
            ->select('*')
            ->from('company_documents')
            ->where('id', $id)
            ->where('client_id', $client_id)
            ->where('deleted_at IS NULL', NULL, FALSE)
            ->get()
            ->row();
    }

    /**
     * Get active document count for a company
     *
     * @param int $company_id Company ID
     * @return int Count of documents
     */
    public function get_count($company_id)
    {
        return $this->db
            ->where('company_id', $company_id)
            ->where('deleted_at IS NULL', NULL, FALSE)
            ->count_all_results('company_documents');
    }
}
