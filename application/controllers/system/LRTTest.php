<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

use \stdClass as stdClass;

/**
 * 
 */
class LRTTest extends Auth_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'system/developer:r',
				'lrt1min' => 'system/developer:r',
			)
		);

		// Loads LongRunTaskLib library
		$this->load->library('LongRunTaskLib');
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Everything has a beginning
	 */
	public function index()
	{
		$this->load->view('system/lrtTest.php');
	}

	/**
	 *
	 */
	public function lrt1min()
	{
		$lrtInput = new stdClass();
		$lrtInput->sleep = 1; // Sleep for 1 min

		$this->outputJsonSuccess(
			$this->longruntasklib->addNewLrtToQueue(
				'LRTDummy', // LRT type
				getAuthUID(), // UID executer
				$lrtInput // LRT input
			)
		);
	}
}

