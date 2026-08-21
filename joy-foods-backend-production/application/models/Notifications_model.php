<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Notifications Model
 *
 * Handles database operations for in-app notifications.
 *
 * @category  Models
 * @package   Joy_Foods
 * @author    ZooBit Infotech <contact@zoobitinfotech.com>
 * @copyright 2025 Joy Foods. All rights reserved.
 * @license   Proprietary
 * @developed_by ZooBit Infotech for Joy Foods.
 * @version   1.0.0
 * @since     2026-02-24
 */
class Notifications_model extends CI_Model
{
    /**
     * Create a new notification
     *
     * @param array $data Notification data
     * @return int Insert ID
     */
    public function create($data)
    {
        $this->db->insert('notifications', $data);
        return $this->db->insert_id();
    }

    /**
     * Get paginated notifications for an employee
     *
     * @param int $employee_id Employee ID
     * @param int $limit       Items per page
     * @param int $offset      Offset
     * @return array List of notifications
     */
    public function get_list($employee_id, $limit = 20, $offset = 0)
    {
        return $this->db
            ->select('id, type, title, message, order_id, order_number, module, data, created_at')
            ->from('notifications')
            ->where('employee_id', $employee_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    /**
     * Get total notification count for an employee
     *
     * @param int $employee_id Employee ID
     * @return int Total count
     */
    public function get_count($employee_id)
    {
        return $this->db
            ->where('employee_id', $employee_id)
            ->count_all_results('notifications');
    }
}
