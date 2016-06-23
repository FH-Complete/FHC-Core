<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class FHC_Controller extends CI_Controller
{
	protected $_uid; 
	
    public function __construct()  
	{
        parent::__construct();
		$this->load->library('session');
		$this->load->helper('fhcauth');
		
		$this->_uid = getAuthUID();
	}

	public function getUID()
	{
		if (empty($this->_uid))
			return false;
		else
			return $this->_uid;
	}

}
