<?php

class Employee extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('main_model');
        $this->load->library('custom');
        $this->check_permission->load_user_acl($_SESSION['pms_role']);
    }

    //---------VIEW ADD EMPLOYEE PAGE
    public function add() {
        $this->load->view('add_employee');
    }

    //---------VIEW ADD EMPLOYEE PAGE ENDS
    //--------------------Fill Manager-------------------
    public function fill_manager() {
//        print_r($_POST);
//        die;
        $data = array();
        $id = $_POST['role_id'];
        $role_parent_id = $this->main_model->get_name_from_id('roles', "parent_id", 'id', $id);
//      echo '<pre>';print_r($role_parent_id); die;
        if ($role_parent_id > 0) {
            $filters[0]['id'] = "role";
            $filters[0]['value'] = $role_parent_id;
            $req_fields1 = array('name');
            $employee_id = $this->main_model->get_many_records("users", $filters, $req_fields1, '');
            $i = 0;
            foreach ($employee_id as $value1) {
                $filters1[0]['id'] = "employee";
                $filters1[0]['value'] = $value1['name'];
                if (!empty($_POST['region_id'])) {
                    $filters1[1]['id'] = "region";
                    $filters1[1]['value'] = $_POST['region_id'];
                }
                $req_fields2 = array('employee');
                $region_detail[$i] = $this->main_model->get_many_records("employee-region", $filters1, $req_fields2, '');
                $i++;
            }
            foreach ($region_detail as $kye => $value2) {
                foreach ($value2 as $val) {
                    $emp_names[$val['employee']] = $this->main_model->get_name_from_id("employee", "name", "id", $val['employee']);
                }
            }
            if(!empty($emp_names)){
            $j = 0;
            foreach ($emp_names as $key => $valnew) {
                $data[$j]['id'] = $key;
                $data[$j]['name'] = $valnew;
                $j++;
            }
            }
        }
        echo json_encode($data);
    }

    //--------------------End Fill Manager---------------
    //---------------------------------------FILL EMPLOYEE REGION---------------
    public function fill_employee_region() {
//        echo '<pre>'; print_r($_POST); die;
        $filters[0]['id'] = "employee";
        $filters[0]['value'] = $_POST['emp_id'];
        $req_fields = array('region');
        $region_id=$this->main_model->get_many_records("employee-region", $filters, $req_fields, '');
        foreach ($region_id as $kye => $val){
            $region_names[$val['region']] = $this->main_model->get_name_from_id("region", "name", "id", $val['region']);
        }
        if(!empty($region_names)){
            $j = 0;
            foreach ($region_names as $key => $valnew) {
                $data[$j]['id'] = $key;
                $data[$j]['name'] = $valnew;
                $j++;
            }
            }
            echo json_encode($data);
    }
    //--------------------------------------END FILL EMPLOYEE REGION------------
    //----------------Fill Base Office Location----------------------------------
    public function fill_base_office() {
        $result=array();
        $filters[0]['id'] = "id";
        $filters[0]['value'] = $_POST['emp_region'];
        $req_fields = array('base_office');
        $value['base_office']=$this->main_model->get_many_records("region", $filters, $req_fields, '');
        $filters1[0]['id'] = "region";
        $filters1[0]['value'] = $_POST['emp_region'];
        $filters1[1]['id'] = "role";
        $filters1[1]['value'] = $_POST['emp_role'];
        $req_fields1 = array('da_amount');
        $value['da_amount']=$this->main_model->get_many_records("da_details", $filters1, $req_fields1, '');
        //print_r($value); die;
        if (!empty($value['base_office'])) {
            $result[0]['base_name']=$value['base_office'][0]['base_office'];
            $result[1]['da']=$value['da_amount'][0]['da_amount'];
        }
        else{
            $result[0]['base_name']='Please Select Region';
            $result[1]['da']='Please Select Role';
        }
        echo json_encode($result);
    }
    //---------------End Fill Base Office location------------------------------
    //---------INSERT EMPLOYEE
    public function set($table = '') {//for inserting/updating database
//        echo '<pre>';print_r($_POST);        die;
        $region = $_POST['region'];
        $role = $_POST['role'];
        $password = $_POST['password'];
        $confpass = $_POST['confirm_password'];
        $email = $_POST['email1'];
//        $validation = $this->main_model->check_email_exist($email);
//        if ($validation == 1) {
//            $data['message'] = 'Email already exist !!!';
//            $this->load->view('add_employee', $data);
//        } else {
        unset($_POST['region']);
        unset($_POST['role']);
        unset($_POST['password']);
        unset($_POST['confirm_password']);
        $multiple_table = 0;
        if ((isset($_POST['child'][0]['table'])) || (isset($_POST['child'][1]['table']))) {//handling secondary table data
            $multiple_table = 1;
            foreach ($_POST['children'] as $key => $table_data) {
                $records[$key]['data'] = $table_data;
                $records[$key]['table'] = $_POST['child'][$key]['table'];
                $records[$key]['foreign_id'] = $_POST['child'][$key]['foreign_id'];
            }//echo '<pre>';var_dump($records);die;
            unset($_POST['child']);
            unset($_POST['children']);
        }
//         echo '<pre>';print_r($_POST);        die;
        if (isset($_POST['id'])) {//sending different parameters for update and insert
            $return_id = $this->main_model->add_update_record('employee', $_POST, 'id');
        } else {


            $validation = $this->main_model->check_email_exist($email);
            if ($validation == 1) {
//                    $data['message'] = 'Email already exist !!!';
//                     $_SESSION['pms_msg_hdr'] = "Information !";
                $_SESSION['msg_str'] = "This Email id is already registered. Email Should be Unique.";
                die(header('Location: ' . base_url() . 'employee/add'));
            } else {

                $return_id = $this->main_model->add_update_record('employee', $_POST);
            }
        }
        //=======================EMPLOYEE REGION=============================
        $employee_region['employee'] = $return_id;
        $employee_region['region'] = $region;

        $employee_data = $this->main_model->get_records_from_id("employee-region", $return_id, "employee", '*');
        if (!empty($employee_data)) {
            $this->main_model->add_update_record("employee-region", $employee_region, "employee");
        } else {
            $employee_region['created_by'] = $_SESSION['pms_user_id'];
            $employee_region['created_date'] = $_POST['created_date'];
            $this->main_model->add_update_record("employee-region", $employee_region);
        }
        //====================================================
        //=====================EMPLOYEE CREDENTIAL==============================
        $employee_user['name'] = $return_id;
        $employee_user['role'] = $role;
        $employee_user['email'] = $email;
        if ($password == $confpass) {
            $employee_user['password'] = md5($password);
            $users_data = $this->main_model->get_records_from_id("users", $return_id, "name", '*');
            if (!empty($users_data)) {
                $this->main_model->add_update_record("users", $employee_user, "name");
            } else {

                $employee_user['created_by'] = $_SESSION['pms_user_id'];
                $employee_user['created_date'] = $_POST['created_date'];
                $this->main_model->add_update_record("users", $employee_user);
            }
        } else {
            header('Location: ' . base_url() . 'employee/add?msg=Password Not Match');
        }


        //=======================PHOTO UPLOAD=============================
        $id = trim($return_id);

        if ($_FILES['photo_file']['size']) {
            $extn = end(explode(".", $_FILES['photo_file']['name']));
            $config['upload_path'] = './assets/images/uploads/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp';
            $config['max_size'] = '2000'; //size upto 2MB
            $config['overwrite'] = True;
            $config['file_name'] = 'employee' . '-' . $id . '-' . 'photo' . '.' . $extn;
            $this->load->library('upload');
            $this->upload->initialize($config);
            $file_name = $this->main_model->get_name_from_id('employee', "photo_file", $id);
            $path = $config['upload_path'] . $file_name;
            if (file_exists($path) && $file_name) {
                unlink($path) or die('failed deleting: ' . $path);
            }
            if (!$this->upload->do_upload('photo_file')) {
                $error = array('error' => $this->upload->display_errors());
                die(var_dump($error));
            } else {
                $upload_return = array('upload_data' => $this->upload->data());
            }
            $data['id'] = $id;
            $data['photo_file'] = 'employee' . '-' . $id . '-' . 'photo' . '.' . $extn;
            $id = $this->main_model->add_update_record('employee', $data, 'id');
        }
        //================================++SIGNATURE UPLOAD ================
        if ($_FILES['signature_file']['size']) {
            $extn = end(explode(".", $_FILES['signature_file']['name']));
            $config['upload_path'] = './assets/images/uploads/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp';
            $config['max_size'] = '2000'; //size upto 2MB
            $config['overwrite'] = True;
            $config['file_name'] = 'employee' . '-' . $id . '-' . 'sign' . '.' . $extn;
            $this->load->library('upload');
            $this->upload->initialize($config);
            $file_name = $this->main_model->get_name_from_id('employee', "signature_file", $id);
            $path = $config['upload_path'] . $file_name;
            if (file_exists($path) && $file_name) {
                unlink($path) or die('failed deleting: ' . $path);
            }
            if (!$this->upload->do_upload('signature_file')) {
                $error = array('error' => $this->upload->display_errors());
                die(var_dump($error));
            } else {
                $upload_return = array('upload_data' => $this->upload->data());
            }

            $data['id'] = $id;
            $data['signature_file'] = 'employee' . '-' . $id . '-' . 'sign' . '.' . $extn;
            $id = $this->main_model->add_update_record('employee', $data, 'id');
        }
//        -------------------------------------------------------------------------------------------
        if ($multiple_table) {//for updating records in child table with foreign key
            //echo '<pre>';var_dump($records);die;
            foreach ($records as $tables) {
                $this->main_model->add_update_many_records($tables['table'], $tables['data'], $tables['foreign_id'], $return_id);
            }
        }
        header('Location: ' . base_url() . 'employee/manage');
    }

    //---------INSERT EMPLOYEE ENDS
    //---------MANAGE EMPLOYEE
    public function manage($id = '') {
        $req_fields = array("id", "name", "phone1", "email1");
        $emp_data['data'] = $this->main_model->get_many_records("employee", '', $req_fields, "id");
        foreach ($emp_data['data'] as $key => $value) {
            $role_id = $this->main_model->get_name_from_id('users', 'role', "name", $value['id']);
            if (!empty($role_id)) {
                $value['role'] = $this->main_model->get_name_from_id('roles', 'name', "id", $role_id);
            } else {
                $value['role'] = 'Not Assigned';
            }
            $emp_data['data'][$key] = $value;
        }
        $this->load->view('manage_employee', $emp_data);
    }

    //---------MANAGE EMPLOYEE ENDS
    //---------EDIT EMPLOYEE
    public function edit($id = 0) {//for add/update form
        $row_data = array();
        $query = $this->main_model->get_records('employee', 'id', $id);
        $row_data = $query[0];
        $employee_array = (array) $row_data;

        $region_id = $this->main_model->get_name_from_id('employee-region', 'region', "employee", $employee_array['id']);
        $employee_array['region'] = $region_id;

        $role_id = $this->main_model->get_name_from_id('users', 'role', "name", $employee_array['id']);
        $employee_array['role'] = $role_id;
        //   echo '<pre>';
//        print_r($employee_array); die;
        $this->load->view('edit_employee', $employee_array);
    }

    //---------EDIT EMPLOYEE ENDS
    //---------DELETE EMPLOYEE
    public function delete($id = 0, $child_table = "") {//$id is value of primary key "id" to be deleted, - $foreign_key is the foreign key name in the child table
        $this->main_model->my_delete_record('employee', 'id', $id);
        header('Location: ' . base_url() . 'employee/manage');
    }

    //---------DELETE EMPLOYEE ENDS
   
}
?>
