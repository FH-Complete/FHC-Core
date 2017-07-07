<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class UDF extends VileSci_Controller 
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
		
		$udfs = $this->PersonModel->getUDFs();
		
		$udfs['person_id'] = 1;
		$udfs['prestudent_id'] = 1;
		$udfs['caller'] = 'system/UDF?person_id=1';
		
		$data = array(
			'udfs' => $udfs
		);
		
		$this->load->view('system/udf', $data);
	}
}