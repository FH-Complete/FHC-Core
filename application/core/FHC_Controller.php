<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FHC_Controller extends CI_Controller
{
    function __construct()  
	{
        parent::__construct();
		//$this->load->helper('language');
	}
}

require_once APPPATH . '/libraries/REST_Controller.php';

class APIv1_Controller extends REST_Controller 
{
	function __construct()  
	{
        parent::__construct();
		//$this->load->library('session'); -> autoload
		//$this->load->library('database'); -> autoload
    }
 
}
