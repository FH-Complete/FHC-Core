<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This is the super class for a job.
 * All the controllers that extends this class can only be called from command line.
 * Provides utility methods to log into database
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
		$this->load->library(
			'LogLib',
			array(
				'classIndex' => 5,
				'functionIndex' => 5,
				'lineIndex' => 4,
				'dbLogType' => 'job', // required
				'dbExecuteUser' => 'Cronjob system',
				'requestId' => 'JOB',
				'requestDataFormatter' => function($data) {
					return json_encode($data);
				}
			),
			'LogLibJob' // library alias case sensitive
		);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 * Writes a cronjob info log
	 */
	protected function logInfo($response, $parameters = null)
	{
		$this->_log(LogLib::INFO, $response, $parameters);
	}

	/**
	 * Writes a cronjob debug log
	 */
	protected function logDebug($response, $parameters = null)
	{
		$this->_log(LogLib::DEBUG, $response, $parameters);
	}

	/**
	 * Writes a cronjob warning log
	 */
	protected function logWarning($response, $parameters = null)
	{
		$this->_log(LogLib::WARNING, $response, $parameters);
	}

	/**
	 * Writes a cronjob error log
	 */
	protected function logError($response, $parameters = null)
	{
		$this->_log(LogLib::ERROR, $response, $parameters);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Writes a log to database
	 */
	private function _log($level, $response, $parameters)
	{
		$data = new stdClass();

		$data->response = $response;
		if ($parameters != null) $data->parameters = $parameters;

		switch($level)
		{
			case LogLib::INFO:
				$this->LogLibJob->logInfoDB($data);
				break;
			case LogLib::DEBUG:
				$this->LogLibJob->logDebugDB($data);
				break;
			case LogLib::WARNING:
				$this->LogLibJob->logWarningDB($data);
				break;
			case LogLib::ERROR:
				$this->LogLibJob->logErrorDB($data);
				break;
		}
	}
}

