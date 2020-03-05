<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library that contains all the needed functionalities to operate with the Jobs Queue System
 */
class JobsQueueLib
{
	// Job types
	// SAP
	const JOB_TYPE_SAP_STAMMDATEN_UPDATE = 'SAPStammdatenUpdate';
	const JOB_TYPE_SAP_PAYMENT = 'SAPPayment';
	// DVUH
	const JOB_TYPE_OEH_PAYMENT = 'OEHPayment';

	// Job statuses
	const STATUS_NEW = 'new';
	const STATUS_RUNNING = 'running';
	const STATUS_DONE = 'done';
	const STATUS_FAILED = 'failed';

	// Parameter names
	const PARAM_JOB_TYPE = 'type';
	const PARAM_JOB_STATUS = 'status';
	const PARAM_JOBS = 'jobs';

	private $_ci; // CI instance

	/**
	 * Constructor
	 */
	public function __construct($authenticate = true)
	{
		// Gets CI instance
		$this->_ci =& get_instance();
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 *
	 */
	public function getJobsByType()
	{
	}

	/**
	 *
	 */
	public function addNewJobsToQueue()
	{
	}

	/**
	 *
	 */
	public function getJobsByStatus()
	{
	}
}
