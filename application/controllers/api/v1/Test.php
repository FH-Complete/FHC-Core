<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'/libraries/REST_Controller.php';

/**
 * Testing class for REST calls and authentication
 */
class Test extends REST_Controller
{
    public function __construct()
    {
		parent::__construct();

		// Loads helper message to manage returning messages
		$this->load->helper('message');
    }

	/**
	 * Test HTTP GET method
	 */
    public function getTest()
    {
		$this->response(success('API HTTP GET call test succeed'), REST_Controller::HTTP_OK);
    }

    /**
	 * Test HTTP POST method
	 */
    public function postTest()
    {
		$this->response(success('API HTTP POST call test succeed'), REST_Controller::HTTP_OK);
    }
}
