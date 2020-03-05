<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller acts as REST JSON interface between the JobsQueueLib, that contains all the needed functionalities to
 * operate with the Jobs Queue System, and other tools that cannot access directly to such library
 */
class JobsQueueManager extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'getJobsByType' => 'admin:r',
				'addNewJobsToQueue' => 'admin:rw'
			)
		);

		// Loads JobsQueueLib
		$this->load->library('JobsQueueLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * To get all the most recently added jobs using the given job type
	 */
	public function getLastJobs()
	{
		$type = $this->input->get(JobsQueueLib::PARAM_JOB_TYPE);

		$this->outputJson($this->jobsqueuelib->getLastJobs($type));
	}

	/**
	 * Add new jobs in the jobs queue with the given type
	 * jobs is an array of job objects
	 */
	public function addNewJobsToQueue()
	{
		$type = $this->input->post(JobsQueueLib::PARAM_JOB_TYPE);
		$jobs = $this->input->post(JobsQueueLib::PARAM_JOBS);

		$this->outputJson($this->jobsqueuelib->addNewJobsToQueue($type, $jobs));
	}
}
