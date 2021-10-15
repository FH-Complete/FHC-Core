<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
abstract class FHC_Controller extends CI_Controller
{
	const FHC_CONTROLLER_ID = 'fhc_controller_id'; // name of the parameter used to identify uniquely a call to a controller

	private $_controllerId; // contains the unique identifier of a call to a controller

	/**
	 * Standard construct for all the controllers
	 * - initialize the object properties
	 * - loads the authentication system
	 * - loads all the helpers that later are always needed
	 */
	public function __construct()
	{
		parent::__construct();

		// NOTE: placed here before performing anything else!!!
		$this->_checkHTTPS();

		$this->_controllerId = null; // set _controllerId as null by default

		// Loads helper message to manage returning messages
		$this->load->helper('hlp_return_object');

		// Loads helper with generic utility function
		$this->load->helper('hlp_common');

		// Loads helper session to manage the php session
		$this->load->helper('hlp_session');

		// Loads language helper
		$this->load->helper('hlp_language');

		// Loads header helper
		$this->load->helper('hlp_header');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Wrapper to load phrases using the PhrasesLib
	 * NOTE: The library is loaded with the alias 'p', so must me used with this alias in the rest of the code.
	 *		EX: $this->p->t(<category>, <phrase name>)
	 */
	protected function loadPhrases($categories, $language = null)
	{
		$this->load->library('PhrasesLib', array($categories, $language), 'p');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 * Sets the unique id for the called controller
	 * NOTE: it is only working with HTTP GET request, not neeaded with POST
	 *		because the first call to the controller is via HTTP GET,
	 *		therefore a fhc_controller_id is already generated
	 */
	protected function setControllerId()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'GET')
		{
			$this->_controllerId = $this->input->get(self::FHC_CONTROLLER_ID);

			if (isEmptyString($this->_controllerId))
			{
				$this->_controllerId = uniqid(); // generate a unique id
				// Redirect to the same URL, but giving FHC_CONTROLLER_ID as HTTP GET parameter
				header(
					sprintf(
						'Location: %s%s%s=%s',
						$_SERVER['REQUEST_URI'],
						strpos($_SERVER['REQUEST_URI'], '?') === false ? '?' : '&', // place the corret character to divide parameters
						self::FHC_CONTROLLER_ID,
						$this->_controllerId
					)
				);
				exit; // terminate immediately the execution of this controller
			}
		}
	}

	/**
	 * Return the value of the property _controllerId
	 */
	protected function getControllerId()
	{
		return $this->_controllerId;
	}

	/**
	 * Utility method to output a success using JSON as content type
	 * Wraps the private method _outputJson
	 */
	protected function outputJsonSuccess($mixed)
	{
		$this->outputJson(success($mixed));
	}

	/**
	 * Utility method to output an error using JSON as content type
	 * Wraps the private method _outputJson
	 */
	protected function outputJsonError($mixed)
	{
		$this->outputJson(error($mixed));
	}

	/**
	 * Terminate the execution of the page after have printed a message encoded to JSON.
	 * Used directly header and echo to speed up the output before the exit command stops the execution.
	 */
	protected function terminateWithJsonError($message)
	{
		header('Content-Type: application/json');
		echo json_encode(error($message));
		exit;
	}

	/**
	 * Utility method to output using JSON as content type
	 */
	protected function outputJson($mixed)
	{
		$this->output->set_content_type('application/json')->set_output(json_encode($mixed));
	}
	
	protected function outputFile($fileObj)
	{
		if (file_exists($fileObj->file))
		{
			$finfo  = new finfo(FILEINFO_MIME);
			
			header('Content-Description: File Transfer');
			header('Content-Type: '. $finfo->file($fileObj->file));
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($fileObj->file));
			
			if (isset($fileObj->disposition) && ($fileObj->disposition == 'inline' || $fileObj->disposition == 'attachment'))
			{
				header('Content-Disposition: '. $fileObj->disposition. '; filename="'. $fileObj->name. '"');
			}
			
			readfile($fileObj->file);
			
			exit;
		}
		
		return false;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks if the call is performed via web and if HTTPS is enabled and used
	 * If NOT then an error is raised and the execution is terminated
	 */
	private function _checkHTTPS()
	{
		// If NOT called from command line and if the HTTPS protocol is NOT enabled
		if (!$this->input->is_cli_request() && !isset($_SERVER['HTTPS']))
		{
			show_error('This web site cannot work correctly without the HTTPS protocol enabled');
		}
	}
}
