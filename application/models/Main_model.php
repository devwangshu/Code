<?php

class Main_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    function add_update_record($tbl, $data = array(), $id_name = '') {//$id_name for update record, id_name field must be present in associative $data array
        if (count($data)) {
            unset($data['submit']);
            $this->db->set($data); //passing associative array to values in SET sql query
            if ($id_name) {//updating record on getting id name - id value is sent through form
                $this->db->where($id_name, $data[$id_name]);
                $query = $this->db->update($tbl);
                return $data[$id_name];
            } else {//adding record in table
                // $data['created_date'] = now();
                $this->db->set($data);
                $query = $this->db->insert($tbl);
                return $this->db->insert_id(); //autoincreament id after insert query
            }
        }
    }

    

   
    function delete($table, $data) {
        $this->db->delete($table, $data);
    }


    public function get_records($table, $id_name = '', $id_value = 0, $orderby = '') { //for orderby field : 'title desc, name asc'
        $this->db->where('is_deleted', 0);
        if ($id_name) {
            $this->db->where($id_name, $id_value);
        }
        if ($orderby) {
            $this->db->order_by($orderby);
        }
        $query = $this->db->get($table);
        return $query->result_array();
    }


}
?>
