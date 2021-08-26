<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Extends the RESTFul_Controller and performs authentication
 */
abstract class API_Controller extends RESTFul_Controller
{
	private $_requiredPermissions;

	/**
	 * Standard constructor for all the RESTful resources
	 */
    public function __construct($requiredPermissions)
    {
        parent::__construct();

		$this->_requiredPermissions = $requiredPermissions;

		// Loads LogLib with different debug trace levels to get data of the job that extends this class
		// It also specify parameters to set database fields
		$this->load->library('LogLib', array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'API', // required
			'dbExecuteUser' => 'RESTful API'
		));
    }

	/**
	 * This method is automatically called by CodeIgniter after the execution of the constructor is completed
	 * - Cheks if the Authlib was loaded, if not it means that the authentication failed
	 * - Loads the permsission lib and calls permissionlib->isEntitled
	 * - Checks if the caller is allowed to access to this content with the given permissions
	 *	 if it is not allowed will set the HTTP header with code 401
	 * - Calls the parent (REST_Controller) _remap method to performs other checks
	 * NOTE: this methods override the parent method!!!
	 */
	public function _remap($object_called, $arguments = [])
	{
		if (isset($this->authlib)) // if set then the authentication is ok
		{
			// Loads permission lib
			$this->load->library('PermissionLib');

			// Cheks if the user has the permission to call a method
			if (!$this->permissionlib->isEntitled($this->_requiredPermissions, $this->router->method))
			{
				// If not...
				$this->response(error('You are not allowed to access to this content'), REST_Controller::HTTP_UNAUTHORIZED);
			}
		}

		// Finally calls the parent _remap to perform other checks
		parent::_remap($object_called, $arguments);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 * Writes a REST call info log
	 */
	protected function logInfo($response, $parameters = null)
	{
		$this->_log(LogLib::INFO, 'RESTful API info', $response, $parameters);
	}

	/**
	 * Writes a REST call debug log
	 */
	protected function logDebug($response, $parameters = null)
	{
		$this->_log(LogLib::DEBUG, 'RESTful API debug', $response, $parameters);
	}

	/**
	 * Writes a REST call warning log
	 */
	protected function logWarning($response, $parameters = null)
	{
		$this->_log(LogLib::WARNING, 'RESTful API warning', $response, $parameters);
	}

	/**
	 * Writes a REST call error log
	 */
	protected function logError($response, $parameters = null)
	{
		$this->_log(LogLib::ERROR, 'RESTful API error', $response, $parameters);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Writes a log to database
	 */
	private function _log($level, $requestId, $response, $parameters)
	{
		$data = new stdClass();

		$data->response = $response;
		if ($parameters != null) $data->parameters = $parameters;

		switch($level)
		{
			case LogLib::INFO:
				$this->loglib->logInfoDB($requestId, json_encode(success($data, LogLib::INFO)));
				break;
			case LogLib::DEBUG:
				$this->loglib->logDebugDB($requestId, json_encode(success($data, LogLib::DEBUG)));
				break;
			case LogLib::WARNING:
				$this->loglib->logWarningDB($requestId, json_encode(error($data, LogLib::WARNING)));
				break;
			case LogLib::ERROR:
				$this->loglib->logErrorDB($requestId, json_encode(error($data, LogLib::ERROR)));
				break;
		}
	}
}
