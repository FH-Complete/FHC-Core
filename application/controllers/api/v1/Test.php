<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Testing class for REST calls and authentication
 */
class Test extends APIv1_Controller
{
    public function __construct()
    {
		parent::__construct();
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
