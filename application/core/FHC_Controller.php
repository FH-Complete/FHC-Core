<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class FHC_Controller extends CI_Controller
{
	public $uid;
	
    function __construct()  
	{
        parent::__construct();
		$this->load->library('session');
		//$this->load->helper('language');
		
		// look if User is logged in and set uid
		if (isset($_SERVER['PHP_AUTH_USER']))
			$this->uid = $_SERVER['PHP_AUTH_USER'];
		if (isset($_SESSION['uid']))
			$this->uid = $_SESSION['uid'];
		$this->session->set_userdata('uid', 'pam');
	}
}
