<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model 
{
	function __construct()  
	{
        parent::__construct();
    }
}

class DB_Model extends MY_Model 
{
	function __construct($uid=null)  
	{
        parent::__construct();
		$this->load->database();
		// Loading Tools for Access Control (Benutzerberechtigungen)
		$this->load->library('FHC_DB_ACL',array('uid' => $uid));
    }

}
