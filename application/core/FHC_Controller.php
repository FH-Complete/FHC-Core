<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FHC_Controller extends CI_Controller
{
	/**
	 * Standard construct for all the controllers, loads the authentication system
	 */
    public function __construct()
	{
        parent::__construct();
		
		$this->load->helper('fhcauth');
	}
}
