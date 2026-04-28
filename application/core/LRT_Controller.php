<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Long Run Task
 *
 * This controller acts as interface of the LongRunTaskLib that contains
 * all the needed functionalities to operate with the Long Run Task system
 * that is built on top of the Jobs Queue System
 * This is an abstract class that provide basic functionalities,
 * it has to be extended to broaden its logic
 */
abstract class LRT_Controller extends JQW_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Changes the needed configs for LogLib
		$this->LogLibJob->setConfigs(
			array(
				'dbExecuteUser' => 'LRTs queue system',
				'requestId' => 'LTR'
			)
		);

		// Loads LongRunTaskLib library
		$this->load->library('LongRunTaskLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 * Get the oldest added LRTs to the queue having status = new
	 */
	protected function getLRTs()
	{
		$lrts = $this->longruntasklib->getLRTs();

		// If an error occurred then log it in database
		if (isError($lrts)) $this->logError(getError($lrts));

		return $lrts;
	}

	/**
	 *
	 */
	protected function addNewLRTsToQueue($type, $lrts)
	{
		$result = $this->longruntasklib->addNewLRTsToQueue($type, $lrts);

		// If an error occurred then log it in database
		if (isError($result)) $this->logError(getError($result), $type);

		return $result;
	}

	/**
	 * Utility method to generate a job with the given parameters and return it inside an array
	 * ready to be used by addNewJobsToQueue and updateJobsQueue
	 */
	protected function generateJobs($status, $input)
	{
		return JobsQueueLib::generateJobs($status, $input);
	}
}

