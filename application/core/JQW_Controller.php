<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 *
 */
abstract class JQW_Controller extends JOB_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads LogLib with different ...
		$this->load->library('LogLib', array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'job', // required
			'dbExecuteUser' => 'Jobs queue system'
		));

		// Loads JobsQueueLib
		$this->load->library('JobsQueueLib');
	}

	// ------------------------------------------------------------------------------------------------------------
	// Protected methods to read/write the jobs queue

	/**
	 *
	 */
	protected function getJobsByType($jobType)
	{
		$jobs = $this->jobsqueuelib->getJobsByType($jobType);

		if (isError($jobs)) $this->logError(getError($jobs), $jobType);

		return $jobs;
	}

	/**
	 *
	 */
	protected function addNewJobsToQueue($jobType, $jobs)
	{
		$result = $this->jobsqueuelib->addNewJobsToQueue($jobType, $jobs);

		if (isError($result)) $this->logError(getError($result), $jobType);

		return $result;
	}
}
