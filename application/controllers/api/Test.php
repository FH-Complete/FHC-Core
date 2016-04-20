<?php

defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

/**
 * Testing class for REST calls and authentication
 */
class Test extends REST_Controller
{
    public function __construct()
    {
		parent::__construct();
    }

	/**
	 * Test HTTP GET method
	 * It responses whith the HTTP status 200 and prints this JSON string
	 * {"success":true,"message":"API HTTP GET call test succeed"}
	 * 
	 * @return void
	 */
    public function getTest()
    {
		$payload = [
			'success' => TRUE,
			'message' => 'API HTTP GET call test succeed'
		];
		$httpstatus = REST_Controller::HTTP_OK;
		$this->response($payload, $httpstatus);
    }
    
    /**
	 * Test HTTP POST method
	 * * It responses whith the HTTP status 200 and prints this JSON string
	 * {"success":true,"message":"API HTTP POST call test succeed"}
	 * 
	 * @return void
	 */
    public function postTest()
    {
		$payload = [
			'success' => TRUE,
			'message' => 'API HTTP POST call test succeed'
		];
		$httpstatus = REST_Controller::HTTP_OK;
		$this->response($payload, $httpstatus);
    }
}