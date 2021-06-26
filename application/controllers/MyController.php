<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MyController extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->model('Main_model');
		$this->load->helper('url');
    
    }

	public function index()
	{
		echo( "Ok ...");
	}
	public function set_employee()
	{
		$this->load->view('add_employee');
	}
	public function add_employee_db()
	{
		$this->load->view('add_employee');
		if (isset($_POST['id'])) {
            $return_id = $this->Main_model->add_update_record('employee', $_POST, 'id');
        } else {
			$return_id = $this->Main_model->add_update_record('employee', $_POST);
		}

		header('Location: ' . base_url() . 'index.php/MyController');
	}
}
