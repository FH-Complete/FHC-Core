<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FHC_Model extends CI_Model 
{
	function __construct()  
	{
        parent::__construct();
    }
}

class DB_Model extends FHC_Model 
{
	function __construct($uid=null)  
	{
        parent::__construct();
		$this->load->database();

		// UID must be set in Production Mode
		if (ENVIRONMENT=='production' && is_null($uid))
			log_message('error', 'UID must be set in Production Mode.');
		elseif (is_null($uid))
			log_message('info', 'UID is not set.');
		
		// Loading Tools for Access Control (Benutzerberechtigungen)
		$this->load->library('FHC_DB_ACL',array('uid' => $uid));
    }
}
