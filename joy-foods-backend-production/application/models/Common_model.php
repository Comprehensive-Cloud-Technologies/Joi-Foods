<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model
{

    public function insert(array $details, $table)
    {
        if ($this->db->insert($table, $details)) {
            $id = $this->db->insert_id();
            return $id ? $id : true;
        } else {
            return false;
        }
    }

    public function delete($id, $table)
    {
        $this->db->where('id', $id);
        $this->db->delete($table);
        return $this->db->affected_rows();
    }

    public function update($table_name, $data, $where)
    {
        $this->db->update($table_name, $data, $where);
        return $this->db->affected_rows();
    }



    // Get Review Details
    public function getnumrows($tableName, array $where)
    {
        $this->db->select("*");
        $this->db->from($tableName);
        if (!empty($where)) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        }

        $return = $this->db->get()->num_rows();

        // echo $this->db->last_query();
        return $return;
    }


    public function getdatabytable($tableName, array $where = null, $index = 'id', $order = 'ASC')
    {
        $this->db->select("*");
        $this->db->from($tableName);
        if ($where != null) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        }

        $this->db->order_by($index, $order);
        $return = $this->db->get()->row();

        return $return;
    }

    public function getdatabytableBinary($tableName, array $where = null)
    {
        $this->db->select("*");
        $this->db->from($tableName);
        if ($where != null) {
            foreach ($where as $key => $value) {
                // Use BINARY keyword to make the comparison case-sensitive
                $this->db->where("BINARY `$key` =", $value, false);
            }
        }

        return $this->db->get()->row();
    }


    public function getdatabytableall($tableName, array $where = null, $index = 'id', $order = 'ASC')
    {
        $this->db->select("*");
        $this->db->from($tableName);
        if ($where != null) {
            foreach ($where as $key => $value) {
                $this->db->where($key, $value);
            }
        }

        $this->db->order_by($index, $order);
        $return = $this->db->get()->result();

        return $return;
    }


    public function deleteWhere($tableName, array $where)
    {
        foreach ($where as $key => $value) {
            $this->db->where($key, $value);
        }
        $this->db->delete($tableName);
        return $this->db->affected_rows();
    }

    public function check_admin(array $post_data)
    {
        $sql = "select * from admin where (phone_number = ? or email = ?) and password = ?";
        $result = $this->db->query($sql, array(
            $post_data['username'],
            $post_data['username'],
            md5($post_data['userpassword'])
        ))->row();
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }


    public function check_client(array $post_data)
    {
        $sql = "SELECT * FROM client_users WHERE (phone = ? OR email = ?) AND is_active = 1 AND deleted_at IS NULL";
        $result = $this->db->query($sql, array(
            $post_data['username'],
            $post_data['username']
        ))->row();

        if ($result && password_verify($post_data['userpassword'], $result->password_hash)) {
            return $result;
        } else {
            return false;
        }
    }

    public function check_company(array $post_data)
    {
        $sql = "SELECT cu.*, c.name as company_name, c.company_code, c.is_active as company_active
                FROM company_users cu
                INNER JOIN companies c ON cu.company_id = c.id
                WHERE (cu.phone = ? OR cu.email = ?)
                AND cu.is_active = 1
                AND cu.deleted_at IS NULL
                AND c.is_active = 1
                AND c.deleted_at IS NULL";
        $result = $this->db->query($sql, array(
            $post_data['username'],
            $post_data['username']
        ))->row();

        if ($result && password_verify($post_data['userpassword'], $result->password_hash)) {
            return $result;
        } else {
            return false;
        }
    }
}
