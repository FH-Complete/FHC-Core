<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class JobsQueueLib
{
	//
	const STATUS_RUNNING = 'running';
	const STATUS_NEW = 'new';
	const STATUS_DONE = 'done';

	//
	const PARAM_JOB_TYPE = 'jobType';
	const PARAM_JOB_STATUS = 'jobStatus';
	const PARAM_JOB_CREATION_TIME = 'jobCreatinTime';

	//
	const JOB_TYPE_SAP_STAMMDATEN_UPDATE = 'SAPStammdatenUpdate';
	const JOB_TYPE_SAP_PAYMENT = 'SAPPayment';
	const JOB_TYPE_OEH_PAYMENT = 'OEHPayment';

	private $_ci; // CI instance

	/**
	 * Construct
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
