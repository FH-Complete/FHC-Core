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
		echo json_encode(error($message)); // KEEP IT!!!
		exit;
	}

	/**
	 * Utility method to output using JSON as content type
	 */
	protected function outputJson($mixed)
	{
		$this->output->set_content_type('application/json')->set_output(json_encode($mixed));
	}

	/**
	 * To download the given file represented by the fileObj parameter.
	 * fileObj has the following structure:
	 * 	$fileObj->file OR $fileObj->file_content
	 * 	$fileObj->name
	 * 	$fileObj->mimetype
	 * 	$fileObj->disposition (inline OR attachment)
	 */
	protected function outputFile($fileObj)
	{
		// If the file exists
		if ((isset($fileObj->file) && !isEmptyString($fileObj->file) && file_exists($fileObj->file))
		|| (isset($fileObj->file_content) && !isEmptyString($fileObj->file_content)))
		{
			$content_length = 0;

			// If file content has been provided
			if (isset($fileObj->file_content) && !isEmptyString($fileObj->file_content))
			{
				$content_length = strlen($fileObj->file_content);
			}
			else // otherwise the path + name of the file
			{
				$content_length = filesize($fileObj->file);
			}

			header('Content-Description: File Transfer');
			header('Content-Type: '. $fileObj->mimetype);
			header('Content-Length: ' . $content_length);
			header('Content-Transfer-Encoding: binary');
			header('Content-Disposition: '. $fileObj->disposition. '; file_name="'. $fileObj->name. '"');
			header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
			header('Expires: ' . date("D, d M Y H:i:s", time()));
			header('Pragma: public');

			// Clean the output buffer
			flush();

			// If file content has been provided
			if (isset($fileObj->file_content) && !isEmptyString($fileObj->file_content))
			{
				echo $fileObj->file_content;
			}
			else // otherwise get the content from file system
			{
				readfile($fileObj->file);
			}

			// Terminate the execution
			exit();
		}

		// Otherwise return an error
		show_error('The provided file does not exist or file content is empty');
	}

	/**
	 *
	 */
	protected function outputImageByContent($mimetype, $file_content)
	{
		if (isEmptyString($file_content)) show_error('The provided file content is not valid');

		$this->_outputImage($mimetype, $file_content);
	}

	/**
	 *
	 */
	protected function outputImageByFile($mimetype, $file_name)
	{
		if (!file_exists($file_name)) show_error('The provided file does not exist');

		$this->_outputImage($mimetype, null, $file_name);
	}

	/**
	 * Return the JSON decoded HTTP POST request
	 * If the request is not in JSON format then a null value is returned
	 */
	protected function getPostJSON()
	{
		return json_decode($this->input->raw_input_stream);
	}

	/**
	 * Utility function to upload a file
	 * - post_field_name: the name of the field in the HTTP POST payload, this is also the index in the super global $_FILES array
	 * - allowed_types: a list of file extensions that are allowed to be uploaded (ex. array('pdf', 'odt') or array('jpg', 'jpeg', 'gif')
	 */
	protected function uploadFile($post_field_name, $allowed_types = array('*'))
	{
		$this->load->library(
			'upload',
			array(
				'upload_path' => sys_get_temp_dir(),
				'allowed_types' => $allowed_types,
				'overwrite' => true
			)
		);

		// If the upload was a success then return the uploaded file info
		if ($this->upload->do_upload($post_field_name)) return success($this->upload->data());

		// If an error occurred then return it without tags
		return error($this->upload->display_errors('', ''));
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

	/**
	 *
	 */
	private function _outputImage($mimetype, $file_content = null, $file_name = null)
	{
		$content_length = 0;

		// If file content has been provided
		if ($file_content != null)
		{
			$content_length = strlen($file_content);
		}
		else // otherwise the path + name of the file
		{
			$content_length = filesize($file_name);
		}

		header('Content-Type: '. $mimetype);
		header('Content-Length: ' . $content_length);
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header('Expires: ' . date("D, d M Y H:i:s", time()));

		// Clean the output buffer
		flush();

		// If file content has been provided
		if ($file_content != null)
		{
			echo $file_content;
		}
		else // otherwise get the content from file system
		{
			readfile($file_name);
		}

		// Terminate the execution
		exit();
	}
}

