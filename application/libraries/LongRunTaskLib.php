<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once 'JobsQueueLib.php';

/**
 * Library that contains all the needed functionalities to operate with the Long Run Tasks
 */
class LongRunTaskLib extends JobsQueueLib
{
	// Config names
	const CFG_LRT_MAX_NUMBER_SYSTEM = 'lrt_max_number_system';
	const CFG_LRT_TYPES = 'lrt_types';
	const CFG_LRT_MAX_RUN = 'lrt_max_run_timeout';
	const CFG_LRT_MAX_NUMBER_USER = 'lrt_max_number_single_user';

	// LRT object properties
	const PROPERTY_UID = 'uid';
	const PROPERTY_PID = 'pid';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads the Long Run Tasks configs
		$this->_ci->config->load('lrt');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods used by the LongRunTaskExecJob

	/**
	 * Get the oldest added LRTs to the queue having status = new
	 * The maximum number of returned queued LRTs is limited by:
	 * number of currently running LRTs - maximum allowed number of LRTs for the system
	 */
	public function getNewLRTs()
	{
		// Get all the running LRTs
		$runningLrtsResult = $this->getJobsByTypeStatus($this->_ci->config->item(self::CFG_LRT_TYPES), JobsQueueLib::STATUS_RUNNING);

		if (isError($runningLrtsResult)) return $runningLrtsResult;

		// The number of the currently running LRTs - the maximum LRTs for the system
		$max_number_of_lrts = $this->_ci->config->item(self::CFG_LRT_MAX_NUMBER_SYSTEM) - count(getData($runningLrtsResult));

		// Get the oldest LRTs added to the queue
		return $this->getOldestJobs($this->_ci->config->item(self::CFG_LRT_TYPES), $max_number_of_lrts);
	}

	/**
	 * Get all the LRT that are running more then CFG_LRT_MAX_RUN hours
	 */
	public function getHangingLRTs()
	{
		// Return the result of the query
		return $this->_ci->JobsQueueModel->execReadOnlyQuery('
			SELECT jq.*
		          FROM system.tbl_jobsqueue jq
			 WHERE jq.type IN ?
			   AND jq.status = ?
			   AND jq.starttime < NOW() - INTERVAL \''.$this->_ci->config->item(self::CFG_LRT_MAX_RUN).' hours\'
		      ORDER BY jq.creationtime DESC',
			array(
				$this->_ci->config->item(self::CFG_LRT_TYPES),
				JobsQueueLib::STATUS_RUNNING
			)
		);
	}

	/**
	 * Get all the LRT that are currently running
	 */
	public function getRunningLRTs()
	{
		// Return the result of the query
		return $this->_ci->JobsQueueModel->execReadOnlyQuery('
			SELECT jq.*
		          FROM system.tbl_jobsqueue jq
			 WHERE jq.type IN ?
			   AND jq.status = ?
		      ORDER BY jq.creationtime DESC',
			array(
				$this->_ci->config->item(self::CFG_LRT_TYPES),
				JobsQueueLib::STATUS_RUNNING
			)
		);
	}

	/**
	 * Execute a LRT in background
	 * - Checks if the wanted LRT exists in the applcation/controllers/lrts directory
	 * - Then executes it in background via CI CLI
	 * - Stores the LRT pid into the database
	 */
	public function executeLrt($lrt)
	{
		$output = array();

		// If does _not_ exist a LRT implementation for this LRT type, then return an error
		if (!file_exists(APPPATH.'controllers/lrts/'.$lrt->{self::PROPERTY_TYPE}.'.php'))
		{
			return error('The required LRT implementation has not been found');
		}

		// Execute the LRT implementation (a CI controller from CLI) providing as parameter the jobid
		exec(
			// Command
			sprintf(
				'/usr/bin/php %s/../index.ci.php lrts/%s/run %s > /dev/null 2>&1 & echo $!',
				APPPATH,
				$lrt->{self::PROPERTY_TYPE},
				$lrt->{self::PROPERTY_JOBID}
			),
			$output // Here goes the output from the standard output and error
		);

		// If a pid has not been returned
		if (isEmptyArray($output) || !is_numeric($output[0])) return error('Not a valid pid has been returned');

		// Set the pid, status and starttime of this LRT into the database
		$updateLrtResult = $this->_ci->JobsQueueModel->update(
			$lrt->{self::PROPERTY_JOBID},
			array(
				'pid' => $output[0],
				'starttime' => 'NOW()',
				'status' => self::STATUS_RUNNING
			)
		);
		if (isError($updateLrtResult)) return $updateLrtResult;
	}

	/**
	 * Kill the provided LRT
	 * To avoid to kill a process that is not this LRT,
	 * since the same PID can be assigned to another process once this ended
	 */
	public function killLrt($lrt)
	{
		// Try to get the pid of this LRT from the system
		$pid = exec(
			sprintf(
				'ps -eo pid,cmd | grep "index.ci.php lrts/%s/run %s" | grep -v grep | awk \'{print $1}\'',
				$lrt->{self::PROPERTY_TYPE},
				$lrt->{self::PROPERTY_JOBID}
			)
		);

		// If the pid is the same then kill the process with a SIGKILL
		if ($pid == $lrt->{self::PROPERTY_PID}) exec('kill -9 '.$lrt->{self::PROPERTY_PID}.' > /dev/null 2>&1');

		// Set the LRT as failed in any case
		$lrtExecFailedResult = $this->_ci->JobsQueueModel->update(
			$lrt->{self::PROPERTY_JOBID},
			array(
				'endtime' => 'NOW()',
				'status' => self::STATUS_FAILED
			)
		);
		// If an error occurred then return it
		if (isError($lrtExecFailedResult)) return $lrtExecFailedResult;
	}

	/**
	 *
	 */
	public function checkExecution($lrt)
	{
		// If the LRT stopped running
		if (!$this->_isRunning($lrt))
		{
			// Loads MessageLib library
			$this->_ci->load->library('MessageLib');
			// Load the BenutzerModel
			$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');

			// Get the benutzer for this uid
			$benutzerResult = $this->_ci->BenutzerModel->loadWhere(array('uid' => $lrt->{LongRunTaskLib::PROPERTY_UID}));
			// If an error occurred then return it
			if (isError($benutzerResult)) return $benutzerResult;
			// If no benutzer has been found
			if (!hasData($benutzerResult)) return error('No benutzer found, uid: '.$lrt->{LongRunTaskLib::PROPERTY_UID});

			$benutzer = getData($benutzerResult)[0];

			// Sends a message to the user
			$messageResult = $this->_ci->messagelib->sendMessageUser(
			        $benutzer->person_id,
			        'Long run task ended',
			        'The long run task '.$lrt->{self::PROPERTY_TYPE}.' ended, output: '.$lrt->{self::PROPERTY_OUTPUT}
			);
			// If an error occurred then return it
			if (isError($messageResult)) return $messageResult;

			// Set the LRT as done
			$lrtExecOverResult = $this->_ci->JobsQueueModel->update(
				$lrt->{self::PROPERTY_JOBID},
				array(
					'endtime' => 'NOW()',
					'status' => self::STATUS_DONE
				)
			);
			// If an error occurred then return it
			if (isError($lrtExecOverResult)) return $lrtExecOverResult;
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods used by the front end applications (standard controllers/end points, ex. controllers/system/LRTTest.php)

	/**
	 * Add a single LRT to the queue
	 */
	public function addNewLrtToQueue($type, $uid, $lrtInput)
	{
		// Checks parameters
		if (isEmptyString($type)) return error('The provided type parameter is not a valid string');
		if (isEmptyString($uid)) return error('The provided uid parameter is not a valid string');

		// Get all the running task for this uid
		// Return the result of the query
                $runningLRTbyUIDResult = $this->_ci->JobsQueueModel->execReadOnlyQuery('
			SELECT jq.*
			  FROM system.tbl_jobsqueue jq
			 WHERE jq.status IN ?
			   AND jq.uid = ?
			',
			array(
				array(
					self::STATUS_NEW,
					self::STATUS_RUNNING
				),
				$uid
			)
                );

		// If an error occurred then return it
		if (isError($runningLRTbyUIDResult)) return $runningLRTbyUIDResult;

		// If the running tasks for this user are too many
		if (count(getData($runningLRTbyUIDResult)) >= $this->_ci->config->item(self::CFG_LRT_MAX_NUMBER_USER))
		{
			return error('Too many running tasks for this user');
		}

		// Convert input to JSON and check it
		$jsonLrtInput = json_encode($lrtInput);
		if ($jsonLrtInput == null) return error('The provided LRT input is not valid');

		// Get all the job types
		$dbResult = $this->_ci->JobTypesModel->load();
		if (isError($dbResult)) return $dbResult;
		$types = getData($dbResult);

		// If the given type is not present in database
		if (!$this->_checkJobType($type, $types)) return error('The provided type parameter is not valid');

		// Get all the job statuses
		$dbResult = $this->_ci->JobStatusesModel->load();
		if (isError($dbResult)) return $dbResult;
		$statuses = getData($dbResult);

		// Create an object that represent the new tbl_jobsqueue record with the provided input
		$lrt = $this->generateJobs(self::STATUS_NEW, $jsonLrtInput)[0];

		// What you asked is what you get!
		$lrt->{self::PROPERTY_TYPE} = $type;
		$lrt->{self::PROPERTY_UID} = $uid;

		// Try to insert the single lrt into database
		$dbNewLrtResult = $this->_ci->JobsQueueModel->insert($lrt);

		// If an error occurred during while inserting in database
		if (isError($dbNewLrtResult)) return $dbNewLrtResult;

		return success('LRT added to the queue');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods used by the LRT implementation (controllers/ltrs/*, ex. controllers/ltrs/LRTDummy)

	/**
	 * Return a single record from the 
	 */
	public function getLrt($jobid)
	{
		$this->_ci->JobsQueueModel->resetQuery();

		return $this->_ci->JobsQueueModel->loadWhere(array('jobid' => $jobid));
	}

	/**
	 * 
	 */
	public function setProgress($jobid, $progress)
	{
		return $this->_ci->JobsQueueModel->update($jobid, array('progress' => $progress));
	}

	/**
	 * 
	 */
	public function setOutuput($jobid, $output)
	{
		return $this->_ci->JobsQueueModel->update($jobid, array('output' => json_encode($output)));
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Return true if the LRT is still running
	 */
	private function _isRunning($lrt)
	{
		// Try to get the pid of this LRT from the system
		$pid = exec(
			sprintf(
				'ps -eo pid,cmd | grep "index.ci.php lrts/%s/run %s" | grep -v grep | awk \'{print $1}\'',
				$lrt->{self::PROPERTY_TYPE},
				$lrt->{self::PROPERTY_JOBID}
			)
		);

		// If the pid is the same then the LRT is still running
		return $pid == $lrt->{self::PROPERTY_PID};
	}
}

