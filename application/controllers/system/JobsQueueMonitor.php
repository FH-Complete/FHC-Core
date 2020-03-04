<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jobs Queue Manager
 */
class JobsQueueMonitor extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'getJobsByType' => 'monitoring:r',
				'getJobsByStatus' => 'monitoring:r',
				'getJobsByCreationTime' => 'monitoring:r'
			)
		);

		// Loads JobsQueueLib
		$this->load->library('JobsQueueLib');
	}

	/**
	 *
	 */
	public function getJobsByType()
	{
		$jobType = $this->input->get(JobsQueueLib::PARAM_JOB_TYPE);

		$this->outputJson($this->jobsqueuelib->getJobsByType($jobType));
	}

	/**
	 *
	 */
	public function getJobsByStatus()
	{
		$jobStatus = $this->input->get(JobsQueueLib::PARAM_JOB_STATUS);

		$this->outputJson($this->jobsqueuelib->getJobsByStatus($jobStatus));
	}

	/**
	 *
	 */
	public function getJobsByCreationTime()
	{
		$jobCreationTime = $this->input->get(JobsQueueLib::PARAM_JOB_CREATION_TIME);

		$this->outputJson($this->jobsqueuelib->getJobsByCreationTime($jobCreationTime));
	}
}
