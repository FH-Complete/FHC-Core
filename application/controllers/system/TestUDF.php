<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class TestUDF extends VileSci_Controller 
{
	public function __construct()
    {
        parent::__construct();
        
        // Loads the widget library
		$this->load->library('WidgetLib');
		
		// 
		$this->load->model('person/Person_model', 'PersonModel');
    }
	
	/**
	 * 
	 */
	public function index()
	{
		$person_id = $this->input->get('person_id');
		
		$person = $this->PersonModel->load($person_id);
		
		$data = array(
			'udfs' => $this->PersonModel->getUDFs()
		);
		
		$this->load->view('system/testudf', $data);
	}
}