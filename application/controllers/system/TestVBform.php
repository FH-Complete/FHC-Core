<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Test VBform Vue Component
 */
class TestVBform extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'system/developer:r'
			)
		);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Everything has a beginning
	 */
	public function index()
	{
		$this->load->view('system/logs/testVBform.php');
	}
}
