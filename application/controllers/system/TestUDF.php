<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class TestUDF extends VileSci_Controller 
{
	public function __construct()
    {
        parent::__construct();
        
        // Loads the widget library
		$this->load->library('WidgetLib');
    }
	
	/**
	 * 
	 */
	public function index()
	{
		$data = array();
		
		$this->load->view('system/testudf', $data);
	}
}