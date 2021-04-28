<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library that contains all the needed functionalities to operate with the Jobs Queue System
 */
class JobsQueueLib
{
	// Job statuses
	const STATUS_NEW = 'new';
	const STATUS_RUNNING = 'running';
	const STATUS_DONE = 'done';
	const STATUS_FAILED = 'failed';

	// Job object properties
	const PROPERTY_JOBID = 'jobid';
	const PROPERTY_CREATIONTIME = 'creationtime';
	const PROPERTY_TYPE = 'type';
	const PROPERTY_STATUS = 'status';
	const PROPERTY_INPUT = 'input';
	const PROPERTY_OUTPUT = 'output';
	const PROPERTY_START_TIME = 'starttime';
	const PROPERTY_END_TIME = 'endtime';
	const PROPERTY_ERROR = 'error';

	private $_ci; // CI instance

	/**
	 * Constructor
	 */
	public function __construct($authenticate = true)
	{
		// Gets CI instance
		$this->_ci =& get_instance();

		// Loads all needed models
		$this->_ci->load->model('system/JobsQueue_model', 'JobsQueueModel');
		$this->_ci->load->model('system/JobTypes_model', 'JobTypesModel');
		$this->_ci->load->model('system/JobStatuses_model', 'JobStatusesModel');
		$this->_ci->load->model('system/JobTriggers_model', 'JobTriggersModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * To get all the most recently added jobs using the given job type
	 */
	public function getLastJobs($type)
	{
		$this->_ci->JobsQueueModel->resetQuery();

		$this->_ci->JobsQueueModel->addOrder('creationtime', 'DESC');

		return $this->_ci->JobsQueueModel->loadWhere(array('status' => self::STATUS_NEW, 'type' => $type));
	}

	/**
	 * To get the oldest added jobs using the given job type
	 */
	public function getOldestJob($type)
	{
		$this->_ci->JobsQueueModel->resetQuery();

		$this->_ci->JobsQueueModel->addOrder('creationtime', 'ASC');
		$this->_ci->JobsQueueModel->addLimit('1');

		return $this->_ci->JobsQueueModel->loadWhere(array('status' => self::STATUS_NEW, 'type' => $type));
	}

	/**
	 * To get all the jobs specified by the given parameters
	 */
	public function getJobsByTypeStatusInput($type, $status, $input)
	{
		$this->_ci->JobsQueueModel->resetQuery();

		$this->_ci->JobsQueueModel->addOrder('creationtime', 'DESC');

		return $this->_ci->JobsQueueModel->loadWhere(array('status' => $status, 'type' => $type, 'input' => $input));
	}

	/**
	 * Add new jobs in the jobs queue with the given type
	 * jobs is an array of job objects
	 */
	public function addNewJobsToQueue($type, $jobs)
	{
		// Checks parameters
		if (isEmptyString($type)) return error('The provided type parameter is not a valid string');
		if (isEmptyArray($jobs)) return error('The provided jobs parameter is not a valid array');

		// Get all the job types
		$dbResult = $this->_ci->JobTypesModel->load();
		if (isError($dbResult)) return $dbResult;
		$types = getData($dbResult);

		// If the given type is not present in database
		if (!$this->_checkJobType($type, $types)) return error('The provided type parameter is not valid');

		$results = $jobs; // returned values
		$errorOccurred = false; // very optimistic

		// Get all the job statuses
		$dbResult = $this->_ci->JobStatusesModel->load();
		if (isError($dbResult)) return $dbResult;
		$statuses = getData($dbResult);

		// Loops through all the provided jobs
		foreach ($results as $job)
		{
			// If the structure of the job object is valid AND the type is valid AND the status is valid
			if ($this->_checkNewJobStructure($job) && $this->_checkJobStatus($job, $statuses))
			{
				$this->_dropNotAllowedPropertiesNewJob($job); // remove the black listed properties from this object

				$job->{self::PROPERTY_TYPE} = $type; // What you asked is what you get!

				// Try to insert the single job into database
				$dbNewJobResult = $this->_ci->JobsQueueModel->insert($job);

				// If an error occurred during while inserting in database
				if (isError($dbNewJobResult))
				{
					$job->{self::PROPERTY_ERROR} = getError($dbNewJobResult); // retrieve the cause and store it in job object
					$errorOccurred = true; // set error occurred flag
				}
				else // otherwise
				{
					$job->{self::PROPERTY_JOBID} = getData($dbNewJobResult); // get the jobid and store it in job object

					$dbNewTriggeredJobResult = $this->_addNewTriggeredJobToQueue($type, $job, array(self::STATUS_NEW));
					// If an error occurred during while inserting in database
					if (isError($dbNewTriggeredJobResult)) return $dbNewTriggeredJobResult;
				}
			}
			else // otherwise
			{
				$errorOccurred = true; // set error occurred flag
			}
		}

		// If an error occurred then returns the results in an error object
		if ($errorOccurred) return error($results);

		return success($results); // otherwise return results in a success object
	}

	/**
	 * Updates jobs already present in the jobs queue
	 * jobs is an array of job objects
	 */
	public function updateJobsQueue($type, $jobs)
	{
		// Checks parameters
		if (isEmptyArray($jobs)) return error('The provided jobs parameter is not a valid array');

		$results = $jobs; // returned values
		$errorOccurred = false; // very optimistic

		// Get all the job statuses
		$dbResultStatuses = $this->_ci->JobStatusesModel->load();
		if (isError($dbResultStatuses)) return $dbResultStatuses;
		$statuses = getData($dbResultStatuses);

		// Loops through all the provided jobs
		foreach ($results as $job)
		{
			// Check if the required job is present in the database
			$dbResultJobs = $this->_ci->JobsQueueModel->load($job->{self::PROPERTY_JOBID});
			if (isError($dbResultJobs))
			{
				$job->{self::PROPERTY_ERROR} = getError($dbResultJobs); // retrieve the cause and store it in job object
				$errorOccurred = true; // set error occurred flag
			}
			elseif (!hasData($dbResultJobs)) // if no jobs were found
			{
				$job->{self::PROPERTY_ERROR} = 'The required job is not present';
				$errorOccurred = true; // set error occurred flag
			}
			else // if a job was found then it could be updated
			{
				// If the structure of the job object is valid
				if ($this->_checkUpdateJobStructure($job) && $this->_checkJobStatus($job, $statuses))
				{
					$this->_dropNotAllowedPropertiesUpdateJob($job); // remove the black listed properties from this object

					$job->{self::PROPERTY_TYPE} = $type; // What you asked is what you get!

					// Try to update the single job into database
					$dbResult = $this->_ci->JobsQueueModel->update($job->{self::PROPERTY_JOBID}, (array)$job);

					// If an error occurred during while updating in database
					if (isError($dbResult))
					{
						$job->{self::PROPERTY_ERROR} = getError($dbResult); // retrieve the cause and store it in job object
						$errorOccurred = true; // set error occurred flag
					}
					else // otherwise
					{
						$dbNewTriggeredJobResult = $this->_addNewTriggeredJobToQueue(
							$type,
							$job,
							array($job->status)
						);
						// If an error occurred during while inserting in database
						if (isError($dbNewTriggeredJobResult)) return $dbNewTriggeredJobResult;
					}
				}
				else // otherwise
				{
					$errorOccurred = true; // set error occurred flag
				}
			}
		}

		// If an error occurred then returns the results in an error object
		if ($errorOccurred) return error($results);

		return success($results); // otherwise return results in a success object
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks the job object structure when needed for insert
	 */
	private function _checkNewJobStructure(&$job)
	{
		// If job is a valid object and contains the required properties AND does NOT already contain the property error
		if (is_object($job)
			&& property_exists($job, self::PROPERTY_STATUS)
			&& !property_exists($job, self::PROPERTY_ERROR))
		{
			return true; // it is valid!
		}

		// If not object then object it!
		if (!is_object($job)) $job = new stdClass();

		// If an error property was not already previously stored then store an error message in job object
		if (!property_exists($job, self::PROPERTY_ERROR))
		{
			$job->{self::PROPERTY_ERROR} = 'The structure of the provided job is not valid';
		}

		return false; // better sorry than wrong
	}

	/**
	 * Checks the job object structure when needed for update
	 */
	private function _checkUpdateJobStructure(&$job)
	{
		// If job is a valid object
		if (is_object($job) && property_exists($job, self::PROPERTY_JOBID)) return true; // it is valid!

		// If not object then object it!
		if (!is_object($job)) $job = new stdClass();

		// If an error property was not already previously stored then store an error message in job object
		if (!property_exists($job, self::PROPERTY_ERROR))
		{
			$job->{self::PROPERTY_ERROR} = 'The structure of the provided job is not valid';
		}

		return false; // better sorry than wrong
	}

	/**
	 * Checks if the given job contains a valid type
	 */
	private function _checkJobType($type, $types)
	{
		return $this->_inArray($type, $types, self::PROPERTY_TYPE);
	}

	/**
	 * Checks if the given job contains a valid status
	 */
	private function _checkJobStatus(&$job, $statuses)
	{
		// If the given job doesn't have the property status then it is not valid
		if (!isset($job->{self::PROPERTY_STATUS}))
		{
			$found = false;
		}
		else // otherwise test if it valid
		{
			$found = $this->_inArray($job->{self::PROPERTY_STATUS}, $statuses, self::PROPERTY_STATUS);
		}

		// No status was found and does NOT already contain the property error
		if (!$found && !property_exists($job, self::PROPERTY_ERROR))
		{
			$job->{self::PROPERTY_ERROR} = 'The provided status of this job is not valid'; // store the error message in the object
		}

		return $found;
	}

	/**
	 * Search in an array the given value
	 * The elements of the given array are objects
	 * The given value is compared with the property specified by the $propertyName parameter of each object of the given array
	 */
	private function _inArray($value, $array, $propertyName)
	{
		$found = false;

		foreach ($array as $element)
		{
			if ($value == $element->{$propertyName})
			{
				$found = true;
				break;
			}
		}

		return $found;
	}

	/**
	 * Drop not allowed properties from the given job
	 */
	private function _dropNotAllowedPropertiesNewJob(&$job)
	{
		unset($job->{self::PROPERTY_JOBID});
		unset($job->{self::PROPERTY_CREATIONTIME});
		unset($job->{self::PROPERTY_TYPE});
	}

	/**
	 * Drop not allowed properties from the given job
	 */
	private function _dropNotAllowedPropertiesUpdateJob(&$job)
	{
		unset($job->{self::PROPERTY_CREATIONTIME});
		unset($job->{self::PROPERTY_TYPE});
	}

	/**
	 * Add e new triggered job to the jobs queue
	 * NOTE:
	 * - In this method there are less checks compared to addNewJobsToQueue method because
	 * the new jobs that will be added are generate in this method
	 * - Job ids in this case are not returned, therefore the caller is not going to be informed about these new jobs
	 */
	private function _addNewTriggeredJobToQueue($type, $job, $triggeredStatuses)
	{
		// Get all the job trigggers for the given type and for the given statuses
		$dbTriggersResult = $this->_ci->JobTriggersModel->getJobtriggersByTypeStatuses($type, $triggeredStatuses);

		// If an error occurred while getting job triggers from database then return it
		if (isError($dbTriggersResult)) return $dbTriggersResult;
		if (hasData($dbTriggersResult)) // If triggers were retrieved
		{
			// The output of the trigging job is the input of the trigged job
			$triggeredJobInput = null;
			if (isset($job->{self::PROPERTY_OUTPUT})) $triggeredJobInput = $job->{self::PROPERTY_OUTPUT};

			// For each trigger
			foreach (getData($dbTriggersResult) as $trigger)
			{
				$triggeredJob = array(
					self::PROPERTY_TYPE => $trigger->following_type, // the new type is the one defined in tbl_jobtriggers
					self::PROPERTY_STATUS => self::STATUS_NEW, // new job status is new
					self::PROPERTY_INPUT => $triggeredJobInput // new job input
				);

				// Try to insert the single job into database
				$dbNewJob = $this->_ci->JobsQueueModel->insert($triggeredJob);
				// If an error occurred during while inserting in database
				if (isError($dbNewJob)) return $dbNewJob;
			}
		}

		return success(); // if here then it was a success!
	}
}

