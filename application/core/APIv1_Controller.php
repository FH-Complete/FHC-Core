<?php

require_once APPPATH . '/libraries/REST_Controller.php';

class APIv1_Controller extends REST_Controller 
{
	function __construct()  
	{
        parent::__construct();
		//$this->load->library('session'); // -> autoload
		//$this->load->library('database'); -> autoload
    }
}