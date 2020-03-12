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
	// Parameter names
	const PARAM_JOBS = 'jobs';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'getLastJobs' => 'admin:r',
				'addNewJobsToQueue' => 'admin:rw',
				'updateJobsQueue' => 'admin:rw'
			)
		);

		// Loading config file jqm
		$this->config->load('jqm');

		// Loads JobsQueueLib
		$this->load->library('JobsQueueLib');
		// Loads permission lib
		$this->load->library('PermissionLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * To get all the most recently added jobs using the given job type
	 */
	public function getLastJobs()
	{
		$type = $this->input->get(JobsQueueLib::PROPERTY_TYPE);

		$this->_checkPermissions($type);

		$this->outputJson($this->jobsqueuelib->getLastJobs($type));
	}

	/**
	 * Add new jobs in the jobs queue with the given type
	 * jobs is an array of job objects
	 */
	public function addNewJobsToQueue()
	{
		$type = $this->input->post(JobsQueueLib::PROPERTY_TYPE);
		$jobs = $this->input->post(self::PARAM_JOBS);

		$this->_checkPermissions($type);

		// Otherwise convert jobs from json to php and call JobsQueueLib library
		$this->outputJson($this->jobsqueuelib->addNewJobsToQueue($type, $this->_convertJobs($jobs)));
	}

	/**
	 * Add new jobs in the jobs queue with the given type
	 * jobs is an array of job objects
	 */
	public function updateJobsQueue()
	{
		$type = $this->input->post(JobsQueueLib::PROPERTY_TYPE);
		$jobs = $this->input->post(self::PARAM_JOBS);

		$this->_checkPermissions($type);

		// Otherwise convert jobs from json to php and call JobsQueueLib library
		$this->outputJson($this->jobsqueuelib->updateJobsQueue($type, $this->_convertJobs($jobs)));
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 *
	 */
	private function _checkPermissions($type)
	{
		// Checks if the caller has the permissions to add new jobs with the given type in the queue
		if (!$this->permissionlib->isEntitled($this->config->item(self::JOB_TYPE_PERMISSIONS_WHITE_LIST), $type))
		{
			// Permissions NOT valid
			$this->terminateWithJsonError('You are not allowed to access to this content');
		}
	}

	/**
	 *
	 */
	private function _convertJobs($jobs)
	{
		if (isEmptyArray($jobs)) return null; // if not a valid array then return null

		$convertedJobsArray = array(); // returned values

		// Loops through all the provided jobs
		foreach ($jobs as $job)
		{
			$tmpObj = json_decode($job); // Try to decode json to php

			// If decode was a success
			if ($tmpObj != null)
			{
				$convertedJobsArray[] = $tmpObj; // then store the decoded object in the result array
			}
			else // otherwise
			{
				// Create a new object and store the error message in it
				$tmpObj = new stdClass();
				$tmpObj->{JobsQueueLib::PROPERTY_ERROR} = 'A not valid json was provided';

				$convertedJobsArray[] = $tmpObj; // store this object into the result array
			}
		}

		return $convertedJobsArray;
	}
}
