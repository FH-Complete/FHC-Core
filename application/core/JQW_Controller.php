<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Job Queue Worker
 *
 * This controller acts as interface of the JobsQueueLib that contains
 * all the needed functionalities to operate with the Jobs Queue System
 * This is an abstract class that provide basic functionalities,
 * it has to be extended to broaden its logic
 */
abstract class JQW_Controller extends JOB_Controller
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
				'dbExecuteUser' => 'Jobs queue system',
				'requestId' => 'JQW'
			)
		);

		// Loads JobsQueueLib library
		$this->load->library('JobsQueueLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 * To get all the most recently added jobs using the given job type
	 */
	protected function getLastJobs($type)
	{
		$jobs = $this->jobsqueuelib->getLastJobs($type);

		// If an error occurred then log it in database
		if (isError($jobs)) $this->logError(getError($jobs), $type);

		return $jobs;
	}

	/**
	 * To get the oldest added job using the given job type
	 */
	protected function getOldestJob($type)
	{
		$jobs = $this->jobsqueuelib->getOldestJob($type);

		// If an error occurred then log it in database
		if (isError($jobs)) $this->logError(getError($jobs), $type);

		return $jobs;
	}

	/**
	 * To get all the jobs specified by the given parameters
	 */
	protected function getJobsByTypeStatusInput($type, $status, $input)
	{
		$jobs = $this->jobsqueuelib->getJobsByTypeStatusInput($type, $status, $input);

		// If an error occurred then log it in database
		if (isError($jobs)) $this->logError(getError($jobs), array($type, $status, $input));

		return $jobs;
	}

	/**
	 * Add new jobs in the jobs queue with the given type
	 * jobs is an array of job objects
	 */
	protected function addNewJobsToQueue($type, $jobs)
	{
		$result = $this->jobsqueuelib->addNewJobsToQueue($type, $jobs);

		// If an error occurred then log it in database
		if (isError($result)) $this->logError(getError($result), $type);

		return $result;
	}

	/**
	 * Updates jobs already present in the jobs queue
	 * jobs is an array of job objects
	 */
	protected function updateJobsQueue($type, $jobs)
	{
		$result = $this->jobsqueuelib->updateJobsQueue($type, $jobs);

		// If an error occurred then log it in database
		if (isError($result)) $this->logError(getError($result), $type);

		return $result;
	}

	/**
	 * Utility method to update the specified properties of the given jobs with the given values
	 */
	protected function updateJobs($jobs, $properties, $values)
	{
		// If not valid arrays of properties and values arrays are not of the same size then exit
		if (isEmptyArray($jobs) || isEmptyArray($properties) || isEmptyArray($values)) return;
		if (count($properties) != count($values)) return;
	
		// For each job
		foreach ($jobs as $job)
		{
			// For each propery of the job
			for ($pI = 0; $pI < count($properties); $pI++)
			{
				// If this property is present in the job object
				if (property_exists($job, $properties[$pI]))
				{
					$job->{$properties[$pI]} = $values[$pI]; // set a new value
				}
			}
		}
	}

	/**
	 * Utility method to generate a job with the given parameters and return it inside an array
	 * ready to be used by addNewJobsToQueue and updateJobsQueue
	 */
	protected function generateJobs($status, $input)
	{
		$job = new stdClass();

		$job->{JobsQueueLib::PROPERTY_STATUS} = $status;
		$job->{JobsQueueLib::PROPERTY_INPUT} = $input;
		
		return array($job);
	}
}

