<?php

require_once APPPATH.'/libraries/REST_Controller.php';

class APIv1_Controller extends REST_Controller
{
	/**
	 * Standard constructor for all the RESTful resources
	 */
    public function __construct()
    {
        parent::__construct();
        
        // Loads return messages
		$this->load->helper('message');
		
		log_message('debug', 'Called API: '.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
    }
}
