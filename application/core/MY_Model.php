<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DB_Model extends CI_Model 
{
	function __construct()  
	{
        parent::__construct();
		$this->load->library('database');
		
    }

}
