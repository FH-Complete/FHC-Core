<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class CallerLibrary extends APIv1_Controller
{
	/**
	 * API constructor
	 */
	public function __construct()
	{
		parent::__construct(array('Call' => 'admin:rw'));

		// Loads the CallerLib
		$this->load->library('CallerLib');
	}

	/**
	 * Manages a HTTP get call
	 */
	public function getCall()
	{
		// Start me up!
		$result = $this->callerlib->callLibrary($this->get());

		// Print the result
		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function postCall()
	{
		// Start me up!
		$result = $this->callerlib->callLibrary($this->post());

		// Print the result
		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function putCall()
	{
		// Start me up!
		$result = $this->callerlib->callLibrary($this->put());

		// Print the result
		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function deleteCall()
	{
		// Start me up!
		$result = $this->callerlib->callLibrary($this->delete());

		// Print the result
		$this->response($result, REST_Controller::HTTP_OK);
	}
}
