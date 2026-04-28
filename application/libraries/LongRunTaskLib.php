<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library that contains all the needed functionalities to operate with the Long Run Tasks
 */
class LongRunTaskLib extends JobsQueueLib
{
	const CFG_LRT_MAX_NUMBER_SYSTEM = 'lrt_max_number_system';
	const CFG_LRT_TYPES = 'lrt_types';

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
	// Public methods

	/**
	 * Get the oldest added LRTs to the queue having status = new
	 * The maximum number of returned queued LRTs is limited by:
	 * number of currently running LRTs - maximum allowed number of LRTs for the system
	 */
	public function getLRTs()
	{
		// Get all the running LRTs
		$runningLrtsResult = $this->getJobsByTypeStatus($this->_ci->config->item(self::CFG_LRT_TYPES)), JobsQueueLib::STATUS_RUNNING);

		if (isError($runningLrtsResult)) return $runningLrtsResult;

		// The number of the currently running LRTs - the maximum LRTs for the system
		$max_number_of_lrts = $this->_ci->config->item(self::CFG_LRT_MAX_NUMBER_SYSTEM)) - count(getData($runningLrtsResult));

		// Get the oldest LRTs added to the queue
		return $this->getOldestJobs($this->_ci->config->item(self::CFG_LRT_TYPES)), $max_number_of_lrts);
	}

	/**
	 *
	 */
	public function addNewLRTsToQueue($type, $lrts)
	{
		return $this->addNewJobsToQueue($type, $lrts);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public static methods

	/**
	 * Utility method to generate a LTR with the given parameters and return it inside an array
	 * ready to be used by addNewLRTsToQueue
	 */
	public static function generateLtrs($status, $input)
	{
		return self::generateJobs($status, $input);
	}
}

