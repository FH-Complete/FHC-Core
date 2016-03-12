<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    function __construct()  
	{
        parent::__construct();
	}
}

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class API_Controller extends REST_Controller 
{
	function __construct()  
	{
        parent::__construct();
		//$this->load->library('session'); -> autoload
		//$this->load->library('database'); -> autoload
		
    }
 
}
