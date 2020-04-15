<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
abstract class JOB_Controller extends CLI_Controller
{
	/**
	 * Constructor
	 */
    public function __construct()
	{
        parent::__construct();

		// Loads LogLib with different debug trace levels to get data of the job that extends this class
		// It also specify parameters to set database fields
		$this->load->library('LogLib', array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'job', // required
			'dbExecuteUser' => 'Cronjob system'
		));
	}

	//------------------------------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 * Writes a cronjob info log
	 */
	protected function logInfo($response, $parameters = null)
	{
		$this->_log(LogLib::INFO, 'Cronjob info', $response, $parameters);
	}

	/**
	 * Writes a cronjob debug log
	 */
	protected function logDebug($response, $parameters = null)
	{
		$this->_log(LogLib::DEBUG, 'Cronjob debug', $response, $parameters);
	}

	/**
	 * Writes a cronjob warning log
	 */
	protected function logWarning($response, $parameters = null)
	{
		$this->_log(LogLib::WARNING, 'Cronjob warning', $response, $parameters);
	}

	/**
	 * Writes a cronjob error log
	 */
	protected function logError($response, $parameters = null)
	{
		$this->_log(LogLib::ERROR, 'Cronjob error', $response, $parameters);
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
