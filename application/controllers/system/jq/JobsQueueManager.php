<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller acts as REST JSON interface between the JobsQueueLib, that contains all the needed functionalities to
 * operate with the Jobs Queue System, and other tools that cannot access directly to such library
 */
class JobsQueueManager extends Auth_Controller
{
	// Config entry name for White list of permissions...
	const JOB_TYPE_PERMISSIONS_WHITE_LIST = 'job_type_permissions_white_list';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'getLastJobs' => 'admin:r',
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

		// Loads permission lib
		$this->load->library('PermissionLib');

		// Checks if the caller has the permissions to add new jobs with the given type in the queue
		if (!$this->permissionlib->isEntitled($this->config->item(self::JOB_TYPE_PERMISSIONS_WHITE_LIST), $type))
		{
			// Permissions NOT valid
			$this->outputJsonError('You are not allowed to access to this content');
		}
		else // Otherwise call JobsQueueLib library
		{
			$this->outputJson($this->jobsqueuelib->addNewJobsToQueue($type, $jobs));
		}
	}
}
